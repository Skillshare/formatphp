<?php

/**
 * This file is part of skillshare/formatphp
 *
 * skillshare/formatphp is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/MIT MIT License
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
            $pattern = $this->applyCallbacks($pattern, $values);
            $formatter = new PhpMessageFormatter((string) $this->locale->baseName(), $pattern);

            return (string) $formatter->format($values);
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
     * @param array<array-key, float | int | string | callable(string):string> $values
     *
     * @throws Parser\Exception\IllegalParserUsageException
     * @throws Parser\Exception\InvalidArgumentException
     * @throws Parser\Exception\InvalidOffsetException
     * @throws Parser\Exception\InvalidSkeletonOption
     * @throws Parser\Exception\InvalidUtf8CodeBoundaryException
     * @throws Parser\Exception\InvalidUtf8CodePointException
     * @throws Parser\Exception\UnableToParseMessageException
     * @throws UnableToFormatMessageException
     * @throws CollectionMismatchException
     */
    private function applyCallbacks(string $pattern, array &$values = []): string
    {
        $callbacks = array_filter($values, fn ($value): bool => is_callable($value));

        // If $values doesn't contain any callables, go ahead and return.
        if (!$callbacks) {
            return $pattern;
        }

        // Remove the callbacks from the values, since we will use them below.
        foreach (array_keys($callbacks) as $key) {
            unset($values[$key]);
        }

        $parser = new Parser($pattern);
        $parsed = $parser->parse();

        if ($parsed->err !== null) {
            throw new Parser\Exception\UnableToParseMessageException($parsed->err);
        }

        assert($parsed->val instanceof Parser\Type\ElementCollection);

        return (new Printer())->printAst($this->processAstWithCallbacks($parsed->val, $callbacks));
    }

    /**
     * @param array<array-key, callable(string):string> $callbacks
     *
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
     */
    private function processAstWithCallbacks(
        Parser\Type\ElementCollection $ast,
        array $callbacks
    ): Parser\Type\ElementCollection {
        $processedAst = new Parser\Type\ElementCollection();

        for ($i = 0; $i < $ast->count(); $i++) {
            $element = $ast[$i];
            assert($element instanceof Parser\Type\ElementInterface);
            $clone = clone $element;

            if ($clone instanceof PluralElement || $clone instanceof SelectElement) {
                foreach ($clone->options as $option) {
                    $option->value = $this->processAstWithCallbacks($option->value, $callbacks);
                }
            }

            if ($clone instanceof Parser\Type\TagElement) {
                $processedAst = $processedAst->merge($this->processTagElement($clone, $callbacks));

                continue;
            }

            if ($clone instanceof Parser\Type\LiteralElement) {
                $clone = $this->processLiteralElement($clone, $callbacks);
            }

            $processedAst[] = $clone;
        }

        return $processedAst;
    }

    /**
     * @param array<array-key, callable(string):string> $callbacks
     *
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
     */
    private function processTagElement(
        Parser\Type\TagElement $tagElement,
        array $callbacks
    ): Parser\Type\ElementCollection {
        if (!array_key_exists($tagElement->value, $callbacks)) {
            // We don't have a callback for this tag.
            return new Parser\Type\ElementCollection([$tagElement]);
        }

        $result = ($callbacks[$tagElement->value])(self::CALLBACK_REPLACEMENT);
        if (preg_match(self::CALLBACK_RESULT_PATTERN, $result, $matches)) {
            $start = new Parser\Type\LiteralElement($matches[1], $tagElement->location);
            $middle = $this->processAstWithCallbacks($tagElement->children, $callbacks);
            $end = new Parser\Type\LiteralElement($matches[2], $tagElement->location);

            return new Parser\Type\ElementCollection([$start, ...array_values($middle->toArray()), $end]);
        }

        return new Parser\Type\ElementCollection([new Parser\Type\LiteralElement($result, $tagElement->location)]);
    }

    /**
     * @param array<array-key, callable(string):string> $callbacks
     *
     * @throws CollectionMismatchException
     * @throws UnableToFormatMessageException
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
