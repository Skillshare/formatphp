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
use FormatPHP\Extractor\IdInterpolatorOptions;
use FormatPHP\Intl\LocaleFactoryInterface;
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
     * If message descriptors are missing the id property, we will use this
     * pattern to automatically generate IDs for them.
     *
     * The pattern follows this format:
     *
     *     [hashAlgorithm:contenthash:encodingAlgorithm:length]
     *
     * When passing this value, provide the hashAlgorithm, encodingAlgorithm,
     * and length, and formatphp will calculate the contenthash.
     *
     * For example, if you wish to use `haval160,4` as the hashing algorithm,
     * `hex` as the encoding algorithm, with a length of 10, you would pass
     * the following string:
     *
     *     [haval160,4:contenthash:hex:10]
     *
     * See <https://www.php.net/hash_algos> for available hashing algorithms.
     *
     * The following binary-to-text encodings are supported:
     *
     * - `base64`
     * - `base64url`
     * - `hex`
     *
     * @see IdInterpolator
     * @see IdInterpolatorOptions
     */
    public function getIdInterpolatorPattern(): string;

    /**
     * Returns locale to use for translation and localization
     */
    public function getLocale(): LocaleInterface;

    /**
     * Returns a factory to use for creating locale instances
     */
    public function getLocaleFactory(): LocaleFactoryInterface;
}
