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

use FormatPHP\Exception\UnableToFormatMessageException;

/**
 * A message formatter designed to fit within the style and function of
 * ECMA-402 formatters
 *
 * @link https://unicode-org.github.io/icu/userguide/format_parse/messages/
 * @link https://github.com/unicode-org/message-format-wg
 * @link https://www.php.net/MessageFormatter
 * @link https://formatjs.io/docs/intl-messageformat/
 * @link https://github.com/tc39/ecma402/issues/92
 * @link http://messageformat.github.io/messageformat/api/core.messageformat/
 */
interface MessageFormatInterface
{
    /**
     * Formats an ICU message format pattern, using a locale configured with the
     * message format instance and replacing any placeholders with the provided
     * values
     *
     * In addition to string and number values, the `$values` parameter may have
     * a callable that accepts a string and returns a string. For any callable,
     * the array key should match a "tag" embedded in the message.
     *
     * For example, if you wish to produce the following HTML:
     *
     *     Hello, <a href="/profile/1234">Ben</a>!
     *
     * Format the message like this:
     *
     *     Hello, <profileLink>{name}</profileLink>!
     *
     * Then, pass a callable to `$values` with the key `profileLink`. It will
     * look something like this:
     *
     * ```php
     * $formatphp->formatMessage(
     *     [
     *         'id' => 'welcome',
     *         'defaultMessage' => 'Hello, <profileLink>{name}</profileLink>!',
     *     ],
     *     [
     *         'name' => 'Ben',
     *         'profileLink' => fn (string $text): string => '<a href="/profile/1234">' . $text . '</a>',
     *     ],
     * );
     * ```
     *
     * @throws UnableToFormatMessageException
     *
     * @psalm-param array<array-key, float | int | string | callable(string=):string> $values
     */
    public function format(string $pattern, array $values = []): string;
}
