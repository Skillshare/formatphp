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

use DateTimeImmutable as PhpDateTimeImmutable;
use DateTimeInterface as PhpDateTimeInterface;
use Exception as PhpException;
use FormatPHP\Intl\DateTimeFormat;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\MessageFormat;
use FormatPHP\Util\MessageCleaner;
use FormatPHP\Util\MessageRetriever;

use function array_merge;
use function gettype;
use function is_int;
use function is_string;
use function sprintf;

/**
 * FormatPHP internationalization and localization
 *
 * @psalm-import-type DateTimeType from FormatterInterface
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
        } catch (Exception\UnableToGenerateMessageIdException $exception) {
            throw new Exception\InvalidArgumentException(
                'The message descriptor must have an ID or default message',
                (int) $exception->getCode(),
                $exception,
            );
        }

        return $this->messageFormat->format($this->cleanMessage($messagePattern), $values);
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDateTimeException
     *
     * @inheritdoc
     */
    public function formatDate($date = null, ?DateTimeFormatOptions $options = null): string
    {
        $formatter = new DateTimeFormat($this->config->getLocale(), $options);

        return $formatter->format($this->convertToDateTime($date));
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDateTimeException
     *
     * @inheritdoc
     */
    public function formatTime($date = null, ?DateTimeFormatOptions $options = null): string
    {
        $options = $options ? clone $options : new DateTimeFormatOptions();

        if ($options->dateStyle === null && $options->timeStyle === null) {
            $options->hour = $options->hour ?? 'numeric';
            $options->minute = $options->minute ?? 'numeric';
        }

        return $this->formatDate($date, $options);
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
    }

    /**
     * @param DateTimeType | mixed $date
     *
     * @throws Exception\InvalidArgumentException
     * @throws PhpException
     */
    private function convertToDateTime($date): PhpDateTimeInterface
    {
        if ($date === null) {
            return new PhpDateTimeImmutable();
        }

        if ($date instanceof PhpDateTimeInterface) {
            return $date;
        }

        if (is_string($date)) {
            return new PhpDateTimeImmutable($date);
        }

        if (is_int($date)) {
            return new PhpDateTimeImmutable('@' . $date);
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Value must be a string, integer, or instance of DateTimeInterface; received \'%s\'',
            gettype($date),
        ));
    }
}
