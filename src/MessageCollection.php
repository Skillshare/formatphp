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

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Exception\UnableToGenerateMessageIdException;
use FormatPHP\Extractor\IdInterpolator;
use IteratorAggregate;
use Ramsey\Collection\AbstractCollection;

use function preg_replace;
use function sprintf;
use function trim;

/**
 * FormatPHP collection of Message instances
 *
 * @extends AbstractCollection<MessageInterface>
 * @implements IteratorAggregate<array-key, MessageInterface>
 */
final class MessageCollection extends AbstractCollection implements IteratorAggregate
{
    private ConfigInterface $config;

    /**
     * @param array<array-key, MessageInterface> $data
     */
    public function __construct(ConfigInterface $config, array $data = [])
    {
        parent::__construct($data);

        $this->config = $config;
    }

    public function getType(): string
    {
        return MessageInterface::class;
    }

    /**
     * @throws \Ramsey\Collection\Exception\InvalidArgumentException
     *
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($value instanceof MessageInterface) {
            $offset = $value->getId();
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * Looks up and returns a message for the given ID and locale
     *
     * @throws MessageNotFoundException
     */
    public function getMessageById(string $id): string
    {
        return $this->cleanMessage($this->lookupMessage($id)->getMessage());
    }

    /**
     * Looks up and returns a message for the given Descriptor and locale
     *
     * @throws InvalidArgumentException
     */
    public function getMessageByDescriptor(DescriptorInterface $descriptor): string
    {
        $messageId = $this->buildMessageId($descriptor);

        try {
            return $this->getMessageById($messageId);
        } catch (MessageNotFoundException $exception) {
            if ($descriptor->getDefaultMessage() !== null) {
                return $this->cleanMessage((string) $descriptor->getDefaultMessage());
            }
        }

        return $messageId;
    }

    /**
     * @throws MessageNotFoundException
     */
    private function lookupMessage(string $messageId): MessageInterface
    {
        $message = $this[$messageId] ?? null;

        if ($message === null) {
            throw new MessageNotFoundException(sprintf(
                'Unable to find message with ID "%s" for locale "%s"',
                $messageId,
                $this->config->getLocale()->toString(),
            ));
        }

        return $message;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function buildMessageId(DescriptorInterface $descriptor): string
    {
        try {
            $messageId = (new IdInterpolator())->generateId(
                $descriptor,
                $this->config->getIdInterpolatorPattern(),
            );
        } catch (UnableToGenerateMessageIdException $exception) {
            $messageId = '';
        }

        return $messageId;
    }

    private function cleanMessage(string $message): string
    {
        return trim((string) preg_replace('/\n\s*/', ' ', $message));
    }
}
