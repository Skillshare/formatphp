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
use Ramsey\Collection\Exception\OutOfBoundsException;

use function array_values;

/**
 * xx-HA pseudo locale
 *
 * A pseudo locale the prefixes each message with `[javascript]`.
 */
class XxHa extends AbstractLocale
{
    private const PREFIX = '[javascript]';

    /**
     * @throws OutOfBoundsException
     */
    protected function generate(Parser\Type\ElementCollection $elementCollection): Parser\Type\ElementCollection
    {
        $element = $elementCollection->first();

        if ($element instanceof Parser\Type\LiteralElement) {
            $element->value = self::PREFIX . $element->value;

            return $elementCollection;
        }

        $mockLocation = new Parser\Type\Location(
            new Parser\Type\LocationDetails(0, 0, 0),
            new Parser\Type\LocationDetails(0, 0, 0),
        );
        $element = new Parser\Type\LiteralElement(self::PREFIX, $mockLocation);

        return new Parser\Type\ElementCollection([$element, ...array_values($elementCollection->toArray())]);
    }
}
