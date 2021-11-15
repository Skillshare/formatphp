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
use FormatPHP\ExtendedDescriptorInterface;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\DescriptorParserInterface;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Util\FileSystemHelper;
use LogicException;
use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\Parser\Php7 as Php7Parser;

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

    private FileSystemHelper $file;
    private Lexer $lexer;
    private Parser $parser;

    /**
     * @throws LogicException
     */
    public function __construct(FileSystemHelper $file)
    {
        $this->file = $file;

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

    public function __invoke(
        string $filePath,
        MessageExtractorOptions $options,
        ParserErrorCollection $errors
    ): DescriptorCollection {
        if (!$this->isPhpFile($filePath)) {
            return new DescriptorCollection();
        }

        $statements = $this->parser->parse($this->file->getContents($filePath));
        assert($statements !== null);

        $descriptorCollector = new DescriptorCollectorVisitor(
            $filePath,
            $errors,
            $options->functionNames,
            $options->preserveWhitespace,
            $options->idInterpolationPattern,
        );

        $traverser = new NodeTraverser();
        $traverser->addVisitor($descriptorCollector);

        $pragmaCollector = null;
        if ($options->pragma !== null) {
            $pragmaCollector = new PragmaCollectorVisitor($filePath, $options->pragma, $errors);
            $traverser->addVisitor($pragmaCollector);
        }

        $traverser->traverse($statements);

        return $this->applyMetadata($descriptorCollector->getDescriptors(), $pragmaCollector);
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
