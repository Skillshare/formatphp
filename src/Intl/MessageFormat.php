<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Intl;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatMessageException;
use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\SelectElement;
use FormatPHP\Icu\MessageFormat\Printer;
use Locale as PhpLocale;
use MessageFormatter as PhpMessageFormatter;
use Ramsey\Collection\Exception\CollectionMismatchException;
use Throwable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;
use function assert;
use function is_callable;
use function is_int;
use function is_numeric;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * Formats an ICU message format pattern
 */
class MessageFormat implements MessageFormatInterface
{
    private const CALLBACK_REPLACEMENT = '__FORMATPHP_CALLBACK_REPLACEMENT__';
    private const CALLBACK_RESULT_PATTERN = '/(.*)' . self::CALLBACK_REPLACEMENT . '(.*)/su';
    private const LITERAL_TAG_PATTERN = '/^<(.*)\/>$/su';

    private LocaleInterface $locale;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?LocaleInterface $locale = null)
    {
        $this->locale = $locale ?? new Locale(PhpLocale::getDefault());
    }

    /**
     * @inheritdoc
     */
    public function format(string $pattern, array $values = []): string
    {
        try {
            $pattern = $this->applyPreprocessing($pattern, $values);
            $formatter = new PhpMessageFormatter((string) $this->locale->baseName(), $pattern);

            $formattedMessage = $formatter->format($values);

            if ($formattedMessage === false) {
                throw new UnableToFormatMessageException(
                    $formatter->getErrorMessage(),
                    $formatter->getErrorCode(),
                );
            }

            return $formattedMessage;
        } catch (Throwable $exception) {
            throw new UnableToFormatMessageException(
                sprintf(
                    'Unable to format message with pattern "%s" for locale "%s"',
                    $pattern,
                    (string) $this->locale->baseName(),
                ),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception,
            );
        }
    }

    /**
     * @throws Parser\Exception\IllegalParserUsageException
     * @throws Parser\Exception\InvalidArgumentException
     * @throws Parser\Exception\InvalidOffsetException
     * @throws Parser\Exception\InvalidSkeletonOption
     * @throws Parser\Exception\InvalidUtf8CodeBoundaryException
     * @throws Parser\Exception\InvalidUtf8CodePointException
     * @throws Parser\Exception\UnableToParseMessageException
     * @throws UnableToFormatMessageException
     * @throws CollectionMismatchException
     *
     * @psalm-param array<array-key, float | int | string | callable(string=):string> $values
     */
    private function applyPreprocessing(string $pattern, array &$values = []): string
    {
        /** @var array<array-key, callable(string=):string> $callbacks */
        $callbacks = array_filter($values, fn ($value): bool => !is_string($value) && is_callable($value));

        // Remove the callbacks from the values, since we will use them below.
        foreach (array_keys($callbacks) as $key) {
            unset($values[$key]);
        }

        /**
         * This is to satisfy static analysis. At this point, $values should
         * not contain any callables.
         *
         * @var array<array-key, float | int | string> $valuesWithoutCallables
         */
        $valuesWithoutCallables = &$values;

        $parserOptions = new Parser\Options();
        $parserOptions->shouldParseSkeletons = true;

        $parser = new Parser($pattern, $parserOptions);
        $parsed = $parser->parse();

        if ($parsed->err !== null) {
            throw new Parser\Exception\UnableToParseMessageException($parsed->err);
        }

        assert($parsed->val instanceof Parser\Type\ElementCollection);

        return (new Printer())->printAst($this->processAst($parsed->val, $callbacks, $valuesWithoutCallables));
    }

    /**
     * @param array<array-key, float | int | string> $values
     *
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
     *
     * @psalm-param array<array-key, callable(string=):string> $callbacks
     */
    private function processAst(
        Parser\Type\ElementCollection $ast,
        array $callbacks,
        array &$values
    ): Parser\Type\ElementCollection {
        $processedAst = new Parser\Type\ElementCollection();

        for ($i = 0; $i < $ast->count(); $i++) {
            $element = $ast[$i];
            assert($element instanceof Parser\Type\ElementInterface);
            $clone = clone $element;

            if ($clone instanceof PluralElement || $clone instanceof SelectElement) {
                foreach ($clone->options as $option) {
                    $option->value = $this->processAst($option->value, $callbacks, $values);
                }
            }

            if ($clone instanceof Parser\Type\TagElement) {
                $processedAst = $processedAst->merge($this->processTagElement($clone, $callbacks, $values));

                continue;
            }

            if ($clone instanceof Parser\Type\NumberElement) {
                $clone = $this->processNumberElement($clone, $values);
            }

            if ($clone instanceof Parser\Type\LiteralElement) {
                $clone = $this->processLiteralElement($clone, $callbacks);
            }

            $processedAst[] = $clone;
        }

        return $processedAst;
    }

    /**
     * @param array<array-key, float | int | string> $values
     *
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
     *
     * @psalm-param array<array-key, callable(string=):string> $callbacks
     */
    private function processTagElement(
        Parser\Type\TagElement $tagElement,
        array $callbacks,
        array &$values
    ): Parser\Type\ElementCollection {
        if (!array_key_exists($tagElement->value, $callbacks)) {
            // We don't have a callback for this tag.
            return new Parser\Type\ElementCollection([$tagElement]);
        }

        $result = ($callbacks[$tagElement->value])(self::CALLBACK_REPLACEMENT);
        if (preg_match(self::CALLBACK_RESULT_PATTERN, $result, $matches)) {
            $start = new Parser\Type\LiteralElement($matches[1], $tagElement->location);
            $middle = $this->processAst($tagElement->children, $callbacks, $values);
            $end = new Parser\Type\LiteralElement($matches[2], $tagElement->location);

            return new Parser\Type\ElementCollection([$start, ...array_values($middle->toArray()), $end]);
        }

        return new Parser\Type\ElementCollection([new Parser\Type\LiteralElement($result, $tagElement->location)]);
    }

    /**
     * Performs special processing for number elements
     *
     * If the parameter is a percent-style number, then we multiply the value
     * by 100. This is in keeping with the ECMA-402 draft, which specifies the
     * `Intl.NumberFormat` rules. When using `Intl.NumberFormat` to format
     * percentages, the number must first be multiplied by 100 before any
     * formatting occurs. See section 15.1.6 of ECMA-402, specifically step 5.b.
     *
     * ECMA-402, however, doesn't define an API for MessageFormat, so FormatJS
     * implements this on their own, using `Intl.NumberFormat` to process any
     * number parameters it encounters. As a result, all number parameters in
     * ICU message syntax that specify the `::percent` stem (i.e.,
     * "{0, number, ::percent}") have their values first multiplied by 100
     * before formatting them.
     *
     * This may not be considered a bug in FormatJS, since it is adhering to the
     * ECMA-402 specification. However, it does not follow the rules for
     * percentages as programmed in icu4c (the underlying library PHP uses), so
     * in order to match the expected output of FormatJS, we multiply percent
     * values by 100 before formatting them.
     *
     * Oddly enough, PHP's `NumberFormatter` has the same rules, and it uses
     * the underlying ICU implementation of the number formatter:
     *
     *     $nf = new NumberFormatter('en-US', NumberFormatter::PERCENT);
     *     echo $nf->format(25); // Produces "2,500%"
     *
     * While:
     *
     *     $mf = new MessageFormatter('en-US', '{0, number, ::percent}');
     *     echo $mf->format([25]); // Produces "25%"
     *
     * So, one could argue this is a bug in the ICU implementation of the
     * percent number skeleton.
     *
     * @link https://tc39.es/ecma402/#sec-partitionnumberpattern
     * @link https://formatjs.io/docs/core-concepts/icu-syntax/#number-type
     *
     * @param array<array-key, float | int | string> $values
     */
    private function processNumberElement(
        Parser\Type\NumberElement $numberElement,
        array &$values
    ): Parser\Type\NumberElement {
        if (!$numberElement->style instanceof Parser\Type\NumberSkeleton) {
            return $numberElement;
        }

        if ($numberElement->style->parsedOptions->style === NumberFormatOptions::STYLE_PERCENT) {
            $key = $numberElement->value;
            if (is_numeric($values[$key])) {
                $values[$key] *= 100;
            }
        }

        return $numberElement;
    }

    /**
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
     *
     * @psalm-param array<array-key, callable(string=):string> $callbacks
     */
    private function processLiteralElement(
        Parser\Type\LiteralElement $literalElement,
        array $callbacks
    ): Parser\Type\LiteralElement {
        if (!preg_match(self::LITERAL_TAG_PATTERN, $literalElement->value, $matches)) {
            // This isn't a literal tag, so there's nothing to process.
            return $literalElement;
        }

        if (!array_key_exists($matches[1], $callbacks)) {
            // We don't have a callback for this tag.
            return $literalElement;
        }

        $result = ($callbacks[$matches[1]])('');
        $literalElement->value = $result;

        return $literalElement;
    }
}
