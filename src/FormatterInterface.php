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

namespace FormatPHP;

use DateTimeInterface as PhpDateTimeInterface;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\DisplayNamesOptions;
use FormatPHP\Intl\NumberFormatOptions;

/**
 * FormatPHP formatter methods
 *
 * @psalm-type MessageDescriptorType = array{id?: string, defaultMessage?: string, description?: string}
 * @psalm-type MessageValuesType = array<array-key, float | int | string | callable(string):string>
 * @psalm-type DateTimeType = PhpDateTimeInterface | string | int
 * @psalm-type NumberType = int | float
 */
interface FormatterInterface
{
    /**
     * Returns a translated string for the given descriptor ID
     *
     * If the descriptor does not have an ID, we will use a combination of the
     * defaultMessage and description to create an ID.
     *
     * If we cannot find the given ID in the configured messages, we will use
     * the descriptor's defaultMessage, if provided.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatMessageException
     *
     * @psalm-param MessageDescriptorType $descriptor
     * @psalm-param MessageValuesType $values
     */
    public function formatMessage(array $descriptor, array $values = []): string;

    /**
     * Returns a date string formatted according to the locale of this formatter
     *
     * Additional options may be provided to configure how the date should be
     * formatted.
     *
     * @param DateTimeType | null $date
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDateTimeException
     */
    public function formatDate($date = null, ?DateTimeFormatOptions $options = null): string;

    /**
     * Returns a date string formatted according to the locale of this formatter,
     * but it differs from `formatDate()` by using "numeric" as the default value
     * for the `hour` and `minute` options
     *
     * Additional options may be provided to configure how the date should be
     * formatted.
     *
     * @param DateTimeType | null $date
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDateTimeException
     */
    public function formatTime($date = null, ?DateTimeFormatOptions $options = null): string;

    /**
     * Returns a number string formatted according to the locale of this formatter
     *
     * Additional options may be provided to configure how the number should be
     * formatted.
     *
     * @param NumberType $number
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatNumberException
     */
    public function formatNumber($number, ?NumberFormatOptions $options = null): string;

    /**
     * Returns a currency string formatted according to the locale of this formatter
     *
     * Additional options may be provided to configure how the currency should be
     * formatted.
     *
     * @link https://www.currency-iso.org/en/home/tables/table-a1.html ISO 4217 currency codes
     *
     * @param NumberType $value
     * @param non-empty-string $currencyCode An ISO 4217 currency code.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatNumberException
     */
    public function formatCurrency($value, string $currencyCode, ?NumberFormatOptions $options = null): string;

    /**
     * Returns a translated, localized display string for the given code
     *
     * Additional options may be provided to configure how the display name
     * should be formatted.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDisplayNameException
     */
    public function formatDisplayName(string $value, ?DisplayNamesOptions $options = null): ?string;
}
