<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use DateTimeImmutable;
use FormatPHP\Exception\InvalidArgumentException;
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
    public function testFormat(array $options, string $ko, string $en, string $skeleton): void
    {
        $koLocale = new Locale('ko');
        $enLocale = new Locale('en');
        $formatOptions = new DateTimeFormatOptions($options);

        $koFormatter = new DateTimeFormat($koLocale, $formatOptions);
        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        $this->assertSame($en, $enFormatter->format($date));
        $this->assertSame($ko, $koFormatter->format($date));

        $this->assertSame($skeleton, $enFormatter->getSkeleton());
        $this->assertSame($skeleton, $koFormatter->getSkeleton());

        // We change the default timezone within the SUT, so let's assert
        // that is changed back to the value we set in this test's setUp().
        $this->assertSame(self::TEST_TIMEZONE, date_default_timezone_get());
    }

    public function testUnknownTimeZoneThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown time zone "America/Foobar"');

        new DateTimeFormat(new Locale('en'), new DateTimeFormatOptions([
            'timeZone' => 'America/Foobar',
        ]));
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
        $enLocale = new Locale('en-u-hc-h11');
        $enLocale = $enLocale->withHourCycle('h23');

        $formatOptions = new DateTimeFormatOptions([
            'hour' => '2-digit',
            // Specify both hourCycle and hour12 to show that hour12 overrides hourCycle.
            'hour12' => true,
            'hourCycle' => 'h24',
            'minute' => '2-digit',
            'second' => '2-digit',
            'timeZone' => 'America/Denver',
        ]);

        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        $this->assertSame('10:48:20 PM', $enFormatter->format($date));

        $this->assertSame('hhmmss', $enFormatter->getSkeleton());
        $this->assertSame('en-u-hc-h12', $enFormatter->getEvaluatedLocale());
    }

    public function testEvaluatedLocaleWithNoOptions(): void
    {
        $locale = new Locale('en-US');
        $formatter = new DateTimeFormat($locale);

        // We automatically look up the locale's preferred hour cycle and add it.
        $this->assertSame('en-US-u-hc-h12', $formatter->getEvaluatedLocale());
    }

    public function testEvaluatedLocaleWithOptionsUsingHour12(): void
    {
        $locale = new Locale('en-US');
        $formatter = new DateTimeFormat($locale, new DateTimeFormatOptions([
            'hour12' => false,
            'hourCycle' => 'h12',
            'calendar' => 'islamic',
            'numberingSystem' => 'thai',
        ]));

        $this->assertSame('en-US-u-ca-islamic-nu-thai-hc-h23', $formatter->getEvaluatedLocale());
    }

    public function testEvaluatedLocaleWithOptionsUsingHourCycle(): void
    {
        $locale = new Locale('en-US');
        $formatter = new DateTimeFormat($locale, new DateTimeFormatOptions([
            'hourCycle' => 'h24',
            'calendar' => 'coptic',
            'numberingSystem' => 'mathmono',
        ]));

        $this->assertSame('en-US-u-ca-coptic-nu-mathmono-hc-h24', $formatter->getEvaluatedLocale());
    }

    public function testUseHourCycleFromLocale(): void
    {
        $enLocale = new Locale('en');
        $enLocale = $enLocale->withHourCycle('h23');

        $formatOptions = new DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
            'second' => '2-digit',
            'timeZone' => 'America/Denver',
        ]);

        $enFormatter = new DateTimeFormat($enLocale, $formatOptions);
        $date = new DateTimeImmutable('@' . self::TS);

        $this->assertSame('22:48:20', $enFormatter->format($date));

        $this->assertSame('HHmmss', $enFormatter->getSkeleton());
        $this->assertSame('en-u-hc-h23', $enFormatter->getEvaluatedLocale());
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
                'skeleton' => 'GGGGyyyyMdEEEEhmszzzz',
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
                'skeleton' => 'GGGGyyyyMdEEEEhmsz',
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
                'skeleton' => 'GGGGyyyyMddEEEEhmsz',
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
                'skeleton' => 'GGGGyyyyMddEEEEhmsz',
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
                'ko' => '서기 2020년 6 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, 6 15, 2020 Anno Domini, 9:48:20 PM PDT',
                'skeleton' => 'GGGGyyyyMddEEEEhhmsz',
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
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, June 15, 20 Anno Domini at 9:48:20 PM PDT',
                'skeleton' => 'GGGGyyMMMMddEEEEhhmsz',
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
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, Jun 15, 20 Anno Domini, 9:48:20 PM PDT',
                'skeleton' => 'GGGGyyMMMddEEEEhhmsz',
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
                'ko' => '서기 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, J 15, 20 Anno Domini, 9:48:20 PM PDT',
                'skeleton' => 'GGGGyyMMMMMddEEEEhhmsz',
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
                'ko' => 'AD 20년 6월 15일 월요일 오후 9시 48분 20초 GMT-7',
                'en' => 'Monday, J 15, 20 AD, 9:48:20 PM PDT',
                'skeleton' => 'GyyMMMMMddEEEEhhmsz',
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
                'ko' => 'AD 20년 6월 15일 (월) 오후 9시 48분 20초 GMT-7',
                'en' => 'M, J 15, 20 AD, 9:48:20 PM PDT',
                'skeleton' => 'GyyMMMMMddEEEEEhhmsz',
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
                'ko' => 'AD 20년 6월 15일 (월) 오후 9시 48분 20초 GMT-7',
                'en' => 'Mon, J 15, 20 AD, 9:48:20 PM PDT',
                'skeleton' => 'GyyMMMMMddEhhmsz',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 월요일',
                'en' => 'Monday, June 15, 2020',
                'skeleton' => 'EEEEMMMMdy',
            ],
            [
                'options' => [
                    'dateStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일',
                'en' => 'June 15, 2020',
                'skeleton' => 'MMMMdy',
            ],
            [
                'options' => [
                    'dateStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020. 6. 15.',
                'en' => 'Jun 15, 2020',
                'skeleton' => 'MMMdy',
            ],
            [
                'options' => [
                    'dateStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '20. 6. 15.',
                'en' => '6/15/20',
                'skeleton' => 'Mdyy',
            ],
            [
                'options' => [
                    'timeStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9시 48분 20초 미 태평양 하계 표준시',
                'en' => '9:48:20 PM Pacific Daylight Time',
                'skeleton' => 'hmmssazzzz',
            ],
            [
                'options' => [
                    'timeStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9시 48분 20초 GMT-7',
                'en' => '9:48:20 PM PDT',
                'skeleton' => 'hmmssaz',
            ],
            [
                'options' => [
                    'timeStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9:48:20',
                'en' => '9:48:20 PM',
                'skeleton' => 'hmmssa',
            ],
            [
                'options' => [
                    'timeStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '오후 9:48',
                'en' => '9:48 PM',
                'skeleton' => 'hmma',
            ],
            [
                'options' => [
                    'dateStyle' => 'long',
                    'timeStyle' => 'full',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 오후 9시 48분 20초 미 태평양 하계 표준시',
                'en' => 'June 15, 2020 at 9:48:20 PM Pacific Daylight Time',
                'skeleton' => 'MMMMdyhmmssazzzz',
            ],
            [
                'options' => [
                    'dateStyle' => 'medium',
                    'timeStyle' => 'long',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020. 6. 15. 오후 9시 48분 20초 GMT-7',
                'en' => 'Jun 15, 2020, 9:48:20 PM PDT',
                'skeleton' => 'MMMdyhmmssaz',
            ],
            [
                'options' => [
                    'dateStyle' => 'short',
                    'timeStyle' => 'medium',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '20. 6. 15. 오후 9:48:20',
                'en' => '6/15/20, 9:48:20 PM',
                'skeleton' => 'Mdyyhmmssa',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'short',
                    'timeZone' => 'America/Los_Angeles',
                ],
                'ko' => '2020년 6월 15일 월요일 오후 9:48',
                'en' => 'Monday, June 15, 2020 at 9:48 PM',
                'skeleton' => 'EEEEMMMMdyhmma',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'full',
                    'calendar' => 'buddhist',
                    'timeZone' => 'America/Denver',
                ],
                'ko' => '불기 2563년 6월 15일 월요일 오후 10시 48분 20초 미 산지 하계 표준시',
                'en' => 'Monday, June 15, 2563 BE at 10:48:20 PM Mountain Daylight Time',
                'skeleton' => 'EEEEMMMMdyhmmssazzzz',
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
                'skeleton' => 'EEEEMMMMdyhmmssazzzz',
            ],
            [
                'options' => [
                    'dateStyle' => 'full',
                    'timeStyle' => 'full',
                    'hourCycle' => 'h23',
                    'timeZone' => 'America/Denver',
                ],
                'ko' => '2020년 6월 15일 월요일 오후 10시 48분 20초 미 산지 하계 표준시',
                'en' => 'Monday, June 15, 2020 at 10:48:20 PM Mountain Daylight Time',
                'skeleton' => 'EEEEMMMMdyhmmssazzzz',
            ],
            [
                'options' => [
                    'dayPeriod' => 'long',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Chicago',
                ],
                'ko' => '밤 11:48:20',
                'en' => '11:48:20 at night',
                'skeleton' => 'hmsB',
            ],
            [
                'options' => [
                    'dayPeriod' => 'short',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Chicago',
                ],
                'ko' => '오후 11:48:20',
                'en' => '11:48:20 PM',
                'skeleton' => 'hmsb',
            ],
            [
                'options' => [
                    'dayPeriod' => 'narrow',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'America/Chicago',
                ],
                'ko' => 'PM 11:48:20',
                'en' => '11:48:20 p',
                'skeleton' => 'hmsbbbbb',
            ],
            [
                'options' => [
                    'dayPeriod' => 'long',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'Asia/Kolkata',
                ],
                'ko' => '오전 10:18:20',
                'en' => '10:18:20 in the morning',
                'skeleton' => 'hmsB',
            ],
            [
                'options' => [
                    'dayPeriod' => 'short',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'Asia/Kolkata',
                ],
                'ko' => '오전 10:18:20',
                'en' => '10:18:20 AM',
                'skeleton' => 'hmsb',
            ],
            [
                'options' => [
                    'dayPeriod' => 'narrow',
                    'hour' => 'numeric',
                    'minute' => 'numeric',
                    'second' => 'numeric',
                    'timeZone' => 'Asia/Kolkata',
                ],
                'ko' => 'AM 10:18:20',
                'en' => '10:18:20 a',
                'skeleton' => 'hmsbbbbb',
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
