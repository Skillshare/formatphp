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

use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\LocaleInterface;

/**
 * FormatPHP configuration
 */
interface ConfigInterface
{
    /**
     * Returns default locale to use, if unable to support the requested locale
     */
    public function getDefaultLocale(): ?LocaleInterface;

    /**
     * Returns a pattern that defines how to generate missing message IDs
     *
     * @see IdInterpolator
     */
    public function getIdInterpolatorPattern(): string;

    /**
     * Returns locale to use for translation and localization
     */
    public function getLocale(): LocaleInterface;
}
