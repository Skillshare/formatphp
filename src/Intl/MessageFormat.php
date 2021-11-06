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

use FormatPHP\ConfigInterface;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Exception\UnableToGenerateMessageIdException;
use FormatPHP\Extractor\IdInterpolator;
use MessageFormatter as PhpMessageFormatter;

use function preg_replace;
use function sprintf;
use function trim;

/**
 * Formats a message using {@link https://unicode-org.github.io/icu/userguide/format_parse/messages/ ICU Message syntax}
 */
class MessageFormat
{
    private ConfigInterface $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Returns a translated string for the given descriptor ID
     *
     * If the descriptor does not have an ID, we will use a combination of the
     * defaultMessage and description to create an ID.
     *
     * If we cannot find the given ID in the configured messages, we will use
     * the descriptor's defaultMessage, if provided.
     *
     * @param array<array-key, int | float | string> $values
     *
     * @throws InvalidArgumentException
     */
    public function format(DescriptorInterface $descriptor, array $values = []): string
    {
        return (string) PhpMessageFormatter::formatMessage(
            (string) $this->config->getLocale()->baseName(),
            $this->getMessage($descriptor),
            $values,
        );
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
     * @throws InvalidArgumentException
     */
    private function getMessage(DescriptorInterface $descriptor): string
    {
        $messageId = $this->buildMessageId($descriptor);

        try {
            return $this->lookupMessage($messageId);
        } catch (MessageNotFoundException $exception) {
            if ($descriptor->getDefaultMessage() !== null) {
                return trim((string) preg_replace('/\n\s*/', ' ', (string) $descriptor->getDefaultMessage()));
            }
        }

        return $messageId;
    }

    /**
     * @throws MessageNotFoundException
     */
    private function lookupMessage(string $messageId): string
    {
        $config = $this->config;

        try {
            return $config->getMessages()->getMessage($messageId, $config->getLocale());
        } catch (MessageNotFoundException $exception) {
            try {
                // Try falling back to a locale made up of just the language.
                return $config->getMessages()->getMessage(
                    $messageId,
                    new Locale((string) $config->getLocale()->language()),
                );
            } catch (MessageNotFoundException $exception) {
                $defaultLocale = $config->getDefaultLocale();
                if ($defaultLocale !== null) {
                    return $config->getMessages()->getMessage($messageId, $defaultLocale);
                }
            }
        }

        throw new MessageNotFoundException(sprintf('Unable to look up message with ID "%s".', $messageId));
    }
}
