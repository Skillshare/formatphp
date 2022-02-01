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

use FormatPHP\Icu\MessageFormat\Parser\Type\OptionSerializer;
use JsonSerializable;

/**
 * @psalm-type CompactDisplayType = "short" | "long"
 * @psalm-type CurrencyDisplayType = "symbol" | "code" | "name" | "narrowSymbol"
 * @psalm-type CurrencySignType = "standard" | "accounting"
 * @psalm-type CurrencyType = non-empty-string
 * @psalm-type DigitsType = positive-int
 * @psalm-type FractionDigitsType = DigitsType | 0
 * @psalm-type NotationType = "standard" | "scientific" | "engineering" | "compact"
 * @psalm-type NumeralType = "adlm" | "ahom" | "arab" | "arabext" | "bali" | "beng" | "bhks" | "brah" | "cakm" | "cham" | "deva" | "fullwide" | "gong" | "gonm" | "gujr" | "guru" | "hanidec" | "hmng" | "java" | "kali" | "khmr" | "knda" | "lana" | "lanatham" | "laoo" | "latn" | "lepc" | "limb" | "mathbold" | "mathdbl" | "mathmono" | "mathsanb" | "mathsans" | "mlym" | "modi" | "mong" | "mroo" | "mtei" | "mymr" | "mymrshan" | "mymrtlng" | "newa" | "nkoo" | "olck" | "orya" | "osma" | "rohg" | "saur" | "shrd" | "sind" | "sora" | "sund" | "takr" | "talu" | "tamldec" | "telu" | "thai" | "tibt" | "tirh" | "vaii" | "wara" | "wcho" | non-empty-string
 * @psalm-type RoundingModeType = "ceil" | "floor" | "expand" | "trunc" | "halfCeil" | "halfFloor" | "halfExpand" | "halfTrunc" | "halfEven" | "halfOdd" | "unnecessary"
 * @psalm-type RoundingPriorityType = "auto" | "morePrecision" | "lessPrecision"
 * @psalm-type ScaleType = float
 * @psalm-type SignDisplayType = "auto" | "always" | "never" | "exceptZero" | "negative"
 * @psalm-type StyleType = "decimal" | "percent" | "currency" | "unit"
 * @psalm-type TrailingZeroDisplayType = "auto" | "stripIfInteger"
 * @psalm-type UnitDisplayType = "short" | "long" | "narrow"
 * @psalm-type UnitType = "acre" | "bit" | "byte" | "celsius" | "centimeter" | "day" | "degree" | "fahrenheit" | "fluid-ounce" | "foot" | "gallon" | "gigabit" | "gigabyte" | "gram" | "hectare" | "hour" | "inch" | "kilobit" | "kilobyte" | "kilogram" | "kilometer" | "liter" | "megabit" | "megabyte" | "meter" | "mile" | "mile-scandinavian" | "milliliter" | "millimeter" | "millisecond" | "minute" | "month" | "ounce" | "percent" | "petabyte" | "pound" | "second" | "stone" | "terabit" | "terabyte" | "week" | "yard" | "year" | non-empty-string
 * @psalm-type UseGroupingType = "always" | "auto" | "false" | "min2" | "thousands" | "true"
 * @psalm-type OptionsType = array{compactDisplay?: CompactDisplayType, currency?: CurrencyType, currencyDisplay?: CurrencyDisplayType, currencySign?: CurrencySignType, maximumFractionDigits?: FractionDigitsType, maximumSignificantDigits?: DigitsType, minimumFractionDigits?: FractionDigitsType, minimumIntegerDigits?: DigitsType, minimumSignificantDigits?: DigitsType, notation?: NotationType, numberingSystem?: NumeralType, roundingMode?: RoundingModeType, roundingPriority?: RoundingPriorityType, scale?: ScaleType, signDisplay?: SignDisplayType, style?: StyleType, trailingZeroDisplay?: TrailingZeroDisplayType, unit?: UnitType, unitDisplay?: UnitDisplayType, useGrouping?: UseGroupingType}
 */
class NumberFormatOptions implements JsonSerializable
{
    use OptionSerializer;

    public const STYLE_CURRENCY = 'currency';
    public const STYLE_DECIMAL = 'decimal';
    public const STYLE_PERCENT = 'percent';
    public const STYLE_UNIT = 'unit';

    public const CURRENCY_SIGN_ACCOUNTING = 'accounting';
    public const CURRENCY_SIGN_STANDARD = 'standard';

    public const CURRENCY_DISPLAY_CODE = 'code';
    public const CURRENCY_DISPLAY_NAME = 'name';
    public const CURRENCY_DISPLAY_NARROW_SYMBOL = 'narrowSymbol';
    public const CURRENCY_DISPLAY_SYMBOL = 'symbol';

    public const UNIT_DISPLAY_LONG = 'long';
    public const UNIT_DISPLAY_NARROW = 'narrow';
    public const UNIT_DISPLAY_SHORT = 'short';

