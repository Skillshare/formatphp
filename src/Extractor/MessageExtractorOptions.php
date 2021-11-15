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
    private const DEFAULT_FUNCTION_NAMES = ['formatMessage'];
    private const DEFAULT_PARSERS = ['php'];

    /**
     * Formatter name or path to a formatter script that controls the shape
     * of the JSON produced for $outFile
     */
    public ?string $format = null;

    /**
     * Target file path to save the JSON output file of all translations
     * extracted from the files
     */
    public ?string $outFile = null;

    /**
     * If message descriptors are missing the id property, we will use this
     * pattern to automatically generate IDs for them
     *
     * @see IdInterpolator
     */
    public string $idInterpolationPattern = IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN;

    /**
     * Whether to extract metadata for the source files
     *
     * If true, the extracted descriptors will each include `file`, `start`,
     * `end`, `line`, and `col` properties.
     */
    public bool $extractSourceLocation = false;

    /**
     * Whether to throw an exception when failing to process any file in the batch
     *
     * The default is to emit warnings, while continuing to process the rest
     * of the files.
     */
    public bool $throws = false;

    /**
     * Allows parsing of additional custom pragma to include custom metadata in
     * the extracted messages
     */
    public ?string $pragma = null;

    /**
     * Whether to preserve whitespace and newlines in extracted messages
     */
    public bool $preserveWhitespace = false;

    /**
     * Function and method names to parse from the application source code
     *
     * @var string[]
     */
    public array $functionNames = self::DEFAULT_FUNCTION_NAMES;

    /**
     * Glob file path patterns to ignore
     *
     * @var string[]
     */
    public array $ignore = [];

    /**
     * Parsers to use for extracting format messages from application source code
     *
     * @var string[]
     */
    public array $parsers = self::DEFAULT_PARSERS;
}
