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

namespace FormatPHP\Format\Writer;

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\Reader\SimpleReader;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

use function ksort;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

/**
 * A simple formatter for FormatPHP, producing message key-value pairs
 *
 * This follows the same format as the simple formatter for FormatJS:
 *
 * ```json
 * {
 *   "my.message": "This is a message for translation."
 * }
 * ```
 *
 * @see SimpleReader
 */
class SimpleWriter implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        $simple = [];
        foreach ($collection as $item) {
            $simple[(string) $item->getId()] = $item->getDefaultMessage();
        }

        ksort($simple, SORT_NATURAL | SORT_FLAG_CASE);

        return $simple;
    }
}
