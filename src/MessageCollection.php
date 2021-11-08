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
use FormatPHP\Intl\LocaleInterface;
use IteratorAggregate;
use Ramsey\Collection\AbstractCollection;

use function array_filter;
use function array_unique;
use function array_values;
use function implode;
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
        $lookupLocale = $this->config->getLocale();
        $localeFactory = $this->config->getLocaleFactory();

        foreach ($this->getFallbackLocales($lookupLocale) as $locale) {
            try {
                return $this->findMessage($messageId, $localeFactory($locale));
            } catch (MessageNotFoundException $exception) {
                continue;
            }
        }

        throw new MessageNotFoundException(sprintf(
            'Unable to find message with ID "%s" for locale "%s"',
            $messageId,
            $lookupLocale->toString(),
        ));
    }

    /**
     * @throws MessageNotFoundException
     */
    private function findMessage(string $id, LocaleInterface $locale): MessageInterface
    {
        foreach ($this as $message) {
            if ($message->getId() === $id && $message->getLocale()->toString() === $locale->toString()) {
                return $message;
            }
        }

        throw new MessageNotFoundException(sprintf('Could not find message with ID "%s".', $id));
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

    /**
     * @return string[]
     */
    private function getFallbackLocales(LocaleInterface $locale): array
    {
        $defaultLocale = $this->config->getDefaultLocale();

        $fallbacks = [
            $locale->toString(),
            $locale->baseName(),
            implode('-', array_filter([$locale->language(), $locale->region()])),
            $locale->language(),
            $defaultLocale ? $defaultLocale->toString() : null,
        ];

        /** @var string[] */
        return array_values(array_unique(array_filter($fallbacks)));
    }

    private function cleanMessage(string $message): string
    {
        return trim((string) preg_replace('/\n\s*/', ' ', $message));
    }
}
