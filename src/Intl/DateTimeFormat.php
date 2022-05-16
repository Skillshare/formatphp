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

namespace FormatPHP\Intl;

use DateTimeInterface as PhpDateTimeInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatDateTimeException;
use IntlCalendar;
use IntlDateFormatter as PhpIntlDateFormatter;
use IntlException as PhpIntlException;
use IntlTimeZone;
use Locale as PhpLocale;
use MessageFormatter as PhpMessageFormatter;
use Throwable;

use function date_default_timezone_get;
use function date_default_timezone_set;
use function is_int;
use function preg_match;
use function sprintf;

/**
 * Formats a date/time for a given locale
 */
class DateTimeFormat implements DateTimeFormatInterface
{
    private const HOUR_PATTERN = '/(hh?|HH?|kk?|KK?)/';

    private const STYLE_MAP = [
        DateTimeFormatOptions::STYLE_FULL => PhpIntlDateFormatter::FULL,
        DateTimeFormatOptions::STYLE_LONG => PhpIntlDateFormatter::LONG,
        DateTimeFormatOptions::STYLE_MEDIUM => PhpIntlDateFormatter::MEDIUM,
        DateTimeFormatOptions::STYLE_SHORT => PhpIntlDateFormatter::SHORT,
    ];

    /**
     * These style properties may not be combined with `dateStyle` or `timeStyle`
     */
    private const STYLE_PROPERTIES = [
        'era',
        'year',
        'month',
        'weekday',
        'day',
        'hour',
        'minute',
        'second',
    ];

    private const SYMBOLS_STYLE_DATE = [
        DateTimeFormatOptions::STYLE_FULL => 'EEEEMMMMdy',
        DateTimeFormatOptions::STYLE_LONG => 'MMMMdy',
        DateTimeFormatOptions::STYLE_MEDIUM => 'MMMdy',
        DateTimeFormatOptions::STYLE_SHORT => 'Mdyy',
    ];

    private const SYMBOLS_STYLE_TIME = [
        DateTimeFormatOptions::STYLE_FULL => 'hmmssazzzz',
        DateTimeFormatOptions::STYLE_LONG => 'hmmssaz',
        DateTimeFormatOptions::STYLE_MEDIUM => 'hmmssa',
        DateTimeFormatOptions::STYLE_SHORT => 'hmma',
    ];

    private const SYMBOLS_ERA = [
        DateTimeFormatOptions::PERIOD_NARROW => 'GGGGG',
        DateTimeFormatOptions::PERIOD_SHORT => 'G',
        DateTimeFormatOptions::PERIOD_LONG => 'GGGG',
    ];

    private const SYMBOLS_YEAR = [
        DateTimeFormatOptions::WIDTH_NUMERIC => 'yyyy',
        DateTimeFormatOptions::WIDTH_2DIGIT => 'yy',
    ];

    private const SYMBOLS_MONTH = [
        DateTimeFormatOptions::WIDTH_NUMERIC => 'M',
        DateTimeFormatOptions::WIDTH_2DIGIT => 'MM',
        DateTimeFormatOptions::PERIOD_SHORT => 'MMM',
        DateTimeFormatOptions::PERIOD_LONG => 'MMMM',
        DateTimeFormatOptions::PERIOD_NARROW => 'MMMMM',
    ];

    private const SYMBOLS_DAY = [
        DateTimeFormatOptions::WIDTH_NUMERIC => 'd',
        DateTimeFormatOptions::WIDTH_2DIGIT => 'dd',
    ];

    private const SYMBOLS_WEEKDAY = [
        DateTimeFormatOptions::PERIOD_NARROW => 'EEEEE',
        DateTimeFormatOptions::PERIOD_SHORT => 'E',
        DateTimeFormatOptions::PERIOD_LONG => 'EEEE',
    ];

    private const SYMBOLS_DAY_PERIOD = [
        DateTimeFormatOptions::PERIOD_NARROW => 'bbbbb',
        DateTimeFormatOptions::PERIOD_SHORT => 'b',
        DateTimeFormatOptions::PERIOD_LONG => 'B',
    ];

    private const SYMBOLS_HOUR = [
        DateTimeFormatOptions::HOUR_H12 => [
            DateTimeFormatOptions::WIDTH_NUMERIC => 'h',
            DateTimeFormatOptions::WIDTH_2DIGIT => 'hh',
        ],
        DateTimeFormatOptions::HOUR_H23 => [
            DateTimeFormatOptions::WIDTH_NUMERIC => 'H',
            DateTimeFormatOptions::WIDTH_2DIGIT => 'HH',
        ],
        DateTimeFormatOptions::HOUR_H24 => [
            DateTimeFormatOptions::WIDTH_NUMERIC => 'k',
            DateTimeFormatOptions::WIDTH_2DIGIT => 'kk',
        ],
        DateTimeFormatOptions::HOUR_H11 => [
            DateTimeFormatOptions::WIDTH_NUMERIC => 'K',
            DateTimeFormatOptions::WIDTH_2DIGIT => 'KK',
        ],
    ];

