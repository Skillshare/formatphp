<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\NumberFormatOptions;
use FormatPHP\Test\TestCase;

use function constant;
use function json_encode;

/**
 * @psalm-import-type OptionsType from NumberFormatOptions
 */
class NumberFormatOptionsTest extends TestCase
{
    /**
     * @dataProvider publicConstantsProvider
     */
    public function testPublicConstants(string $constantName, string $expectedValue): void
    {
        $this->assertSame(constant($constantName), $expectedValue);
    }

    /**
     * @psalm-param OptionsType $options
     * @dataProvider constructorOptionsProvider
     */
    public function testConstructorOptions(array $options): void
    {
        $formatOptions = new NumberFormatOptions($options);

        $this->assertJsonStringEqualsJsonString(
            (string) json_encode((object) $options),
            (string) json_encode($formatOptions),
        );
    }

    /**
     * @return array<array{constantName: string, expectedValue: string}>
     */
    public function publicConstantsProvider(): array
    {
        $class = NumberFormatOptions::class;

        return [
            [
                'constantName' => "$class::COMPACT_DISPLAY_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::COMPACT_DISPLAY_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::CURRENCY_DISPLAY_CODE",
                'expectedValue' => 'code',
            ],
            [
                'constantName' => "$class::CURRENCY_DISPLAY_NAME",
                'expectedValue' => 'name',
            ],
            [
                'constantName' => "$class::CURRENCY_DISPLAY_NARROW_SYMBOL",
                'expectedValue' => 'narrowSymbol',
            ],
            [
                'constantName' => "$class::CURRENCY_DISPLAY_SYMBOL",
                'expectedValue' => 'symbol',
            ],
            [
                'constantName' => "$class::CURRENCY_SIGN_ACCOUNTING",
                'expectedValue' => 'accounting',
            ],
            [
                'constantName' => "$class::CURRENCY_SIGN_STANDARD",
                'expectedValue' => 'standard',
            ],
            [
                'constantName' => "$class::NOTATION_COMPACT",
                'expectedValue' => 'compact',
            ],
            [
                'constantName' => "$class::NOTATION_ENGINEERING",
                'expectedValue' => 'engineering',
            ],
            [
                'constantName' => "$class::NOTATION_SCIENTIFIC",
                'expectedValue' => 'scientific',
            ],
            [
                'constantName' => "$class::NOTATION_STANDARD",
                'expectedValue' => 'standard',
            ],
            [
                'constantName' => "$class::ROUNDING_PRIORITY_AUTO",
                'expectedValue' => 'auto',
            ],
            [
                'constantName' => "$class::ROUNDING_PRIORITY_LESS_PRECISION",
                'expectedValue' => 'lessPrecision',
            ],
            [
                'constantName' => "$class::ROUNDING_PRIORITY_MORE_PRECISION",
                'expectedValue' => 'morePrecision',
            ],
            [
                'constantName' => "$class::SIGN_DISPLAY_ALWAYS",
                'expectedValue' => 'always',
            ],
            [
                'constantName' => "$class::SIGN_DISPLAY_AUTO",
                'expectedValue' => 'auto',
            ],
            [
                'constantName' => "$class::SIGN_DISPLAY_EXCEPT_ZERO",
                'expectedValue' => 'exceptZero',
            ],
            [
                'constantName' => "$class::SIGN_DISPLAY_NEVER",
                'expectedValue' => 'never',
            ],
            [
                'constantName' => "$class::STYLE_CURRENCY",
                'expectedValue' => 'currency',
            ],
            [
                'constantName' => "$class::STYLE_DECIMAL",
                'expectedValue' => 'decimal',
            ],
            [
                'constantName' => "$class::STYLE_PERCENT",
                'expectedValue' => 'percent',
            ],
            [
                'constantName' => "$class::STYLE_UNIT",
                'expectedValue' => 'unit',
            ],
            [
                'constantName' => "$class::TRAILING_ZERO_DISPLAY_AUTO",
                'expectedValue' => 'auto',
            ],
            [
                'constantName' => "$class::TRAILING_ZERO_DISPLAY_STRIP_IF_INTEGER",
                'expectedValue' => 'stripIfInteger',
            ],
            [
                'constantName' => "$class::UNIT_DISPLAY_LONG",
                'expectedValue' => 'long',
            ],
            [
                'constantName' => "$class::UNIT_DISPLAY_NARROW",
                'expectedValue' => 'narrow',
            ],
            [
                'constantName' => "$class::UNIT_DISPLAY_SHORT",
                'expectedValue' => 'short',
            ],
            [
                'constantName' => "$class::USE_GROUPING_ALWAYS",
                'expectedValue' => 'always',
            ],
            [
                'constantName' => "$class::USE_GROUPING_AUTO",
                'expectedValue' => 'auto',
            ],
            [
                'constantName' => "$class::USE_GROUPING_FALSE",
                'expectedValue' => 'false',
            ],
            [
                'constantName' => "$class::USE_GROUPING_MIN2",
                'expectedValue' => 'min2',
            ],
            [
                'constantName' => "$class::USE_GROUPING_THOUSANDS",
                'expectedValue' => 'thousands',
            ],
            [
                'constantName' => "$class::USE_GROUPING_TRUE",
                'expectedValue' => 'true',
            ],
        ];
    }

    /**
     * @return array<array{options: OptionsType}>
     */
    public function constructorOptionsProvider(): array
    {
        return [
            ['options' => []],
            ['options' => ['compactDisplay' => 'long']],
            ['options' => ['currency' => 'EUR']],
            ['options' => ['currencyDisplay' => 'narrowSymbol']],
            ['options' => ['currencySign' => 'accounting']],
            ['options' => ['maximumFractionDigits' => 4]],
            ['options' => ['maximumSignificantDigits' => 10]],
            ['options' => ['minimumFractionDigits' => 2]],
            ['options' => ['minimumIntegerDigits' => 5]],
            ['options' => ['minimumSignificantDigits' => 2]],
            ['options' => ['notation' => 'scientific']],
            ['options' => ['numberingSystem' => 'arab']],
            ['options' => ['roundingPriority' => 'morePrecision']],
            ['options' => ['scale' => 100]],
            ['options' => ['signDisplay' => 'exceptZero']],
            ['options' => ['style' => 'unit', 'unit' => 'acre', 'unitDisplay' => 'long']],
            ['options' => ['useGrouping' => 'always']],
            ['options' => ['trailingZeroDisplay' => 'stripIfInteger']],
        ];
    }
}
