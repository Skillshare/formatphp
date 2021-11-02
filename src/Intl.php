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

use function is_array;
use function is_string;
use function sprintf;

/**
 * FormatPHP internationalization and localization
 */
class Intl implements Intl\Config, Intl\Formatters
{
    private Intl\Locale $locale;
    private Intl\MessageCollection $messages;
    private ?Intl\Locale $defaultLocale = null;

    /**
     * @param Intl\Locale | string $locale
     * @param Intl\MessageCollection | iterable<Intl\Message> $messages
     * @param Intl\Locale | string | null $defaultLocale
     *
     * @throws Exception\InvalidArgument
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function __construct($locale, iterable $messages, $defaultLocale = null)
    {
        $locale = is_string($locale) ? new Locale($locale) : $locale;
        $messages = is_array($messages) ? new Intl\MessageCollection($messages) : $messages;
        $defaultLocale = is_string($defaultLocale) ? new Locale($defaultLocale) : $defaultLocale;

        if (!$locale instanceof Intl\Locale) {
            throw new Exception\InvalidArgument(sprintf(
                'Locale must be an instance of %s or a string locale.',
                Intl\Locale::class,
            ));
        }

        if (!$messages instanceof Intl\MessageCollection) {
            throw new Exception\InvalidArgument(sprintf(
                'Messages must be an instance of %s or an array of %s objects.',
                Intl\MessageCollection::class,
                Intl\Message::class,
            ));
        }

        /** @phpstan-ignore-next-line */
        if ($defaultLocale !== null && !($defaultLocale instanceof Intl\Locale)) {
            throw new Exception\InvalidArgument(sprintf(
                'Default locale must be an instance of %s, a string locale, or null.',
                Intl\Locale::class,
            ));
        }

        $this->locale = $locale;
        $this->messages = $messages;
        $this->defaultLocale = $defaultLocale;
    }

    public function getDefaultLocale(): ?Intl\Locale
    {
        return $this->defaultLocale;
    }

    public function getLocale(): Intl\Locale
    {
        return $this->locale;
    }

    public function getMessages(): Intl\MessageCollection
    {
        return $this->messages;
    }

    public function getIdInterpolatorPattern(): string
    {
        return IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN;
    }

    /**
     * @throws Exception\InvalidArgument
     *
     * @inheritdoc
     */
    public function formatMessage(array $descriptor, ?array $values = null): string
    {
        return Intl\Formatter\MessageFormatter::format($this, $descriptor, $values);
    }
}
