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

namespace FormatPHP\Util;

use FormatPHP\ConfigInterface;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToGenerateMessageIdException;
use FormatPHP\Extractor\IdInterpolator;

/**
 * Provides tools for building message IDs for descriptors
 */
trait DescriptorIdBuilder
{
    abstract protected function getConfig(): ConfigInterface;

    /**
     * Builds a message ID for the given descriptor or returns its existing ID
     *
     * @throws InvalidArgumentException
     * @throws UnableToGenerateMessageIdException
     */
    private function buildMessageId(DescriptorInterface $descriptor): string
    {
        return (new IdInterpolator())->generateId(
            $descriptor,
            $this->getConfig()->getIdInterpolatorPattern(),
        );
    }
}
