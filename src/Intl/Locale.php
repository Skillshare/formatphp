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
     * @var array{language: string | null, script: string | null, region: string | null, variants: array<string>, keywords: array<string, string>, grandfathered: string | null}
     */
    private array $parsedLocale = [
        'language' => null,
        'script' => null,
        'region' => null,
        'variants' => [],
        'keywords' => [],
        'grandfathered' => null,
    ];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $locale, ?LocaleOptions $options = null)
    {
        if (strtolower($locale) === self::UNDEFINED_LOCALE) {
            $locale = PhpLocale::getDefault();
        }

        $canonicalizedLocale = PhpLocale::canonicalize($locale);

        /** @var array{language?: string, script?: string, region?: string, grandfathered?: string} $parsed */
        $parsed = PhpLocale::parseLocale($canonicalizedLocale);
        if ($parsed === []) {
            throw new InvalidArgumentException(sprintf('Unable to parse "%s" as a valid locale string', $locale));
        }

        /** @var array<string, string> $keywords */
        $keywords = PhpLocale::getKeywords($canonicalizedLocale) ?: [];
        $variants = [];

        foreach ($parsed as $key => $value) {
            if (!str_starts_with($key, 'variant')) {
                continue;
            }

            $variants[] = $value;
        }

        $this->parsedLocale['language'] = $parsed['language'] ?? self::UNDEFINED_LOCALE;
        $this->parsedLocale['script'] = $parsed['script'] ?? null;
        $this->parsedLocale['region'] = $parsed['region'] ?? null;
        $this->parsedLocale['grandfathered'] = $parsed['grandfathered'] ?? null;
        $this->parsedLocale['variants'] = $variants;
        $this->parsedLocale['keywords'] = $keywords;

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
        // Ensure return values conform to the expected values for ECMA-402.
        switch ($this->parsedLocale['keywords']['calendar'] ?? null) {
            case 'ethiopic-amete-alem':
                return 'ethioaa';
            case 'gregorian':
                return 'gregory';
        }

        return $this->parsedLocale['keywords']['calendar'] ?? null;
    }

    public function caseFirst(): ?string
    {
        // ECMA-402 expects the string "false," instead of "no."
        if (($this->parsedLocale['keywords']['colcasefirst'] ?? null) === 'no') {
            return 'false';
        }

        /** @var "upper" | "lower" | null */
        return $this->parsedLocale['keywords']['colcasefirst'] ?? null;
    }

    public function collation(): ?string
    {
        // Ensure return values conform to the expected values for ECMA-402.
        switch ($this->parsedLocale['keywords']['collation'] ?? null) {
            case 'dictionary':
                return 'dict';
            case 'gb2312han':
                return 'gb2312';
            case 'phonebook':
                return 'phonebk';
            case 'traditional':
                return 'trad';
        }

        return $this->parsedLocale['keywords']['collation'] ?? null;
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
        // ECMA-402 expects "traditio," instead of "traditional."
        if (($this->parsedLocale['keywords']['numbers'] ?? null) === 'traditional') {
            return 'traditio';
        }

        return $this->parsedLocale['keywords']['numbers'] ?? null;
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
        foreach ($this->parsedLocale['keywords'] as $keyword => $value) {
            $keyAndValue = $this->getUnicodeKeywordWithValue($keyword, $value);
            if ($keyAndValue[1] !== null) {
                $keywords .= "-$keyAndValue[0]-$keyAndValue[1]";
            }
        }

        if (strlen($keywords) > 0) {
            $locale .= '-u' . $keywords;
        }

        return $locale;
    }

    private function applyOptions(LocaleOptions $options): void
    {
        if ($options->calendar !== null) {
            $this->parsedLocale['keywords']['calendar'] = $options->calendar;
        }

        if ($options->caseFirst !== null) {
            $this->parsedLocale['keywords']['colcasefirst'] = $options->caseFirst;
        }

        if ($options->collation !== null) {
            $this->parsedLocale['keywords']['collation'] = $options->collation;
        }

        if ($options->hourCycle !== null) {
            $this->parsedLocale['keywords']['hours'] = $options->hourCycle;
        }

        if ($options->language !== null) {
            $this->parsedLocale['language'] = $options->language;
        }

        if ($options->numberingSystem !== null) {
            $this->parsedLocale['keywords']['numbers'] = $options->numberingSystem;
        }

        if ($options->numeric !== null) {
            $this->parsedLocale['keywords']['colnumeric'] = $options->numeric ? 'yes' : 'no';
        }

        if ($options->region !== null) {
            $this->parsedLocale['region'] = $options->region;
        }

        if ($options->script !== null) {
            $this->parsedLocale['script'] = $options->script;
        }
    }

    /**
     * @return array{0: string, 1: string | null}
     */
    private function getUnicodeKeywordWithValue(string $keyword, string $defaultValue): array
    {
        switch ($keyword) {
            case 'calendar':
                return ['ca', $this->calendar()];
            case 'colcasefirst':
                return ['kf', $this->caseFirst()];
            case 'collation':
                return ['co', $this->collation()];
            case 'hours':
                return ['hc', $this->hourCycle()];
            case 'numbers':
                return ['nu', $this->numberingSystem()];
            case 'colnumeric':
                return ['kn', $this->numericValue()];
        }

        return [$keyword, $defaultValue];
    }

    private function numericValue(): ?string
    {
        $colnumeric = $this->parsedLocale['keywords']['colnumeric'] ?? null;

        switch ($colnumeric) {
            case 'yes':
                return 'true';
            case 'no':
                return 'false';
        }

        return null;
    }
}
