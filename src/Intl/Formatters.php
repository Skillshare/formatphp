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
 * FormatPHP formatter methods
 */
interface Formatters
{
    /**
     * Returns a translated string for the given descriptor ID
     *
     * If the descriptor does not have an ID, we will use a combination of the
     * defaultMessage and description to create an ID.
     *
     * If we cannot find the given ID in the configured messages, we will use
     * the descriptor's defaultMessage, if provided.
     *
     * @param array{id?: string, defaultMessage?: string, description?: string} $descriptor
     * @param array<array-key, int | float | string> $values
     */
    public function formatMessage(array $descriptor, array $values = []): string;
}
