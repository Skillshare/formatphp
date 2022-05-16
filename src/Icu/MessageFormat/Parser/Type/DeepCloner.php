<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Icu\MessageFormat\Parser\Type;

use ReflectionObject;

use function is_array;
use function is_object;

trait DeepCloner
{
    public function __clone()
    {
        $this->cloneMyProperties();
    }

    private function cloneMyProperties(): void
    {
        $reflection = new ReflectionObject($this);

        foreach ($reflection->getProperties() as $reflectionProperty) {
            /** @var mixed $propertyValue */
            $propertyValue = $reflectionProperty->getValue($this);
            $reflectionProperty->setValue($this, $this->cloneValue($propertyValue));
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed The cloned value
     */
    private function cloneValue($value)
    {
        if (is_array($value)) {
            return $this->cloneArray($value);
        }

        if (is_object($value)) {
            return clone $value;
        }

        return $value;
    }

    /**
     * @param array<array-key, mixed> $value
     *
     * @return mixed[]
     *
     * @psalm-suppress MixedAssignment
     */
    private function cloneArray(array $value): array
    {
        /** @var mixed[] $clone */
        $clone = [];

        foreach ($value as $k => $v) {
            $clone[$k] = $this->cloneValue($v);
        }

        return $clone;
    }
}
