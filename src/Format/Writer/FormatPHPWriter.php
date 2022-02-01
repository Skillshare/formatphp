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

namespace FormatPHP\Format\Writer;

use FormatPHP\DescriptorCollection;
use FormatPHP\ExtendedDescriptorInterface;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

use function array_merge;
use function ksort;

use const SORT_FLAG_CASE;
use const SORT_NATURAL;

/**
 * Default formatter for FormatPHP
 *
 * This follows the same format as the default formatter for FormatJS:
 *
 * ```json
 * {
 *   "my.message": {
 *     "defaultMessage": "This is a message for translation.",
 *     "description": "And I'm providing more details for translators here."
 *   }
 * }
 * ```
 *
 * If `--extract-source-location` is provided, this will also include the
 * following properties for each message:
 *
 * - `file` : The path to the source file where the descriptor is located.
 * - `line` : The line in the source file where the descriptor starts.
 * - `start` : The string offset (0-indexed) of the starting character of the descriptor in the source file.
 * - `end` : The string offset (0-indexed) of the last character of the descriptor in the source file.
 *
 * If `--pragma` is provided, this will include a `meta` property with key-value
 * pairs, according to the pragma parsed from each source file.
 *
 * @link https://formatjs.io/docs/getting-started/message-extraction FormatJS message extraction
 * @see FormatPHPReader
 */
class FormatPHPWriter implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        $format = [];

        foreach ($collection as $item) {
            $message = [];
            $message['defaultMessage'] = $item->getDefaultMessage() ?? '';

            if ($item->getDescription() !== null) {
                $message['description'] = $item->getDescription();
            }

            if ($options->includesSourceLocation && $item instanceof ExtendedDescriptorInterface) {
                $message = array_merge($message, [
                    'end' => $item->getSourceEndOffset(),
                    'file' => $item->getSourceFile(),
                    'line' => $item->getSourceLine(),
                    'start' => $item->getSourceStartOffset(),
                ]);
            }

            if ($options->includesPragma && $item instanceof ExtendedDescriptorInterface) {
                $message = array_merge($message, [
                    'meta' => $item->getMetadata(),
                ]);
            }

            ksort($message);

            $format[(string) $item->getId()] = $message;
        }

        ksort($format, SORT_NATURAL | SORT_FLAG_CASE);

        return $format;
    }
}
