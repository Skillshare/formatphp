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

namespace FormatPHP\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser\Type\ArgumentElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\LiteralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonToken;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\PoundElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\SelectElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\SkeletonInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\TagElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\TimeElement;

use function array_filter;
use function array_keys;
use function array_map;
use function assert;
use function count;
use function implode;
use function is_string;
use function preg_replace;
use function str_replace;
use function strtolower;

/**
 * Prints an AST representation of ICU message format as a string
 *
 * @internal
 */
class Printer
{
    /**
     * Returns the string form of the ICU message format for the given AST
     */
    public function printAst(ElementCollection $ast): string
    {
        return $this->doPrintAst($ast, false);
    }

    private function doPrintAst(ElementCollection $ast, bool $isInPlural): string
    {
        $printedNodes = array_map(function (ElementInterface $element) use ($isInPlural): string {
            if ($element instanceof ArgumentElement) {
                return $this->printArgumentElement($element);
            }

            if (
                $element instanceof DateElement
                || $element instanceof TimeElement
                || $element instanceof NumberElement
            ) {
                return $this->printSimpleFormatElement($element);
            }

            if ($element instanceof PluralElement) {
                return $this->printPluralElement($element);
            }

            if ($element instanceof SelectElement) {
                return $this->printSelectElement($element);
            }

            if ($element instanceof PoundElement) {
                return '#';
            }

            if ($element instanceof TagElement) {
                return $this->printTagElement($element);
            }

            assert($element instanceof LiteralElement);

            return $this->printLiteralElement($element, $isInPlural);
        }, $ast->toArray());

        return implode('', $printedNodes);
    }

    private function printEscapedMessage(string $message): string
    {
        return (string) preg_replace('/([{}](?:.*[{}])?)/su', '\'$1\'', $message);
    }

    private function printTagElement(TagElement $element): string
    {
        return "<$element->value>" . $this->printAst($element->children) . "</$element->value>";
    }

    private function printLiteralElement(LiteralElement $element, bool $isInPlural): string
    {
        $escaped = $this->printEscapedMessage($element->value);

        return $isInPlural ? str_replace('#', "'#'", $escaped) : $escaped;
    }

    private function printArgumentElement(ArgumentElement $element): string
    {
        return '{' . $element->value . '}';
    }

    /**
     * @param DateElement | TimeElement | NumberElement $element
     */
    private function printSimpleFormatElement($element): string
    {
        $style = '';
        if ($element->style !== null) {
            $style = ', ' . $this->printArgumentStyle($element->style);
        }

        return '{'
            . $element->value
            . ', '
            . strtolower($element->type->getKey())
            . $style
            . '}';
    }

    /**
     * @param string | SkeletonInterface $style
     */
    private function printArgumentStyle($style): string
    {
        if (is_string($style)) {
            return $this->printEscapedMessage($style);
        }

        if ($style instanceof DateTimeSkeleton) {
            return '::' . $this->printDateTimeSkeleton($style);
        }

        assert($style instanceof NumberSkeleton);

        return '::' . implode(' ', array_map([$this, 'printNumberSkeletonToken'], $style->tokens->toArray()));
    }

    private function printDateTimeSkeleton(DateTimeSkeleton $style): string
    {
        return $style->pattern;
    }

    private function printNumberSkeletonToken(NumberSkeletonToken $token): string
    {
        $stem = $token->stem;
        $options = $token->options;

        return count($options) === 0
            ? $stem
            : $stem . implode('', array_map(fn (string $option): string => "/$option", $options));
    }

    private function printSelectElement(SelectElement $element): string
    {
        $msg = [
            $element->value,
            'select',
            implode(
                ' ',
                array_map(
                    fn (string $id): string => "$id{" . $this->doPrintAst($element->options[$id]->value, false) . '}',
                    array_keys($element->options),
                ),
            ),
        ];

        return '{' . implode(', ', $msg) . '}';
    }

    private function printPluralElement(PluralElement $element): string
    {
        $msg = [
            $element->value,
            $element->pluralType === 'cardinal' ? 'plural' : 'selectordinal',
            implode(' ', array_filter([
                $element->offset ? "offset:$element->offset" : '',
                ...array_map(
                    fn (string $id): string => "$id{" . $this->doPrintAst($element->options[$id]->value, true) . '}',
                    array_keys($element->options),
                ),
            ])),
        ];

        return '{' . implode(', ', $msg) . '}';
    }
}
