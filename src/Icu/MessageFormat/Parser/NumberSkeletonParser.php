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

namespace FormatPHP\Icu\MessageFormat\Parser;

use Closure;
use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Util\CodePointHelper;
use FormatPHP\Intl\NumberFormatOptions as IntlNumberFormatOptions;

use function array_key_exists;
use function array_shift;
use function assert;
use function count;
use function is_array;
use function mb_strlen;
use function mb_substr;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;
use function preg_split;

use const PREG_SPLIT_NO_EMPTY;

class NumberSkeletonParser
{
    private const WHITE_SPACE_REGEX = '/[' . CodePointHelper::WHITE_SPACE_TOKENS . ']/iu';
    private const FRACTION_PRECISION_REGEX = '/^\.(?:(0+)(\*)?|(#+)|(0+)(#+))$/u';
    private const SIGNIFICANT_PRECISION_REGEX = '/^(@+)?(\+|#+)?[rs]?$/u';
    private const INTEGER_WIDTH_REGEX = '/(\*)(0+)|(#+)(0+)|(0+)/u';
    private const CONCISE_INTEGER_WIDTH_REGEX = '/^(0+)$/u';

    private const ROUNDING_MODE_SYMBOLS = [
        'rounding-mode-ceiling' => IntlNumberFormatOptions::ROUNDING_MODE_CEIL,
        'rounding-mode-floor' => IntlNumberFormatOptions::ROUNDING_MODE_FLOOR,
        'rounding-mode-down' => IntlNumberFormatOptions::ROUNDING_MODE_TRUNC,
        'rounding-mode-up' => IntlNumberFormatOptions::ROUNDING_MODE_EXPAND,
        'rounding-mode-half-even' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_EVEN,
        'rounding-mode-half-odd' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_ODD,
        'rounding-mode-half-ceiling' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_CEIL,
        'rounding-mode-half-floor' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_FLOOR,
        'rounding-mode-half-down' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_TRUNC,
        'rounding-mode-half-up' => IntlNumberFormatOptions::ROUNDING_MODE_HALF_EXPAND,
        'rounding-mode-unnecessary' => IntlNumberFormatOptions::ROUNDING_MODE_UNNECESSARY,
    ];

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\InvalidNotationException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\UnsupportedOptionException
     */
    public function parse(
        string $skeleton,
        Type\Location $location,
        bool $shouldParseSkeletons
    ): Type\NumberSkeleton {
        $tokens = $this->parseTokens($skeleton);
        $options = $shouldParseSkeletons ? $this->parseOptions($tokens) : null;

        return new Type\NumberSkeleton($tokens, $location, $options);
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function parseTokens(string $skeleton): Type\NumberSkeletonTokenCollection
    {
        if (mb_strlen($skeleton, Parser::ENCODING) === 0) {
            throw new Exception\InvalidArgumentException('Number skeleton cannot be empty');
        }

        // Parse the skeleton
        $stringTokens = preg_split(self::WHITE_SPACE_REGEX, $skeleton, -1, PREG_SPLIT_NO_EMPTY);
        assert(is_array($stringTokens));

        $tokens = new Type\NumberSkeletonTokenCollection();

        foreach ($stringTokens as $stringToken) {
            /** @var string[] $stemAndOptions */
            $stemAndOptions = preg_split('/[\/]/u', $stringToken);

            if (count($stemAndOptions) === 0) {
                throw new Exception\InvalidArgumentException('Invalid number skeleton');
            }

            $stem = array_shift($stemAndOptions);
            $options = $stemAndOptions;
            foreach ($options as $option) {
                if (mb_strlen($option, Parser::ENCODING) === 0) {
                    throw new Exception\InvalidArgumentException('Invalid number skeleton');
                }
            }

            $tokens[] = new Type\NumberSkeletonToken($stem, $options);
        }

        return $tokens;
    }

    /**
     * @throws Exception\InvalidNotationException
     * @throws Exception\InvalidSkeletonOption
     * @throws Exception\UnsupportedOptionException
     */
    public function parseOptions(Type\NumberSkeletonTokenCollection $tokens): Type\NumberFormatOptions
    {
        $options = new Type\NumberFormatOptions();

        foreach ($tokens as $token) {
            switch ($token->stem) {
                case 'percent':
                case '%':
                    $options->style = IntlNumberFormatOptions::STYLE_PERCENT;

                    continue 2;
                case '%x100':
                    $options->style = IntlNumberFormatOptions::STYLE_PERCENT;
                    $options->scale = 100.0;

                    continue 2;
                case 'currency':
                    $options->style = IntlNumberFormatOptions::STYLE_CURRENCY;
                    $options->currency = $token->options[0] ?: null;

                    continue 2;
                case 'group-off':
                case ',_':
                    $options->useGrouping = IntlNumberFormatOptions::USE_GROUPING_FALSE;

                    continue 2;
                case 'group-min2':
                case ',?':
                    $options->useGrouping = IntlNumberFormatOptions::USE_GROUPING_MIN2;

                    continue 2;
                case 'group-auto':
                    $options->useGrouping = null;

                    continue 2;
                case 'group-on-aligned':
                case ',!':
                    $options->useGrouping = IntlNumberFormatOptions::USE_GROUPING_ALWAYS;

                    continue 2;
                case 'group-thousands':
                    $options->useGrouping = IntlNumberFormatOptions::USE_GROUPING_THOUSANDS;

                    continue 2;
                case 'precision-integer':
                case '.':
                    $options->maximumFractionDigits = 0;

                    continue 2;
                case 'measure-unit':
                case 'unit':
                    $options->style = IntlNumberFormatOptions::STYLE_UNIT;
                    $options->unit = preg_replace('/^(.*?)-/u', '', $token->options[0]) ?: null;

                    continue 2;
                case 'compact-short':
                case 'K':
                    $options->notation = IntlNumberFormatOptions::NOTATION_COMPACT;
                    $options->compactDisplay = IntlNumberFormatOptions::COMPACT_DISPLAY_SHORT;

                    continue 2;
                case 'compact-long':
                case 'KK':
                    $options->notation = IntlNumberFormatOptions::NOTATION_COMPACT;
                    $options->compactDisplay = IntlNumberFormatOptions::COMPACT_DISPLAY_LONG;

                    continue 2;
                case 'scientific':
                    $options->notation = IntlNumberFormatOptions::NOTATION_SCIENTIFIC;
                    $this->parseNotation($token->options, $options);

                    continue 2;
                case 'engineering':
                    $options->notation = IntlNumberFormatOptions::NOTATION_ENGINEERING;
                    $this->parseNotation($token->options, $options);

                    continue 2;
                case 'notation-simple':
                    $options->notation = IntlNumberFormatOptions::NOTATION_STANDARD;

                    continue 2;
                case 'unit-width-narrow':
                    $options->currencyDisplay = IntlNumberFormatOptions::CURRENCY_DISPLAY_NARROW_SYMBOL;
                    $options->unitDisplay = IntlNumberFormatOptions::UNIT_DISPLAY_NARROW;

                    continue 2;
                case 'unit-width-short':
                    $options->currencyDisplay = IntlNumberFormatOptions::CURRENCY_DISPLAY_CODE;
                    $options->unitDisplay = IntlNumberFormatOptions::UNIT_DISPLAY_SHORT;

                    continue 2;
                case 'unit-width-full-name':
                    $options->currencyDisplay = IntlNumberFormatOptions::CURRENCY_DISPLAY_NAME;
                    $options->unitDisplay = IntlNumberFormatOptions::UNIT_DISPLAY_LONG;

                    continue 2;
                case 'unit-width-iso-code':
                    $options->currencyDisplay = IntlNumberFormatOptions::CURRENCY_DISPLAY_SYMBOL;

                    continue 2;
                case 'scale':
                    $options->scale = (float) $token->options[0];

                    continue 2;
                case 'integer-width':
                    if (count($token->options) > 1) {
                        throw new Exception\InvalidSkeletonOption(
                            'integer-width stems only accept a single optional option',
                        );
                    }

                    preg_replace_callback(
                        self::INTEGER_WIDTH_REGEX,
                        $this->integerWidthCallback($options),
                        $token->options[0] ?? '',
                    );

                    continue 2;
            }

            // https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html#integer-width
            if (preg_match(self::CONCISE_INTEGER_WIDTH_REGEX, $token->stem)) {
                /** @var positive-int $digits */
                $digits = mb_strlen($token->stem, Parser::ENCODING);
                $options->minimumIntegerDigits = $digits;

                continue;
            }

            if (preg_match(self::FRACTION_PRECISION_REGEX, $token->stem)) {
                // Precision
                // https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html#fraction-precision
                // precision-integer case
                if (count($token->options) > 1) {
                    throw new Exception\InvalidSkeletonOption(
                        'Fraction-precision stems only accept a single optional option',
                    );
                }

                preg_replace_callback(
                    self::FRACTION_PRECISION_REGEX,
                    $this->fractionPrecisionCallback($options),
                    $token->stem,
                );

                // https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html#trailing-zero-display
                $opt = $token->options[0] ?? '';
                if ($opt === 'w') {
                    $options->trailingZeroDisplay = IntlNumberFormatOptions::TRAILING_ZERO_DISPLAY_STRIP_IF_INTEGER;
                } elseif ($opt) {
                    $this->parseSignificantPrecision($opt, $options);
                }

                continue;
            }

            // https://unicode-org.github.io/icu/userguide/format_parse/numbers/skeletons.html#significant-digits-precision
            if (preg_match(self::SIGNIFICANT_PRECISION_REGEX, $token->stem)) {
                $this->parseSignificantPrecision($token->stem, $options);

                continue;
            }

            $this->parseSign($token->stem, $options);
            $this->parseRoundingMode($token->stem, $options);
            $this->parseConciseScientificAndEngineeringStem($token->stem, $options);
        }

        return $options;
    }

    /**
     * @param string[] $options
     */
    private function parseNotation(array $options, Type\NumberFormatOptions $numberFormatOptions): void
    {
        foreach ($options as $option) {
            $this->parseSign($option, $numberFormatOptions);
        }
    }

    private function parseSign(string $option, Type\NumberFormatOptions $numberFormatOptions): void
    {
        switch ($option) {
            case 'sign-auto':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_AUTO;

                break;
            case 'sign-accounting':
            case '()':
                $numberFormatOptions->currencySign = IntlNumberFormatOptions::CURRENCY_SIGN_ACCOUNTING;

                break;
            case 'sign-always':
            case '+!':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_ALWAYS;

                break;
            case 'sign-accounting-always':
            case '()!':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_ALWAYS;
                $numberFormatOptions->currencySign = IntlNumberFormatOptions::CURRENCY_SIGN_ACCOUNTING;

                break;
            case 'sign-except-zero':
            case '+?':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_EXCEPT_ZERO;

                break;
            case 'sign-negative':
            case '+-':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_NEGATIVE;

                break;
            case 'sign-accounting-except-zero':
            case '()?':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_EXCEPT_ZERO;
                $numberFormatOptions->currencySign = IntlNumberFormatOptions::CURRENCY_SIGN_ACCOUNTING;

                break;
            case 'sign-accounting-negative':
            case '()-':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_NEGATIVE;
                $numberFormatOptions->currencySign = IntlNumberFormatOptions::CURRENCY_SIGN_ACCOUNTING;

                break;
            case 'sign-never':
            case '+_':
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_NEVER;

                break;
        }
    }

    private function parseRoundingMode(string $option, Type\NumberFormatOptions $numberFormatOptions): void
    {
        if (array_key_exists($option, self::ROUNDING_MODE_SYMBOLS)) {
            $numberFormatOptions->roundingMode = self::ROUNDING_MODE_SYMBOLS[$option];
        }
    }

    private function parseSignificantPrecision(string $option, Type\NumberFormatOptions $options): void
    {
        $lastChar = $option[mb_strlen($option, Parser::ENCODING) - 1];

        if ($lastChar === 'r') {
            $options->roundingPriority = IntlNumberFormatOptions::ROUNDING_PRIORITY_MORE_PRECISION;
        } elseif ($lastChar === 's') {
            $options->roundingPriority = IntlNumberFormatOptions::ROUNDING_PRIORITY_LESS_PRECISION;
        }

        preg_replace_callback(
            self::SIGNIFICANT_PRECISION_REGEX,
            $this->significantPrecisionCallback($options),
            $option,
        );
    }

    /**
     * @throws Exception\InvalidNotationException
     */
    private function parseConciseScientificAndEngineeringStem(
        string $stem,
        Type\NumberFormatOptions $numberFormatOptions
    ): void {
        $didSetNotation = false;
        if ($stem[0] === 'E' && $stem[1] === 'E') {
            $numberFormatOptions->notation = IntlNumberFormatOptions::NOTATION_ENGINEERING;
            $stem = mb_substr($stem, 2, null, Parser::ENCODING);
            $didSetNotation = true;
        } elseif ($stem[0] === 'E') {
            $numberFormatOptions->notation = IntlNumberFormatOptions::NOTATION_SCIENTIFIC;
            $stem = mb_substr($stem, 1, null, Parser::ENCODING);
            $didSetNotation = true;
        }

        if ($didSetNotation) {
            $signDisplay = mb_substr($stem, 0, 2, Parser::ENCODING);

            if ($signDisplay === '+!') {
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_ALWAYS;
                $stem = mb_substr($stem, 2, null, Parser::ENCODING);
            } elseif ($signDisplay === '+?') {
                $numberFormatOptions->signDisplay = IntlNumberFormatOptions::SIGN_DISPLAY_EXCEPT_ZERO;
                $stem = mb_substr($stem, 2, null, Parser::ENCODING);
            }

            if (!preg_match(self::CONCISE_INTEGER_WIDTH_REGEX, $stem)) {
                throw new Exception\InvalidNotationException('Malformed concise eng/scientific notation');
            }

            /** @var positive-int $digits */
            $digits = mb_strlen($stem, Parser::ENCODING);
            $numberFormatOptions->minimumIntegerDigits = $digits;
        }
    }

    /**
     * @return Closure(string[]):string
     */
    private function integerWidthCallback(Type\NumberFormatOptions $options): Closure
    {
        /**
         * @param string[] $m
         *
         * @throws Exception\UnsupportedOptionException
         */
        return function (array $m) use ($options): string {
            $matches = [
                $m[0] ?? '',
                $m[1] ?? '',
                $m[2] ?? '',
                $m[3] ?? '',
                $m[4] ?? '',
                $m[5] ?? '',
            ];

            if ($matches[1] !== '') {
                /** @var positive-int $digits */
                $digits = mb_strlen($matches[2], Parser::ENCODING);
                $options->minimumIntegerDigits = $digits;
            } elseif ($matches[3] !== '' && $matches[4] !== '') {
                throw new Exception\UnsupportedOptionException('We currently do not support maximum integer digits');
            } elseif ($matches[5] !== '') {
                throw new Exception\UnsupportedOptionException('We currently do not support exact integer digits');
            }

            return '';
        };
    }

    /**
     * @return Closure(string[]):string
     */
    private function fractionPrecisionCallback(Type\NumberFormatOptions $options): Closure
    {
        /**
         * @param string[] $m
         */
        return function (array $m) use ($options): string {
            $matches = [$m[0] ?? '', $m[1] ?? '', $m[2] ?? '', $m[3] ?? '', $m[4] ?? '', $m[5] ?? ''];

            // .000* case (before ICU67 it was .000+)
            if ($matches[2] === '*') {
                /** @var positive-int $digits */
                $digits = mb_strlen($matches[1], Parser::ENCODING);
                $options->minimumFractionDigits = $digits;
            // .### case
            } elseif (($matches[3][0] ?? '') === '#') {
                /** @var positive-int $digits */
                $digits = mb_strlen($matches[3], Parser::ENCODING);
                $options->maximumFractionDigits = $digits;
            // .00## case
            } elseif ($matches[4] !== '' && $matches[5] !== '') {
                /** @var positive-int $minDigits */
                $minDigits = mb_strlen($matches[4], Parser::ENCODING);
                /** @var positive-int $maxDigits */
                $maxDigits = mb_strlen($matches[5], Parser::ENCODING);
                $options->minimumFractionDigits = $minDigits;
                $options->maximumFractionDigits = $options->minimumFractionDigits + $maxDigits;
            } else {
                /** @var positive-int $digits */
                $digits = mb_strlen($matches[1], Parser::ENCODING);
                $options->minimumFractionDigits = $digits;
                $options->maximumFractionDigits = $digits;
            }

            return '';
        };
    }

    /**
     * @return Closure(string[]):string
     */
    private function significantPrecisionCallback(Type\NumberFormatOptions $options): Closure
    {
        /**
         * @param string[] $m
         */
        return function (array $m) use ($options): string {
            $matches = [
                $m[0] ?? '',
                $m[1] ?? '',
                $m[2] ?? '',
            ];

            /** @var positive-int $digits */
            $digits = mb_strlen($matches[1], Parser::ENCODING);

            // @@@ case
            if ($matches[2] === '') {
                $options->minimumSignificantDigits = $digits;
                $options->maximumSignificantDigits = $digits;
            // @@@+ case
            } elseif ($matches[2] === '+') {
                $options->minimumSignificantDigits = $digits;
            // .### case
            } elseif (($matches[1][0] ?? '') === '#') {
                $options->maximumSignificantDigits = $digits;
            // .@@## or .@@@ case
            } else {
                /** @var positive-int $maxDigits */
                $maxDigits = mb_strlen($matches[2], Parser::ENCODING);
                $options->minimumSignificantDigits = $digits;
                $options->maximumSignificantDigits = $options->minimumSignificantDigits + $maxDigits;
            }

            return '';
        };
    }
}
