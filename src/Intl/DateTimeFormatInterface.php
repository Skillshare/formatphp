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

namespace FormatPHP\Intl;

use DateTimeInterface;
use FormatPHP\Exception\UnableToFormatDateTimeException;

/**
 * A date formatter designed to fit within the style and function of
 * ECMA-402 formatters
 *
 * @link https://unicode-org.github.io/icu/userguide/format_parse/datetime/
 * @link https://www.php.net/IntlDateFormatter
 * @link https://tc39.es/ecma402/#datetimeformat-objects
 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat
 */
interface DateTimeFormatInterface
{
    /**
     * Formats a date or time, using a locale configured with the date/time
     * format instance
     *
     * @throws UnableToFormatDateTimeException
     */
    public function format(DateTimeInterface $date): string;
}
