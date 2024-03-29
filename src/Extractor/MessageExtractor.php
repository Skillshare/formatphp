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

namespace FormatPHP\Extractor;

use Closure;
use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\FormatPHPExceptionInterface;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\ExtendedDescriptorInterface;
use FormatPHP\Extractor\Parser\Descriptor\PhpParser;
use FormatPHP\Extractor\Parser\DescriptorParserInterface;
use FormatPHP\Extractor\Parser\ParserError;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;
use FormatPHP\Icu\MessageFormat\Manipulator;
use FormatPHP\Icu\MessageFormat\Parser as MessageFormatParser;
use FormatPHP\Icu\MessageFormat\Printer;
use FormatPHP\Icu\MessageFormat\Validator;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use FormatPHP\Util\Globber;
use LogicException;
use Psr\Log\LoggerInterface;
use Ramsey\Collection\Exception\CollectionMismatchException;

use function array_filter;
use function class_exists;
use function count;
use function is_a;
use function is_callable;
use function sprintf;
use function strtolower;

/**
 * Extracts message descriptors from application source code
 *
 * @psalm-import-type DescriptorParserCallable from DescriptorParserInterface
 * @psalm-import-type WriterType from WriterInterface
 */
class MessageExtractor
{
    private FileSystemHelper $file;
    private Globber $globber;
    private LoggerInterface $logger;
    private MessageExtractorOptions $options;
    private ParserErrorCollection $errors;
    private FormatHelper $formatHelper;
    private Manipulator $manipulator;
    private Printer $printer;

    public function __construct(
        MessageExtractorOptions $options,
        LoggerInterface $logger,
        Globber $globber,
        FileSystemHelper $file,
        FormatHelper $formatHelper
    ) {
        $this->options = $options;
        $this->logger = $logger;
        $this->globber = $globber;
        $this->file = $file;
        $this->formatHelper = $formatHelper;
        $this->errors = new ParserErrorCollection();
        $this->manipulator = new Manipulator();
        $this->printer = new Printer();
    }

    /**
     * Processes the list of files according to the options set
     *
     * @param string[] $files
     *
     * @throws UnableToProcessFileException
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     * @throws LogicException
     * @throws CollectionMismatchException
     */
    public function process(array $files): void
    {
        try {
            $formatter = $this->formatHelper->getWriter($this->options->format);
        } catch (FormatPHPExceptionInterface $exception) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);

