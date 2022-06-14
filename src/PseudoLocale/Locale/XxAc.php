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

use function mb_strtoupper;

/**
 * xx-AC (all caps) pseudo locale
 */
class XxAc extends AbstractLocale
{
    protected function generate(Parser\Type\ElementCollection $elementCollection): Parser\Type\ElementCollection
    {
        foreach ($elementCollection as $element) {
            if ($element instanceof Parser\Type\LiteralElement) {
                $element->value = mb_strtoupper($element->value, Parser::ENCODING);
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
