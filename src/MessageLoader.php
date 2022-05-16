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

namespace FormatPHP;

use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Exception\LocaleNotFoundException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;

use function array_filter;
use function array_unique;
use function array_values;
use function file_exists;
use function implode;
use function is_callable;
use function scandir;
use function sprintf;
use function str_replace;
use function strtolower;

use const DIRECTORY_SEPARATOR;
use const SCANDIR_SORT_NONE;

/**
 * Loads messages for a given locale from the file system or cache
 *
 * @psalm-import-type ReaderType from ReaderInterface
 */
class MessageLoader
{
    private const MESSAGE_FILE_EXTENSION = '.json';

    private ConfigInterface $config;
    private FileSystemHelper $fileSystemHelper;
    private FormatHelper $formatHelper;
    private string $messagesDirectory;

    /**
     * @var ReaderType
     */
    private $formatReader;

    /**
     * @param ReaderType | string | null $formatReader
     *
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     */
    public function __construct(
        string $messagesDirectory,
        ConfigInterface $config,
        $formatReader = null,
        ?FileSystemHelper $fileSystemHelper = null,
        ?FormatHelper $formatHelper = null
    ) {
        $this->config = $config;
        $this->fileSystemHelper = $fileSystemHelper ?? new FileSystemHelper();
        $this->formatHelper = $formatHelper ?? new FormatHelper($this->fileSystemHelper);
        $this->messagesDirectory = $this->fileSystemHelper->getRealPath($messagesDirectory);
        $this->formatReader = $this->loadFormatReader($formatReader);

        if (!$this->fileSystemHelper->isDirectory($this->messagesDirectory)) {
            throw new InvalidArgumentException(sprintf(
                'Messages directory "%s" is not a valid directory',
                $messagesDirectory,
            ));
        }
    }

    /**
     * Returns a MessageCollection according to the configuration provided to
     * this MessageLoader
     *
     * @throws InvalidMessageShapeException
     * @throws LocaleNotFoundException
     */
    public function loadMessages(): MessageCollection
    {
        return ($this->formatReader)($this->getLocaleMessages());
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws LocaleNotFoundException
     */
    private function getLocaleMessages(): array
    {
        $messagesContents = false;

        foreach ($this->getFallbackLocales() as $locale) {
            try {
                $messagesContents = $this->fileSystemHelper->getJsonContents(
                    $this->getFilePathForLocale($locale),
                );

                break;
            } catch (UnableToProcessFileException $exception) {
                continue;
            }
        }

        if ($messagesContents === false) {
            throw new LocaleNotFoundException(sprintf(
                'Unable to find a suitable locale for "%s" in %s; please set a default locale',
                $this->config->getLocale()->toString(),
                $this->messagesDirectory,
            ));
        }

        return $messagesContents;
    }

    /**
     * @return string[]
     */
    private function getFallbackLocales(): array
    {
        $locale = $this->config->getLocale();
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

    /**
     * @param ReaderType | string | null $formatReader
     *
     * @return ReaderType
     *
     * @throws ImproperContextException
     * @throws InvalidArgumentException
     */
    private function loadFormatReader($formatReader): callable
    {
        if ($formatReader === null) {
            return new FormatPHPReader();
        }

        if ($formatReader instanceof ReaderInterface) {
            return $formatReader;
        }

        if (is_callable($formatReader)) {
            return $this->formatHelper->validateReaderCallable($formatReader);
        }

        return $this->formatHelper->getReader($formatReader);
    }

    private function getFilePathForLocale(string $locale): string
    {
        $filePath = $this->messagesDirectory . DIRECTORY_SEPARATOR . $locale . self::MESSAGE_FILE_EXTENSION;
        if (file_exists($filePath)) {
            return $filePath;
        }

        // If the file doesn't exist, check for alternate casings and notations.
        // e.g., en-XB, en_XB, en-xb, en_xb, EN-XB, EN_XB, eN-xB, etc.
        $normalize = fn (string $filename): string => str_replace('_', '-', strtolower($filename));
        $searchFile = $normalize($locale . self::MESSAGE_FILE_EXTENSION);
        $localeFiles = scandir($this->messagesDirectory, SCANDIR_SORT_NONE) ?: [];

        foreach ($localeFiles as $localeFile) {
            if ($normalize($localeFile) === $searchFile) {
                return $this->messagesDirectory . DIRECTORY_SEPARATOR . $localeFile;
            }
        }

        throw new UnableToProcessFileException(sprintf(
            'Could not find file for locale "%s" in %s',
            $locale,
            $this->messagesDirectory,
        ));
    }
}
