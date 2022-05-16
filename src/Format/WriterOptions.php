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

namespace FormatPHP\Format;

use FormatPHP\ExtendedDescriptorInterface;

/**
 * Options for format writers
 *
 * Writer implementations may choose to use or ignore any of the options
 * provided to them.
 */
class WriterOptions
{
    /**
     * Whether the descriptors include source file metadata
     *
     * If true, the descriptors provided by to the writer may be instances of
     * {@see ExtendedDescriptorInterface}, which include methods for additional
     * information related to the source files processed during extraction.
     *
     * @see ExtendedDescriptorInterface::getSourceFile()
     * @see ExtendedDescriptorInterface::getSourceLine()
     * @see ExtendedDescriptorInterface::getSourceStartOffset()
     * @see ExtendedDescriptorInterface::getSourceEndOffset()
     */
    public bool $includesSourceLocation = false;

    /**
     * Whether the descriptors include pragma metadata
     *
     * If true, the descriptors provided to the writer may be instances of
     * {@see ExtendedDescriptorInterface}, which includes the method
     * {@see ExtendedDescriptorInterface::getMetadata()} that may contain
     * additional information parsed from source file pragma.
     */
    public bool $includesPragma = false;
}
