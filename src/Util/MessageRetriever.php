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

use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Exception\UnableToGenerateMessageIdException;
use FormatPHP\MessageCollection;

use function sprintf;

/**
 * Provides tools for retrieving message strings from collections
 */
trait MessageRetriever
{
    use DescriptorIdBuilder;

    /**
     * Returns a message from the collection for the given descriptor
     *
     * @throws InvalidArgumentException
     * @throws UnableToGenerateMessageIdException
     */
    private function getMessageForDescriptor(
        MessageCollection $collection,
        DescriptorInterface $descriptor
    ): string {
        $messageId = $this->buildMessageId($descriptor);

        try {
            return $this->getMessageForId($collection, $messageId);
        } catch (MessageNotFoundException $exception) {
            if ($descriptor->getDefaultMessage() !== null) {
                return (string) $descriptor->getDefaultMessage();
            }
        }

        return $messageId;
    }

    /**
     * Returns a message from the collection for the given message ID
     *
     * @throws MessageNotFoundException
     */
    private function getMessageForId(MessageCollection $collection, string $messageId): string
    {
        $message = $collection[$messageId] ?? null;

        if ($message === null) {
            throw new MessageNotFoundException(sprintf(
                'Unable to find message with ID "%s"',
                $messageId,
            ));
        }

        return $message->getMessage();
    }
}
