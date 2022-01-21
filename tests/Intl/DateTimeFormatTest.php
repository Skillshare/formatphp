<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use DateTimeImmutable;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatDateTimeException;
use FormatPHP\Intl\DateTimeFormat;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\Locale;
use FormatPHP\Test\TestCase;

use function array_merge;
use function date_default_timezone_get;
use function date_default_timezone_set;

/**
 * @psalm-import-type OptionsType from DateTimeFormatOptions
 */
class DateTimeFormatTest extends TestCase
{
    /**
     * Timestamp taken from FormatJS tests for DateTimeFormat
     *
     * @link https://github.com/formatjs/formatjs/blob/da104e8421dcc5480e38aeec4a32891f8941332f/packages/intl-datetimeformat/tests/format.test.ts#L277
     */
    private const TS = 1592282900;

    private const TEST_TIMEZONE = 'America/Chicago';

    private ?string $defaultTimezone;

    protected function setUp(): void
    {
        $this->defaultTimezone = date_default_timezone_get();
        date_default_timezone_set(self::TEST_TIMEZONE);
    }

    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone ?? 'UTC');
    }

    /**
     * @psalm-param OptionsType $options
     * @dataProvider formatProvider
     */
    public function testFormat(array $options, string $ko, string $en): void
    {
        $koLocale = new Locale('ko');
        $enLocale = new Locale('en');
        $formatOptions = new DateTimeFormatOptions($options);

        $koFormatter = new DateTimeFormat($koLocale, $formatOptions);
        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        $this->assertSame($en, $enFormatter->format($date));
        $this->assertSame($ko, $koFormatter->format($date));

        // We change the default timezone within the SUT, so let's assert
        // that is changed back to the value we set in this test's setUp().
        $this->assertSame(self::TEST_TIMEZONE, date_default_timezone_get());
    }

    public function testFormatThrowsException(): void
    {
        $formatter = new DateTimeFormat(new Locale('en'), new DateTimeFormatOptions([
            'timeZone' => 'America/Foobar',
        ]));

        $this->expectException(UnableToFormatDateTimeException::class);
        $this->expectExceptionMessage(
            'Unable to format date "Fri, 07 Jan 2022 21:36:52 +0000" for locale "en"',
        );

        $formatter->format(new DateTimeImmutable('@' . 1641591412));
    }

    /**
     * @psalm-param OptionsType $additionalOptions
     * @dataProvider formatThrowsExceptionWhenDateStyleOrTimeStyleMixedWithStylePropertyProvider
     */
    public function testFormatThrowsExceptionWhenDateStyleOrTimeStyleMixedWithStyleProperty(
        ?string $dateStyle,
        ?string $timeStyle,
        array $additionalOptions
    ): void {
        /** @var OptionsType $combinedOptions */
        $combinedOptions = array_merge(
            [
                'dateStyle' => $dateStyle,
                'timeStyle' => $timeStyle,
            ],
            $additionalOptions,
        );

        $options = new DateTimeFormatOptions($combinedOptions);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('dateStyle and timeStyle may not be used with other DateTimeFormat options');

        new DateTimeFormat(null, $options);
    }

    public function testHour12OverridesHourCycle(): void
    {
        $enLocale = new Locale('en');
        $formatOptions = new DateTimeFormatOptions([
            'dateStyle' => 'full',
            'timeStyle' => 'full',
            // Specify both hourCycle and hour12 to show that hour12 overrides hourCycle.
            'hourCycle' => 'h23',
            'hour12' => true,
            'timeZone' => 'America/Denver',
        ]);

        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        $this->assertSame(
            'Monday, June 15, 2020 at 10:48:20 PM Mountain Daylight Time',
            $enFormatter->format($date),
        );
    }

    public function testUseHourCycleFromLocale(): void
    {
        $enLocale = new Locale('en');
        $enLocale = $enLocale->withHourCycle('h23');

        $formatOptions = new DateTimeFormatOptions([
            'dateStyle' => 'full',
            'timeStyle' => 'full',
            'timeZone' => 'America/Denver',
        ]);

        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        // This should be 22 instead of 10 (because of the "h23" hourCycle,
        // but PHP's MessageFormatter (perhaps through extension of icu4c's
        // u_formatMessage) always takes the locale's formatting into
        // consideration and renders this without the 24-hour time.
        $this->assertSame(
            'Monday, June 15, 2020 at 10:48:20 PM Mountain Daylight Time',
            $enFormatter->format($date),
        );
    }

    /**
     * Tests taken from FormatJS tests for DateTimeFormat
     *
     * @link https://github.com/formatjs/formatjs/blob/da104e8421dcc5480e38aeec4a32891f8941332f/packages/intl-datetimeformat/tests/format.test.ts#L15-L275
     *
     * @return array<array{options: OptionsType, ko: string, en: string}>
     */
    public function formatProvider(): array
    {
        return [
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => 'numeric',
                    'month' => 'numeric',
                    'day' => 'numeric',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'hour12' => true,
                    'timeZone' => 'UTC',
                    'timeZoneName' => 'long',
                ],
                'ko' => '서기 2020년 6 16일 화요일 오전 4시 48분 20초 협정 세계시',
                'en' => 'Tuesday, 6 16, 2020 Anno Domini, 4:48:20 AM Coordinated Universal Time',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => 'numeric',
                    'month' => 'numeric',
                    'day' => 'numeric',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'hour12' => true,
                    'timeZone' => 'America/New_York',
                    'timeZoneName' => 'short',
                ],
                'ko' => '서기 2020년 6 16일 화요일 오전 12시 48분 20초 GMT-4',
                'en' => 'Tuesday, 6 16, 2020 Anno Domini, 12:48:20 AM EDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => 'numeric',
                    'month' => 'numeric',
                    'day' => '2-digit',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'hour12' => true,
                    'timeZone' => 'America/New_York',
                    'timeZoneName' => 'short',
                ],
                'ko' => '서기 2020년 6 16일 화요일 오전 12시 48분 20초 GMT-4',
                'en' => 'Tuesday, 6 16, 2020 Anno Domini, 12:48:20 AM EDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => 'numeric',
                    'month' => 'numeric',
                    'day' => '2-digit',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/New_York',
                    'timeZoneName' => 'short',
                ],
                'ko' => '서기 2020년 6 16일 화요일 오전 12시 48분 20초 GMT-4',
                'en' => 'Tuesday, 6 16, 2020 Anno Domini, 12:48:20 AM EDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => 'numeric',
                    'month' => 'numeric',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => '서기 2020년 6 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, 6 15, 2020 Anno Domini, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => '2-digit',
                    'month' => 'long',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, June 15, 20 Anno Domini, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => '2-digit',
                    'month' => 'short',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, Jun 15, 20 Anno Domini, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'long',
                    'year' => '2-digit',
                    'month' => 'narrow',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, J 15, 20 Anno Domini, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'long',
                    'era' => 'short',
                    'year' => '2-digit',
                    'month' => 'narrow',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => 'AD 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, J 15, 20 AD, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'narrow',
                    'era' => 'short',
                    'year' => '2-digit',
                    'month' => 'narrow',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => 'AD 20년 6월 15일 (월) 오후 9시 48분 20초 GMT-7',
                'en' => 'M, J 15, 20 AD, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'weekday' => 'short',
                    'era' => 'short',
                    'year' => '2-digit',
                    'month' => 'narrow',
                    'day' => '2-digit',
                    'hour' => '2-digit',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Los_Angeles',
                    'timeZoneName' => 'short',
                ],
                // This should be 09 instead of 9, but PHP's MessageFormatter
                // (perhaps through extension of icu4c's u_formatMessage) always
                // takes the locale's formatting into consideration and renders
                // this without the zero padding.
                'ko' => 'AD 20년 6월 15일 (월) 오후 9시 48분 20초 GMT-7',
                'en' => 'Mon, J 15, 20 AD, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 월요일',
                'en' => 'Monday, June 15, 2020',
            ],
            [
                'options' => [
                    'dateStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일',
                'en' => 'June 15, 2020',
            ],
            [
                'options' => [
                    'dateStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020. 6. 15.',
                'en' => 'Jun 15, 2020',
            ],
            [
                'options' => [
                    'dateStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '20. 6. 15.',
                'en' => '6/15/20',
            ],
            [
                'options' => [
                    'timeStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9시 48분 20초 미 태평양 하계 표준시',
                'en' => '9:48:20 PM Pacific Daylight Time',
            ],
            [
                'options' => [
                    'timeStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9시 48분 20초 GMT-7',
                'en' => '9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'timeStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9:48:20',
                'en' => '9:48:20 PM',
            ],
            [
                'options' => [
                    'timeStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9:48',
                'en' => '9:48 PM',
            ],
            [
                'options' => [
                    'dateStyle' => 'long',
                    'timeStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 오후 9시 48분 20초 미 태평양 하계 표준시',
                'en' => 'June 15, 2020 at 9:48:20 PM Pacific Daylight Time',
            ],
            [
                'options' => [
                    'dateStyle' => 'medium',
                    'timeStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020. 6. 15. 오후 9시 48분 20초 GMT-7',
                'en' => 'Jun 15, 2020, 9:48:20 PM PDT',
            ],
            [
                'options' => [
                    'dateStyle' => 'short',
                    'timeStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '20. 6. 15. 오후 9:48:20',
                'en' => '6/15/20, 9:48:20 PM',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 월요일 오후 9:48',
                'en' => 'Monday, June 15, 2020 at 9:48 PM',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'full',
                    'calendar' => 'buddhist',
                    'timeZone' => 'America/Denver',
                ],
                'ko' => 'AD 2020년 6월 15일 월요일 오후 10시 48분 20초 미 산지 하계 표준시',
                'en' => 'Monday, June 15, 2020 AD at 10:48:20 PM Mountain Daylight Time',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'full',
                    'numberingSystem' => 'hant',
                    'timeZone' => 'America/Denver',
                ],
                'ko' => '二千零二十년 六월 十五일 월요일 오후 十시 四十八분 二十초 미 산지 하계 표준시',
                'en' => 'Monday, June 十五, 二千零二十 at 十:四十八:二十 PM Mountain Daylight Time',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'full',
                    'hourCycle' => 'h23',
                    'timeZone' => 'America/Denver',
                ],
                // This should be 22 instead of 10 (because of the "h23" hourCycle,
                // but PHP's MessageFormatter (perhaps through extension of icu4c's
                // u_formatMessage) always takes the locale's formatting into
                // consideration and renders this without the 24-hour time.
                'ko' => '2020년 6월 15일 월요일 오후 10시 48분 20초 미 산지 하계 표준시',
                'en' => 'Monday, June 15, 2020 at 10:48:20 PM Mountain Daylight Time',
            ],
        ];
    }

    /**
     * @return array<array{dateStyle: string | null, timeStyle: string | null, additionalOptions: OptionsType}>
     */
    public function formatThrowsExceptionWhenDateStyleOrTimeStyleMixedWithStylePropertyProvider(): array
    {
        $styleProperties = [
            'era' => 'short',
            'year' => 'numeric',
            'month' => 'numeric',
            'weekday' => 'long',
            'day' => 'numeric',
            'hour' => '2-digit',
            'minute' => '2-digit',
            'second' => '2-digit',
        ];

        $tests = [];
        foreach ($styleProperties as $property => $value) {
            $tests[] = [
                'dateStyle' => 'full',
                'timeStyle' => null,
                'additionalOptions' => [$property => $value],
            ];
            $tests[] = [
                'dateStyle' => null,
                'timeStyle' => 'full',
                'additionalOptions' => [$property => $value],
            ];
        }

        /** @var array<array{dateStyle: string | null, timeStyle: string | null, additionalOptions: OptionsType}> */
        return $tests;
    }
}
