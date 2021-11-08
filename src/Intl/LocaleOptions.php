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
 * Configuration options for the locale identifier
 */
class LocaleOptions
{
    /**
     * The locale's calendar era
     */
    public ?string $calendar = null;

    /**
     * Whether case should be accounted for in the locale's collation rules
     * (i.e. `"upper"`, `"lower"`, or `"false"`)
     *
     * @psalm-var "upper" | "lower" | "false"
     */
    public ?string $caseFirst = null;

    /**
     * The locale's collation type
     */
    public ?string $collation = null;

    /**
     * The locale's time-keeping convention (i.e., `"h11"`, `"h12"`, `"h23"`,
     * or `"h24"`)
     *
     * @psalm-var "h11" | "h12" | "h23" | "h24" | null
     */
    public ?string $hourCycle = null;

    /**
     * The locale's language
     */
    public ?string $language = null;

    /**
     * The locale's numeral system
     */
    public ?string $numberingSystem = null;

    /**
     * Whether the locale has special collation handling for numeric strings
     */
    public ?bool $numeric = null;

    /**
     * The locale's region
     */
    public ?string $region = null;

    /**
     * The locale's script used for writing
     */
    public ?string $script = null;

    /**
     * @param string | null $calendar The locale's calendar era
     * @param string | null $caseFirst Whether case should be accounted for in
     *     the locale's collation rules (i.e. `"upper"`, `"lower"`, or `"false"`)
     * @param string | null $collation The locale's collation type
     * @param string | null $hourCycle The locale's time-keeping convention
     *     (i.e., `"h11"`, `"h12"`, `"h23"`, or `"h24"`)
     * @param string | null $language The locale's language
     * @param string | null $numberingSystem The locale's numeral system
     * @param bool | null $numeric Whether the locale has special collation
     *     handling for numeric strings
     * @param string | null $region The locale's region
     * @param string | null $script The locale's script used for writing
     *
     * @psalm-param "upper" | "lower" | "false" | null $caseFirst
     * @psalm-param "h11" | "h12" | "h23" | "h24" | null $hourCycle
     */
    public function __construct(
        ?string $calendar = null,
        ?string $caseFirst = null,
        ?string $collation = null,
        ?string $hourCycle = null,
        ?string $language = null,
        ?string $numberingSystem = null,
        ?bool $numeric = null,
        ?string $region = null,
        ?string $script = null
    ) {
        $this->numeric = $numeric;
        $this->calendar = $calendar;
        $this->caseFirst = $caseFirst;
        $this->collation = $collation;
        $this->hourCycle = $hourCycle;
        $this->language = $language;
        $this->numberingSystem = $numberingSystem;
        $this->region = $region;
        $this->script = $script;
    }
}
