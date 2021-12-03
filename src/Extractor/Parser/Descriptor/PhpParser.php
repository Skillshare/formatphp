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

use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\ExtendedDescriptorInterface;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\DescriptorParserInterface;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Util\FileSystemHelper;
use LogicException;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\Parser\Php7 as Php7Parser;
use PhpParser\PrettyPrinter\Standard as PhpPrinter;

use function assert;
use function count;
use function in_array;
use function pathinfo;

use const PATHINFO_EXTENSION;

/**
 * Parses message descriptors from application PHP source code files
 */
class PhpParser implements DescriptorParserInterface
{
    private const PHP_PATH_EXTENSIONS = ['php', 'phtml'];

    private const LEXER_OPTIONS = [
        'usedAttributes' => [
            'comments',
            'endFilePos',
            'endLine',
            'endTokenPos',
            'startFilePos',
            'startLine',
            'startTokenPos',
        ],
    ];

    private FileSystemHelper $fileSystemHelper;

    public function __construct(FileSystemHelper $fileSystemHelper)
    {
        $this->fileSystemHelper = $fileSystemHelper;
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnableToWriteFileException
     * @throws LogicException
     * @throws UnableToProcessFileException
     */
    public function __invoke(
        string $filePath,
        MessageExtractorOptions $options,
        ParserErrorCollection $errors
    ): DescriptorCollection {
        if (!$this->isPhpFile($filePath)) {
            return new DescriptorCollection();
        }

        $lexer = new Emulative(self::LEXER_OPTIONS);
        $parser = new Php7Parser($lexer);
        $statements = $parser->parse($this->fileSystemHelper->getContents($filePath));

        $descriptorCollector = new DescriptorCollectorVisitor(
            $filePath,
            $errors,
            $options->functionNames,
            $options->preserveWhitespace,
            $options->idInterpolationPattern,
            $options->addGeneratedIdsToSourceCode,
        );

        $pragmaCollector = null;
        if ($options->pragma !== null) {
            $pragmaCollector = new PragmaCollectorVisitor($filePath, $options->pragma, $errors);
        }

        assert($statements !== null);

        if ($options->addGeneratedIdsToSourceCode) {
            $this->traverseWithUpdate($filePath, $statements, $lexer, $descriptorCollector, $pragmaCollector);
        } else {
            $this->traverseWithoutUpdate($statements, $descriptorCollector, $pragmaCollector);
        }

        return $this->applyMetadata($descriptorCollector->getDescriptors(), $pragmaCollector);
    }

    /**
     * @param Node[] $statements
     */
    private function traverseWithoutUpdate(
        array $statements,
        DescriptorCollectorVisitor $descriptorCollector,
        ?PragmaCollectorVisitor $pragmaCollector
    ): void {
        $traverser = new NodeTraverser();
        $traverser->addVisitor($descriptorCollector);

        if ($pragmaCollector !== null) {
            $traverser->addVisitor($pragmaCollector);
        }

        $traverser->traverse($statements);
    }

    /**
     * @param Node[] $originalStatements
     *
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     */
    private function traverseWithUpdate(
        string $filePath,
        array $originalStatements,
        Lexer $lexer,
        DescriptorCollectorVisitor $descriptorCollector,
        ?PragmaCollectorVisitor $pragmaCollector
    ): void {
        $originalTokens = $lexer->getTokens();

        $cloningTraverser = new NodeTraverser();
        $cloningTraverser->addVisitor(new CloningVisitor());
        $clonedStatements = $cloningTraverser->traverse($originalStatements);

        $modifyingTraverser = new NodeTraverser();
        $modifyingTraverser->addVisitor($descriptorCollector);

        if ($pragmaCollector !== null) {
            $modifyingTraverser->addVisitor($pragmaCollector);
        }

        $modifiedStatements = $modifyingTraverser->traverse($clonedStatements);

        if (count($descriptorCollector->getDescriptors()) > 0) {
            $updatedSourceCode = (new PhpPrinter())->printFormatPreserving(
                $modifiedStatements,
                $originalStatements,
                $originalTokens,
            );

            $this->fileSystemHelper->writeContents($filePath, $updatedSourceCode);
        }
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
            if ($descriptor instanceof ExtendedDescriptorInterface) {
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
