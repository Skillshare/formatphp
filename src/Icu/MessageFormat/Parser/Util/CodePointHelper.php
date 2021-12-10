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

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidUtf8CodePointException;

use function is_string;
use function mb_chr;
use function mb_ord;

/**
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

    public function isAlpha(int $codepoint): bool
    {
        return $this->isAlpha->matches($codepoint);
    }

    public function isAlphaOrSlash(int $codepoint): bool
    {
        return $this->isAlpha->matchesWithSlash($codepoint);
    }

    public function isWhiteSpace(int $codepoint): bool
    {
        return $this->isWhiteSpace->matches($codepoint);
    }

    public function isPatternSyntax(int $codepoint): bool
    {
        return $this->isPatternSyntax->matches($codepoint);
    }

    public function isPotentialElementNameChar(int $codepoint): bool
    {
        return $this->isPotentialElementNameChar->matches($codepoint);
    }

    /**
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

    public function fromCharCode(int $code): ?string
    {
        $char = mb_chr($code, Parser::ENCODING);

        if (!is_string($char)) {
            return null;
        }

        return $char;
    }

    /**
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
