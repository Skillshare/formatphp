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

    public ?string $fallback;
    public ?string $languageDisplay;
    public ?string $style;
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
