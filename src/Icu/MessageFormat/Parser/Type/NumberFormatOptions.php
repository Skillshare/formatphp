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

namespace FormatPHP\Icu\MessageFormat\Parser\Type;

use JsonSerializable;

/**
 * @psalm-type CompactDisplayType = "short" | "long"
 * @psalm-type CurrencyDisplayType = "symbol" | "code" | "name" | "narrowSymbol"
 * @psalm-type CurrencySignType = "standard" | "accounting"
 * @psalm-type LocaleMatcherType = "lookup" | "best fit"
 * @psalm-type NotationType = "standard" | "scientific" | "engineering" | "compact"
 * @psalm-type NumeralType = "arab" | "arabext" | "bali" | "beng" | "deva" | "fullwide" | "gujr" | "guru" | "hanidec" | "khmr" | "knda" | "laoo" | "latn" | "limb" | "mlym" | "mong" | "mymr" | "orya" | "tamldec" | "telu" | "thai" | "tibt"
 * @psalm-type RoundingPriorityType = "auto" | "morePrecision" | "lessPrecision"
 * @psalm-type SignDisplayType = "auto" | "always" | "never" | "exceptZero"
 * @psalm-type StyleType = "decimal" | "percent" | "currency" | "unit"
 * @psalm-type TrailingZeroDisplayType = "auto" | "stripIfInteger"
 * @psalm-type UnitDisplayType = "short" | "long" | "narrow"
 */
class NumberFormatOptions implements JsonSerializable
{
    use OptionSerializer;

    /**
     * @var CompactDisplayType | null
     */
    public ?string $compactDisplay = null;

    /**
     * @var NotationType | null
     */
    public ?string $notation = null;

    /**
     * @var SignDisplayType | null
     */
    public ?string $signDisplay = null;

    public ?string $unit = null;

    /**
     * @var UnitDisplayType | null
     */
    public ?string $unitDisplay = null;

    public ?int $minimumIntegerDigits = null;
    public ?int $minimumSignificantDigits = null;
    public ?int $maximumSignificantDigits = null;
    public ?int $minimumFractionDigits = null;
    public ?int $maximumFractionDigits = null;

    /**
     * @var LocaleMatcherType | null
     */
    public ?string $localeMatcher = null;

    /**
     * @var StyleType | null
     */
    public ?string $style = null;

    /**
     * @link https://www.currency-iso.org/en/home/tables/table-a1.html
     *
     * @var string | null Possible values are the ISO 4217 currency codes, such
     *     as "USD" for the US dollar, "EUR" for the euro, or "CNY" for the
     *     Chinese RMB.
     */
    public ?string $currency = null;

    /**
     * @var CurrencyDisplayType | null
     */
    public ?string $currencyDisplay = null;

    /**
     * @var CurrencySignType | null
     */
    public ?string $currencySign = null;

    /**
     * @var NumeralType | null
     */
    public ?string $numberingSystem = null;

    /**
     * @var TrailingZeroDisplayType | null
     */
    public ?string $trailingZeroDisplay = null;

    /**
     * @var RoundingPriorityType | null
     */
    public ?string $roundingPriority = null;

    public ?float $scale = null;

    public ?bool $useGrouping = null;
}