    public const NOTATION_COMPACT = 'compact';
    public const NOTATION_ENGINEERING = 'engineering';
    public const NOTATION_SCIENTIFIC = 'scientific';
    public const NOTATION_STANDARD = 'standard';

    public const SIGN_DISPLAY_ALWAYS = 'always';
    public const SIGN_DISPLAY_AUTO = 'auto';
    public const SIGN_DISPLAY_EXCEPT_ZERO = 'exceptZero';
    public const SIGN_DISPLAY_NEVER = 'never';
    public const SIGN_DISPLAY_NEGATIVE = 'negative';

    public const COMPACT_DISPLAY_LONG = 'long';
    public const COMPACT_DISPLAY_SHORT = 'short';

    public const ROUNDING_PRIORITY_AUTO = 'auto';
    public const ROUNDING_PRIORITY_LESS_PRECISION = 'lessPrecision';
    public const ROUNDING_PRIORITY_MORE_PRECISION = 'morePrecision';

    public const ROUNDING_MODE_CEIL = 'ceil';
    public const ROUNDING_MODE_FLOOR = 'floor';
    public const ROUNDING_MODE_EXPAND = 'expand';
    public const ROUNDING_MODE_TRUNC = 'trunc';
    public const ROUNDING_MODE_HALF_CEIL = 'halfCeil';
    public const ROUNDING_MODE_HALF_FLOOR = 'halfFloor';
    public const ROUNDING_MODE_HALF_EXPAND = 'halfExpand';
    public const ROUNDING_MODE_HALF_TRUNC = 'halfTrunc';
    public const ROUNDING_MODE_HALF_EVEN = 'halfEven';
    public const ROUNDING_MODE_HALF_ODD = 'halfOdd';
    public const ROUNDING_MODE_UNNECESSARY = 'unnecessary';

    public const TRAILING_ZERO_DISPLAY_AUTO = 'auto';
    public const TRAILING_ZERO_DISPLAY_STRIP_IF_INTEGER = 'stripIfInteger';

    public const USE_GROUPING_ALWAYS = 'always';
    public const USE_GROUPING_AUTO = 'auto';
    public const USE_GROUPING_FALSE = 'false';
    public const USE_GROUPING_MIN2 = 'min2';
    public const USE_GROUPING_THOUSANDS = 'thousands';
    public const USE_GROUPING_TRUE = 'true';

    /**
     * Only used when `notation` is "compact".
     *
     * Takes either "short" (default) or "long".
     *
     * @var CompactDisplayType | null
     */
    public ?string $compactDisplay = null;

    /**
     * The currency to use in currency formatting.
     *
     * Possible values are the ISO 4217 currency codes, such as "USD" for the US
     * dollar, "EUR" for the euro, or "CNY" for the Chinese RMB — see the current
     * currency & funds code list.
     *
     * There is no default value; if the style is "currency", the currency
     * property must be provided.
     *
     * @link https://www.currency-iso.org/en/home/tables/table-a1.html current currency & funds code list
     *
     * @var CurrencyType | null
     */
    public ?string $currency = null;

    /**
     * How to display the currency in currency formatting.
     *
     * @var CurrencyDisplayType | null
     */
    public ?string $currencyDisplay = null;

    /**
     * In many locales, accounting format means to wrap the number with
     * parentheses instead of appending a minus sign. You can enable this
     * formatting by setting the currencySign option to "accounting". The
     * default value is "standard".
     *
     * @var CurrencySignType | null
     */
    public ?string $currencySign = null;

    /**
     * The maximum number of fraction digits to use.
     *
     * Possible values are from 0 to 20; the default for plain number formatting
     * is the larger of minimumFractionDigits and 3; the default for currency
     * formatting is the larger of minimumFractionDigits and the number of minor
     * unit digits provided by the ISO 4217 currency code list (2 if the list
     * doesn't provide that information); the default for percent formatting is
     * the larger of minimumFractionDigits and 0.
     *
     * @link https://www.currency-iso.org/en/home/tables/table-a1.html ISO 4217 currency code list
     *
     * @var FractionDigitsType | null
     */
    public ?int $maximumFractionDigits = null;

    /**
     * The maximum number of significant digits to use.
     *
     * Possible values are from 1 to 21; the default is 21.
     *
     * @var DigitsType | null
     */
    public ?int $maximumSignificantDigits = null;

    /**
     * The minimum number of fraction digits to use.
     *
     * Possible values are from 0 to 20; the default for plain number and percent
     * formatting is 0; the default for currency formatting is the number of
     * minor unit digits provided by the ISO 4217 currency code list (2 if the
     * list doesn't provide that information).
     *
     * @var FractionDigitsType | null
     */
    public ?int $minimumFractionDigits = null;

    /**
     * The minimum number of integer digits to use.
     *
     * Possible values are from 1 to 21; the default is 1.
     *
     * @var DigitsType | null
     */
    public ?int $minimumIntegerDigits = null;

