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

namespace FormatPHP\Extractor;

/**
 * IdInterpolator options
 *
 * @see IdInterpolator
 */
class IdInterpolatorOptions
{
    private const DEFAULT_HASHING_ALGORITHM = 'sha512';
    private const DEFAULT_ENCODING_ALGORITHM = 'base64';
    private const DEFAULT_LENGTH = 6;

    /**
     * The hashing algorithm to use when creating the ID
     */
    public string $hashingAlgorithm;

    /**
     * The encoding algorithm to use to encode the ID
     */
    public string $encodingAlgorithm;

    /**
     * The length of the ID; if the generated ID is longer than this, it will be truncated
     */
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
