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

namespace FormatPHP\Icu\MessageFormat\Parser\Util;

/**
 * @internal
 */
class IsPotentialElementNameChar implements CodePointMatcherInterface
{
    /**
     * Checks whether the code point could be used in an element name
     *
     * We could convert this to an array, like we did for the other code point
     * matchers, but this list has 54,128 code points in it, so we have opted
     * to use a logical statement instead.
     */
    public function matches(int $codepoint): bool
    {
        return $codepoint === 45 /* '-' */
            || $codepoint === 46 /* '.' */
            || ($codepoint >= 48 && $codepoint <= 57) /* 0..9 */
            || $codepoint === 95 /* '_' */
            || ($codepoint >= 97 && $codepoint <= 122) /* a..z */
            || ($codepoint >= 65 && $codepoint <= 90) /* A..Z */
            || $codepoint === 0xb7
            || ($codepoint >= 0xc0 && $codepoint <= 0xd6)
            || ($codepoint >= 0xd8 && $codepoint <= 0xf6)
            || ($codepoint >= 0xf8 && $codepoint <= 0x37d)
            || ($codepoint >= 0x37f && $codepoint <= 0x1fff)
            || ($codepoint >= 0x200c && $codepoint <= 0x200d)
            || ($codepoint >= 0x203f && $codepoint <= 0x2040)
            || ($codepoint >= 0x2070 && $codepoint <= 0x218f)
            || ($codepoint >= 0x2c00 && $codepoint <= 0x2fef)
            || ($codepoint >= 0x3001 && $codepoint <= 0xd7ff)
            || ($codepoint >= 0xf900 && $codepoint <= 0xfdcf)
            || ($codepoint >= 0xfdf0 && $codepoint <= 0xfffd)
            || ($codepoint >= 0x10000 && $codepoint <= 0xeffff);
    }
}
