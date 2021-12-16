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

namespace FormatPHP\Icu\MessageFormat\Parser\Exception;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use RuntimeException as PhpRuntimeException;
use Throwable;

/**
 * Thrown when ICU message validation fails
 */
class InvalidMessageException extends PhpRuntimeException implements ParserExceptionInterface
{
    private Error $error;

    public function __construct(Error $error, ?Throwable $previous = null)
    {
        parent::__construct('Syntax error', 0, $previous);
        $this->error = $error;
    }

    /**
     * Returns the specific syntax error that caused validation to fail
     */
    public function getParserError(): Error
    {
        return $this->error;
    }
}
