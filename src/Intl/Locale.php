<?php

/**
 * This file is part of skillshare/formatphp
 *
 * skillshare/formatphp is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace FormatPHP\Intl;

use BadMethodCallException;
use FormatPHP\Exception\InvalidArgumentException;
use Locale as PhpLocale;

use function array_filter;
use function array_values;
use function implode;
use function is_bool;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strtolower;

/**
 * An implementation of an ECMA-402 locale identifier
 */
class Locale implements LocaleInterface
{
    private const UNDEFINED_LOCALE = 'und';

    /**
     * PHP's canonicalization (through ICU) converts calendar values to those
     * on the "left" of this map. For ECMA-402 compliance, we convert them back
     * to the values on the "right."
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/calendar
     */
    private const CALENDAR_MAP = [
        'ethiopic-amete-alem' => 'ethioaa',
        'gregorian' => 'gregory',
    ];

    /**
     * PHP's canonicalization (through ICU) converts colcasefirst values to those
     * on the "left" of this map. For ECMA-402 compliance, we convert them back
     * to the values on the "right."
     *
     * The "false" in this map is intentionally a string value and not boolean.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/caseFirst
     */
    private const CASE_FIRST_MAP = [
        'no' => 'false',
    ];

    /**
     * PHP's canonicalization (through ICU) converts collation values to those
     * on the "left" of this map. For ECMA-402 compliance, we convert them back
     * to the values on the "right."
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/collation
     */
    private const COLLATION_MAP = [
        'dictionary' => 'dict',
        'gb2312han' => 'gb2312',
        'phonebook' => 'phonebk',
        'traditional' => 'trad',
    ];

    /**
     * PHP's canonicalization (through ICU) converts numbers values to those
     * on the "left" of this map. For ECMA-402 compliance, we convert them back
     * to the values on the "right."
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/numberingSystem
     */
    private const NUMBERING_SYSTEM_MAP = [
        'traditional' => 'traditio',
    ];

    /**
     * PHP's canonicalization (through ICU) converts colnumeric values to those
     * on the "left" of this map. For ECMA-402 compliance, we convert them back
     * to the values on the "right."
     *
     * These are intentionally string values and not boolean.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/Locale/numeric
     */
    private const NUMERIC_MAP = [
        'yes' => 'true',
        'no' => 'false',
    ];

