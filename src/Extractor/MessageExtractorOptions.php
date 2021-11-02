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

/**
 * MessageExtractor options
 */
class MessageExtractorOptions
{
    public ?string $format = null;
    public ?string $outFile = null;
    public string $idInterpolationPattern = IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN;
    public bool $extractSourceLocation = false;
    public bool $throws = false;
    public ?string $pragma = null;
    public bool $preserveWhitespace = false;

    /**
     * @var string[]
     */
    public array $additionalFunctionNames = [];

    /**
     * Glob file path patterns to ignore
     *
     * @var string[]
     */
    public array $ignore = [];
}
