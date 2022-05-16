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

namespace FormatPHP;

/**
 * Extended descriptor information
 */
interface ExtendedDescriptorInterface extends DescriptorInterface
{
    /**
     * Returns the filename where the descriptor appears
     */
    public function getSourceFile(): ?string;

    /**
     * Returns the file code string offset of the first character where the
     * descriptor appears
     */
    public function getSourceStartOffset(): ?int;

    /**
     * Returns the file code string offset of the last character where the
     * descriptor appears
     */
    public function getSourceEndOffset(): ?int;

    /**
     * Returns the line number on which the descriptor starts
     */
    public function getSourceLine(): ?int;

    /**
     * Sets metadata related to this descriptor
     *
     * @param array<string, string> $metadata
     */
    public function setMetadata(array $metadata): void;

    /**
     * Returns metadata related to this descriptor
     *
     * @return array<string, string>
     */
    public function getMetadata(): array;
}
