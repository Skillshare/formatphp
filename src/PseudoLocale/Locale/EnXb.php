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

namespace FormatPHP\PseudoLocale\Locale;

use FormatPHP\Icu\MessageFormat\Parser;

use function array_keys;
use function array_map;
use function array_search;
use function implode;
use function mb_str_split;
use function str_repeat;

/**
 * en-XB pseudo locale
 *
 * Similar to en-XA, this is a pseudo locale consisting of all Latin alphabet
 * characters converted to the same characters with accent marks. Additionally,
 * every third character is repeated 3 times, and the beginning and ending of
 * each literal string is prefixed with `[!!` and postfixed with `!!]`.
 */
class EnXb extends AbstractLocale
{
    protected function generate(Parser\Type\ElementCollection $elementCollection): Parser\Type\ElementCollection
    {
        foreach ($elementCollection as $element) {
            if ($element instanceof Parser\Type\LiteralElement) {
                $splitString = mb_str_split($element->value, 1, Parser::ENCODING);
                $pseudoString = implode(
                    '',
                    array_map(
                        /** @param array-key $key */
                        function (string $char, $key): string {
                            $index = array_search($char, self::ASCII);
                            $canPad = ((int) $key + 1) % 3 === 0;

                            if ($index === false) {
                                return $char;
                            }

                            return $canPad
                                ? str_repeat(self::ACCENTED_ASCII[$index], 3)
                                : self::ACCENTED_ASCII[$index];
                        },
                        $splitString,
                        array_keys($splitString),
                    ),
                );

                $element->value = "[!! $pseudoString !!]";
            } elseif ($element instanceof Parser\Type\PluralElement || $element instanceof Parser\Type\SelectElement) {
                foreach ($element->options as $option) {
                    $this->generate($option->value);
                }
            } elseif ($element instanceof Parser\Type\TagElement) {
                $this->generate($element->children);
            }
        }

        return $elementCollection;
    }
}
