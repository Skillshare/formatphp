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

namespace FormatPHP\Intl;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToFormatMessageException;
use IntlException as PhpIntlException;
use Locale as PhpLocale;
use MessageFormatter as PhpMessageFormatter;

use function sprintf;

/**
 * Formats an ICU message format pattern
 */
class MessageFormat implements MessageFormatInterface
{
    private LocaleInterface $locale;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(?LocaleInterface $locale = null)
    {
        $this->locale = $locale ?? new Locale(PhpLocale::getDefault());
    }

    /**
     * @inheritdoc
     */
    public function format(string $pattern, array $values = []): string
    {
        try {
            $formatter = new PhpMessageFormatter((string) $this->locale->baseName(), $pattern);

            return (string) $formatter->format($values);
        } catch (PhpIntlException $exception) {
            throw new UnableToFormatMessageException(
                sprintf(
                    'Unable to format message with pattern "%s" for locale "%s"',
                    $pattern,
                    (string) $this->locale->baseName(),
                ),
                (int) $exception->getCode(),
                $exception,
            );
        }
    }
}
