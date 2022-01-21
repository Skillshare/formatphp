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

use DateTimeInterface as PhpDateTimeInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatDateTimeException;
use IntlDateFormatter as PhpIntlDateFormatter;
use IntlException as PhpIntlException;
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
        'full' => PhpIntlDateFormatter::FULL,
        'long' => PhpIntlDateFormatter::LONG,
        'medium' => PhpIntlDateFormatter::MEDIUM,
        'short' => PhpIntlDateFormatter::SHORT,
        'none' => PhpIntlDateFormatter::NONE,
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

    private const SYMBOLS_ERA = [
        'narrow' => 'GGGGG',
        'short' => 'G',
        'long' => 'GGGG',
    ];

    private const SYMBOLS_YEAR = [
        'numeric' => 'yyyy',
        '2-digit' => 'yy',
    ];

    private const SYMBOLS_MONTH = [
        'numeric' => 'M',
        '2-digit' => 'MM',
        'short' => 'MMM',
        'long' => 'MMMM',
        'narrow' => 'MMMMM',
    ];

    private const SYMBOLS_DAY = [
        'numeric' => 'd',
        '2-digit' => 'dd',
    ];

    private const SYMBOLS_WEEKDAY = [
        'narrow' => 'EEEEE',
        'short' => 'E',
        'long' => 'EEEE',
    ];

    private const SYMBOLS_HOUR = [
        'h12' => [
            'numeric' => 'h',
            '2-digit' => 'hh',
        ],
        'h23' => [
            'numeric' => 'H',
            '2-digit' => 'HH',
        ],
        'h24' => [
            'numeric' => 'k',
            '2-digit' => 'kk',
        ],
        'h11' => [
            'numeric' => 'K',
            '2-digit' => 'KK',
        ],
    ];

    private const SYMBOLS_MINUTE = [
        'numeric' => 'm',
        '2-digit' => 'mm',
    ];

    private const SYMBOLS_SECOND = [
        'numeric' => 's',
        '2-digit' => 'ss',
    ];

    private const SYMBOLS_TIME_ZONE = [
        'short' => 'z',
        'long' => 'zzzz',
        'shortOffset' => 'Z',
        'longOffset' => 'ZZZZ',
        'shortGeneric' => 'v',
        'longGeneric' => 'vvvv',
    ];

    private const HOUR_CYCLE_MAP = [
        'h' => 'h12',
        'hh' => 'h12',
        'H' => 'h23',
        'HH' => 'h23',
        'k' => 'h24',
        'kk' => 'h24',
        'K' => 'h11',
        'KK' => 'h11',
    ];

    private string $originalLocaleName;
    private string $localeName;
    private int $dateType;
    private int $timeType;
    private ?string $pattern;
    private ?string $timeZone;

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

        $this->localeName = $locale->toString();
        $this->dateType = $this->getDateStyleFallback($options);
        $this->timeType = self::STYLE_MAP[$options->timeStyle] ?? PhpIntlDateFormatter::NONE;
        $this->pattern = $this->buildPattern($options, $locale, $this->dateType, $this->timeType);
        $this->timeZone = $options->timeZone;
    }

    /**
     * @throws UnableToFormatDateTimeException
     */
    public function format(PhpDateTimeInterface $date): string
    {
        try {
            return $this->doFormat($date);
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
    }

    /**
     * @throws PhpIntlException
     */
    private function doFormat(PhpDateTimeInterface $date): string
    {
        if ($this->pattern === null) {
            $formatter = new PhpIntlDateFormatter($this->localeName, $this->dateType, $this->timeType, $this->timeZone);

            return (string) $formatter->format($date);
        }

        // This is a hack, since PHP's MessageFormatter, unlike its
        // IntlDateFormatter, has no way to set the timezone it should use when
        // formatting dates/times.
        $defaultTZ = date_default_timezone_get();
        date_default_timezone_set($this->timeZone ?? $defaultTZ);

        // PHP's `IntlDateFormatter::setPattern()` method leaves much to be desired,
        // so we will use the PHP `MessageFormatter` class, instead.
        $formatter = new PhpMessageFormatter($this->localeName, $this->pattern);

        $formattedDate = (string) $formatter->format([$date]);

        // Restore the system timezone.
        date_default_timezone_set($defaultTZ);

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

    /**
     * If `dateStyle` is not set, this returns an appropriate fallback style,
     * depending on whether other style properties are set
     */
    private function getDateStyleFallback(DateTimeFormatOptions $options): int
    {
        foreach (self::STYLE_PROPERTIES as $property) {
            if ($options->{$property} !== null) {
                return PhpIntlDateFormatter::NONE;
            }
        }

        if ($options->timeStyle === null) {
            // If everything else is `null`, then default to the "short" style,
            // as is the practice in FormatJS.
            return self::STYLE_MAP[$options->dateStyle] ?? PhpIntlDateFormatter::SHORT;
        }

        return self::STYLE_MAP[$options->dateStyle] ?? PhpIntlDateFormatter::NONE;
    }

    private function withHourCycleFallback(LocaleInterface $locale, DateTimeFormatOptions $options): LocaleInterface
    {
        if ($options->hourCycle !== null) {
            return $locale->withHourCycle($options->hourCycle);
        }

        // The `hour12` property overrides the `hourCycle` property, in case
        // both are present.
        if ($options->hour12 !== null) {
            return $locale->withHourCycle($options->hour12 ? 'h12' : 'h23');
        }

        if ($locale->hourCycle() !== null) {
            return $locale;
        }

        // If neither `hourCycle` nor `hour12` are set, we will use PHP's
        // IntlDateFormatter class to determine the default hour cycle for
        // the locale.
        $dateFormatter = new PhpIntlDateFormatter(
            $locale->toString(),
            PhpIntlDateFormatter::FULL,
            PhpIntlDateFormatter::FULL,
        );

        preg_match(self::HOUR_PATTERN, $dateFormatter->getPattern(), $matches);

        // Fallback to h12, if we can't determine the hour cycle from this locale.
        return $locale->withHourCycle(self::HOUR_CYCLE_MAP[$matches[1] ?? 'h']);
    }

    private function buildPattern(
        DateTimeFormatOptions $options,
        LocaleInterface $locale,
        int $dateType,
        int $timeType
    ): ?string {
        if ($dateType !== PhpIntlDateFormatter::NONE || $timeType !== PhpIntlDateFormatter::NONE) {
            return null;
        }

        $hourCycle = $locale->hourCycle() ?? '';

        $pattern = self::SYMBOLS_ERA[$options->era] ?? '';
        $pattern .= self::SYMBOLS_YEAR[$options->year] ?? '';
        $pattern .= self::SYMBOLS_MONTH[$options->month] ?? '';
        $pattern .= self::SYMBOLS_DAY[$options->day] ?? '';
        $pattern .= self::SYMBOLS_WEEKDAY[$options->weekday] ?? '';
        $pattern .= self::SYMBOLS_HOUR[$hourCycle][$options->hour] ?? '';
        $pattern .= self::SYMBOLS_MINUTE[$options->minute] ?? '';
        $pattern .= self::SYMBOLS_SECOND[$options->second] ?? '';
        $pattern .= self::SYMBOLS_TIME_ZONE[$options->timeZoneName] ?? '';

        return "{0, date, ::$pattern}";
    }
}