            return;
        }

        $filesProcessed = 0;
        $descriptors = new DescriptorCollection();

        foreach ($this->globber->find($files, $this->options->ignore) as $path) {
            $filesProcessed++;
            $this->logger->debug('Extracting from {file}', ['file' => $path]);

            try {
                $descriptors = $this->parse($descriptors, $path);
            } catch (UnableToProcessFileException $exception) {
                if ($this->options->throws === true) {
                    throw $exception;
                }

                $this->logger->warning($exception->getMessage(), ['exception' => $exception]);
            }
        }

        if ($filesProcessed === 0) {
            $this->logger->warning('Could not find files', ['files' => $files]);

            return;
        }

        if ($this->options->validateMessages) {
            $this->validateDescriptors($descriptors);
        }

        $this->write($formatter, $descriptors);
    }

    public function getErrors(): ParserErrorCollection
    {
        return $this->errors;
    }

    /**
     * @throws UnableToProcessFileException
     * @throws ImproperContextException
     * @throws LogicException
     * @throws CollectionMismatchException
     */
    private function parse(DescriptorCollection $descriptors, string $filePath): DescriptorCollection
    {
        foreach ($this->getDescriptorParsers() as $parser) {
            /** @var DescriptorCollection $descriptors */
            $descriptors = $descriptors->merge($parser($filePath, $this->options, $this->errors));
        }

        return $descriptors;
    }

    /**
     * @return DescriptorParserInterface[]
     *
     * @throws ImproperContextException
     * @throws LogicException
     */
    private function getDescriptorParsers(): array
    {
        $parsers = [];

        foreach ($this->options->parsers as $parser) {
            $parsers[] = $this->loadDescriptorParser($parser);
        }

        return $parsers;
    }

    /**
     * @throws ImproperContextException
     * @throws LogicException
     */
    private function loadDescriptorParser(string $parserNameOrScript): DescriptorParserInterface
    {
        if (strtolower($parserNameOrScript) === 'php') {
            return new PhpParser($this->file);
        }

        if (class_exists($parserNameOrScript) && is_a($parserNameOrScript, DescriptorParserInterface::class, true)) {
            $parser = new $parserNameOrScript();
        } else {
            /** @var DescriptorParserCallable | null $parser */
            $parser = $this->file->loadClosureFromScript($parserNameOrScript);
        }

        if ($parser instanceof DescriptorParserInterface) {
            return $parser;
        }

        if (is_callable($parser)) {
            return $this->createInvokableDescriptorParser($parser);
        }

        throw new InvalidArgumentException(sprintf(
            'The parser provided is not a known descriptor parser, an instance of '
            . '%s, or a callable of the shape `callable(string,%s,%s):%s`.',
            DescriptorParserInterface::class,
            MessageExtractorOptions::class,
            ParserErrorCollection::class,
            DescriptorCollection::class,
        ));
    }

    /**
     * @see WriterInterface
     *
     * @param WriterType $formatter
     *
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     */
    private function write(callable $formatter, DescriptorCollection $descriptors): void
    {
        if ($this->options->validateMessages === true && count($this->errors) > 0) {
            $this->logger->error('Validation errors encountered; extraction failed');

            return;
        }

        if ($this->options->flatten === true) {
            /** @var DescriptorInterface[] $flattened */
            $flattened = $descriptors->map($this->flattenMessage())->toArray();
            $descriptors = new DescriptorCollection(array_filter($flattened));
        }

        $file = $this->options->outFile ?? 'php://output';

        $writerOptions = new WriterOptions();
        $writerOptions->includesSourceLocation = $this->options->extractSourceLocation;
        $writerOptions->includesPragma = $this->options->pragma !== null;

        $messages = $formatter($descriptors, $writerOptions);
        if (count($messages) === 0) {
            $messages = (object) $messages;
        }

        $this->file->writeJsonContents($file, $messages);

        if ($this->options->outFile !== null) {
            $this->logger->notice(
                'Message descriptors extracted and written to {file}',
                ['file' => $this->options->outFile],
            );
        }
    }

    private function createInvokableDescriptorParser(callable $parser): DescriptorParserInterface
    {
        return new class ($parser) implements DescriptorParserInterface {
            private Closure $parser;

            public function __construct(callable $parser)
            {
                $this->parser = Closure::fromCallable($parser);
            }

            public function __invoke(
                string $filePath,
                MessageExtractorOptions $options,
                ParserErrorCollection $errors
            ): DescriptorCollection {
                /** @var DescriptorCollection */
                return ($this->parser)($filePath, $options, $errors);
            }
        };
    }

    /**
     * @psalm-return Closure(DescriptorInterface):mixed
     */
    private function flattenMessage(): Closure
    {
        /**
         * @var Closure(DescriptorInterface):mixed
         */
        return function (Descriptor $descriptor): ?Descriptor {
            $message = $descriptor->getDefaultMessage();
            $messageFormatParser = new MessageFormatParser((string) $message);
            $result = $messageFormatParser->parse();

            if ($result->err !== null) {
                return null;
            }

            /** @var MessageFormatParser\Type\ElementCollection $messageAst */
            $messageAst = $result->val;

            $hoistedAst = $this->manipulator->hoistSelectors($messageAst);
            $descriptor->setDefaultMessage($this->printer->printAst($hoistedAst));

            return $descriptor;
        };
    }

    private function validateDescriptors(DescriptorCollection $descriptors): void
    {
        $validator = new Validator();

        foreach ($descriptors as $descriptor) {
            try {
                $validator->validate((string) $descriptor->getDefaultMessage());
            } catch (MessageFormatParser\Exception\InvalidMessageException $exception) {
                $sourceFile = '';
                $sourceLine = -1;

                if ($descriptor instanceof ExtendedDescriptorInterface) {
                    $sourceFile = $descriptor->getSourceFile() ?? '';
                    $sourceLine = $descriptor->getSourceLine() ?? -1;
                }

                $this->errors[] = new ParserError($exception->getMessage(), $sourceFile, $sourceLine, $exception);
            }
        }
    }
}
