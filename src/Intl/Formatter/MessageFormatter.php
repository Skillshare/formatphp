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

use FormatPHP\Descriptor;
use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Exception\MessageNotFound;
use FormatPHP\Exception\UnableToGenerateMessageId;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Config;
use FormatPHP\Intl\Descriptor as IntlDescriptor;
use MessageFormatter as IntlMessageFormatter;

use function is_array;
use function is_object;
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
     * @param IntlDescriptor | array{id?: string, defaultMessage?: string, description?: string} $descriptor
     * @param object | array<array-key, int | float | string> | null $values
     *
     * @throws InvalidArgument
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public static function format(Config $config, $descriptor, $values = null): string
    {
        return (string) IntlMessageFormatter::formatMessage(
            $config->getLocale()->getId(),
            self::getMessage($config, $descriptor),
            self::buildMessageValues($values),
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
     * @param IntlDescriptor | array{id?: string, defaultMessage?: string, description?: string} | mixed $descriptor
     *
     * @throws InvalidArgument
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private static function buildDescriptor($descriptor): IntlDescriptor
    {
        if ($descriptor instanceof IntlDescriptor) {
            return $descriptor;
        }

        if (is_object($descriptor)) {
            $descriptor = (array) $descriptor;
        }

        if (!is_array($descriptor)) {
            throw new InvalidArgument(sprintf(
                'Descriptor must be a %s, array, or object with public properties.',
                IntlDescriptor::class,
            ));
        }

        return new Descriptor(
            isset($descriptor['id']) ? (string) $descriptor['id'] : null,
            isset($descriptor['defaultMessage']) ? (string) $descriptor['defaultMessage'] : null,
            isset($descriptor['description']) ? (string) $descriptor['description'] : null,
        );
    }

    /**
     * @param object | array<array-key, int | float | string> | mixed | null $values
     *
     * @return mixed[]
     *
     * @throws InvalidArgument
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private static function buildMessageValues($values): array
    {
        if (is_object($values)) {
            $values = (array) $values;
        }

        if (!is_array($values) && $values !== null) {
            throw new InvalidArgument(
                'Values must be an array, an object with public properties, or null.',
            );
        }

        return $values ?? [];
    }

    /**
     * @param IntlDescriptor | array{id?: string, defaultMessage?: string, description?: string} $descriptor
     *
     * @throws InvalidArgument
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private static function getMessage(Config $config, $descriptor): string
    {
        $messageDescriptor = self::buildDescriptor($descriptor);
        $messageId = self::buildMessageId($messageDescriptor, $config);

        try {
            return self::lookupMessage($config, $messageId);
        } catch (MessageNotFound $exception) {
            if ($messageDescriptor->getDefaultMessage() !== null) {
                return trim((string) preg_replace('/\n\s*/', ' ', (string) $messageDescriptor->getDefaultMessage()));
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
