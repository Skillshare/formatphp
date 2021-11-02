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

use FormatPHP\Exception\MessageNotFound;
use IteratorAggregate;
use Ramsey\Collection\AbstractCollection;

use function sprintf;

/**
 * FormatPHP collection of Message instances
 *
 * @extends AbstractCollection<Message>
 * @implements IteratorAggregate<array-key, Message>
 */
final class MessageCollection extends AbstractCollection implements IteratorAggregate
{
    public function getType(): string
    {
        return Message::class;
    }

    /**
     * Looks up and returns a message for the given ID and locale
     *
     * @throws MessageNotFound
     */
    public function getMessage(string $id, Locale $locale): string
    {
        return $this->findMessage($id, $locale)->getMessage();
    }

    /**
     * @throws MessageNotFound
     */
    private function findMessage(string $id, Locale $locale): Message
    {
        foreach ($this as $message) {
            if ($message->getId() === $id && $message->getLocale()->getId() === $locale->getId()) {
                return $message;
            }
        }

        throw new MessageNotFound(sprintf('Could not find message with ID "%s".', $id));
    }
}
