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

/**
 * FormatPHP descriptor
 *
 * The descriptor describes the translation message to retrieve when
 * calling the message formatting from the application's source code.
 */
interface DescriptorInterface
{
    /**
     * Returns an optional default message to use for extraction or if no
     * translation message is found
     */
    public function getDefaultMessage(): ?string;

    /**
     * Returns an optional description that may be helpful to translators
     */
    public function getDescription(): ?string;

    /**
     * Returns an optional identifier for looking up the translation message
     */
    public function getId(): ?string;

    /**
     * Sets an identifier used for looking up the translation message
     *
     * This setter allows later steps to generate and set identifiers for any
     * descriptors that are missing them.
     */
    public function setId(string $id): void;

    /**
     * Returns an array representation of the descriptor
     *
     * @return array<string, string | int | array | null>
     */
    public function toArray(): array;
}