    private const SYMBOLS_MINUTE = [
        DateTimeFormatOptions::WIDTH_NUMERIC => 'm',
        DateTimeFormatOptions::WIDTH_2DIGIT => 'mm',
    ];

    private const SYMBOLS_SECOND = [
        DateTimeFormatOptions::WIDTH_NUMERIC => 's',
        DateTimeFormatOptions::WIDTH_2DIGIT => 'ss',
    ];

    private const SYMBOLS_TIME_ZONE = [
        DateTimeFormatOptions::TIME_ZONE_NAME_SHORT => 'z',
        DateTimeFormatOptions::TIME_ZONE_NAME_LONG => 'zzzz',
        DateTimeFormatOptions::TIME_ZONE_NAME_SHORT_OFFSET => 'Z',
        DateTimeFormatOptions::TIME_ZONE_NAME_LONG_OFFSET => 'ZZZZ',
        DateTimeFormatOptions::TIME_ZONE_NAME_SHORT_GENERIC => 'v',
        DateTimeFormatOptions::TIME_ZONE_NAME_LONG_GENERIC => 'vvvv',
    ];

    private const HOUR_CYCLE_MAP = [
        'h' => DateTimeFormatOptions::HOUR_H12,
        'hh' => DateTimeFormatOptions::HOUR_H12,
        'H' => DateTimeFormatOptions::HOUR_H23,
        'HH' => DateTimeFormatOptions::HOUR_H23,
        'k' => DateTimeFormatOptions::HOUR_H24,
        'kk' => DateTimeFormatOptions::HOUR_H24,
        'K' => DateTimeFormatOptions::HOUR_H11,
        'KK' => DateTimeFormatOptions::HOUR_H11,
    ];

