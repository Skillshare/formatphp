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

namespace FormatPHP;

use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Intl\LocaleInterface;
use IteratorAggregate;
use Ramsey\Collection\AbstractCollection;

use function sprintf;

/**
 * FormatPHP collection of Message instances
 *
 * @extends AbstractCollection<MessageInterface>
 * @implements IteratorAggregate<array-key, MessageInterface>
 */
final class MessageCollection extends AbstractCollection implements IteratorAggregate
{
    public function getType(): string
    {
        return MessageInterface::class;
    }

    /**
     * Looks up and returns a message for the given ID and locale
     *
     * @throws MessageNotFoundException
     */
    public function getMessage(string $id, LocaleInterface $locale): string
    {
        return $this->findMessage($id, $locale)->getMessage();
    }

    /**
     * @throws MessageNotFoundException
     */
    private function findMessage(string $id, LocaleInterface $locale): MessageInterface
    {
        foreach ($this as $message) {
            if ($message->getId() === $id && $message->getLocale()->getId() === $locale->getId()) {
                return $message;
            }
        }

        throw new MessageNotFoundException(sprintf('Could not find message with ID "%s".', $id));
    }
}
