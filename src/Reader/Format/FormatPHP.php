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

namespace FormatPHP\Reader\Format;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\Message;
use FormatPHP\MessageCollection;
use FormatPHP\Reader\FormatInterface;

use function assert;
use function gettype;
use function is_array;
use function is_string;
use function sprintf;

/**
 * Returns a MessageCollection parsed from JSON-decoded data that was written
 * using Writer\Format\FormatPHP
 *
 * @see \FormatPHP\Writer\Format\FormatPHP
 */
class FormatPHP implements FormatInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(Config $config, array $data, LocaleInterface $localeResolved): MessageCollection
    {
        $messages = new MessageCollection($config);

        foreach ($data as $messageId => $message) {
            $this->validateShape($messageId, $message);
            assert(is_string($messageId));
            assert(isset($message['defaultMessage']));
            assert(is_string($message['defaultMessage']));

            $messages[] = new Message($localeResolved, $messageId, $message['defaultMessage']);
        }

        return $messages;
    }

    /**
     * @param array-key $messageId
     * @param mixed $message
     *
     * @throws InvalidMessageShapeException
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    private function validateShape($messageId, $message): void
    {
        if (!is_string($messageId)) {
            throw new InvalidMessageShapeException(sprintf(
                '%s expects a string message ID; received %s',
                self::class,
                gettype($messageId),
            ));
        }

        if (!is_array($message) || !is_string($message['defaultMessage'] ?? null)) {
            throw new InvalidMessageShapeException(sprintf(
                '%s expects a string defaultMessage property; defaultMessage does not exist or is not a string',
                self::class,
            ));
        }
    }
}
