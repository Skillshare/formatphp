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

namespace FormatPHP\Icu\MessageFormat\Parser\Type;

use MyCLabs\Enum\Enum;

/**
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase
 *
 * @method static ElementType Literal()
 * @method static ElementType Argument()
 * @method static ElementType Number()
 * @method static ElementType Date()
 * @method static ElementType Time()
 * @method static ElementType Select()
 * @method static ElementType Plural()
 * @method static ElementType Pound()
 * @method static ElementType Tag()
 *
 * @psalm-immutable
 * @extends Enum<int>
 */
final class ElementType extends Enum
{
    /**
     * Raw text
     */
    private const Literal = 0; // @phpstan-ignore-line

    /**
     * Variable without any format, e.g `var` in `this is a {var}`
     */
    private const Argument = 1; // @phpstan-ignore-line

    /**
     * Variable with number format
     */
    private const Number = 2; // @phpstan-ignore-line

    /**
     * Variable with date format
     */
    private const Date = 3; // @phpstan-ignore-line

    /**
     * Variable with time format
     */
    private const Time = 4; // @phpstan-ignore-line

    /**
     * Variable with select format
     */
    private const Select = 5; // @phpstan-ignore-line

    /**
     * Variable with plural format
     */
    private const Plural = 6; // @phpstan-ignore-line

    /**
     * The `#` symbol that will be substituted with the count
     *
     * This is only possible within plural argument.
     */
    private const Pound = 7; // @phpstan-ignore-line

    /**
     * XML-like tag
     */
    private const Tag = 8; // @phpstan-ignore-line
}
