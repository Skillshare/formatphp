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

namespace FormatPHP\PseudoLocale;

use Zalgo\Zalgo;

use function class_exists;

/**
 * Constant values for pseudo locales
 */
class PseudoLocale
{
    public const EN_XA = 'en-XA';
    public const EN_XB = 'en-XB';
    public const XX_AC = 'xx-AC';
    public const XX_HA = 'xx-HA';
    public const XX_LS = 'xx-LS';
    public const XX_ZA = 'xx-ZA';

    /**
     * @return string[]
     */
    public static function getSupportedPseudoLocales(): array
    {
        $supportedLocales = [
            self::EN_XA,
            self::EN_XB,
            self::XX_AC,
            self::XX_HA,
            self::XX_LS,
        ];

        if (class_exists(Zalgo::class)) {
            $supportedLocales[] = self::XX_ZA;
        }

        return $supportedLocales;
    }
}
