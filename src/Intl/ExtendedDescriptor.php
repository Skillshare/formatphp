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

/**
 * Extended descriptor information
 */
interface ExtendedDescriptor extends Descriptor
{
    /**
     * Returns the filename where the descriptor appears
     */
    public function getFile(): ?string;

    /**
     * Returns the file code string offset of the first character where the
     * descriptor appears
     */
    public function getStart(): ?int;

    /**
     * Returns the file code string offset of the last character where the
     * descriptor appears
     */
    public function getEnd(): ?int;

    /**
     * Returns the line number on which the descriptor starts
     */
    public function getLine(): ?int;

    /**
     * Returns the line's column number in which the descriptor starts
     */
    public function getCol(): ?int;

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
