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

use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\LocaleInterface;

/**
 * FormatPHP configuration
 */
class Config implements ConfigInterface
{
    private LocaleInterface $locale;
    private ?LocaleInterface $defaultLocale;
    private string $idInterpolatorPattern;

    /**
     * @var array<string, callable(string):string>
     */
    private array $defaultRichTextElements;

    /**
     * @param array<string, callable(string):string> $defaultRichTextElements
     */
    public function __construct(
        LocaleInterface $locale,
        ?LocaleInterface $defaultLocale = null,
        array $defaultRichTextElements = [],
        string $idInterpolatorPattern = IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN
    ) {
        $this->locale = $locale;
        $this->defaultLocale = $defaultLocale;
        $this->idInterpolatorPattern = $idInterpolatorPattern;
        $this->defaultRichTextElements = $defaultRichTextElements;
    }

    public function getDefaultLocale(): ?LocaleInterface
    {
        return $this->defaultLocale;
    }

    public function getIdInterpolatorPattern(): string
    {
        return $this->idInterpolatorPattern;
    }

    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * @return array<string, callable(string):string>
     */
    public function getDefaultRichTextElements(): array
    {
        return $this->defaultRichTextElements;
    }
}
