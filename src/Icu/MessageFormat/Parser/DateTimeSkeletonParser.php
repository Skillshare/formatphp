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

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Intl\DateTimeFormatOptions as IntlDateTimeFormatOptions;

use function mb_strlen;
use function preg_match_all;

/**
 * @psalm-import-type PeriodType from IntlDateTimeFormatOptions
 * @psalm-import-type WidthType from IntlDateTimeFormatOptions
 */
class DateTimeSkeletonParser
{
    private const DATE_TIME_REGEX = '/(?:[Eec]{1,6}|G{1,5}|[Qq]{1,5}|(?:[yYur]+|U{1,5})|[ML]{1,5}|d{1,2}|D{1,3}|F|'
        . "[abB]{1,5}|[hkHK]{1,2}|w{1,2}|W|m{1,2}|s{1,2}|[zZOvVxX]{1,4})(?=([^']*'[^']*')*[^']*$)/u";

    /**
     * Parse date time skeleton into DateTimeFormatOptions
     *
     * @link https://unicode.org/reports/tr35/tr35-dates.html#Date_Field_Symbol_Table
     *
     * @throws Exception\InvalidSkeletonOption
     */
    public function parse(string $skeleton): Type\DateTimeFormatOptions
    {
        $options = new Type\DateTimeFormatOptions();

        if (!preg_match_all(self::DATE_TIME_REGEX, $skeleton, $matches)) {
            return $options;
        }

        foreach ($matches[0] as $match) {
            $this->setOption($match, $options);
        }

        return $options;
    }

    /**
     * @throws Exception\InvalidSkeletonOption
     */
    private function setOption(string $skeletonOption, Type\DateTimeFormatOptions $options): void
    {
        $length = mb_strlen($skeletonOption, Parser::ENCODING);

        switch ($skeletonOption[0] ?? '') {
            // Era
            case 'G':
                $options->era = $this->getPeriodForLength($length);

                break;
            // Year
            case 'y':
                $options->year = $this->getWidthForLength($length);

                break;
            case 'Y':
            case 'u':
            case 'U':
            case 'r':
                throw new Exception\InvalidSkeletonOption(
                    '"Y/u/U/r" (year) patterns are not supported, use "y" instead',
                );

            // Quarter
            case 'q':
            case 'Q':
                throw new Exception\InvalidSkeletonOption('"q/Q" (quarter) patterns are not supported');

            // Month
            case 'M':
            case 'L':
                $options->month = $this->getMonthWidthPeriodForLength($length);

                break;
            // Week
            case 'w':
            case 'W':
                throw new Exception\InvalidSkeletonOption('"w/W" (week) patterns are not supported');
            case 'd':
                $options->day = $this->getWidthForLength($length);

                break;
            case 'D':
            case 'F':
            case 'g':
                throw new Exception\InvalidSkeletonOption(
                    '"D/F/g" (day) patterns are not supported, use "d" instead',
                );

            // Weekday
            case 'E':
                $options->weekday = $this->getPeriodForLength($length);

                break;
            case 'e':
                if ($length < 4) {
                    throw new Exception\InvalidSkeletonOption(
                        '"e..eee" (weekday) patterns are not supported',
                    );
                }

                // Subtract 1, since length should be 4 or more.
                $options->weekday = $this->getPeriodForLength($length - 1);

                break;
            case 'c':
                if ($length < 4) {
                    throw new Exception\InvalidSkeletonOption(
                        '"c..ccc" (weekday) patterns are not supported',
                    );
                }

                // Subtract 1, since length should be 4 or more.
                $options->weekday = $this->getPeriodForLength($length - 1);

                break;
            // Period
            case 'a': // AM, PM
                $options->hour12 = true;

                break;
            case 'b': // am, pm, noon, midnight
                if ($length === 5) {
                    $options->dayPeriod = IntlDateTimeFormatOptions::PERIOD_NARROW;
                } else {
                    $options->dayPeriod = IntlDateTimeFormatOptions::PERIOD_SHORT;
                }

                break;
            case 'B': // flexible day periods
                $options->dayPeriod = IntlDateTimeFormatOptions::PERIOD_LONG;

                break;
            // Hour
            case 'h':
                $options->hourCycle = IntlDateTimeFormatOptions::HOUR_H12;
                $options->hour = $this->getWidthForLength($length);

                break;
            case 'H':
                $options->hourCycle = IntlDateTimeFormatOptions::HOUR_H23;
                $options->hour = $this->getWidthForLength($length);

                break;
            case 'K':
                $options->hourCycle = IntlDateTimeFormatOptions::HOUR_H11;
                $options->hour = $this->getWidthForLength($length);

                break;
            case 'k':
                $options->hourCycle = IntlDateTimeFormatOptions::HOUR_H24;
                $options->hour = $this->getWidthForLength($length);

                break;
            case 'j':
            case 'J':
            case 'C':
                throw new Exception\InvalidSkeletonOption(
                    '"j/J/C" (hour) patterns are not supported, use "h/H/K/k" instead',
                );

            // Minute
            case 'm':
                $options->minute = $this->getWidthForLength($length);

                break;

            // Second
            case 's':
                $options->second = $this->getWidthForLength($length);

                break;
            case 'S':
            case 'A':
                throw new Exception\InvalidSkeletonOption(
                    '"S/A" (second) patterns are not supported, use "s" instead',
                );

            // Zone
            case 'z': // 1..3, 4: specific non-location format
                $options->timeZoneName = $length < 4
                    ? IntlDateTimeFormatOptions::TIME_ZONE_NAME_SHORT
                    : IntlDateTimeFormatOptions::TIME_ZONE_NAME_LONG;

                break;
            case 'Z': // 1..3, 4, 5: The ISO8601 various formats
            case 'O': // 1, 4: milliseconds in day short, long
            case 'v': // 1, 4: generic non-location format
            case 'V': // 1, 2, 3, 4: time zone ID or city
            case 'X': // 1, 2, 3, 4: The ISO8601 various formats
            case 'x': // 1, 2, 3, 4: The ISO8601 various formats
                throw new Exception\InvalidSkeletonOption(
                    '"Z/O/v/V/X/x" (timeZone) patterns are not supported, use "z" instead',
                );
        }
    }

    /**
     * @return WidthType
     */
    private function getWidthForLength(int $length): string
    {
        if ($length === 2) {
            return IntlDateTimeFormatOptions::WIDTH_2DIGIT;
        }

        return IntlDateTimeFormatOptions::WIDTH_NUMERIC;
    }

    /**
     * @return PeriodType
     */
    private function getPeriodForLength(int $length): string
    {
        switch ($length) {
            case 4:
                return IntlDateTimeFormatOptions::PERIOD_LONG;
            case 5:
                return IntlDateTimeFormatOptions::PERIOD_NARROW;
            default:
                return IntlDateTimeFormatOptions::PERIOD_SHORT;
        }
    }

    /**
     * @return WidthType | PeriodType
     */
    private function getMonthWidthPeriodForLength(int $length): string
    {
        $monthLengths = [
            IntlDateTimeFormatOptions::WIDTH_NUMERIC,
            IntlDateTimeFormatOptions::WIDTH_2DIGIT,
            IntlDateTimeFormatOptions::PERIOD_SHORT,
            IntlDateTimeFormatOptions::PERIOD_LONG,
            IntlDateTimeFormatOptions::PERIOD_NARROW,
        ];

        return $monthLengths[$length - 1] ?? IntlDateTimeFormatOptions::PERIOD_SHORT;
    }
}
