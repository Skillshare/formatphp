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

class Options
{
    /**
     * Whether to treat HTML/XML tags as string literal
     * instead of parsing them as tag token.
     * When this is false we only allow simple tags without
     * any attributes
     */
    public bool $ignoreTag = false;

    /**
     * Should `select`, `selectordinal`, and `plural` arguments always include
     * the `other` case clause.
     */
    public bool $requiresOtherClause = false;

    /**
     * Whether to parse number/datetime skeleton
     * into NumberFormatOptions and DateTimeFormatOptions, respectively.
     */
    public bool $shouldParseSkeletons = false;

    /**
     * Capture location info in AST
     * Default is false
     */
    public bool $captureLocation = false;
}
