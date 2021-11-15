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

namespace FormatPHP\Util;

use function preg_replace;
use function trim;

/**
 * Provides tools for cleaning messages
 */
trait MessageCleaner
{
    /**
     * Removes newlines and extra whitespace from the given message string
     */
    private function cleanMessage(string $message): string
    {
        return trim((string) preg_replace('/\n\s*/', ' ', $message));
    }
}
