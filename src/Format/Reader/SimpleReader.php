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

namespace FormatPHP\Format\Reader;

use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Message;
use FormatPHP\MessageCollection;

use function assert;
use function gettype;
use function is_string;
use function sprintf;

/**
 * Returns a MessageCollection parsed from JSON-decoded data that was written
 * using {@see SimpleWriter}
 */
class SimpleReader implements ReaderInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(array $data): MessageCollection
    {
        $messages = new MessageCollection();

        foreach ($data as $messageId => $message) {
            $this->validateShape($messageId, $message);
            assert(is_string($messageId));
            assert(is_string($message));

            $messages[$messageId] = new Message($messageId, $message);
        }

        return $messages;
    }

    /**
     * @param array-key $messageId
     * @param mixed $message
     *
     * @throws InvalidMessageShapeException
     */
    private function validateShape($messageId, $message): void
    {
        if (!is_string($messageId)) {
            throw new InvalidMessageShapeException(sprintf(
                '%s expects a string message ID; received %s',
                self::class,
                gettype($messageId),
            ));
        }

        if (!is_string($message)) {
            throw new InvalidMessageShapeException(sprintf(
                '%s expects a string message; received %s',
                self::class,
                gettype($message),
            ));
        }
    }
}
