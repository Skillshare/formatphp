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

namespace FormatPHP\Extractor\Parser;

use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Extractor\MessageExtractorOptions;

/**
 * Parses message descriptors from application source code files
 */
interface DescriptorParserInterface
{
    /**
     * Returns a collection of descriptors parsed from a source code file
     *
     * @param string $filePath The path to the source code file to parse
     * @param MessageExtractorOptions $options Options to apply to message extraction
     * @param ParserErrorCollection $errors Errors encountered during extraction
     *     (add any additional errors that occur to this collection)
     *
     * @throws UnableToProcessFileException
     */
    public function __invoke(
        string $filePath,
        MessageExtractorOptions $options,
        ParserErrorCollection $errors
    ): DescriptorCollection;
}