    /**
     * @var array{language: string | null, script: string | null, region: string | null, variants: array<string>,
     *     keywords: array<string, string>, grandfathered: string | null}
     */
    private array $parsedLocale;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $locale, ?LocaleOptions $options = null)
    {
        if (strtolower($locale) === self::UNDEFINED_LOCALE) {
            $locale = PhpLocale::getDefault();
        }

        $this->parsedLocale = $this->parseLocale($locale);

        if ($options !== null) {
            $this->applyOptions($options);
        }
    }

    public function baseName(): ?string
    {
        if (!$this->parsedLocale['language']) {
            return '';
        }

        $parts = [
            $this->parsedLocale['language'],
            $this->parsedLocale['script'],
            $this->parsedLocale['region'],
            ...array_values($this->parsedLocale['variants']),
        ];

        return implode('-', array_filter($parts));
    }

    public function calendar(): ?string
    {
        $calendar = $this->parsedLocale['keywords']['calendar'] ?? null;

        return self::CALENDAR_MAP[$calendar] ?? $calendar;
    }

    public function caseFirst(): ?string
    {
        $colcasefirst = $this->parsedLocale['keywords']['colcasefirst'] ?? null;

        /** @var "false" | "upper" | "lower" | null */
        return self::CASE_FIRST_MAP[$colcasefirst] ?? $colcasefirst;
    }

    public function collation(): ?string
    {
        $collation = $this->parsedLocale['keywords']['collation'] ?? null;

        return self::COLLATION_MAP[$collation] ?? $collation;
    }

    public function hourCycle(): ?string
    {
        /** @var "h11" | "h12" | "h23" | "h24" | null */
        return $this->parsedLocale['keywords']['hours'] ?? null;
    }

    public function language(): ?string
    {
        return $this->parsedLocale['language'] ?? null;
    }

    /**
     * @return no-return
     *
     * @throws BadMethodCallException
     */
    public function maximize(): LocaleInterface
    {
        throw new BadMethodCallException('Method not implemented');
    }

    /**
     * @return no-return
     *
     * @throws BadMethodCallException
     */
    public function minimize(): LocaleInterface
    {
        throw new BadMethodCallException('Method not implemented');
    }

    public function numberingSystem(): ?string
    {
        $numbers = $this->parsedLocale['keywords']['numbers'] ?? null;

        return self::NUMBERING_SYSTEM_MAP[$numbers] ?? $numbers;
    }

    public function numeric(): bool
    {
        return ($this->parsedLocale['keywords']['colnumeric'] ?? null) === 'yes';
    }

    public function region(): ?string
    {
        return $this->parsedLocale['region'] ?? null;
    }

    public function script(): ?string
    {
        return $this->parsedLocale['script'] ?? null;
    }

    public function toString(): string
    {
        $locale = (string) $this->baseName();

        $keywords = '';
        foreach ($this->parsedLocale['keywords'] as $keyword => $defaultValue) {
            [$key, $value] = $this->getUnicodeKeywordWithValue($keyword, $defaultValue);
            if ($value() !== null) {
                $keywords .= "-$key-" . (string) $value();
            }
        }

        if (strlen($keywords) > 0) {
            $locale .= '-u' . $keywords;
        }

        return $locale;
    }

    private function applyOptions(LocaleOptions $options): void
    {
        $baseProperties = [
            'language' => $options->language,
            'script' => $options->script,
            'region' => $options->region,
        ];

        $keywords = [
            'calendar' => $options->calendar,
            'colcasefirst' => $options->caseFirst,
            'collation' => $options->collation,
            'hours' => $options->hourCycle,
            'numbers' => $options->numberingSystem,
            'colnumeric' => is_bool($options->numeric) ? ($options->numeric ? 'yes' : 'no') : null,
        ];

        $isNotNull = fn (?string $value): bool => $value !== null;
        $baseProperties = array_filter($baseProperties, $isNotNull);
        $keywords = array_filter($keywords, $isNotNull);

        foreach ($baseProperties as $key => $value) {
            $this->parsedLocale[$key] = $value;
        }

        foreach ($keywords as $key => $value) {
            $this->parsedLocale['keywords'][$key] = (string) $value;
        }
    }

    /**
     * @return array{0: string, 1: callable}
     */
    private function getUnicodeKeywordWithValue(string $keyword, string $defaultValue): array
    {
        $keywordValueMap = [
            'calendar' => ['ca', fn (): ?string => $this->calendar()],
            'colcasefirst' => ['kf', fn (): ?string => $this->caseFirst()],
            'collation' => ['co', fn (): ?string => $this->collation()],
            'hours' => ['hc', fn (): ?string => $this->hourCycle()],
            'numbers' => ['nu', fn (): ?string => $this->numberingSystem()],
            'colnumeric' => ['kn', fn (): ?string => $this->numericValue()],
        ];

        return $keywordValueMap[$keyword] ?? [$keyword, fn (): string => $defaultValue];
    }

    private function numericValue(): ?string
    {
        $colnumeric = $this->parsedLocale['keywords']['colnumeric'] ?? null;

        return self::NUMERIC_MAP[$colnumeric] ?? $colnumeric;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @psalm-return array{language: string | null, script: string | null, region: string | null, variants: array<string>, keywords: array<string, string>, grandfathered: string | null}
     */
    private function parseLocale(string $locale): array
    {
        $canonicalizedLocale = PhpLocale::canonicalize($locale);

        /** @var array{language?: string, script?: string, region?: string, grandfathered?: string} $parsed */
        $parsed = PhpLocale::parseLocale($canonicalizedLocale);

        if ($parsed === []) {
            throw new InvalidArgumentException(sprintf('Unable to parse "%s" as a valid locale string', $locale));
        }

        $variants = [];
        foreach ($parsed as $key => $value) {
            if (!str_starts_with($key, 'variant')) {
                continue;
            }

            $variants[] = $value;
        }

        /** @var array<string, string> $keywords */
        $keywords = PhpLocale::getKeywords($canonicalizedLocale) ?: [];

        return [
            'language' => $parsed['language'] ?? self::UNDEFINED_LOCALE,
            'script' => $parsed['script'] ?? null,
            'region' => $parsed['region'] ?? null,
            'grandfathered' => $parsed['grandfathered'] ?? null,
            'variants' => $variants,
            'keywords' => $keywords,
        ];
    }
}
