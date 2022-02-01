<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl\NumberFormat;

use FormatPHP\Intl\Locale;
use FormatPHP\Intl\NumberFormat;
use FormatPHP\Intl\NumberFormatOptions;
use FormatPHP\Test\TestCase;

/**
 * The test cases in this test class were taken from the FormatJS
 * intl-numberformat package.
 *
 * @link https://github.com/formatjs/formatjs/blob/78d35580ccd31f143bc1f3884326ecfe234c6929/packages/intl-numberformat/tests/percent.test.ts
 *
 * @psalm-import-type OptionsType from NumberFormatOptions
 */
class PercentTest extends TestCase
{
    private const NUMBER = 10000;

    private const LOCALES = [
        'en',
        'en-GB',
        'da',
        'de',
        'es',
        'fr',
        'id',
        'it',
        'ja',
        'ko',
        'ms',
        'nl',
        'pl',
        'pt',
        'ru',
        'sv',
        'th',
        'tr',
        'uk',
        'zh',
        'en-BS',
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
     * is percent_test.js.
     */
    public function testFormat(): void
    {
        $tests = $this->percentPermutationsWithLocales();
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

        $tests = $this->percentPermutationsWithLocales();
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
    public function percentPermutationsWithLocales(): array
    {
        $tests = [];

        foreach (self::LOCALES as $locale) {
            foreach (self::SIGN_DISPLAYS as $signDisplay) {
                foreach (self::NOTATIONS as $notation) {
                    foreach (self::COMPACT_DISPLAYS as $compactDisplay) {
                        $description = "$locale percent signDisplay/$signDisplay "
                            . "notation/$notation compactDisplay/$compactDisplay";
                        $tests[$description] = [
                            'locale' => $locale,
                            'options' => [
                                'style' => 'percent',
                                'notation' => $notation,
                                'signDisplay' => $signDisplay,
                                'compactDisplay' => $compactDisplay,
                            ],
                        ];
                    }
                }
            }
        }

        return $tests;
    }
}
