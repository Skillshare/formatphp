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

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatNumberException;
use IntlException as PhpIntlException;
use Locale as PhpLocale;
use MessageFormatter as PhpMessageFormatter;
use Throwable;

use function array_filter;
use function implode;
use function in_array;
use function is_int;
use function sprintf;
use function str_pad;
use function str_repeat;
use function strpos;
use function substr;
use function trim;

/**
 * Formats a number for a given locale
 *
 * @psalm-import-type FractionDigitsType from NumberFormatOptions
 */
class NumberFormat implements NumberFormatInterface
{
    /**
     * Prefixes compiled from the CLDR data files
     *
     * @link https://github.com/unicode-org/cldr/blob/main/common/validity/unit.xml
     */
    private const UNIT_PREFIXES = [
        'acceleration',
        'angle',
        'area',
        'concentr',
        'consumption',
        'digital',
        'duration',
        'electric',
        'energy',
        'force',
        'frequency',
        'graphics',
        'length',
        'light',
        'mass',
        'power',
        'pressure',
        'speed',
        'temperature',
        'torque',
        'volume',
    ];

    private const SYMBOLS_ACCOUNTING_SIGN_DISPLAY = [
        NumberFormatOptions::SIGN_DISPLAY_ALWAYS => 'sign-accounting-always',
        NumberFormatOptions::SIGN_DISPLAY_EXCEPT_ZERO => 'sign-accounting-except-zero',
        NumberFormatOptions::SIGN_DISPLAY_NEGATIVE => 'sign-accounting-negative',
    ];

    private const SYMBOLS_COMPACT_DISPLAY = [
        NumberFormatOptions::COMPACT_DISPLAY_LONG => 'compact-long',
        NumberFormatOptions::COMPACT_DISPLAY_SHORT => 'compact-short',
    ];

    private const SYMBOLS_CURRENCY_DISPLAY = [
        NumberFormatOptions::CURRENCY_DISPLAY_CODE => 'unit-width-iso-code',
        NumberFormatOptions::CURRENCY_DISPLAY_NAME => 'unit-width-full-name',
        NumberFormatOptions::CURRENCY_DISPLAY_NARROW_SYMBOL => 'unit-width-narrow',
        NumberFormatOptions::CURRENCY_DISPLAY_SYMBOL => 'unit-width-short',
    ];

    private const SYMBOLS_ROUNDING_PRIORITY = [
        NumberFormatOptions::ROUNDING_PRIORITY_LESS_PRECISION => 's',
        NumberFormatOptions::ROUNDING_PRIORITY_MORE_PRECISION => 'r',
    ];

    private const SYMBOLS_SIGN_DISPLAY = [
        NumberFormatOptions::SIGN_DISPLAY_ALWAYS => 'sign-always',
        NumberFormatOptions::SIGN_DISPLAY_EXCEPT_ZERO => 'sign-except-zero',
        NumberFormatOptions::SIGN_DISPLAY_NEVER => 'sign-never',
        NumberFormatOptions::SIGN_DISPLAY_NEGATIVE => 'sign-negative',
    ];

    private const SYMBOLS_UNIT_WIDTH_DISPLAY_TYPE = [
        NumberFormatOptions::UNIT_DISPLAY_LONG => 'unit-width-full-name',
        NumberFormatOptions::UNIT_DISPLAY_NARROW => 'unit-width-narrow',
        NumberFormatOptions::UNIT_DISPLAY_SHORT => 'unit-width-short',
    ];

    private const SYMBOLS_USE_GROUPING = [
        NumberFormatOptions::USE_GROUPING_ALWAYS => 'group-on-aligned',
        NumberFormatOptions::USE_GROUPING_FALSE => 'group-off',
        NumberFormatOptions::USE_GROUPING_MIN2 => 'group-min2',
        NumberFormatOptions::USE_GROUPING_THOUSANDS => 'group-thousands',
        NumberFormatOptions::USE_GROUPING_TRUE => 'group-on-aligned',
    ];

