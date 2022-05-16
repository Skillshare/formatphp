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
 * Checks whether the code point could be used in an HTML tag element name
 *
 * @internal
 */
class IsPotentialElementNameChar implements CodePointMatcherInterface
{
    /**
     * Returns true if the code point could be used in an HTML tag element name
     *
     * This function matches exactly 971,632 code points.
     *
     * A tag name must start with an ASCII lower/upper case letter. The grammar
     * is based on the HTML custom element name except that a dash is NOT always
     * mandatory and uppercase letters are accepted:
     *
     * ```
     * tag ::= "<" tagName (whitespace)* "/>" |
     *     "<" tagName (whitespace)* ">" message "</" tagName (whitespace)* ">"
     * tagName ::= [a-z] (PENChar)*
     * PENChar ::=
     *     "-" | "." | [0-9] | "_" | [a-z] | [A-Z] | #xB7 | [#xC0-#xD6] |
     *     [#xD8-#xF6] | [#xF8-#x37D] | [#x37F-#x1FFF] | [#x200C-#x200D] |
     *     [#x203F-#x2040] | [#x2070-#x218F] | [#x2C00-#x2FEF] |
     *     [#x3001-#xD7FF] | [#xF900-#xFDCF] | [#xFDF0-#xFFFD] |
     *     [#x10000-#xEFFFF]
     * ```
     *
     * @link https://html.spec.whatwg.org/multipage/custom-elements.html#valid-custom-element-name HTML custom element name
     */
    public function matches(int $codepoint): bool
    {
        return ($codepoint >= 0x002d && $codepoint <= 0x002e)
            || ($codepoint >= 0x0030 && $codepoint <= 0x0039)
            || ($codepoint >= 0x0041 && $codepoint <= 0x005a)
            || $codepoint === 0x005f
            || ($codepoint >= 0x0061 && $codepoint <= 0x007a)
            || $codepoint === 0x00b7
            || ($codepoint >= 0x00c0 && $codepoint <= 0x00d6)
            || ($codepoint >= 0x00d8 && $codepoint <= 0x00f6)
            || ($codepoint >= 0x00f8 && $codepoint <= 0x037d)
            || ($codepoint >= 0x037f && $codepoint <= 0x1fff)
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
