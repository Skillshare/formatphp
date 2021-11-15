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

namespace FormatPHP\Extractor\Parser;

use Throwable;

/**
 * An error that occurred while parsing application source code
 */
class ParserError
{
    public string $message;
    public string $sourceFile;
    public ?int $sourceLine;
    public ?Throwable $exception;

    public function __construct(
        string $message,
        string $sourceFile,
        ?int $sourceLine = null,
        ?Throwable $exception = null
    ) {
        $this->message = $message;
        $this->sourceFile = $sourceFile;
        $this->sourceLine = $sourceLine;
        $this->exception = $exception;
    }
}
