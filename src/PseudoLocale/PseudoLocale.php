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

namespace FormatPHP\PseudoLocale;

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

    public const LOCALES = [
        self::EN_XA,
        self::EN_XB,
        self::XX_AC,
        self::XX_HA,
        self::XX_LS,
        self::XX_ZA,
    ];
}
