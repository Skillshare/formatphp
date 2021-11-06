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

use FormatPHP\Exception\UnableToProcessFile;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Extractor\Parser\DescriptorParser;
use FormatPHP\Extractor\Parser\Error;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Intl\ExtendedDescriptor;
use FormatPHP\Util\File;
use LogicException;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\Parser\Php7 as Php7Parser;

use function array_merge;
use function assert;
use function count;
use function in_array;
use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * Parses message descriptors from application PHP source code files
 */
class PhpParser implements DescriptorParser
{
    private const PHP_PATH_EXTENSIONS = ['php', 'phtml'];

    private File $file;
    private Lexer $lexer;
    private Parser $parser;
    private ?string $pragma;
    private bool $preserveWhitespace;
    private string $idInterpolatorPattern;

    /**
     * @var string[]
     */
    private array $functionNames;

    /**
     * @var Error[]
     */
    private array $errors = [];

    /**
     * @param string[] $functionNames Function names from which to parse
     *     descriptors from source code
     *
     * @throws LogicException
     */
    public function __construct(
        File $file,
        array $functionNames = [],
        ?string $pragma = null,
        bool $preserveWhitespace = false,
        string $idInterpolatorPattern = IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN
    ) {
        $this->file = $file;
        $this->functionNames = $functionNames;
        $this->pragma = $pragma;
        $this->preserveWhitespace = $preserveWhitespace;
        $this->idInterpolatorPattern = $idInterpolatorPattern;

        $this->lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'endFilePos',
                'endLine',
                'endTokenPos',
                'startFilePos',
                'startLine',
                'startTokenPos',
            ],
        ]);

        $this->parser = new Php7Parser($this->lexer);
    }

    /**
     * @throws UnableToProcessFile
     */
    public function parse(string $filePath): DescriptorCollection
    {
        if (!$this->isPhpFile($filePath)) {
            return new DescriptorCollection();
        }

        $statements = $this->parser->parse($this->file->getContents($filePath));
        assert($statements !== null);

        $descriptorCollector = new DescriptorCollectorVisitor(
            $filePath,
            $this->functionNames,
            $this->preserveWhitespace,
            $this->idInterpolatorPattern,
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($descriptorCollector);

        $pragmaCollector = null;
        if ($this->pragma !== null) {
            $pragmaCollector = new PragmaCollectorVisitor($filePath, $this->pragma);
            $traverser->addVisitor($pragmaCollector);
        }

        $traverser->traverse($statements);

        $this->errors = $descriptorCollector->getErrors();
        if ($pragmaCollector !== null) {
            $this->errors = array_merge($this->errors, $pragmaCollector->getErrors());
        }

        return $this->applyMetadata($descriptorCollector->getDescriptors(), $pragmaCollector);
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    private function applyMetadata(
        DescriptorCollection $descriptors,
        ?PragmaCollectorVisitor $pragmaCollector
    ): DescriptorCollection {
        if ($pragmaCollector === null || count($pragmaCollector->getMetadata()) === 0) {
            return $descriptors;
        }

        $metadata = $pragmaCollector->getMetadata();

        foreach ($descriptors as $descriptor) {
            if ($descriptor instanceof ExtendedDescriptor) {
                $descriptor->setMetadata($metadata);
            }
        }

        return $descriptors;
    }

    private function isPhpFile(string $filePath): bool
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return in_array($extension, self::PHP_PATH_EXTENSIONS);
    }
}
