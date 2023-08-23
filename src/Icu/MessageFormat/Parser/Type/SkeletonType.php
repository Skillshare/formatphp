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

namespace FormatPHP\Icu\MessageFormat\Parser\Type;

use MyCLabs\Enum\Enum;

/**
 * phpcs:disable Generic.NamingConventions.UpperCaseConstantName.ClassConstantNotUpperCase
 *
 * @method static SkeletonType Number()
 * @method static SkeletonType DateTime()
 * @psalm-immutable
 * @extends Enum<int>
 */
final class SkeletonType extends Enum
{
    /**
     * @link https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html
     */
    private const Number = 0; // @phpstan-ignore-line

    /**
     * @link https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetimepatterngenerator
     */
    private const DateTime = 1; // @phpstan-ignore-line
}
