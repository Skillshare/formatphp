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

namespace FormatPHP\Extractor\Parser\Descriptor;

use FormatPHP\Exception\FormatPHPExceptionInterface;
use FormatPHP\Exception\UnableToParsePragmaException;
use FormatPHP\Extractor\Parser\ParserError;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function assert;
use function count;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function sprintf;
use function strtolower;
use function trim;

/**
 * A PhpParser\NodeVisitor that collects additional metadata from parsed source code
 */
class PragmaCollectorVisitor extends NodeVisitorAbstract
{
    public ParserErrorCollection $errors;

    /**
     * @var array<string, string>
     */
    private array $parsedPragma = [];

    private string $filePath;
    private ?string $pragmaName;

    public function __construct(string $filePath, string $pragmaName, ParserErrorCollection $errors)
    {
        $this->filePath = $filePath;
        $this->errors = $errors;

        preg_match('/^[a-z0-9_\-]+$/i', $pragmaName, $nameMatches);
        $this->pragmaName = $nameMatches[0] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function getMetadata(): array
    {
        return $this->parsedPragma;
    }

    /**
     * @return int | Node | null
     */
    public function enterNode(Node $node)
    {
        if ($this->pragmaName === null) {
            return null;
        }

        foreach ($node->getComments() as $comment) {
            $this->parseComment($comment);
        }

        return null;
    }

    private function parseComment(Comment $comment): void
    {
        assert($this->pragmaName !== null);

        preg_match_all('/@' . $this->pragmaName . '(?: |\b)(.*)/i', $comment->getText(), $matches);

        if (count($matches[1]) === 0) {
            // This comment does not contain pragma metadata.
            return;
        }

        foreach ($matches[1] as $metadata) {
            try {
                $this->parseMetadata($metadata);
            } catch (FormatPHPExceptionInterface $exception) {
                $this->errors[] = new ParserError(
                    $exception->getMessage(),
                    $this->filePath,
                    $comment->getStartLine(),
                    $exception,
                );
            }
        }
    }

    /**
     * @throws UnableToParsePragmaException
     */
    private function parseMetadata(string $metadata): void
    {
        $metadata = trim($metadata);

        if ($metadata === '') {
            throw new UnableToParsePragmaException('Pragma found without a value');
        }

        // We want to check whether the parsed metadata matches the original
        // string. If not, then we potentially lost some data, so we will
        // capture this and report it as an error, but we won't stop processing.
        // To compare, we'll convert the pre-parsed and parsed values into
        // strings, with all whitespace removed and converted to lowercase.
        $comparePreparsed = preg_replace('/\s+/', '', strtolower($metadata));
        $compareParsed = '';

        preg_match_all('/(([a-z0-9_\-]+):([a-z0-9_\-]+))+/i', $metadata, $matches);

        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         * @var int $index
         * @var string $propertyName
         */
        foreach ($matches[2] as $index => $propertyName) {
            $compareParsed .= preg_replace('/\s+/', '', strtolower("$propertyName:{$matches[3][$index]}"));
            $this->parsedPragma[$propertyName] = $matches[3][$index];
        }

        if ($comparePreparsed !== $compareParsed) {
            throw new UnableToParsePragmaException(sprintf(
                'Pragma contains data that could not be parsed: "%s"',
                $metadata,
            ));
        }
    }
}
