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

namespace FormatPHP\Reader;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

/**
 * Returns a collection of messages parsed from JSON-decoded message data
 */
interface FormatInterface
{
    /**
     * @param array<array-key, mixed> $data An arbitrary array of JSON-decoded
     *     data, loaded from a message file.
     * @param LocaleInterface $localeResolved We utilize a "fallback" algorithm
     *     to look up a suitable replacement locale (i.e., if we receive "en-US"
     *     and have only a locale for "en," we will use "en" instead). This
     *     parameter is the actual locale we used, which may be different from
     *     the one provided on Config.
     *
     * @throws InvalidMessageShapeException
     */
    public function __invoke(Config $config, array $data, LocaleInterface $localeResolved): MessageCollection;
}
