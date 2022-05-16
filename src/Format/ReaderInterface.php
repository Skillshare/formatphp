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

use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\MessageCollection;

/**
 * Returns a collection of messages parsed from JSON-decoded message data
 *
 * @psalm-type ReaderCallableType = callable(mixed[]):MessageCollection
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
    public function __invoke(array $data): MessageCollection;
}
