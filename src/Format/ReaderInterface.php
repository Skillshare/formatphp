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

namespace FormatPHP\Format;

use FormatPHP\ConfigInterface;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\MessageCollection;

/**
 * Returns a collection of messages parsed from JSON-decoded message data
 *
 * @psalm-type ReaderCallableType = callable(ConfigInterface,mixed[]):MessageCollection
 * @psalm-type ReaderType = ReaderInterface | ReaderCallableType
 */
interface ReaderInterface
{
    /**
     * @param array<array-key, mixed> $data An arbitrary array of JSON-decoded
     *     data, loaded from a message file.
     *
     * @throws InvalidMessageShapeException
     */
    public function __invoke(ConfigInterface $config, array $data): MessageCollection;
}
