<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\DisplayNamesOptions;
use FormatPHP\Test\TestCase;

use function constant;
use function json_encode;

/**
 * @psalm-import-type OptionsType from DisplayNamesOptions
 */
class DisplayNamesOptionsTest extends TestCase
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
        $class = DisplayNamesOptions::class;

        return [
            [
                'constantName' => "$class::FALLBACK_CODE",
                'expectedValue' => 'code',
            ],
            [
                'constantName' => "$class::FALLBACK_NONE",
                'expectedValue' => 'none',
            ],
            [
                'constantName' => "$class::LANGUAGE_DISPLAY_DIALECT",
                'expectedValue' => 'dialect',
            ],
            [
                'constantName' => "$class::LANGUAGE_DISPLAY_STANDARD",
                'expectedValue' => 'standard',
            ],
            [
                'constantName' => "$class::STYLE_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::STYLE_NARROW",
                'expectedValue' => 'narrow',
            ],
            [
                'constantName' => "$class::STYLE_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::TYPE_CALENDAR",
                'expectedValue' => 'calendar',
            ],
            [
                'constantName' => "$class::TYPE_CURRENCY",
                'expectedValue' => 'currency',
            ],
            [
                'constantName' => "$class::TYPE_DATE_TIME_FIELD",
                'expectedValue' => 'dateTimeField',
            ],
            [
                'constantName' => "$class::TYPE_LANGUAGE",
                'expectedValue' => 'language',
            ],
            [
                'constantName' => "$class::TYPE_REGION",
                'expectedValue' => 'region',
            ],
            [
                'constantName' => "$class::TYPE_SCRIPT",
                'expectedValue' => 'script',
            ],
        ];
    }

    /**
     * @psalm-param OptionsType $options
     * @dataProvider constructorOptionsProvider
     */
    public function testConstructorOptions(array $options): void
    {
        $formatOptions = new DisplayNamesOptions($options);

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
            ['options' => ['fallback' => 'code']],
            ['options' => ['languageDisplay' => 'standard']],
            ['options' => ['style' => 'long']],
            ['options' => ['type' => 'region']],
        ];
    }
}
