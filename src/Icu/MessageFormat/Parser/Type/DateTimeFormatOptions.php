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

use JsonSerializable;

/**
 * @psalm-import-type LocaleMatcherType from NumberFormatOptions
 * @psalm-import-type NumeralType from NumberFormatOptions
 * @psalm-type CalendarType = "buddhist" | "chinese" | "coptic" | "ethiopia" | "ethiopic" | "gregory" | "hebrew" | "indian" | "islamic" | "iso8601" | "japanese" | "persian" | "roc"
 * @psalm-type FormatMatcherType = "basic" | "best fit"
 * @psalm-type FractionDigitsType = 0 | 1 | 2 | 3
 * @psalm-type HourType = "h11" | "h12" | "h23" | "h24"
 * @psalm-type PeriodType = "narrow" | "short" | "long"
 * @psalm-type StyleType = "full" | "long" | "medium" | "short" | "none"
 * @psalm-type TimeZoneType = "long" | "short" | "shortOffset" | "longOffset" | "shortGeneric" | "longGeneric"
 * @psalm-type WidthType = "numeric" | "2-digit"
 */
class DateTimeFormatOptions implements JsonSerializable
{
    use OptionSerializer;

    public ?string $format = null;

    /**
     * @var StyleType | null
     */
    public ?string $dateStyle = null;

    /**
     * @var StyleType | null
     */
    public ?string $timeStyle = null;

    /**
     * @var CalendarType | null
     */
    public ?string $calendar = null;

    /**
     * @var PeriodType | null
     */
    public ?string $dayPeriod = null;

    /**
     * @var NumeralType | null
     */
    public ?string $numberingSystem = null;

    /**
     * @var LocaleMatcherType | null
     */
    public ?string $localeMatcher = null;

    /**
     * The time zone to use. The only value implementations must recognize is
     * "UTC"; the default is the runtime's default time zone. Implementations
     * may also recognize the time zone names of the IANA time zone database,
     * such as "Asia/Shanghai", "Asia/Kolkata", "America/New_York".
     *
     * @link https://www.iana.org/time-zones IANA time zone database
     */
    public ?string $timeZone = null;

    public ?bool $hour12 = null;

    /**
     * @var HourType | null
     */
    public ?string $hourCycle = null;

    /**
     * @var FormatMatcherType | null
     */
    public ?string $formatMatcher = null;

    /**
     * @var PeriodType | null
     */
    public ?string $weekday = null;

    /**
     * @var PeriodType | null
     */
    public ?string $era = null;

    /**
     * @var WidthType | null
     */
    public ?string $year = null;

    /**
     * @var WidthType | PeriodType | null
     */
    public ?string $month = null;

    /**
     * @var WidthType | null
     */
    public ?string $day = null;

    /**
     * @var WidthType | null
     */
    public ?string $hour = null;

    /**
     * @var WidthType | null
     */
    public ?string $minute = null;

    /**
     * @var WidthType | null
     */
    public ?string $second = null;

    /**
     * @var FractionDigitsType | null
     */
    public ?int $fractionalSecondDigits = null;

    /**
     * @var TimeZoneType | null
     */
    public ?string $timeZoneName = null;
}
