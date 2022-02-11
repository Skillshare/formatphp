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
use FormatPHP\Exception\UnableToFormatDisplayNameException;
use FormatPHP\Icu\MessageFormat\Parser;
use Locale as PhpLocale;
use MessageFormatter;

use function in_array;
use function mb_strtolower;
use function preg_match;
use function sprintf;
use function str_replace;
use function trim;

/**
 * Formats a locale-appropriate display name for properties of a given locale
 */
class DisplayNames implements DisplayNamesInterface
{
    private const CURRENCY_FORMAT = '{0, number, ::currency/%s %s precision-currency-standard/w}';

    private const STYLE_CURRENCY_WIDTH = [
        'long' => 'unit-width-full-name',
        'narrow' => 'unit-width-narrow',
        'short' => 'unit-width-iso-code',
    ];

    private const STYLE_CURRENCY_DEFAULT = 'unit-width-full-name';

    private string $localeName;
    private DisplayNamesOptions $options;

    private const VALID_TYPES = [
        DisplayNamesOptions::TYPE_CURRENCY,
        DisplayNamesOptions::TYPE_LANGUAGE,
        DisplayNamesOptions::TYPE_REGION,
        DisplayNamesOptions::TYPE_SCRIPT,
    ];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?LocaleInterface $locale = null, ?DisplayNamesOptions $options = null)
    {
        $this->options = $options ? clone $options : new DisplayNamesOptions();

        if ($this->options->type === null) {
            throw new InvalidArgumentException('The type property must be set');
        }

        if (!in_array($this->options->type, self::VALID_TYPES)) {
            throw new InvalidArgumentException(
                'The type property must be either "language", "region", "script", or "currency"',
            );
        }

        $locale = $locale ?? new Locale(PhpLocale::getDefault());
        $this->localeName = $locale->toString();
    }

    /**
     * @throws UnableToFormatDisplayNameException
     */
    public function of(string $code): ?string
    {
        return $this->doFormat($code);
    }

    /**
     * @throws UnableToFormatDisplayNameException
     */
    private function doFormat(string $code): ?string
    {
        $result = '';
        $changed = false;

        switch ($this->options->type) {
            case DisplayNamesOptions::TYPE_LANGUAGE:
                [$result, $changed] = $this->formatLanguage($code);

                break;
            case DisplayNamesOptions::TYPE_REGION:
                [$result, $changed] = $this->formatRegion($code);

                break;
            case DisplayNamesOptions::TYPE_SCRIPT:
                [$result, $changed] = $this->formatScript($code);

                break;
            case DisplayNamesOptions::TYPE_CURRENCY:
                [$result, $changed] = $this->formatCurrency($code);

                break;
        }

        // If the value hasn't changed, then we assume ICU couldn't find a
        // display name for it for this locale. Normally, we return the original
        // code as the fallback, but if fallback is "none," we return `null`.
        if (!$changed && $this->options->fallback === DisplayNamesOptions::FALLBACK_NONE) {
            return null;
        }

        return $result;
    }

    /**
     * @return array{string, bool}
     */
    private function formatLanguage(string $code): array
    {
        $result = PhpLocale::getDisplayName($code, $this->localeName);

        return [$result, $this->isChanged($result, $code)];
    }

    /**
     * @return array{string, bool}
     *
     * @throws UnableToFormatDisplayNameException
     */
    private function formatRegion(string $code): array
    {
        if (!preg_match('/^([A-Za-z]{2}|[0-9]{3})$/', $code)) {
            throw new UnableToFormatDisplayNameException(sprintf('Invalid value "%s" for option region', $code));
        }

        $result = PhpLocale::getDisplayRegion("-$code", $this->localeName);

        return [$result, $this->isChanged($result, $code)];
    }

    /**
     * @return array{string, bool}
     *
     * @throws UnableToFormatDisplayNameException
     */
    private function formatScript(string $code): array
    {
        if (!preg_match('/^([A-Za-z]{4})$/', $code)) {
            throw new UnableToFormatDisplayNameException(sprintf('Invalid value "%s" for option script', $code));
        }

        $result = PhpLocale::getDisplayScript("-$code", $this->localeName);

        return [$result, $this->isChanged($result, $code)];
    }

    /**
     * @return array{string, bool}
     *
     * @throws UnableToFormatDisplayNameException
     */
    private function formatCurrency(string $code): array
    {
        if (!preg_match('/^([A-Za-z]{3})$/', $code)) {
            throw new UnableToFormatDisplayNameException(sprintf('Invalid value "%s" for option currency', $code));
        }

        $pattern = sprintf(
            self::CURRENCY_FORMAT,
            $code,
            self::STYLE_CURRENCY_WIDTH[$this->options->style] ?? self::STYLE_CURRENCY_DEFAULT,
        );

        $formatter = new MessageFormatter($this->localeName, $pattern);

        $result = trim(str_replace('1', '', (string) $formatter->format([1])), "\x20\xC2\xA0");

        return [$result, $this->isCurrencyChanged($result, $code)];
    }

    /**
     * Returns `true` if the currency result is not the same as the original
     * currency code (i.e., it has changed)
     *
     * We apply special handling if currency style is "short." In this event,
     * if the currency code is "USD" and the style is "short," then some locales
     * will use "USD" as the short version. The normal `isChanged()` method will
     * return `false`, in this case. However, if it's a valid currency, we want
     * to track it as if it did change. To check whether it's a valid currency,
     * we will generate a long version of the currency name and compare it to
     * the $result provided. If they are different, then it's a valid currency,
     * and we can return `true` and track it as if it changed.
     *
     * If they are the same, then we can assume that ICU could not find a
     * display name for the currency code in this locale, so we will return
     * `false` so the calling code can decide how best to handle this condition.
     */
    private function isCurrencyChanged(string $result, string $code): bool
    {
        if ($this->options->style !== DisplayNamesOptions::STYLE_SHORT) {
            return $this->isChanged($result, $code);
        }

        $formatter = new MessageFormatter(
            $this->localeName,
            sprintf(self::CURRENCY_FORMAT, $code, self::STYLE_CURRENCY_DEFAULT),
        );

        $longResult = trim(str_replace('1', '', (string) $formatter->format([1])), "\x20\xC2\xA0");

        return $this->isChanged($longResult, $result);
    }

    /**
     * Returns `true` if the result is not the same as the original code
     * (i.e., it has changed)
     */
    private function isChanged(string $result, string $code): bool
    {
        return mb_strtolower($result, Parser::ENCODING) !== mb_strtolower($code, Parser::ENCODING);
    }
}
