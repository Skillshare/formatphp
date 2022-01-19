<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl\NumberFormat;

use FormatPHP\Intl\Locale;
use FormatPHP\Intl\NumberFormat;
use FormatPHP\Intl\NumberFormatOptions;
use FormatPHP\Test\TestCase;

/**
 * @psalm-import-type OptionsType from NumberFormatOptions
 */
class CurrencyTest extends TestCase
{
    private const NUMBER = 1234.567;

    private const LOCALES = [
        'en',
        'de',
        'zh',
    ];

    private const CURRENCIES = [
        'USD',
        'EUR',
        'CNY',
    ];

    private const CURRENCY_SIGNS = [
        'standard',
        'accounting',
    ];

    private const CURRENCY_DISPLAYS = [
        'symbol',
        'narrowSymbol',
        'code',
        'name',
    ];

    private const SIGN_DISPLAYS = [
        'auto',
        'always',
        'never',
        'exceptZero',
    ];

    private const NOTATIONS = [
        'engineering',
        'scientific',
        'compact',
        'standard',
    ];

    private const COMPACT_DISPLAYS = [
        'long',
        'short',
    ];

    /**
     * Rather than use a data provider and make thousands of snapshot files,
     * we'll build up one big snapshot file and assert it.
     *
     * The snapshot file for this test was created using Intl.NumberFormat in
     * NodeJS, in order to ensure parity. The script that generated the snapshot
     * is currency_test.js.
     */
    public function testFormat(): void
    {
        $tests = $this->currencyPermutationsWithLocales();
        $results = [];

        foreach ($tests as $test => $parameters) {
            $locale = new Locale($parameters['locale']);
            $formatOptions = new NumberFormatOptions($parameters['options']);
            $formatter = new NumberFormat($locale, $formatOptions);

            $results[$test] = [
                'result' => $formatter->format(self::NUMBER),
            ];
        }

        $this->assertMatchesJsonSnapshot($results);
    }

    public function testSkeleton(): void
    {
        // Use only one locale to test the skeleton output.
        $localeToTest = 'en';

        $tests = $this->currencyPermutationsWithLocales();
        $results = [];

        $localeTests = [];
        foreach ($tests as $test => $parameters) {
            if ($parameters['locale'] === $localeToTest) {
                $localeTests[$test] = $parameters['options'];
            }
        }

        foreach ($localeTests as $test => $options) {
            $locale = new Locale($localeToTest);
            $formatOptions = new NumberFormatOptions($options);
            $formatter = new NumberFormat($locale, $formatOptions);

            $results[$test] = [
                'skeleton' => $formatter->getSkeleton(),
            ];
        }

        $this->assertMatchesJsonSnapshot($results);
    }

    /**
     * @return array<array{locale: string, options: OptionsType}>
     */
    public function currencyPermutationsWithLocales(): array
    {
        $tests = [];

        foreach (self::LOCALES as $locale) {
            foreach (self::CURRENCIES as $currency) {
                foreach (self::CURRENCY_SIGNS as $currencySign) {
                    foreach (self::CURRENCY_DISPLAYS as $currencyDisplay) {
                        foreach (self::SIGN_DISPLAYS as $signDisplay) {
                            foreach (self::NOTATIONS as $notation) {
                                foreach (self::COMPACT_DISPLAYS as $compactDisplay) {
                                    $description = "$locale currency $currency "
                                        . "currencySign/$currencySign currencyDisplay/$currencyDisplay "
                                        . "signDisplay/$signDisplay notation/$notation "
                                        . "compactDisplay/$compactDisplay";
                                    $tests[$description] = [
                                        'locale' => $locale,
                                        'options' => [
                                            'style' => 'currency',
                                            'currency' => $currency,
                                            'currencySign' => $currencySign,
                                            'currencyDisplay' => $currencyDisplay,
                                            'notation' => $notation,
                                            'signDisplay' => $signDisplay,
                                            'compactDisplay' => $compactDisplay,
                                        ],
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        return $tests;
    }
}
