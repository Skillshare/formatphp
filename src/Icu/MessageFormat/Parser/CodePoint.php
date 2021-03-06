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

namespace FormatPHP\Icu\MessageFormat\Parser;

class CodePoint
{
    public const NEWLINE = 0x000a;
    public const NUMBER_SIGN = 0x0023;
    public const STRAIGHT_APOSTROPHE = 0x0027;
    public const COMMA = 0x002c;
    public const FORWARD_SLASH = 0x002f;
    public const ZERO = 0x0030;
    public const NINE = 0x0039;
    public const LEFT_ANGLE_BRACKET = 0x003c;
    public const RIGHT_ANGLE_BRACKET = 0x003e;
    public const LEFT_CURLY_BRACE = 0x007b;
    public const RIGHT_CURLY_BRACE = 0x007d;

    /**
     * Boundary for the Unicode Basic Multilingual Plane (Plane 0)
     *
     * @link https://en.wikipedia.org/wiki/Plane_(Unicode) Unicode Plane
     */
    public const BMP = 0x10000;
}
