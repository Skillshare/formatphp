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
use Ramsey\Collection\Exception\OutOfBoundsException;

/**
 * xx-LS (long string) pseudo locale
 */
class XxLs extends AbstractLocale
{
    private const LONG_STRING = 'SSSSSSSSSSSSSSSSSSSSSSSSS';

    /**
     * @throws OutOfBoundsException
     */
    protected function generate(Parser\Type\ElementCollection $elementCollection): Parser\Type\ElementCollection
    {
        $element = $elementCollection->last();

        if ($element instanceof Parser\Type\LiteralElement) {
            $element->value .= self::LONG_STRING;

            return $elementCollection;
        }

        $mockLocation = new Parser\Type\Location(
            new Parser\Type\LocationDetails(0, 0, 0),
            new Parser\Type\LocationDetails(0, 0, 0),
        );
        $element = new Parser\Type\LiteralElement(self::LONG_STRING, $mockLocation);

        $elementCollection->add($element);

        return $elementCollection;
    }
}
