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

/**
 * An ECMA-402 locale identifier
 *
 * This defines an interface for PHP that conforms to Intl.Locale defined in the
 * ECMAScript 2022 Internationalization API Specification (ECMA-402 9th Edition).
 *
 * @link https://tc39.es/ecma402/#locale-objects
 */
interface LocaleInterface
{
    /**
     * Returns a substring of this locale that provides basic locale information
     */
    public function baseName(): ?string;

    /**
     * Returns this locale's calendar era
     */
    public function calendar(): ?string;

    /**
     * Returns whether case is accounted for in this locale's collation rules
     *
     * @psalm-return "upper" | "lower" | "false" | null
     */
    public function caseFirst(): ?string;

    /**
     * Returns this locale's collation type
     */
    public function collation(): ?string;

    /**
     * Returns this locale's time-keeping convention
     *
     * @psalm-return "h11" | "h12" | "h23" | "h24" | null
     */
    public function hourCycle(): ?string;

    /**
     * Returns this locale's language
     */
    public function language(): ?string;

    /**
     * Using the existing values set on this locale instance, returns the most
     * likely values that can be determined for language, script, and region
     */
    public function maximize(): LocaleInterface;

    /**
     * Removes any information from the locale that would be added by calling
     * maximize()
     */
    public function minimize(): LocaleInterface;

    /**
     * Returns this locale's numeral system
     */
    public function numberingSystem(): ?string;

    /**
     * Returns whether this locale has special collation handling for
     * numeric strings
     */
    public function numeric(): bool;

    /**
     * Returns this locale's region
     */
    public function region(): ?string;

    /**
     * Returns this locale's script used for writing
     */
    public function script(): ?string;

    /**
     * Returns the full string identifier for this locale
     */
    public function toString(): string;
}
