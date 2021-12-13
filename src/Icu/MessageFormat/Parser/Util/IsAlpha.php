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
 * Checks whether a code point is an alphabet character
 *
 * @internal
 */
class IsAlpha implements CodePointMatcherInterface
{
    /**
     * Returns true if the code point is a Latin uppercase letter A-Z or
     * lowercase letter a-z
     */
    public function matches(int $codepoint): bool
    {
        return ($codepoint >= 0x0041 && $codepoint <= 0x005a)
            || ($codepoint >= 0x0061 && $codepoint <= 0x007a);
    }
}
