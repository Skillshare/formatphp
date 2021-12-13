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

use IteratorAggregate;
use JsonSerializable;
use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\CollectionInterface;
use Ramsey\Collection\Exception\CollectionMismatchException;
use ReturnTypeWillChange;

/**
 * @extends AbstractCollection<ElementInterface>
 * @implements IteratorAggregate<array-key, ElementInterface>
 */
final class ElementCollection extends AbstractCollection implements
    IteratorAggregate,
    JsonSerializable
{
    public function getType(): string
    {
        return ElementInterface::class;
    }

    /**
     * @param ElementCollection ...$collections
     *
     * @return ElementCollection
     *
     * @throws CollectionMismatchException
     *
     * @psalm-suppress MoreSpecificImplementedParamType, MoreSpecificReturnType
     */
    public function merge(CollectionInterface ...$collections): CollectionInterface
    {
        /** @var ElementCollection */
        return parent::merge(...$collections);
    }

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function __clone()
    {
        $items = [];

        foreach ($this->data as $datum) {
            $items[] = clone $datum;
        }

        $this->data = $items;
    }
}
