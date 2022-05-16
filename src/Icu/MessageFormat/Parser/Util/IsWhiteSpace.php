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

namespace FormatPHP\Icu\MessageFormat\Parser\Util;

/**
 * Checks whether a code point is in the Unicode Character Database
 * White_Space and Pattern_White_Space groups
 *
 * @internal
 */
class IsWhiteSpace implements CodePointMatcherInterface
{
    /**
     * Returns true if the code point is in either the White_Space or
     * Pattern_White_Space group in the Unicode Character Database (UCD)
     *
     * FormatJS claims its `_isWhiteSpace()` function is the code point
     * equivalent of `\p{White_Space}`, but this is incorrect. In reality,
     * their function, as implemented in JavaScript, is the code point
     * equivalent of `\p{Pattern_White_Space}`, which is a subset of
     * `\p{White_Space}`, along with `0x200e` (left-to-right bidi mark) and
     * `0x200f` (right-to-left bidi mark) added.
     *
     * FormatJS uses this function in the case the user's browser doesn't
     * support the native `\p{White_Space}` regular expression pattern. If the
     * browser does support it, then it uses `\p{White_Space}` instead.
     *
     * In our implementation, we've combined both `White_Space` and
     * `Pattern_White_Space` code points to accommodate both sets, for maximum
     * interoperability.
     *
     * This function matches exactly 27 code points. The values are derived
     * from PropList-14.0.0.txt, dated 2021-08-12, 23:13:05 GMT. Ranges have
     * been collapsed for optimal performance.
     *
     * @link https://github.com/formatjs/formatjs/blob/9c50fd13c5a4966adc7e9c22ed21553b7fef5337/packages/icu-messageformat-parser/parser.ts#L1334-L1347 FormatJS _isWhiteSpace()
     * @link https://www.unicode.org/Public/UCD/latest/ucd/PropList.txt UCD PropList, Latest
     * @link https://www.unicode.org/Public/14.0.0/ucd/PropList.txt UCD PropList, 14.0.0
     */
    public function matches(int $codepoint): bool
    {
        return ($codepoint >= 0x0009 && $codepoint <= 0x000d)
            || $codepoint === 0x0020
            || $codepoint === 0x0085
            || $codepoint === 0x00a0
            || $codepoint === 0x1680
            || ($codepoint >= 0x2000 && $codepoint <= 0x200a)
            || ($codepoint >= 0x200e && $codepoint <= 0x200f)
            || ($codepoint >= 0x2028 && $codepoint <= 0x2029)
            || $codepoint === 0x202f
            || $codepoint === 0x205f
            || $codepoint === 0x3000;
    }
}
