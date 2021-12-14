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
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Exception\LocaleNotFoundException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Util\FileSystemHelper;

use function array_filter;
use function array_unique;
use function array_values;
use function implode;
use function sprintf;

use const DIRECTORY_SEPARATOR;

/**
 * Loads messages for a given locale from the file system or cache
 */
class MessageLoader
{
    private ConfigInterface $config;
    private FileSystemHelper $fileSystemHelper;
    private ReaderInterface $formatReader;
    private string $messagesDirectory;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $messagesDirectory,
        ConfigInterface $config,
        ?ReaderInterface $formatReader = null,
        ?FileSystemHelper $fileSystemHelper = null
    ) {
        $this->config = $config;
        $this->formatReader = $formatReader ?? new FormatPHPReader();
        $this->fileSystemHelper = $fileSystemHelper ?? new FileSystemHelper();
        $this->messagesDirectory = $this->fileSystemHelper->getRealPath($messagesDirectory);

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
                $messagesFile = $this->messagesDirectory . DIRECTORY_SEPARATOR . $locale . '.json';
                $messagesContents = $this->fileSystemHelper->getJsonContents($messagesFile);

                break;
            } catch (UnableToProcessFileException $exception) {
                continue;
            }
        }

        if ($messagesContents === false) {
            throw new LocaleNotFoundException(sprintf(
                'Unable to find a suitable locale for "%s"; please set a default locale',
                $this->config->getLocale()->toString(),
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
}
