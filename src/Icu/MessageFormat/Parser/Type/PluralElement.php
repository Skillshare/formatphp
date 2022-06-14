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

namespace FormatPHP\Icu\MessageFormat\Parser\Type;

/**
 * @psalm-type PluralKeyType = "zero" | "one" | "two" | "few" | "many" | "other" | string
 * @psalm-type PluralType = "cardinal" | "ordinal"
 */
final class PluralElement extends AbstractElement
{
    /**
     * @var array<PluralKeyType, PluralOrSelectOption>
     */
    public array $options;

    public ?int $offset;

    /**
     * @var PluralType | null
     */
    public ?string $pluralType;

    /**
     * @param array<PluralKeyType, PluralOrSelectOption> $options
     * @param PluralType | null $pluralType
     */
    public function __construct(string $value, array $options, ?int $offset, ?string $pluralType, Location $location)
    {
        $this->type = ElementType::Plural();
        $this->value = $value;
        $this->options = $options;
        $this->offset = $offset;
        $this->pluralType = $pluralType;
        $this->location = $location;
    }
}
