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

use IteratorAggregate;
use Ramsey\Collection\AbstractCollection;
use Ramsey\Collection\Exception\InvalidArgumentException;

/**
 * FormatPHP collection of Message instances
 *
 * @extends AbstractCollection<MessageInterface>
 * @implements IteratorAggregate<array-key, MessageInterface>
 */
final class MessageCollection extends AbstractCollection implements IteratorAggregate
{
    public function getType(): string
    {
        return MessageInterface::class;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if ($value instanceof MessageInterface) {
            $offset = $value->getId();
        }

        parent::offsetSet($offset, $value);
    }
}
