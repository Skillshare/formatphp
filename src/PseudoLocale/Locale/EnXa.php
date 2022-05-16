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

namespace FormatPHP\PseudoLocale\Locale;

use FormatPHP\Icu\MessageFormat\Parser;

use function array_map;
use function array_search;
use function implode;
use function mb_str_split;

/**
 * en-XA pseudo locale
 *
 * A pseudo locale consisting of all Latin alphabet characters converted to the
 * same characters with accent marks.
 */
class EnXa extends AbstractLocale
{
    protected function generate(Parser\Type\ElementCollection $elementCollection): Parser\Type\ElementCollection
    {
        foreach ($elementCollection as $element) {
            if ($element instanceof Parser\Type\LiteralElement) {
                $element->value = implode(
                    '',
                    array_map(
                        function (string $char): string {
                            $index = array_search($char, self::ASCII);
                            if ($index === false) {
                                return $char;
                            }

                            return self::ACCENTED_ASCII[$index];
                        },
                        mb_str_split($element->value, 1, Parser::ENCODING),
                    ),
                );
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