    /**
     * The minimum number of significant digits to use.
     *
     * Possible values are from 1 to 21; the default is 1.
     *
     * @var DigitsType | null
     */
    public ?int $minimumSignificantDigits = null;

    /**
     * The formatting that should be displayed for the number.
     *
     * The default is "standard".
     *
     * @var NotationType | null
     */
    public ?string $notation = null;

    /**
     * Numbering system.
     *
     * @var NumeralType | null
     */
    public ?string $numberingSystem = null;

    /**
     * @var RoundingModeType | null
     */
    public ?string $roundingMode = null;

    /**
     * An indication declaring how to resolve conflicts between maximum fraction
     * digits and maximum significant digits.
     *
     * There are two modes, "morePrecision" and "lessPrecision":
     *
     * - "morePrecision", or relaxed, means to relax one of the two constraints
     *   (fraction digits or significant digits) in order to round the number to
     *   a higher level of precision.
     * - "lessPrecision", or strict, means to enforce both constraints, resulting
     *   in the number being rounded to a lower level of precision.
     *
     * The default settings for compact notation rounding are Max-Fraction = 0
     * (round to the nearest integer), Max-Significant = 2 (round to 2
     * significant digits), and priority RELAXED (choose the constraint that
     * results in more digits being displayed).
     *
     * Conflicting *minimum* fraction and significant digits are always resolved
     * in the direction that results in more trailing zeros.
     *
     * @link https://unicode-org.github.io/icu-docs/apidoc/released/icu4j/com/ibm/icu/number/NumberFormatter.RoundingPriority.html
     *
     * @var RoundingPriorityType | null
     */
    public ?string $roundingPriority = null;

    /**
     * The scale is a decimal number by which to multiply before formatting.
     *
     * For example, if scale is 100, then the number being formatted will be
     * multiplied by 100 before formatting.
     *
     * @var ScaleType | null
     */
    public ?float $scale = null;

    /**
     * When to display the sign for the number.
     *
     * This defaults to "auto".
     *
     * @var SignDisplayType | null
     */
    public ?string $signDisplay = null;

    /**
     * The formatting style to use.
     *
     * The default is "decimal".
     *
     * @var StyleType | null
     */
    public ?string $style = null;

    /**
     * @var TrailingZeroDisplayType | null
     */
    public ?string $trailingZeroDisplay = null;

    /**
     * The unit to use in unit formatting.
     *
     * Possible values are core unit identifiers, defined in UTS #35, Part 2,
     * Section 6. A subset of units from the full list was selected for use in
     * ECMAScript. Pairs of simple units can be concatenated with "-per-" to
     * make a compound unit. There is no default value; if the style is "unit",
     * the unit property must be provided.
     *
     * @link https://unicode.org/reports/tr35/tr35-general.html#Unit_Elements UTS #3, Part 2, Section 6
     * @link https://tc39.es/proposal-unified-intl-numberformat/section6/locales-currencies-tz_proposed_out.html#sec-issanctionedsimpleunitidentifier ECMAScript subset of CLDR units
     * @link https://github.com/unicode-org/cldr/blob/master/common/validity/unit.xml Full list of CLDR units
     *
     * @var UnitType | null
     */
    public ?string $unit = null;

    /**
     * The unit formatting style to use in unit formatting.
     *
     * The default is "short".
     *
     * @var UnitDisplayType | null
     */
    public ?string $unitDisplay = null;

    /**
     * Whether to use grouping separators, such as thousands separators or
     * thousand/lakh/crore separators.
     *
     * @var UseGroupingType | null
     */
    public ?string $useGrouping = null;

    /**
     * @psalm-param OptionsType $options
     */
    public function __construct(array $options = [])
    {
        $this->compactDisplay = $options['compactDisplay'] ?? null;
        $this->currency = $options['currency'] ?? null;
        $this->currencyDisplay = $options['currencyDisplay'] ?? null;
        $this->currencySign = $options['currencySign'] ?? null;
        $this->maximumFractionDigits = $options['maximumFractionDigits'] ?? null;
        $this->maximumSignificantDigits = $options['maximumSignificantDigits'] ?? null;
        $this->minimumFractionDigits = $options['minimumFractionDigits'] ?? null;
        $this->minimumIntegerDigits = $options['minimumIntegerDigits'] ?? null;
        $this->minimumSignificantDigits = $options['minimumSignificantDigits'] ?? null;
        $this->notation = $options['notation'] ?? null;
        $this->numberingSystem = $options['numberingSystem'] ?? null;
        $this->roundingMode = $options['roundingMode'] ?? null;
        $this->roundingPriority = $options['roundingPriority'] ?? null;
        $this->scale = $options['scale'] ?? null;
        $this->signDisplay = $options['signDisplay'] ?? null;
        $this->style = $options['style'] ?? null;
        $this->trailingZeroDisplay = $options['trailingZeroDisplay'] ?? null;
        $this->unit = $options['unit'] ?? null;
        $this->unitDisplay = $options['unitDisplay'] ?? null;
        $this->useGrouping = $options['useGrouping'] ?? null;
    }
}
