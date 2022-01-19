<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Test\TestCase;

use function constant;
use function json_encode;

/**
 * @psalm-import-type OptionsType from DateTimeFormatOptions
 */
class DateTimeFormatOptionsTest extends TestCase
{
    /**
     * @param string | int $expectedValue
     *
     * @dataProvider publicConstantsProvider
     */
    public function testPublicConstants(string $constantName, $expectedValue): void
    {
        $this->assertSame(constant($constantName), $expectedValue);
    }

    /**
     * @return array<array{constantName: string, expectedValue: string | int}>
     */
    public function publicConstantsProvider(): array
    {
        $class = DateTimeFormatOptions::class;

        return [
            [
                'constantName' => "$class::STYLE_FULL",
                'expectedValue' => 'full',
            ],
            [
                'constantName' => "$class::STYLE_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::STYLE_MEDIUM",
                'expectedValue' => 'medium',
            ],
            [
                'constantName' => "$class::STYLE_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::STYLE_NONE",
                'expectedValue' => 'none',
            ],
            [
                'constantName' => "$class::PERIOD_NARROW",
                'expectedValue' => 'narrow',
            ],
            [
                'constantName' => "$class::PERIOD_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::PERIOD_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::HOUR_H11",
                'expectedValue' => 'h11',
            ],
            [
                'constantName' => "$class::HOUR_H12",
                'expectedValue' => 'h12',
            ],
            [
                'constantName' => "$class::HOUR_H23",
                'expectedValue' => 'h23',
            ],
            [
                'constantName' => "$class::HOUR_H24",
                'expectedValue' => 'h24',
            ],
            [
                'constantName' => "$class::WIDTH_NUMERIC",
                'expectedValue' => 'numeric',
            ],
            [
                'constantName' => "$class::WIDTH_2DIGIT",
                'expectedValue' => '2-digit',
            ],
            [
                'constantName' => "$class::FRACTION_DIGITS_0",
                'expectedValue' => 0,
            ],
            [
                'constantName' => "$class::FRACTION_DIGITS_1",
                'expectedValue' => 1,
            ],
            [
                'constantName' => "$class::FRACTION_DIGITS_2",
                'expectedValue' => 2,
            ],
            [
                'constantName' => "$class::FRACTION_DIGITS_3",
                'expectedValue' => 3,
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_SHORT_OFFSET",
                'expectedValue' => 'shortOffset',
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_LONG_OFFSET",
                'expectedValue' => 'longOffset',
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_SHORT_GENERIC",
                'expectedValue' => 'shortGeneric',
            ],
            [
                'constantName' => "$class::TIME_ZONE_NAME_LONG_GENERIC",
                'expectedValue' => 'longGeneric',
            ],
        ];
    }

    /**
     * @psalm-param OptionsType $options
     * @dataProvider constructorOptionsProvider
     */
    public function testConstructorOptions(array $options): void
    {
        $formatOptions = new DateTimeFormatOptions($options);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode((object) $options),
            (string) json_encode($formatOptions),
        );
    }

    /**
     * @return array<array{options: OptionsType}>
     */
    public function constructorOptionsProvider(): array
    {
        return [
            ['options' => []],
            ['options' => ['dateStyle' => 'full']],
            ['options' => ['timeStyle' => 'long']],
            ['options' => ['calendar' => 'buddhist']],
            ['options' => ['dayPeriod' => 'short']],
            ['options' => ['numberingSystem' => 'finance']],
            ['options' => ['timeZone' => 'America/New_York']],
            ['options' => ['hour12' => true]],
            ['options' => ['hourCycle' => 'h23']],
            ['options' => ['weekday' => 'narrow']],
            ['options' => ['era' => 'long']],
            ['options' => ['year' => 'numeric']],
            ['options' => ['month' => 'short']],
            ['options' => ['day' => 'numeric']],
            ['options' => ['hour' => '2-digit']],
            ['options' => ['minute' => '2-digit']],
            ['options' => ['second' => '2-digit']],
            ['options' => ['fractionalSecondDigits' => 3]],
            ['options' => ['timeZoneName' => 'short']],
        ];
    }
}
