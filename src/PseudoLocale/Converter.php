<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\PseudoLocale;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\Format\WriterOptions;
use FormatPHP\Message;
use FormatPHP\PseudoLocale\Locale\EnXa;
use FormatPHP\PseudoLocale\Locale\EnXb;
use FormatPHP\PseudoLocale\Locale\XxAc;
use FormatPHP\PseudoLocale\Locale\XxHa;
use FormatPHP\PseudoLocale\Locale\XxLs;
use FormatPHP\PseudoLocale\Locale\XxZa;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use Psr\Log\LoggerInterface;

use function array_change_key_case;
use function count;
use function sprintf;
use function strtolower;

use const CASE_LOWER;

/**
 * Uses a set of rules to convert a locale to a pseudo locale that may be used
 * for testing purposes
 */
class Converter
{
    private const LOCALES = [
        PseudoLocale::EN_XA => EnXa::class,
        PseudoLocale::EN_XB => EnXb::class,
        PseudoLocale::XX_AC => XxAc::class,
        PseudoLocale::XX_HA => XxHa::class,
        PseudoLocale::XX_LS => XxLs::class,
        PseudoLocale::XX_ZA => XxZa::class,
    ];

    private ConverterOptions $options;
    private FileSystemHelper $fileSystemHelper;
    private FormatHelper $formatHelper;
    private LoggerInterface $logger;

    public function __construct(
        ConverterOptions $options,
        FileSystemHelper $fileSystemHelper,
        FormatHelper $formatHelper,
        LoggerInterface $logger
    ) {
        $this->options = $options;
        $this->fileSystemHelper = $fileSystemHelper;
        $this->formatHelper = $formatHelper;
        $this->logger = $logger;
    }

    /**
     * Processes the locale file, converting it to the pseudo locale
     *
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     * @throws UnableToProcessFileException
     * @throws UnableToWriteFileException
     */
    public function convert(string $file, string $pseudoLocale): void
    {
        $contents = $this->fileSystemHelper->getJsonContents($file);
        $reader = $this->formatHelper->getReader($this->options->inFormat);
        $writer = $this->formatHelper->getWriter($this->options->outFormat);
        $localeConverter = $this->getPseudoLocale($pseudoLocale);

        $messages = $reader($contents);
        $descriptors = new DescriptorCollection();

        /** @var Message $message */
        foreach ($messages as $message) {
            $descriptors[] = new Descriptor(
                $message->getId(),
                $localeConverter->convert($message->getMessage()),
            );
        }

        $this->write($pseudoLocale, $writer, $descriptors);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getPseudoLocale(string $pseudoLocale): PseudoLocaleInterface
    {
        $locales = array_change_key_case(self::LOCALES, CASE_LOWER);

        /**
         * @var class-string<PseudoLocaleInterface> | null $localeClass
         * @psalm-suppress InvalidArrayOffset
         */
        $localeClass = $locales[strtolower($pseudoLocale)] ?? null;

        if ($localeClass === null) {
            throw new InvalidArgumentException(sprintf(
                'Unknown pseudo locale "%s"',
                $pseudoLocale,
            ));
        }

        return new $localeClass();
    }

    /**
     * @param callable(DescriptorCollection,WriterOptions):array<mixed> $formatter
     *
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     */
    private function write(string $pseudoLocale, callable $formatter, DescriptorCollection $descriptors): void
    {
        $file = $this->options->outFile ?? 'php://output';

        $messages = $formatter($descriptors, new WriterOptions());
        if (count($messages) === 0) {
            $messages = (object) $messages;
        }

        $this->fileSystemHelper->writeJsonContents($file, $messages);

        if ($this->options->outFile !== null) {
            $this->logger->notice(
                'Messages converted to pseudo locale {locale} and written to {file}',
                [
                    'locale' => $pseudoLocale,
                    'file' => $this->options->outFile,
                ],
            );
        }
    }
}
