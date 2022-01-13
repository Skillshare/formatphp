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
use FormatPHP\Exception\UnableToGenerateMessageIdException;
use FormatPHP\Intl\MessageFormat;
use FormatPHP\Util\MessageCleaner;
use FormatPHP\Util\MessageRetriever;

use function array_merge;
use function is_int;

/**
 * FormatPHP internationalization and localization
 */
class FormatPHP implements FormatterInterface
{
    use MessageCleaner;
    use MessageRetriever;

    private ConfigInterface $config;
    private MessageCollection $messages;
    private MessageFormat $messageFormat;

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(
        ConfigInterface $config,
        MessageCollection $messages
    ) {
        $this->config = $config;
        $this->messages = $messages;
        $this->messageFormat = new MessageFormat($config->getLocale());
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatMessageException
     *
     * @inheritdoc
     */
    public function formatMessage(array $descriptor, array $values = []): string
    {
        // Combine the global default rich text element callbacks with the values,
        // giving preference to values provided with the same keys.
        $values = array_merge($this->config->getDefaultRichTextElements(), $values);

        try {
            $messagePattern = $this->getMessageForDescriptor(
                $this->messages,
                new Descriptor(
                    $descriptor['id'] ?? null,
                    $descriptor['defaultMessage'] ?? null,
                    $descriptor['description'] ?? null,
                ),
            );
        } catch (UnableToGenerateMessageIdException $exception) {
            throw new InvalidArgumentException(
                'The message descriptor must have an ID or default message',
                is_int($exception->getCode()) ? $exception->getCode() : 0, // @phpstan-ignore-line
                $exception,
            );
        }

        return $this->messageFormat->format($this->cleanMessage($messagePattern), $values);
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }
}