    private const SYMBOLS_ROUNDING_MODE = [
        NumberFormatOptions::ROUNDING_MODE_CEIL => 'rounding-mode-ceiling',
        NumberFormatOptions::ROUNDING_MODE_FLOOR => 'rounding-mode-floor',
        NumberFormatOptions::ROUNDING_MODE_EXPAND => 'rounding-mode-up',
        NumberFormatOptions::ROUNDING_MODE_TRUNC => 'rounding-mode-down',
        NumberFormatOptions::ROUNDING_MODE_HALF_CEIL => 'rounding-mode-half-ceiling',
        NumberFormatOptions::ROUNDING_MODE_HALF_FLOOR => 'rounding-mode-half-floor',
        NumberFormatOptions::ROUNDING_MODE_HALF_EXPAND => 'rounding-mode-half-up',
        NumberFormatOptions::ROUNDING_MODE_HALF_TRUNC => 'rounding-mode-half-down',
        NumberFormatOptions::ROUNDING_MODE_HALF_EVEN => 'rounding-mode-half-even',
        NumberFormatOptions::ROUNDING_MODE_HALF_ODD => 'rounding-mode-half-odd',
        NumberFormatOptions::ROUNDING_MODE_UNNECESSARY => 'rounding-mode-unnecessary',
    ];

    private const SCI_ENG_NOTATION = [
        NumberFormatOptions::NOTATION_SCIENTIFIC,
        NumberFormatOptions::NOTATION_ENGINEERING,
    ];

