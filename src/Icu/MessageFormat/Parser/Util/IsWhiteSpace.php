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
class IsWhiteSpace extends AbstractCodePointMatcher
{
    /**
     * White space code points
     *
     * This list is derived from the White_Space code points listed in the
     * Unicode Character Database PropList. We converted it to an array for
     * better performance.
     *
     * @link https://www.unicode.org/Public/UCD/latest/ucd/PropList.txt UCD PropList
     */
    protected const CODE_POINTS = [
        0x0009,
        0x000a,
        0x000b,
        0x000c,
        0x000d,
        0x0020,
        0x0085,
        0x200e,
        0x200f,
        0x2028,
        0x2029,
    ];

    /**
     * @inheritdoc
     */
    protected function getCodePoints(): array
    {
        return self::CODE_POINTS;
    }
}
