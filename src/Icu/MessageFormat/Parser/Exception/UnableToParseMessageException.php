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

namespace FormatPHP\Icu\MessageFormat\Parser\Exception;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use RuntimeException as PhpRuntimeException;
use Throwable;

use function sprintf;

/**
 * Thrown with a message format parser Error to indicate a syntax error
 * encountered while parsing a message
 */
class UnableToParseMessageException extends PhpRuntimeException implements ParserExceptionInterface
{
    public function __construct(Error $error, ?Throwable $previous = null)
    {
        parent::__construct($this->createMessageForError($error), 0, $previous);
    }

    private function createMessageForError(Error $error): string
    {
        return sprintf(
            'Syntax error %s found while parsing message "%s"',
            $error->getErrorKindName(),
            $error->message,
        );
    }
}
