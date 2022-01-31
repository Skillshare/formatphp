<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatNumberException;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\NumberFormat;
use FormatPHP\Intl\NumberFormatOptions;
use FormatPHP\Test\TestCase;

/**
 * @psalm-import-type OptionsType from NumberFormatOptions
 */
class NumberFormatTest extends TestCase
{
    public function testThrowsWhenUnitPropertyEmptyForUnitStyle(): void
    {
        $locale = new Locale('en-US');
        $options = new NumberFormatOptions(['style' => 'unit']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The unit property must be provided when the style is "unit"');

        new NumberFormat($locale, $options);
    }

    public function testThrowsWhenCurrencyPropertyEmptyForCurrencyStyle(): void
    {
        $locale = new Locale('en-US');
        $options = new NumberFormatOptions(['style' => 'currency']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The currency property must be provided when the style is "currency"');

        new NumberFormat($locale, $options);
    }

    public function testThrowsWhenMinimumFractionDigitsIsGreaterThanMaximumFractionDigits(): void
    {
        $locale = new Locale('en-US');
        $options = new NumberFormatOptions([
            'minimumFractionDigits' => 5,
            'maximumFractionDigits' => 4,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minimumFractionDigits is greater than maximumFractionDigits');

        new NumberFormat($locale, $options);
    }

    public function testThrowsWhenMinimumSignificantDigitsIsGreaterThanMaximumSignificantDigits(): void
    {
        $locale = new Locale('en-US');
        $options = new NumberFormatOptions([
            'minimumSignificantDigits' => 5,
            'maximumSignificantDigits' => 4,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('minimumSignificantDigits is greater than maximumSignificantDigits');

        new NumberFormat($locale, $options);
    }

    public function testThrowsWhenUnableToFormatNumber(): void
    {
        $formatter = new NumberFormat(new Locale('en'), new NumberFormatOptions([
            'numberingSystem' => 'foobar',
        ]));

        $this->expectException(UnableToFormatNumberException::class);
        $this->expectExceptionMessage('Unable to format number "100" for locale "en"');

        $formatter->format(100);
    }

    /**
     * @param int | float $number
     *
     * @psalm-param OptionsType $options
     * @dataProvider formatProvider
     */
    public function testFormat($number, string $locale, array $options, string $expected, string $skeleton): void
    {
        $locale = new Locale($locale);
        $formatOptions = new NumberFormatOptions($options);
        $formatter = new NumberFormat($locale, $formatOptions);

        $this->assertSame($skeleton, $formatter->getSkeleton());
        $this->assertSame($expected, $formatter->format($number));
    }

    /**
     * @return array<array{number: int | float, locale: string, options: OptionsType, expected: string, skeleton: string}>
     */
    public function formatProvider(): array
    {
        return [
            [
                'number' => -5000,
                'locale' => 'de',
                'options' => [],
                'expected' => '-5.000',
                'skeleton' => '',
            ],
            [
                'number' => -5000,
                'locale' => 'de',
                'options' => ['notation' => 'standard'],
                'expected' => '-5.000',
                'skeleton' => '',
            ],
            [
                'number' => -5000,
                'locale' => 'de',
                'options' => ['signDisplay' => 'auto'],
                'expected' => '-5.000',
                'skeleton' => '',
            ],
            [
                'number' => -5000,
                'locale' => 'de',
                'options' => ['signDisplay' => 'never'],
                'expected' => '5.000',
                'skeleton' => 'sign-never',
            ],
            [
                'number' => 0,
                'locale' => 'de',
                'options' => ['signDisplay' => 'always'],
                'expected' => '+0',
                'skeleton' => 'sign-always',
            ],
            [
                'number' => -0,
                'locale' => 'de',
                'options' => ['signDisplay' => 'exceptZero'],
                'expected' => '0',
                'skeleton' => 'sign-except-zero',
            ],
            [
                'number' => -5000,
                'locale' => 'de',
                'options' => ['useGrouping' => false],
                'expected' => '-5000',
                'skeleton' => 'group-off',
            ],
            [
                'number' => 1234,
                'locale' => 'th-TH',
                'options' => ['numberingSystem' => 'thai'],
                'expected' => '๑,๒๓๔',
                'skeleton' => 'numbering-system/thai',
            ],
            [
                'number' => 42,
                'locale' => 'en',
                'options' => ['minimumIntegerDigits' => 4],
                'expected' => '0,042',
                'skeleton' => 'integer-width/*0000',
            ],
            [
                'number' => 42,
                'locale' => 'en',
                'options' => ['minimumFractionDigits' => 4],
                'expected' => '42.0000',
                'skeleton' => '.0000*',
            ],
            [
                'number' => 42.256,
                'locale' => 'en',
                'options' => ['maximumFractionDigits' => 1],
                'expected' => '42.3',
                'skeleton' => '.#',
            ],
            [
                'number' => 42.256789123,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 21,
                    'minimumFractionDigits' => 20,
                ],
                'expected' => '000,000,000,000,000,000,042.25678912300000000000',
                'skeleton' => 'integer-width/*000000000000000000000 .00000000000000000000*',
            ],
            [
                'number' => 42.25678912345678,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 2,
                    'maximumFractionDigits' => 10,
                ],
                'expected' => '42.2567891235',
                'skeleton' => 'integer-width/*00 .##########',
            ],
            [
                'number' => 42.256789123,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 3,
                    'minimumFractionDigits' => 4,
                    'maximumFractionDigits' => 5,
                ],
                'expected' => '042.25679',
                'skeleton' => 'integer-width/*000 .0000#',
            ],
            [
                'number' => 42.256789123,
                'locale' => 'en',
                'options' => [
                    'minimumFractionDigits' => 3,
                    'maximumFractionDigits' => 3,
                ],
                'expected' => '42.257',
                'skeleton' => '.000',
            ],
            [
                'number' => 42,
                'locale' => 'en',
                'options' => [
                    'minimumSignificantDigits' => 5,
                ],
                'expected' => '42.000',
                'skeleton' => '@@@@@*',
            ],
            [
                'number' => 123.456789,
                'locale' => 'en',
                'options' => [
                    'maximumSignificantDigits' => 5,
                ],
                'expected' => '123.46',
                'skeleton' => '@####',
            ],
            [
                'number' => 123.456789,
                'locale' => 'en',
                'options' => [
                    'minimumSignificantDigits' => 3,
                    'maximumSignificantDigits' => 3,
                ],
                'expected' => '123',
                'skeleton' => '@@@',
            ],
            [
                'number' => 123.456,
                'locale' => 'en',
                'options' => [
                    'minimumSignificantDigits' => 3,
                    'maximumSignificantDigits' => 4,
                ],
                'expected' => '123.5',
                'skeleton' => '@@@#',
            ],
            [
                'number' => 23.456,
                'locale' => 'en',
                'options' => [
                    'minimumSignificantDigits' => 3,
                    'maximumSignificantDigits' => 4,
                ],
                'expected' => '23.46',
                'skeleton' => '@@@#',
            ],
            [
                'number' => 23,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 3,
                    'minimumSignificantDigits' => 3,
                ],
                'expected' => '023.0',
                'skeleton' => 'integer-width/*000 @@@*',
            ],
            [
                'number' => 23.4567,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 3,
                    'maximumSignificantDigits' => 4,
                ],
                'expected' => '023.46',
                'skeleton' => 'integer-width/*000 @###',
            ],
            [
                'number' => 3.141,
                'locale' => 'en',
                'options' => [
                    'minimumFractionDigits' => 1,
                    'maximumFractionDigits' => 2,
                    'minimumSignificantDigits' => 1,
                    'maximumSignificantDigits' => 3,
                ],
                'expected' => '3.14',
                'skeleton' => '.0#/@##',
            ],
            [
                'number' => 3.141,
                'locale' => 'en',
                'options' => [
                    'minimumIntegerDigits' => 2,
                    'minimumFractionDigits' => 1,
                    'maximumFractionDigits' => 2,
                    'minimumSignificantDigits' => 1,
                    'maximumSignificantDigits' => 3,
                ],
                'expected' => '03.14',
                'skeleton' => 'integer-width/*00 .0#/@##',
            ],
            [
                'number' => 3.141,
                'locale' => 'en',
                'options' => [
                    'maximumFractionDigits' => 1,
                    'maximumSignificantDigits' => 3,
                    'roundingPriority' => 'lessPrecision',
                ],
                'expected' => '3.1',
                'skeleton' => '.#/@##s',
            ],
            [
                'number' => 3.141,
                'locale' => 'en',
                'options' => [
                    'maximumFractionDigits' => 1,
                    'maximumSignificantDigits' => 3,
                    'roundingPriority' => 'morePrecision',
                ],
                'expected' => '3.14',
                'skeleton' => '.#/@##r',
            ],
            [
                'number' => 8317,
                'locale' => 'en',
                'options' => [
                    'maximumFractionDigits' => 1,
                    'maximumSignificantDigits' => 3,
                    'roundingPriority' => 'lessPrecision',
                ],
                'expected' => '8,320',
                'skeleton' => '.#/@##s',
            ],
            [
                'number' => 8317,
                'locale' => 'en',
                'options' => [
                    'maximumFractionDigits' => 1,
                    'maximumSignificantDigits' => 3,
                    'roundingPriority' => 'morePrecision',
                ],
                'expected' => '8,317',
                'skeleton' => '.#/@##r',
            ],
            [
                'number' => 1234.456,
                'locale' => 'de',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'EUR',
                    'currencyDisplay' => 'symbol',
                ],
                'expected' => '1.234,46 €',
                'skeleton' => 'currency/EUR unit-width-short',
            ],
            [
                'number' => 1234.456,
                'locale' => 'de',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'EUR',
                    'currencyDisplay' => 'code',
                ],
                'expected' => '1.234,46 EUR',
                'skeleton' => 'currency/EUR unit-width-iso-code',
            ],
            [
                'number' => 1234.456,
                'locale' => 'de',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'EUR',
                    'currencyDisplay' => 'name',
                ],
                'expected' => '1.234,46 Euro',
                'skeleton' => 'currency/EUR unit-width-full-name',
            ],
            [
                'number' => 1234.456,
                'locale' => 'de',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'EUR',
                    'currencyDisplay' => 'narrowSymbol',
                ],
                'expected' => '1.234,46 €',
                'skeleton' => 'currency/EUR unit-width-narrow',
            ],
            [
                'number' => -1234.456,
                'locale' => 'en-US',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'currencyDisplay' => 'narrowSymbol',
                ],
                'expected' => '-$1,234.46',
                'skeleton' => 'currency/USD unit-width-narrow',
            ],
            [
                'number' => -1234.456,
                'locale' => 'en-US',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'currencyDisplay' => 'narrowSymbol',
                    'currencySign' => 'standard',
                ],
                'expected' => '-$1,234.46',
                'skeleton' => 'currency/USD unit-width-narrow',
            ],
            [
                'number' => -1234.456,
                'locale' => 'en-US',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'currencyDisplay' => 'narrowSymbol',
                    'currencySign' => 'accounting',
                ],
                'expected' => '($1,234.46)',
                'skeleton' => 'currency/USD unit-width-narrow sign-accounting',
            ],
            [
                'number' => 24,
                'locale' => 'es',
                'options' => [
                    'style' => 'unit',
                    'unit' => 'hour',
                ],
                'expected' => '24 h',
                'skeleton' => 'unit/hour .###',
            ],
            [
                'number' => 24,
                'locale' => 'es',
                'options' => [
                    'style' => 'unit',
                    'unit' => 'hour',
                    'unitDisplay' => 'long',
                ],
                'expected' => '24 horas',
                'skeleton' => 'unit/hour unit-width-full-name .###',
            ],
            [
                'number' => 24,
                'locale' => 'es',
                'options' => [
                    'style' => 'unit',
                    'unit' => 'hour',
                    'unitDisplay' => 'short',
                ],
                'expected' => '24 h',
                'skeleton' => 'unit/hour unit-width-short .###',
            ],
            [
                'number' => 24,
                'locale' => 'es',
                'options' => [
                    'style' => 'unit',
                    'unit' => 'hour',
                    'unitDisplay' => 'narrow',
                ],
                'expected' => '24h',
                'skeleton' => 'unit/hour unit-width-narrow .###',
            ],
            [
                'number' => 55,
                'locale' => 'en',
                'options' => [
                    'style' => 'unit',
                    'unit' => 'speed-mile-per-hour',
                    'unitDisplay' => 'short',
                ],
                'expected' => '55 mph',
                'skeleton' => 'measure-unit/speed-mile-per-hour unit-width-short .###',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'signDisplay' => 'always',
                ],
                'expected' => '+1,234.456',
                'skeleton' => 'sign-always',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'notation' => 'scientific',
                    'signDisplay' => 'always',
                ],
                'expected' => '1.234E+3',
                'skeleton' => 'scientific/sign-always .###',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'notation' => 'compact',
                ],
                'expected' => '1.2K',
                'skeleton' => 'compact-short',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'notation' => 'compact',
                    'compactDisplay' => 'short',
                ],
                'expected' => '1.2K',
                'skeleton' => 'compact-short',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'notation' => 'compact',
                    'compactDisplay' => 'long',
                ],
                'expected' => '1.2 thousand',
                'skeleton' => 'compact-long',
            ],
            [
                'number' => 1234.456,
                'locale' => 'en',
                'options' => [
                    'notation' => 'compact',
                    'compactDisplay' => 'long',
                    'signDisplay' => 'always',
                ],
                'expected' => '+1.2 thousand',
                'skeleton' => 'compact-long sign-always',
            ],
            [
                'number' => .25678,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                ],
                'expected' => '26%',
                'skeleton' => 'percent scale/100 precision-integer',
            ],
            [
                'number' => .25678,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                    'signDisplay' => 'always',
                ],
                'expected' => '+26%',
                'skeleton' => 'percent scale/100 sign-always precision-integer',
            ],
            [
                'number' => .25678,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                    'minimumIntegerDigits' => 3,
                ],
                'expected' => '026%',
                'skeleton' => 'percent scale/100 integer-width/*000 precision-integer',
            ],
            [
                'number' => .25678,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                    'minimumIntegerDigits' => 3,
                    'signDisplay' => 'exceptZero',
                ],
                'expected' => '+026%',
                'skeleton' => 'percent scale/100 sign-except-zero integer-width/*000 precision-integer',
            ],
            [
                'number' => .25678,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                    'minimumFractionDigits' => 1,
                    'maximumFractionDigits' => 2,
                    'maximumSignificantDigits' => 3,
                    'roundingPriority' => 'morePrecision',
                ],
                'expected' => '25.68%',
                'skeleton' => 'percent scale/100 .0#/@##r',
            ],
            [
                'number' => .3,
                'locale' => 'en',
                'options' => [
                    'scale' => 100,
                ],
                'expected' => '30',
                'skeleton' => 'scale/100',
            ],
            [
                'number' => .3,
                'locale' => 'en',
                'options' => [
                    'style' => 'percent',
                ],
                'expected' => '30%',
                'skeleton' => 'percent scale/100 precision-integer',
            ],
            [
                'number' => 1234.0056,
                'locale' => 'en',
                'options' => [
                    'minimumFractionDigits' => 2,
                    'maximumFractionDigits' => 2,
                    'maximumSignificantDigits' => 3,
                    'trailingZeroDisplay' => 'auto',
                ],
                'expected' => '1,230.00',
                'skeleton' => '.00/@##',
            ],
            [
                'number' => 1234.0056,
                'locale' => 'en',
                'options' => [
                    'minimumFractionDigits' => 2,
                    'maximumFractionDigits' => 2,
                    'maximumSignificantDigits' => 3,
                    'trailingZeroDisplay' => 'stripIfInteger',
                ],
                'expected' => '1,230',
                'skeleton' => '.00/@##/w',
            ],
            [
                'number' => 1234.0056,
                'locale' => 'en',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'minimumFractionDigits' => 2,
                    'maximumFractionDigits' => 2,
                    'maximumSignificantDigits' => 3,
                    'trailingZeroDisplay' => 'auto',
                ],
                'expected' => '$1,230.00',
                'skeleton' => 'currency/USD .00/@##',
            ],
            [
                'number' => 1234.0056,
                'locale' => 'en',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'minimumFractionDigits' => 2,
                    'maximumFractionDigits' => 2,
                    'maximumSignificantDigits' => 3,
                    'trailingZeroDisplay' => 'stripIfInteger',
                ],
                'expected' => '$1,230',
                'skeleton' => 'currency/USD .00/@##/w',
            ],
            [
                'number' => 1234.005,
                'locale' => 'en',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'trailingZeroDisplay' => 'auto',
                ],
                'expected' => '$1,234.00',
                'skeleton' => 'currency/USD',
            ],
            [
                'number' => 1234.005,
                'locale' => 'en',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'trailingZeroDisplay' => 'stripIfInteger',
                ],
                'expected' => '$1,234',
                'skeleton' => 'currency/USD precision-currency-standard/w',
            ],
            [
                'number' => 1234.567,
                'locale' => 'en',
                'options' => [
                    'style' => 'currency',
                    'currency' => 'USD',
                    'maximumFractionDigits' => 0,
                ],
                'expected' => '$1,235',
                'skeleton' => 'currency/USD precision-integer',
            ],
        ];
    }
}