    private string $originalLocaleName;
    private string $localeName;
    private string $skeleton;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?LocaleInterface $locale = null, ?NumberFormatOptions $options = null)
    {
        $locale = $locale ?? new Locale(PhpLocale::getDefault());
        $this->originalLocaleName = $locale->toString();
        $options = $options ? clone $options : new NumberFormatOptions();

        if ($options->numberingSystem !== null) {
            $locale = $locale->withNumberingSystem($options->numberingSystem);
        }

        $this->localeName = $locale->toString();
        $this->skeleton = $this->buildSkeleton($options);
    }

    /**
     * @inheritDoc
     */
    public function format($number): string
    {
        try {
            return $this->doFormat($number);
        } catch (Throwable $exception) {
            throw new UnableToFormatNumberException(
                sprintf(
                    'Unable to format number "%s" for locale "%s"',
                    $number,
                    $this->originalLocaleName,
                ),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception,
            );
        }
    }

    /**
     * Returns the number skeleton generated from the options provided
     */
    public function getSkeleton(): string
    {
        return $this->skeleton;
    }

    /**
     * @param int | float $number
     *
     * @throws PhpIntlException
     */
    private function doFormat($number): string
    {
        $pattern = "{0, number, ::$this->skeleton}";

        // PHP's `NumberFormatter::setPattern()` method leaves much to be desired,
        // so we will use the PHP `MessageFormatter` class, instead.
        $formatter = new PhpMessageFormatter($this->localeName, $pattern);

        return (string) $formatter->format([$number]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function buildSkeleton(NumberFormatOptions $options): string
    {
        $skeleton = [];

        $skeleton = $this->buildStyleSkeleton($skeleton, $options);
        $skeleton = $this->buildNotation($skeleton, $options);
        $skeleton = $this->buildUseGrouping($skeleton, $options);
        $skeleton = $this->buildRoundingMode($skeleton, $options);
        $skeleton = $this->buildNumberingSystem($skeleton, $options);
        $skeleton = $this->buildDigits($skeleton, $options);
        $skeleton = $this->buildScale($skeleton, $options);

        return implode(' ', array_filter($skeleton));
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function buildStyleSkeleton(array $skeleton, NumberFormatOptions $options): array
    {
        switch ($options->style) {
            case NumberFormatOptions::STYLE_CURRENCY:
                return $this->buildStyleCurrencySkeleton($skeleton, $options);
            case NumberFormatOptions::STYLE_UNIT:
                return $this->buildStyleUnitSkeleton($skeleton, $options);
            case NumberFormatOptions::STYLE_PERCENT:
                return $this->buildStylePercentSkeleton($skeleton, $options);
        }

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function buildStyleCurrencySkeleton(array $skeleton, NumberFormatOptions $options): array
    {
        if ($options->currency === null) {
            throw new InvalidArgumentException('The currency property must be provided when the style is "currency"');
        }

        $skeleton[] = 'currency/' . $options->currency;
        $skeleton[] = self::SYMBOLS_CURRENCY_DISPLAY[$options->currencyDisplay] ?? '';

        if ($options->currencySign === NumberFormatOptions::CURRENCY_SIGN_ACCOUNTING) {
            $skeleton[] = self::SYMBOLS_ACCOUNTING_SIGN_DISPLAY[$options->signDisplay] ?? 'sign-accounting';
        }

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function buildStyleUnitSkeleton(array $skeleton, NumberFormatOptions $options): array
    {
        if ($options->unit === null) {
            throw new InvalidArgumentException('The unit property must be provided when the style is "unit"');
        }

        // Determine whether to use the long skeleton or the concise skeleton,
        // based on whether the unit begins with one of the UNIT_PREFIXES.
        $stem = 'unit';
        $prefix = substr($options->unit, 0, (int) strpos($options->unit, '-'));
        if (in_array($prefix, self::UNIT_PREFIXES)) {
            $stem = 'measure-unit';
        }

        $skeleton[] = $stem . '/' . $options->unit;
        $skeleton[] = self::SYMBOLS_UNIT_WIDTH_DISPLAY_TYPE[$options->unitDisplay] ?? '';

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildStylePercentSkeleton(array $skeleton, NumberFormatOptions $options): array
    {
        $skeleton[] = 'percent';

        // By default, scale percentages by 100.
        if ($options->scale === null) {
            $skeleton[] = 'scale/100';
        }

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildNotation(array $skeleton, NumberFormatOptions $options): array
    {
        switch ($options->notation) {
            case NumberFormatOptions::NOTATION_SCIENTIFIC:
            case NumberFormatOptions::NOTATION_ENGINEERING:
                // @phpstan-ignore-next-line
                return $this->buildNotationSciEngSkeleton($options->notation, $skeleton, $options);
            case NumberFormatOptions::NOTATION_COMPACT:
                $skeleton = $this->buildNotationCompactSkeleton($skeleton, $options);

                break;
        }

        $skeleton[] = $this->getSignDisplay($options);

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildNotationSciEngSkeleton(string $type, array $skeleton, NumberFormatOptions $options): array
    {
        $notation = [$type, $this->getSignDisplay($options)];

        $skeleton[] = implode('/', array_filter($notation));

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildNotationCompactSkeleton(array $skeleton, NumberFormatOptions $options): array
    {
        $skeleton[] = self::SYMBOLS_COMPACT_DISPLAY[$options->compactDisplay] ?? 'compact-short';

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildUseGrouping(array $skeleton, NumberFormatOptions $options): array
    {
        $skeleton[] = self::SYMBOLS_USE_GROUPING[$options->useGrouping] ?? '';

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildRoundingMode(array $skeleton, NumberFormatOptions $options): array
    {
        $skeleton[] = self::SYMBOLS_ROUNDING_MODE[$options->roundingMode] ?? '';

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildNumberingSystem(array $skeleton, NumberFormatOptions $options): array
    {
        if ($options->numberingSystem !== null) {
            $skeleton[] = 'numbering-system/' . $options->numberingSystem;
        }

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     *
     * @throws InvalidArgumentException
     */
    private function buildDigits(array $skeleton, NumberFormatOptions $options): array
    {
        $skeleton[] = $this->getIntegerDigitsStem($options);

        $fractionDigitsStem = $this->getFractionDigitsStem($options);
        $significantDigitsStem = $this->getSignificantDigitsStem($options);

        $separator = ' ';
        $roundingPriority = '';

        if ($fractionDigitsStem && $significantDigitsStem) {
            $separator = '/';
            $roundingPriority = self::SYMBOLS_ROUNDING_PRIORITY[$options->roundingPriority] ?? '';
        }

        $stem = trim($fractionDigitsStem . $separator . $significantDigitsStem . $roundingPriority);

        // Determine whether to display trailing zeros on fractions, and if
        // this is currency but there's no precision stem, use the currency
        // precision stem to indicate removal of trailing zeros.
        if ($options->trailingZeroDisplay === NumberFormatOptions::TRAILING_ZERO_DISPLAY_STRIP_IF_INTEGER) {
            if ($stem !== '') {
                $stem .= '/w';
            } elseif ($options->style === NumberFormatOptions::STYLE_CURRENCY) {
                $skeleton[] = 'precision-currency-standard/w';
            }
        }

        $skeleton[] = $stem;

        // Special case for style: percent, which needs "precision-integer" if
        // it has no special handling for fraction or significant digits.
        if ($options->style === NumberFormatOptions::STYLE_PERCENT && !$fractionDigitsStem && !$significantDigitsStem) {
            $skeleton[] = 'precision-integer';
        }

        return $skeleton;
    }

    /**
     * @param string[] $skeleton
     *
     * @return string[]
     */
    private function buildScale(array $skeleton, NumberFormatOptions $options): array
    {
        if ($options->scale !== null) {
            $skeleton[] = 'scale/' . $options->scale;
        }

        return $skeleton;
    }

    private function getIntegerDigitsStem(NumberFormatOptions $options): string
    {
        if ($options->minimumIntegerDigits !== null) {
            return 'integer-width/*' . str_repeat('0', $options->minimumIntegerDigits);
        }

        return '';
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getFractionDigitsStem(NumberFormatOptions $options): string
    {
        if (
            $options->maximumFractionDigits !== null
            && (int) $options->minimumFractionDigits > $options->maximumFractionDigits
        ) {
            throw new InvalidArgumentException(
                'minimumFractionDigits is greater than maximumFractionDigits',
            );
        }

        $options->minimumFractionDigits = $this->getMinimumFractionDigits($options);
        $options->maximumFractionDigits = $this->getMaximumFractionDigits($options);

        if ($options->maximumFractionDigits === 0) {
            return 'precision-integer';
        }

        $stem = str_pad(
            str_repeat('0', (int) $options->minimumFractionDigits),
            (int) $options->maximumFractionDigits,
            '#',
        );

        if ($options->minimumFractionDigits !== null && $options->maximumFractionDigits === null) {
            $stem .= '*';
        }

        return $stem ? ".$stem" : '';
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getSignificantDigitsStem(NumberFormatOptions $options): string
    {
        if (
            $options->maximumSignificantDigits !== null
            && (int) $options->minimumSignificantDigits > $options->maximumSignificantDigits
        ) {
            throw new InvalidArgumentException(
                'minimumSignificantDigits is greater than maximumSignificantDigits',
            );
        }

        $stem = str_repeat('@', (int) $options->minimumSignificantDigits);

        if ($stem === '' && $options->maximumSignificantDigits !== null) {
            $stem = '@';
        }

        $stem = str_pad($stem, (int) $options->maximumSignificantDigits, '#');

        if ($options->minimumSignificantDigits !== null && $options->maximumSignificantDigits === null) {
            $stem .= '*';
        }

        return $stem;
    }

    private function getSignDisplay(NumberFormatOptions $options): string
    {
        // The sign display for accounting is handled in buildStyleCurrencySkeleton.
        if (
            $options->style === NumberFormatOptions::STYLE_CURRENCY
            || $options->currencySign === NumberFormatOptions::CURRENCY_SIGN_ACCOUNTING
        ) {
            return '';
        }

        return self::SYMBOLS_SIGN_DISPLAY[$options->signDisplay] ?? '';
    }

    /**
     * @return FractionDigitsType | null
     */
    private function getMinimumFractionDigits(NumberFormatOptions $options): ?int
    {
        $minimumFractionDigits = $options->minimumFractionDigits;

        if ($options->style === NumberFormatOptions::STYLE_CURRENCY) {
            return $minimumFractionDigits;
        }

        if (
            in_array($options->notation, self::SCI_ENG_NOTATION)
            || (
                $options->style === NumberFormatOptions::STYLE_UNIT
                && $options->notation !== NumberFormatOptions::NOTATION_COMPACT
            )
        ) {
            $minimumFractionDigits = $minimumFractionDigits ?? 0;
        }

        return $minimumFractionDigits;
    }

    /**
     * @return FractionDigitsType | null
     */
    private function getMaximumFractionDigits(NumberFormatOptions $options): ?int
    {
        $maximumFractionDigits = $options->maximumFractionDigits;

        if ($maximumFractionDigits !== null || $options->style === NumberFormatOptions::STYLE_CURRENCY) {
            return $maximumFractionDigits;
        }

        if (
            in_array($options->notation, self::SCI_ENG_NOTATION)
            || (
                $options->style === NumberFormatOptions::STYLE_UNIT
                && $options->notation !== NumberFormatOptions::NOTATION_COMPACT
            )
        ) {
            $maximumFractionDigits = $options->minimumFractionDigits > 3 ? $maximumFractionDigits : 3;
        }

        return $maximumFractionDigits;
    }
}
