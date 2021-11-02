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

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function count;
use function preg_match;
use function preg_match_all;

/**
 * A PhpParser\NodeVisitor that collects additional metadata from parsed source code
 */
class PragmaCollectorVisitor extends NodeVisitorAbstract
{
    /**
     * @var array<string, string>
     */
    private array $parsedPragma = [];

    private ?string $pragmaName;

    public function __construct(string $pragmaName)
    {
        preg_match('/^[a-z0-9_\-]+$/', $pragmaName, $nameMatches);
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

        $comments = $node->getComments();
        if (count($comments) === 0) {
            return null;
        }

        foreach ($comments as $comment) {
            preg_match('/@' . $this->pragmaName . ' (.*)/', $comment->getText(), $pragmaMatches);
            if (!isset($pragmaMatches[1])) {
                continue;
            }

            preg_match_all('/(([a-z0-9_\-]+):([a-z0-9_\-]+))+/', $pragmaMatches[1], $propertyMatches);
            foreach ($propertyMatches[2] as $index => $propertyName) {
                $this->parsedPragma[$propertyName] = $propertyMatches[3][$index];
            }
        }

        return null;
    }
}
