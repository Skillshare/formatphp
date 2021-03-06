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

use FormatPHP\Exception\UnableToFormatDisplayNameException;

/**
 * Enables the consistent translation of language, region, and script display names
 *
 * @link https://www.php.net/Locale
 * @link https://tc39.es/ecma402/#intl-displaynames-objects
 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DisplayNames
 * @link https://formatjs.io/docs/intl#formatdisplayname
 */
interface DisplayNamesInterface
{
    /**
     * Returns a translated, localized display string for the given code
     *
     * @throws UnableToFormatDisplayNameException
     */
    public function of(string $code): ?string;
}
