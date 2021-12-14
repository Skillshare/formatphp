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
use ReflectionObject;
use RuntimeException as PhpRuntimeException;
use Throwable;

use function array_flip;
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
            $this->getErrorTypeName($error),
            $error->message,
        );
    }

    private function getErrorTypeName(Error $error): string
    {
        $reflection = new ReflectionObject($error);

        // @phpstan-ignore-next-line
        $constants = array_flip($reflection->getConstants());

        return $constants[$error->kind] ?? '';
    }
}
