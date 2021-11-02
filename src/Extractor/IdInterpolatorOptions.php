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

namespace FormatPHP\Extractor;

use FormatPHP\Intl\Config;

/**
 * IdInterpolator options
 *
 * @see Config::getIdInterpolatorPattern()
 */
class IdInterpolatorOptions
{
    private const DEFAULT_HASHING_ALGORITHM = 'sha512';
    private const DEFAULT_ENCODING_ALGORITHM = 'base64';
    private const DEFAULT_LENGTH = 6;

    public string $hashingAlgorithm;
    public string $encodingAlgorithm;
    public int $length;

    public function __construct(
        string $hashingAlgorithm = self::DEFAULT_HASHING_ALGORITHM,
        string $encodingAlgorithm = self::DEFAULT_ENCODING_ALGORITHM,
        int $length = self::DEFAULT_LENGTH
    ) {
        $this->hashingAlgorithm = $hashingAlgorithm;
        $this->encodingAlgorithm = $encodingAlgorithm;
        $this->length = $length;
    }
}
