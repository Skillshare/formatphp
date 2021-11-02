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

use FormatPHP\Descriptor;
use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Exception\UnableToGenerateMessageId;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Descriptor as IntlDescriptor;
use FormatPHP\Intl\DescriptorCollection;
use PhpParser\Node;
use PhpParser\NodeAbstract;
use PhpParser\NodeVisitorAbstract;

use function assert;
use function in_array;
use function preg_replace;
use function trim;

/**
 * A PhpParser\NodeVisitor that collects message descriptors from parsed source code
 */
class DescriptorCollectorVisitor extends NodeVisitorAbstract
{
    private DescriptorCollection $descriptors;
    private string $filePath;
    private bool $preserveWhitespace;
    private IdInterpolator $idInterpolator;
    private string $idInterpolatorPattern;

    /**
     * @var string[]
     */
    private array $functionNames;

    /**
     * @param string[] $functionNames Function names from which to parse
     *     descriptors from source code
     */
    public function __construct(
        string $filePath,
        array $functionNames = [],
        bool $preserveWhitespace = false,
        string $idInterpolatorPattern = IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN
    ) {
        $this->filePath = $filePath;
        $this->functionNames = $functionNames;
        $this->preserveWhitespace = $preserveWhitespace;
        $this->descriptors = new DescriptorCollection();
        $this->idInterpolator = new IdInterpolator();
        $this->idInterpolatorPattern = $idInterpolatorPattern;
    }

    public function getDescriptors(): DescriptorCollection
    {
        return $this->descriptors;
    }

    /**
     * @return int | Node | null
     *
     * @throws InvalidArgument
     * @throws UnableToGenerateMessageId
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof Node\Expr\MethodCall && !$node instanceof Node\Expr\FuncCall) {
            return null;
        }

        if (!$node->name instanceof Node\Identifier && !$node->name instanceof Node\Name) {
            return null;
        }

        $functionName = $this->parseFunctionName($node->name);

        if ($this->isFunctionForParsing($functionName)) {
            $descriptor = $node->getArgs()[0] ?? null;

            if ($descriptor === null) {
                return null;
            }

            if (!$descriptor->value instanceof Node\Expr\Array_) {
                return null;
            }

            $parsedDescriptor = $this->parseDescriptor($descriptor);
            if ($parsedDescriptor !== null) {
                $this->descriptors[] = $this->ensureId($parsedDescriptor);
            }
        }

        return null;
    }

    private function parseDescriptor(Node\Arg $descriptorArgument): ?IntlDescriptor
    {
        /** @var array{id?: string, defaultMessage?: string, description?: string} $properties */
        $properties = [];

        assert($descriptorArgument->value instanceof Node\Expr\Array_);

        foreach ($descriptorArgument->value->items as $item) {
            if (!$this->isValidDescriptorItem($item)) {
                continue;
            }

            assert($item !== null);
            assert($item->key instanceof Node\Scalar\String_);
            assert($item->value instanceof Node\Scalar\String_);

            $properties[$item->key->value] = $item->value->value;
        }

        if (!isset($properties['id']) && !isset($properties['defaultMessage']) && !isset($properties['description'])) {
            return null;
        }

        return new Descriptor(
            $properties['id'] ?? null,
            $this->clean($properties['defaultMessage'] ?? null) ?? $properties['id'] ?? null,
            $this->clean($properties['description'] ?? null) ?? null,
            $this->filePath,
            $descriptorArgument->getStartFilePos(),
            $descriptorArgument->getEndFilePos(),
            $descriptorArgument->getLine(),
        );
    }

    /**
     * @param Node\Identifier | Node\Name $node
     */
    private function parseFunctionName(NodeAbstract $node): string
    {
        if ($node instanceof Node\Identifier) {
            return $node->name;
        }

        return $node->getLast();
    }

    private function isFunctionForParsing(string $name): bool
    {
        return in_array($name, $this->functionNames);
    }

    private function isValidDescriptorItem(?Node\Expr\ArrayItem $item): bool
    {
        return $item !== null
            && $item->key instanceof Node\Scalar\String_
            && $item->value instanceof Node\Scalar\String_;
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($this->preserveWhitespace === true) {
            return $value;
        }

        return trim((string) preg_replace('/\s+/m', ' ', $value));
    }

    /**
     * @throws InvalidArgument
     * @throws UnableToGenerateMessageId
     */
    private function ensureId(IntlDescriptor $descriptor): IntlDescriptor
    {
        $descriptor->setId($this->idInterpolator->generateId($descriptor, $this->idInterpolatorPattern));

        return $descriptor;
    }
}
