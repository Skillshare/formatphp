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

use ReturnTypeWillChange;
use stdClass;

use function get_object_vars;

trait OptionSerializer
{
    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $options = [];

        /**
         * @var string $property
         * @var scalar | mixed[] | null $value
         */
        foreach (get_object_vars($this) as $property => $value) {
            if ($value === null) {
                continue;
            }

            $options[$property] = $value;
        }

        // If empty, return object to serialize value as an object in JSON.
        return $options ?: new stdClass();
    }
}
