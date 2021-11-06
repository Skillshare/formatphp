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
 * FormatPHP internationalization and localization
 */
class FormatPHP implements ConfigInterface, FormatterInterface
{
    private LocaleInterface $locale;
    private MessageCollection $messages;
    private ?LocaleInterface $defaultLocale;

    public function __construct(
        LocaleInterface $locale,
        MessageCollection $messages,
        ?LocaleInterface $defaultLocale = null
    ) {
        $this->locale = $locale;
        $this->messages = $messages;
        $this->defaultLocale = $defaultLocale;
    }

    public function getDefaultLocale(): ?LocaleInterface
    {
        return $this->defaultLocale;
    }

    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    public function getMessages(): MessageCollection
    {
        return $this->messages;
    }

    public function getIdInterpolatorPattern(): string
    {
        return IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN;
    }

    /**
     * @throws Exception\InvalidArgumentException
     *
     * @inheritdoc
     */
    public function formatMessage(array $descriptor, array $values = []): string
    {
        $descriptorInstance = new Descriptor(
            $descriptor['id'] ?? null,
            $descriptor['defaultMessage'] ?? null,
            $descriptor['description'] ?? null,
        );

        $formatter = new Intl\MessageFormat($this);

        return $formatter->format($descriptorInstance, $values);
    }
}
