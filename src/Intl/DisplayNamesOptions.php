<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Intl;

use FormatPHP\Icu\MessageFormat\Parser\Type\OptionSerializer;
use JsonSerializable;

/**
 * Options for formatting display names
 *
 * @link https://tc39.es/ecma402/#sec-intl-displaynames-constructor
 *
 * @psalm-type FallbackType = "code" | "none"
 * @psalm-type LanguageDisplayType = "dialect" | "standard"
 * @psalm-type StyleType = "long" | "narrow" | "short"
 * @psalm-type TypeType = "calendar" | "currency" | "dateTimeField" | "language" | "region" | "script"
 * @psalm-type OptionsType = array{fallback?: FallbackType, languageDisplay?: LanguageDisplayType, style?: StyleType, type?: TypeType}
 */
class DisplayNamesOptions implements JsonSerializable
{
    use OptionSerializer;

    public const FALLBACK_CODE = 'code';
    public const FALLBACK_NONE = 'none';

    public const LANGUAGE_DISPLAY_DIALECT = 'dialect';
    public const LANGUAGE_DISPLAY_STANDARD = 'standard';

    public const STYLE_LONG = 'long';
    public const STYLE_NARROW = 'narrow';
    public const STYLE_SHORT = 'short';

    public const TYPE_CALENDAR = 'calendar';
    public const TYPE_CURRENCY = 'currency';
    public const TYPE_DATE_TIME_FIELD = 'dateTimeField';
    public const TYPE_LANGUAGE = 'language';
    public const TYPE_REGION = 'region';
    public const TYPE_SCRIPT = 'script';

    /**
     * The fallback strategy to use
     *
     * If we are unable to format a display name, we will return the same code
     * provided if `fallback` is set to "code." If `fallback` is "none," then
     * we return `null`. The default `fallback` is "code."
     *
     * @var FallbackType | null
     */
    public ?string $fallback;

    /**
     * A suggestion for displaying the language according to the locale's
     * dialect or standard representation
     *
     * In JavaScript, this defaults to "dialect," and some implementations do
     * not appear to honor "standard" at all, though this might be a result of
     * the version of the ICU data files bundled with the implementation.
     *
     * For now, PHP supports only the "standard" representation, so "dialect"
     * has no effect.
     *
     * @var LanguageDisplayType | null
     */
    public ?string $languageDisplay;

    /**
     * The formatting style to use
     *
     * This currently only affects the display name when `type` is "currency."
     *
     * @var StyleType | null
     */
    public ?string $style;

    /**
     * The type of data for which we wish to format a display name
     *
     * This currently supports "currency," "language," "region," and "script."
     *
     * While ECMA-402 defines "calendar" and "dateTimeField" as additional types,
     * these types are not implemented in Node.js or in any browsers. In fact,
     * if set, the implementations throw exceptions, so this implementation
     * follows the same pattern.
     *
     * @var TypeType | null
     */
    public ?string $type;

    /**
     * @psalm-param OptionsType $options
     */
    public function __construct(array $options = [])
    {
        $this->fallback = $options['fallback'] ?? null;
        $this->languageDisplay = $options['languageDisplay'] ?? null;
        $this->style = $options['style'] ?? null;
        $this->type = $options['type'] ?? null;
    }
}
