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

use FormatPHP\Icu\MessageFormat\Parser\Type\OptionSerializer;
use JsonSerializable;

/**
 * @psalm-import-type NumeralType from NumberFormatOptions
 * @psalm-type CalendarType = "buddhist" | "chinese" | "coptic" | "dangi" | "ethioaa" | "ethiopic" | "gregory" | "hebrew" | "indian" | "islamic" | "islamic-civil" | "islamic-rgsa" | "islamic-tbla" | "islamic-umalqura" | "iso8601" | "japanese" | "persian" | "roc" | non-empty-string
 * @psalm-type FractionDigitsType = 0 | 1 | 2 | 3
 * @psalm-type HourType = "h11" | "h12" | "h23" | "h24"
 * @psalm-type PeriodType = "narrow" | "short" | "long"
 * @psalm-type StyleType = "full" | "long" | "medium" | "short"
 * @psalm-type TimeZoneNameType = "long" | "short" | "shortOffset" | "longOffset" | "shortGeneric" | "longGeneric"
 * @psalm-type TimeZoneType = non-empty-string
 * @psalm-type WidthType = "numeric" | "2-digit"
 * @psalm-type OptionsType = array{dateStyle?: StyleType, timeStyle?: StyleType, calendar?: CalendarType, dayPeriod?: PeriodType, numberingSystem?: NumeralType, timeZone?: TimeZoneType, hour12?: bool, hourCycle?: HourType, weekday?: PeriodType, era?: PeriodType, year?: WidthType, month?: WidthType | PeriodType, day?: WidthType, hour?: WidthType, minute?: WidthType, second?: WidthType, fractionalSecondDigits?: FractionDigitsType, timeZoneName?: TimeZoneNameType}
 */
class DateTimeFormatOptions implements JsonSerializable
{
    use OptionSerializer;

    public const STYLE_FULL = 'full';
    public const STYLE_LONG = 'long';
    public const STYLE_MEDIUM = 'medium';
    public const STYLE_SHORT = 'short';

    public const PERIOD_NARROW = 'narrow';
    public const PERIOD_SHORT = 'short';
    public const PERIOD_LONG = 'long';

    public const HOUR_H11 = 'h11';
    public const HOUR_H12 = 'h12';
    public const HOUR_H23 = 'h23';
    public const HOUR_H24 = 'h24';

    public const WIDTH_NUMERIC = 'numeric';
    public const WIDTH_2DIGIT = '2-digit';

    public const FRACTION_DIGITS_0 = 0;
    public const FRACTION_DIGITS_1 = 1;
    public const FRACTION_DIGITS_2 = 2;
    public const FRACTION_DIGITS_3 = 3;

    public const TIME_ZONE_NAME_LONG = 'long';
    public const TIME_ZONE_NAME_SHORT = 'short';
    public const TIME_ZONE_NAME_SHORT_OFFSET = 'shortOffset';
    public const TIME_ZONE_NAME_LONG_OFFSET = 'longOffset';
    public const TIME_ZONE_NAME_SHORT_GENERIC = 'shortGeneric';
    public const TIME_ZONE_NAME_LONG_GENERIC = 'longGeneric';

    /**
     * The date formatting style to use when calling `format()`
     *
     * **Note:** `dateStyle` can be used with `timeStyle`, but not with other
     * options (e.g. `weekday`, `hour`, `month`, etc.).
     *
     * @var StyleType | null
     */
    public ?string $dateStyle = null;

    /**
     * The time formatting style to use when calling `format()`
     *
     * **Note:** `timeStyle` can be used with `dateStyle`, but not with other
     * options (e.g. `weekday`, `hour`, `month`, etc.).
     *
     * @var StyleType | null
     */
    public ?string $timeStyle = null;

    /**
     * The calendar system to use
     *
     * @var CalendarType | null
     */
    public ?string $calendar = null;

    /**
     * The formatting style used for day periods like "in the morning", "am",
     * "noon", "n" etc.
     *
     * @var PeriodType | null
     */
    public ?string $dayPeriod = null;

    /**
     * The numeral system to use
     *
     * @var NumeralType | null
     */
    public ?string $numberingSystem = null;

    /**
     * The time zone to use. The only value implementations must recognize is
     * "UTC"; the default is the runtime's default time zone. Implementations
     * may also recognize the time zone names of the IANA time zone database,
     * such as "Asia/Shanghai", "Asia/Kolkata", "America/New_York".
     *
     * @link https://www.iana.org/time-zones IANA time zone database
     *
     * @var TimeZoneType | null
     */
    public ?string $timeZone = null;

    /**
     * If true, hourCycle will be "h12," if false, hourCycle will be "h23"
     *
     * This property overrides any value set by hourCycle.
     */
    public ?bool $hour12 = null;

    /**
     * The hour cycle to use
     *
     * If this property is specified, it overrides the hc property of the
     * language tag, if set. The hour12 property takes precedence over
     * this value.
     *
     * @var HourType | null
     */
    public ?string $hourCycle = null;

    /**
     * The locale representation of the weekday name.
     *
     * @var PeriodType | null
     */
    public ?string $weekday = null;

    /**
     * The locale representation of the era (e.g. "AD", "BC")
     *
     * @var PeriodType | null
     */
    public ?string $era = null;

    /**
     * The locale representation of the year
     *
     * @var WidthType | null
     */
    public ?string $year = null;

    /**
     * The locale representation of the month
     *
     * @var WidthType | PeriodType | null
     */
    public ?string $month = null;

    /**
     * The locale representation of the day
     *
     * @var WidthType | null
     */
    public ?string $day = null;

    /**
     * The locale representation of the hour
     *
     * @var WidthType | null
     */
    public ?string $hour = null;

    /**
     * The locale representation of the minute
     *
     * @var WidthType | null
     */
    public ?string $minute = null;

    /**
     * The locale representation of the seconds
     *
     * @var WidthType | null
     */
    public ?string $second = null;

    /**
     * The number of digits used to represent fractions of a second (any
     * additional digits are truncated)
     *
     * @var FractionDigitsType | null
     */
    public ?int $fractionalSecondDigits = null;

    /**
     * An indicator for how to format the localized representation of the time
     * zone name
     *
     * @var TimeZoneNameType | null
     */
    public ?string $timeZoneName = null;

    /**
     * @psalm-param OptionsType $options
     */
    public function __construct(array $options = [])
    {
        $this->dateStyle = $options['dateStyle'] ?? null;
        $this->timeStyle = $options['timeStyle'] ?? null;
        $this->calendar = $options['calendar'] ?? null;
        $this->dayPeriod = $options['dayPeriod'] ?? null;
        $this->numberingSystem = $options['numberingSystem'] ?? null;
        $this->timeZone = $options['timeZone'] ?? null;
        $this->hour12 = $options['hour12'] ?? null;
        $this->hourCycle = $options['hourCycle'] ?? null;
        $this->weekday = $options['weekday'] ?? null;
        $this->era = $options['era'] ?? null;
        $this->year = $options['year'] ?? null;
        $this->month = $options['month'] ?? null;
        $this->day = $options['day'] ?? null;
        $this->hour = $options['hour'] ?? null;
        $this->minute = $options['minute'] ?? null;
        $this->second = $options['second'] ?? null;
        $this->fractionalSecondDigits = $options['fractionalSecondDigits'] ?? null;
        $this->timeZoneName = $options['timeZoneName'] ?? null;
    }
}
