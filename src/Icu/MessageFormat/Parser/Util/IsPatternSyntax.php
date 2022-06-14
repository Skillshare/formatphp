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
 * Checks whether a code point is in the Unicode Character Database
 * Pattern_Syntax group
 *
 * @internal
 */
class IsPatternSyntax implements CodePointMatcherInterface
{
    /**
     * Returns true if the code point is in the Pattern_Syntax group in the
     * Unicode Character Database (UCD)
     *
     * This function matches exactly 2760 code points. The values are derived
     * from PropList-14.0.0.txt, dated 2021-08-12, 23:13:05 GMT. Ranges have
     * been collapsed for optimal performance.
     *
     * @link https://www.unicode.org/Public/UCD/latest/ucd/PropList.txt UCD PropList, Latest
     * @link https://www.unicode.org/Public/14.0.0/ucd/PropList.txt UCD PropList, 14.0.0
     */
    public function matches(int $codepoint): bool
    {
        return ($codepoint >= 0x0021 && $codepoint <= 0x002f)
            || ($codepoint >= 0x003a && $codepoint <= 0x0040)
            || ($codepoint >= 0x005b && $codepoint <= 0x005e)
            || $codepoint === 0x0060
            || ($codepoint >= 0x007b && $codepoint <= 0x007e)
            || ($codepoint >= 0x00a1 && $codepoint <= 0x00a7)
            || $codepoint === 0x00a9
            || ($codepoint >= 0x00ab && $codepoint <= 0x00ac)
            || $codepoint === 0x00ae
            || ($codepoint >= 0x00b0 && $codepoint <= 0x00b1)
            || $codepoint === 0x00b6
            || $codepoint === 0x00bb
            || $codepoint === 0x00bf
            || $codepoint === 0x00d7
            || $codepoint === 0x00f7
            || ($codepoint >= 0x2010 && $codepoint <= 0x2027)
            || ($codepoint >= 0x2030 && $codepoint <= 0x203e)
            || ($codepoint >= 0x2041 && $codepoint <= 0x2053)
            || ($codepoint >= 0x2055 && $codepoint <= 0x205e)
            || ($codepoint >= 0x2190 && $codepoint <= 0x245f)
            || ($codepoint >= 0x2500 && $codepoint <= 0x2775)
            || ($codepoint >= 0x2794 && $codepoint <= 0x2bff)
            || ($codepoint >= 0x2e00 && $codepoint <= 0x2e7f)
            || ($codepoint >= 0x3001 && $codepoint <= 0x3003)
            || ($codepoint >= 0x3008 && $codepoint <= 0x3020)
            || $codepoint === 0x3030
            || ($codepoint >= 0xfd3e && $codepoint <= 0xfd3f)
            || ($codepoint >= 0xfe45 && $codepoint <= 0xfe46);
    }
}
