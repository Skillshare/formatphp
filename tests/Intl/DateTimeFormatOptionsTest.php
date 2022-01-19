<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Test\TestCase;

use function json_encode;

/**
 * @psalm-import-type OptionsType from DateTimeFormatOptions
 */
class DateTimeFormatOptionsTest extends TestCase
{
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
