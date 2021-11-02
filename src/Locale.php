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

use FormatPHP\Exception\InvalidArgument;
use InvalidArgumentException;
use Yiisoft\I18n\Locale as YiiLocale;

/**
 * FormatPHP locale
 */
class Locale implements Intl\Locale
{
    private YiiLocale $locale;

    /**
     * @throws InvalidArgument
     */
    public function __construct(string $locale)
    {
        try {
            $this->locale = new YiiLocale($locale);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgument($exception->getMessage(), (int) $exception->getCode(), $exception);
        }
    }

    public function getId(): string
    {
        return $this->locale->asString();
    }

    public function getFallbackLocale(): Intl\Locale
    {
        return new self($this->locale->fallbackLocale()->asString());
    }
}
