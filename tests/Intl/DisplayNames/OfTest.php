<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl\DisplayNames;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Intl\DisplayNames;
use FormatPHP\Intl\DisplayNamesOptions;
use FormatPHP\Intl\Locale;
use FormatPHP\Test\TestCase;

/**
 * @psalm-import-type OptionsType from DisplayNamesOptions
 */
class OfTest extends TestCase
{
    private const LOCALES = [
        'en-US',
        'de-DE',
        'ko-KR',
    ];

    private const STYLE = [
        'long',
        'narrow',
        'short',
    ];

    private const TYPE = [
        'currency' => ['USD', 'EUR', 'FOO'],
        'language' => ['en-Latn-US', 'en-GB', 'pt-BR', 'foobar'],
        'region' => ['US', 'BR', '419', 'YY', '123'],
        'script' => ['Latn', 'Arab', 'Foob'],
        // 'calendar' => [], // These values have no support in JS.
        // 'dateTimeField' => [],
    ];

    private const LANGUAGE_DISPLAY = [
        // 'dialect', // PHP isn't able to support "dialect"
        'standard',
    ];

    private const FALLBACK = [
        'code',
        'none',
    ];

    public function testThrowsExceptionWhenTypeNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type property must be set');

        new DisplayNames();
    }

    public function testThrowsExceptionForInvalidTypes(): void
    {
        $options = new DisplayNamesOptions();
        $options->type = 'calendar';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The type property must be either "language", "region", "script", or "currency"');

        new DisplayNames(null, $options);
    }

    /**
     * Rather than use a data provider and make thousands of snapshot files,
     * we'll build up one big snapshot file and assert it.
     *
     * The snapshot file for this test was created using Intl.DisplayNames in
     * NodeJS, in order to ensure parity. The script that generated the snapshot
     * is displayNames_test.js.
     */
    public function testOf(): void
    {
        $tests = $this->displayNamesPermutationsWithLocales();
        $results = [];

        foreach ($tests as $test => $parameters) {
            $locale = new Locale($parameters['locale']);
            $formatOptions = new DisplayNamesOptions($parameters['options']);
            $formatter = new DisplayNames($locale, $formatOptions);

            $results[$test] = [
                'result' => $formatter->of($parameters['testValue']),
                'options' => $parameters['options'],
            ];
        }

        $this->assertMatchesJsonSnapshot($results);
    }

    /**
     * @return array<array{locale: string, testValue: string, options: OptionsType}>
     */
    public function displayNamesPermutationsWithLocales(): array
    {
        $tests = [];

        foreach (self::LOCALES as $locale) {
            foreach (self::STYLE as $style) {
                foreach (self::LANGUAGE_DISPLAY as $languageDisplay) {
                    foreach (self::FALLBACK as $fallback) {
                        foreach (self::TYPE as $type => $testValues) {
                            foreach ($testValues as $testValue) {
                                $description = "$locale style/$style "
                                    . "languageDisplay/$languageDisplay "
                                    . "fallback/$fallback "
                                    . "type/$type "
                                    . "of($testValue)";
                                $tests[$description] = [
                                    'locale' => $locale,
                                    'testValue' => $testValue,
                                    'options' => [
                                        'style' => $style,
                                        'languageDisplay' => $languageDisplay,
                                        'fallback' => $fallback,
                                        'type' => $type,
                                    ],
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $tests;
    }
}
