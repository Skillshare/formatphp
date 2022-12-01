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
use FormatPHP\Intl\DisplayNames;
use FormatPHP\Intl\DisplayNamesOptions;
use FormatPHP\Intl\MessageFormat;
use FormatPHP\Intl\NumberFormat;
use FormatPHP\Intl\NumberFormatOptions;
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
 * @psalm-import-type MessageValuesType from FormatterInterface
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
        ?ConfigInterface $config = null,
        ?MessageCollection $messages = null
    ) {
        $this->config = $config ?? new Config();
        $this->messages = $messages ?? new MessageCollection();
        $this->messageFormat = new MessageFormat($this->config->getLocale());
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
        /** @var MessageValuesType $values */
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

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatNumberException
     *
     * @inheritdoc
     */
    public function formatNumber($number, ?NumberFormatOptions $options = null): string
    {
        $formatter = new NumberFormat($this->config->getLocale(), $options);

        return $formatter->format($number);
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatNumberException
     *
     * @inheritdoc
     */
    public function formatCurrency($value, string $currencyCode, ?NumberFormatOptions $options = null): string
    {
        $options = $options ?? new NumberFormatOptions();
        $options->style = NumberFormatOptions::STYLE_CURRENCY;
        $options->currency = $currencyCode;

        return $this->formatNumber($value, $options);
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToFormatDisplayNameException
     *
     * @inheritdoc
     */
    public function formatDisplayName(string $value, ?DisplayNamesOptions $options = null): ?string
    {
        $formatter = new DisplayNames($this->config->getLocale(), $options);

        return $formatter->of($value);
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