    private string $originalLocaleName;
    private string $localeName;
    private string $skeleton;
    private ?string $dateStyle;
    private ?string $timeStyle;
    private IntlCalendar $intlCalendar;
    private IntlTimeZone $intlTimeZone;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?LocaleInterface $locale = null, ?DateTimeFormatOptions $options = null)
    {
        $locale = $locale ?? new Locale(PhpLocale::getDefault());
        $this->originalLocaleName = $locale->toString();
        $options = $options ? clone $options : new DateTimeFormatOptions();

        $this->checkDateTimeStyle($options);

        $locale = $this->combineLocaleWithOptions($locale, $options);

        // Store these options for later use.
        $this->dateStyle = $options->dateStyle;
        $this->timeStyle = $options->timeStyle;

        $timeZoneId = $options->timeZone ?? date_default_timezone_get();
        $this->intlTimeZone = IntlTimeZone::createTimeZone($timeZoneId);

        if ($this->intlTimeZone->getID() === IntlTimeZone::getUnknown()->getID()) {
            throw new InvalidArgumentException(sprintf('Unknown time zone "%s"', $timeZoneId));
        }

        $this->intlCalendar = IntlCalendar::createInstance(
            $this->intlTimeZone,
            $locale->toString(),
        );

        $this->localeName = $locale->toString();
        $this->skeleton = $this->buildSkeleton($options, $locale);
    }

    /**
     * @throws UnableToFormatDateTimeException
     */
    public function format(PhpDateTimeInterface $date): string
    {
        try {
            return $this->doFormat($date);
        // @codeCoverageIgnoreStart
        } catch (Throwable $exception) {
            throw new UnableToFormatDateTimeException(
                sprintf(
                    'Unable to format date "%s" for locale "%s"',
                    $date->format('r'),
                    $this->originalLocaleName,
                ),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception,
            );
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns the date skeleton generated from the options provided
     *
     * @internal
     */
    public function getSkeleton(): string
    {
        return $this->skeleton;
    }

    /**
     * Returns the locale constructed from the date/time format options
     *
     * @internal
     */
    public function getEvaluatedLocale(): string
    {
        return $this->localeName;
    }

    /**
     * @throws PhpIntlException
     * @throws UnableToFormatDateTimeException
     */
    private function doFormat(PhpDateTimeInterface $date): string
    {
        if ($this->dateStyle !== null || $this->timeStyle !== null) {
            return $this->doFormatWithIntlDateFormatter($date);
        }

        return $this->doFormatWithMessageFormatter($date);
    }

    /**
     * @throws UnableToFormatDateTimeException
     */
    private function doFormatWithIntlDateFormatter(PhpDateTimeInterface $date): string
    {
        $formatter = new PhpIntlDateFormatter(
            $this->localeName,
            self::STYLE_MAP[$this->dateStyle] ?? PhpIntlDateFormatter::NONE,
            self::STYLE_MAP[$this->timeStyle] ?? PhpIntlDateFormatter::NONE,
            $this->intlTimeZone,
            $this->intlCalendar,
        );

        $formattedDate = $formatter->format($date);

        if ($formattedDate === false) {
            // @codeCoverageIgnoreStart
            // This statement may be unreachable with the current logic, but
            // it remains in case there is an unknown condition that could
            // cause this.
            throw new UnableToFormatDateTimeException($formatter->getErrorMessage(), $formatter->getErrorCode());
            // @codeCoverageIgnoreEnd
        }

        return $formattedDate;
    }

    /**
     * @throws PhpIntlException
     * @throws UnableToFormatDateTimeException
     */
    private function doFormatWithMessageFormatter(PhpDateTimeInterface $date): string
    {
        $pattern = "{0, date, ::$this->skeleton}";

        // Change the time zone to accommodate formatting the date/time string.
        $defaultTZ = date_default_timezone_get();
        date_default_timezone_set($this->intlTimeZone->getID());

        // PHP's `IntlDateFormatter::setPattern()` method leaves much to be desired,
        // so we will use the PHP `MessageFormatter` class, instead.
        $formatter = new PhpMessageFormatter($this->localeName, $pattern);

        $formattedDate = $formatter->format([$date]);

        // Restore the system time zone.
        date_default_timezone_set($defaultTZ);

        if ($formattedDate === false) {
            // @codeCoverageIgnoreStart
            // This statement may be unreachable with the current logic, but
            // it remains in case there is an unknown condition that could
            // cause this.
            throw new UnableToFormatDateTimeException($formatter->getErrorMessage(), $formatter->getErrorCode());
            // @codeCoverageIgnoreEnd
        }

        return $formattedDate;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function checkDateTimeStyle(DateTimeFormatOptions $options): void
    {
        if ($options->dateStyle === null && $options->timeStyle === null) {
            return;
        }

        foreach (self::STYLE_PROPERTIES as $property) {
            if ($options->{$property} !== null) {
                throw new InvalidArgumentException(
                    'dateStyle and timeStyle may not be used with other DateTimeFormat options',
                );
            }
        }
    }

    private function combineLocaleWithOptions(LocaleInterface $locale, DateTimeFormatOptions $options): LocaleInterface
    {
        if ($options->calendar !== null) {
            $locale = $locale->withCalendar($options->calendar);
        }

        if ($options->numberingSystem !== null) {
            $locale = $locale->withNumberingSystem($options->numberingSystem);
        }

        return $this->withHourCycleFallback($locale, $options);
    }

    private function withHourCycleFallback(LocaleInterface $locale, DateTimeFormatOptions $options): LocaleInterface
    {
        // The `hour12` property overrides the `hourCycle` property.
        if ($options->hour12 !== null) {
            $options->hourCycle = $options->hour12 ? 'h12' : 'h23';
        }

        if ($options->hourCycle !== null) {
            return $locale->withHourCycle($options->hourCycle);
        }

        if ($locale->hourCycle() !== null) {
            $options->hourCycle = $locale->hourCycle();

            return $locale;
        }

        // If neither `hourCycle` nor `hour12` are set, and we can't find the
        // hour cycle on the locale, we will use PHP's IntlDateFormatter class
        // to determine the default hour cycle for the locale.
        $dateFormatter = new PhpIntlDateFormatter(
            $locale->toString(),
            PhpIntlDateFormatter::FULL,
            PhpIntlDateFormatter::FULL,
        );

        preg_match(self::HOUR_PATTERN, $dateFormatter->getPattern(), $matches);

        // Fallback to h12, if we can't determine the hour cycle from this locale.
        return $locale->withHourCycle(self::HOUR_CYCLE_MAP[$matches[1] ?? 'h']);
    }

    private function buildSkeleton(DateTimeFormatOptions $options, LocaleInterface $locale): string
    {
        $hourCycle = $locale->hourCycle() ?? '';

        $pattern = self::SYMBOLS_STYLE_DATE[$options->dateStyle] ?? '';
        $pattern .= self::SYMBOLS_STYLE_TIME[$options->timeStyle] ?? '';
        $pattern .= self::SYMBOLS_ERA[$options->era] ?? '';
        $pattern .= self::SYMBOLS_YEAR[$options->year] ?? '';
        $pattern .= self::SYMBOLS_MONTH[$options->month] ?? '';
        $pattern .= self::SYMBOLS_DAY[$options->day] ?? '';
        $pattern .= self::SYMBOLS_WEEKDAY[$options->weekday] ?? '';
        $pattern .= self::SYMBOLS_HOUR[$hourCycle][$options->hour] ?? '';
        $pattern .= self::SYMBOLS_MINUTE[$options->minute] ?? '';
        $pattern .= self::SYMBOLS_SECOND[$options->second] ?? '';
        $pattern .= self::SYMBOLS_TIME_ZONE[$options->timeZoneName] ?? '';
        $pattern .= self::SYMBOLS_DAY_PERIOD[$options->dayPeriod] ?? '';

        if ($pattern === '') {
            // Use the "short" style as the default.
            $pattern = self::SYMBOLS_STYLE_DATE[DateTimeFormatOptions::STYLE_SHORT];
            $this->dateStyle = DateTimeFormatOptions::STYLE_SHORT;
        }

        return $pattern;
    }
}
