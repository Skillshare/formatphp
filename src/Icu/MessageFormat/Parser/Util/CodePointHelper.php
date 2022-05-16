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

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidUtf8CodePointException;

use function is_string;
use function mb_chr;
use function mb_ord;

/**
 * A helper for working with code points
 *
 * @internal
 */
class CodePointHelper
{
    public const WHITE_SPACE_TOKENS = "\t\n\v\f\r \u{0085}\u{200E}\u{200F}\u{2028}\u{2029}";

    private IsAlpha $isAlpha;
    private IsWhiteSpace $isWhiteSpace;
    private IsPatternSyntax $isPatternSyntax;
    private IsPotentialElementNameChar $isPotentialElementNameChar;

    public function __construct()
    {
        $this->isAlpha = new IsAlpha();
        $this->isWhiteSpace = new IsWhiteSpace();
        $this->isPatternSyntax = new IsPatternSyntax();
        $this->isPotentialElementNameChar = new IsPotentialElementNameChar();
    }

    /**
     * Checks whether a code point is an alphabet character
     *
     * @see IsAlpha::matches()
     */
    public function isAlpha(int $codepoint): bool
    {
        return $this->isAlpha->matches($codepoint);
    }

    /**
     * Checks whether a code point is an alphabet character or a forward slash ("/")
     *
     * @see IsAlpha::matches()
     */
    public function isAlphaOrSlash(int $codepoint): bool
    {
        return $codepoint === 0x002f || $this->isAlpha->matches($codepoint);
    }

    /**
     * Checks whether a code point is in the Unicode Character Database
     * White_Space and Pattern_White_Space groups
     *
     * @see IsWhiteSpace::matches()
     */
    public function isWhiteSpace(int $codepoint): bool
    {
        return $this->isWhiteSpace->matches($codepoint);
    }

    /**
     * Checks whether a code point is in the Unicode Character Database
     * Pattern_Syntax group
     *
     * @see IsPatternSyntax::matches()
     */
    public function isPatternSyntax(int $codepoint): bool
    {
        return $this->isPatternSyntax->matches($codepoint);
    }

    /**
     * Checks whether the code point could be used in an HTML tag element name
     *
     * @see IsPotentialElementNameChar::matches()
     */
    public function isPotentialElementNameChar(int $codepoint): bool
    {
        return $this->isPotentialElementNameChar->matches($codepoint);
    }

    /**
     * Returns the code point for a character in a string array at a given offset
     *
     * @param string[] $stringArray
     */
    public function charCodeAt(array $stringArray, int $offset): ?int
    {
        $char = $stringArray[$offset] ?? null;

        if ($char !== null && $char !== '') {
            $code = mb_ord($char, Parser::ENCODING);

            if ($code !== false) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Returns the string character for a given code point
     */
    public function fromCharCode(int $code): ?string
    {
        $char = mb_chr($code, Parser::ENCODING);

        if (!is_string($char)) {
            return null;
        }

        return $char;
    }

    /**
     * Returns a string of characters for the given code points
     *
     * @throws InvalidUtf8CodePointException
     */
    public function fromCodePoint(int ...$codePoints): string
    {
        $value = '';

        foreach ($codePoints as $code) {
            $char = $this->fromCharCode($code);

            if ($char === null) {
                throw new InvalidUtf8CodePointException("Code $code is an invalid UTF-8 code point");
            }

            $value .= $char;
        }

        return $value;
    }
}
