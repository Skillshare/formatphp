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

namespace FormatPHP\Extractor;

use Closure;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToGenerateMessageIdException;

use function base64_encode;
use function bin2hex;
use function count;
use function hash;
use function hash_algos;
use function in_array;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function strlen;
use function strtr;
use function substr;
use function trim;

/**
 * IdInterpolator supports generation of message descriptor IDs
 *
 * If message descriptors are missing the id property, we will use this
 * pattern to automatically generate IDs for them.
 *
 * The pattern follows this format:
 *
 *     [hashAlgorithm:contenthash:encodingAlgorithm:length]
 *
 * When passing this value, provide the hashAlgorithm, encodingAlgorithm,
 * and length, and formatphp will calculate the contenthash.
 *
 * For example, if you wish to use `haval160,4` as the hashing algorithm,
 * `hex` as the encoding algorithm, with a length of 10, you would pass
 * the following string:
 *
 *     [haval160,4:contenthash:hex:10]
 *
 * See <https://www.php.net/hash_algos> for available hashing algorithms.
 *
 * The following binary-to-text encodings are supported:
 *
 * - `base64`
 * - `base64url`
 * - `hex`
 */
class IdInterpolator
{
    public const DEFAULT_ID_INTERPOLATION_PATTERN = '[sha512:contenthash:base64:6]';
    private const PATTERN_MATCHER = '/\[(?:([^:\]]+):)?(?:hash|contenthash)(?::([a-z0-9]+))?(?::(\d+))?\]/';

    /**
     * Generates and returns an ID for the given message descriptor
     *
     * If the message descriptor already has an ID, we do not generate a new
     * one; we return the current one, instead.
     *
     * If the message descriptor does not have a default message, we cannot
     * generate an ID, so we throw `UnableToGenerateMessageId`.
     *
     * @throws InvalidArgumentException
     * @throws UnableToGenerateMessageIdException
     */
    public function generateId(
        DescriptorInterface $descriptor,
        string $idInterpolationPattern = self::DEFAULT_ID_INTERPOLATION_PATTERN
    ): string {
        if ($descriptor->getId() !== null) {
            return (string) $descriptor->getId();
        }

        $contentHash = $this->buildContentHash($descriptor);
        $options = $this->parsePattern($idInterpolationPattern);

        if (!in_array($options->hashingAlgorithm, hash_algos())) {
            throw new InvalidArgumentException(sprintf(
                'Unknown or unsupported hashing algorithm: "%s".',
                $options->hashingAlgorithm,
            ));
        }

        $encoder = $this->getEncoder($options->encodingAlgorithm);
        $binaryHash = hash($options->hashingAlgorithm, $contentHash, true);

        return substr($encoder($binaryHash), 0, $options->length);
    }

    /**
     * @return Closure(string):string
     *
     * @throws InvalidArgumentException
     */
    private function getEncoder(string $encodingType): Closure
    {
        switch ($encodingType) {
            case 'base64':
                return fn (string $data): string => base64_encode($data);
            case 'base64url':
                return fn (string $data): string => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
            case 'hex':
                return fn (string $data): string => bin2hex($data);
        }

        throw new InvalidArgumentException(sprintf('Unknown or unsupported encoding algorithm: "%s".', $encodingType));
    }

    /**
     * @throws UnableToGenerateMessageIdException
     */
    private function buildContentHash(DescriptorInterface $descriptor): string
    {
        $data = '';

        if ($descriptor->getDefaultMessage() !== null) {
            $data .= trim((string) preg_replace('/\n\s*/', ' ', (string) $descriptor->getDefaultMessage()));
        }

        if (strlen($data) > 0 && $descriptor->getDescription() !== null) {
            $data .= '#' . (string) $descriptor->getDescription();
        }

        if ($data === '') {
            throw new UnableToGenerateMessageIdException(
                'To auto-generate a message ID, the message descriptor must '
                . 'have a default message and, optionally, a description.',
            );
        }

        return $data;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function parsePattern(string $pattern): IdInterpolatorOptions
    {
        preg_match(self::PATTERN_MATCHER, $pattern, $matches);

        if (count($matches) !== 4) {
            throw new InvalidArgumentException(
                sprintf('Pattern is not a valid ID interpolation pattern: "%s".', $pattern),
            );
        }

        return new IdInterpolatorOptions($matches[1], $matches[2], (int) $matches[3]);
    }
}
