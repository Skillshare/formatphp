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
use FormatPHP\Format\Reader\SmartlingReader;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

/**
 * Smartling formatter for FormatPHP
 *
 * This follows the same format as the Smartling formatter for FormatJS and
 * implements the Smartling JSON format.
 *
 * ```json
 * {
 *   "smartling": {
 *     "string_format": "icu",
 *     "translate_paths": [
 *       {
 *         "instruction": "*\/description",
 *         "key": "{*}\/message",
 *         "path": "*\/message"
 *       }
 *     ],
 *     "variants_enabled": true
 *   },
 *   "my.message": {
 *     "description": "And I'm providing more details for translators here."
 *     "message": "This is a message for translation."
 *   }
 * }
 * ```
 *
 * @link https://help.smartling.com/hc/en-us/articles/360008000733 Smartling JSON Format
 * @see SmartlingReader
 */
class SmartlingWriter implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        $format = [
            'smartling' => [
                'string_format' => 'icu',
                'translate_paths' => [
                    [
                        'instruction' => '*/description',
                        'key' => '{*}/message',
                        'path' => '*/message',
                    ],
                ],
                'variants_enabled' => true,
            ],
        ];

        foreach ($collection as $item) {
            $message = [];

            if ($item->getDescription() !== null) {
                $message['description'] = $item->getDescription();
            }

            $message['message'] = $item->getDefaultMessage() ?? '';

            $format[(string) $item->getId()] = $message;
        }

        return $format;
    }
}
