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
 * @psalm-type CalendarType = "buddhist" | "chinese" | "coptic" | "dangi" | "ethioaa" | "ethiopia" | "ethiopic" | "gregory" | "hebrew" | "indian" | "islamic" | "islamic-civil" | "islamic-rgsa" | "islamic-tbla" | "islamic-umalqura" | "iso8601" | "japanese" | "persian" | "roc" | non-empty-string
 * @psalm-type FractionDigitsType = 0 | 1 | 2 | 3
 * @psalm-type HourType = "h11" | "h12" | "h23" | "h24"
 * @psalm-type PeriodType = "narrow" | "short" | "long"
 * @psalm-type StyleType = "full" | "long" | "medium" | "short" | "none"
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
    public const STYLE_NONE = 'none';

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

    public ?bool $hour12 = null;

    /**
     * @var HourType | null
     */
    public ?string $hourCycle = null;

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
