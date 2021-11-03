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

namespace FormatPHP\Intl\Formatter;

use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Exception\MessageNotFound;
use FormatPHP\Exception\UnableToGenerateMessageId;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Config;
use FormatPHP\Intl\Descriptor as IntlDescriptor;
use MessageFormatter as IntlMessageFormatter;

use function preg_replace;
use function sprintf;
use function trim;

/**
 * Formats a message using {@link https://unicode-org.github.io/icu/userguide/format_parse/messages/ ICU Message syntax}
 *
 * @internal
 */
final class MessageFormatter
{
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
     * @throws InvalidArgument
     */
    public static function format(Config $config, IntlDescriptor $descriptor, array $values = []): string
    {
        return (string) IntlMessageFormatter::formatMessage(
            $config->getLocale()->getId(),
            self::getMessage($config, $descriptor),
            $values,
        );
    }

    /**
     * @throws InvalidArgument
     */
    private static function buildMessageId(IntlDescriptor $descriptor, Config $config): string
    {
        try {
            $messageId = (new IdInterpolator())->generateId(
                $descriptor,
                $config->getIdInterpolatorPattern(),
            );
        } catch (UnableToGenerateMessageId $exception) {
            $messageId = '';
        }

        return $messageId;
    }

    /**
     * @throws InvalidArgument
     */
    private static function getMessage(Config $config, IntlDescriptor $descriptor): string
    {
        $messageId = self::buildMessageId($descriptor, $config);

        try {
            return self::lookupMessage($config, $messageId);
        } catch (MessageNotFound $exception) {
            if ($descriptor->getDefaultMessage() !== null) {
                return trim((string) preg_replace('/\n\s*/', ' ', (string) $descriptor->getDefaultMessage()));
            }
        }

        return $messageId;
    }

    /**
     * @throws MessageNotFound
     */
    private static function lookupMessage(Config $config, string $messageId): string
    {
        try {
            return $config->getMessages()->getMessage($messageId, $config->getLocale());
        } catch (MessageNotFound $exception) {
            try {
                return $config->getMessages()->getMessage($messageId, $config->getLocale()->getFallbackLocale());
            } catch (MessageNotFound $exception) {
                $defaultLocale = $config->getDefaultLocale();
                if ($defaultLocale !== null) {
                    return $config->getMessages()->getMessage($messageId, $defaultLocale);
                }
            }
        }

        throw new MessageNotFound(sprintf('Unable to look up message with ID "%s".', $messageId));
    }
}
