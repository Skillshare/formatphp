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

namespace FormatPHP\PseudoLocale;

/**
 * Converter options
 */
class ConverterOptions
{
    /**
     * Formatter name or path to a formatter script that controls the shape
     * of the JSON read from the input
     */
    public ?string $inFormat = null;

    /**
     * Formatter name or path to a formatter script that controls the shape
     * of the JSON produced for $outFile
     */
    public ?string $outFormat = null;

    /**
     * Target file path to save the JSON output file for the pseudo locale
     */
    public ?string $outFile = null;
}
