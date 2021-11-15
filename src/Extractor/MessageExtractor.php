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
use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\FormatPHPExceptionInterface;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\Extractor\Parser\Descriptor\PhpParser;
use FormatPHP\Extractor\Parser\DescriptorParserInterface;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Format\Writer\FormatPHPWriter;
use FormatPHP\Format\Writer\SimpleWriter;
use FormatPHP\Format\Writer\SmartlingWriter;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\Globber;
use LogicException;
use Psr\Log\LoggerInterface;

use function assert;
use function class_exists;
use function count;
use function fopen;
use function is_a;
use function is_callable;
use function is_resource;
use function json_encode;
use function preg_replace;
use function sprintf;
use function strtolower;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

/**
 * Extracts message descriptors from application source code
 */
class MessageExtractor
{
    private const JSON_ENCODE_FLAGS = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

    private FileSystemHelper $file;
    private Globber $globber;
    private LoggerInterface $logger;
    private MessageExtractorOptions $options;
    private ParserErrorCollection $errors;

    /**
     * @throws LogicException
     */
    public function __construct(
        MessageExtractorOptions $options,
        LoggerInterface $logger,
        Globber $globber,
        FileSystemHelper $file
    ) {
        $this->options = $options;
        $this->logger = $logger;
        $this->globber = $globber;
        $this->file = $file;
        $this->errors = new ParserErrorCollection();
    }

    /**
     * Processes the list of files according to the options set
     *
     * @param string[] $files
     *
     * @throws UnableToProcessFileException
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     */
    public function process(array $files): void
    {
        try {
            $formatter = $this->getFormatter($this->options->format);
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

        $this->writeOutput($this->prepareOutput($formatter, $descriptors));
    }

    public function getErrors(): ParserErrorCollection
    {
        return $this->errors;
    }

    /**
     * @throws UnableToProcessFileException
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
     * @throws LogicException
     */
    private function loadDescriptorParser(string $parserNameOrScript): DescriptorParserInterface
    {
        switch (strtolower($parserNameOrScript)) {
            case 'php':
                return new PhpParser($this->file);
        }

        if (class_exists($parserNameOrScript) && is_a($parserNameOrScript, DescriptorParserInterface::class, true)) {
            $parser = new $parserNameOrScript();
        } else {
            /** @var Closure(string,MessageExtractorOptions,ParserErrorCollection):DescriptorCollection | null $parser */
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
     * @return callable(DescriptorCollection,MessageExtractorOptions):array<mixed>
     *
     * @throws ImproperContextException
     * @throws InvalidArgumentException
     */
    private function getFormatter(?string $format): callable
    {
        if ($format === null) {
            return new FormatPHPWriter();
        }

        switch (strtolower($format)) {
            case 'simple':
                return new SimpleWriter();
            case 'smartling':
                return new SmartlingWriter();
            case 'formatjs':
            case 'formatphp':
                return new FormatPHPWriter();
        }

        if (class_exists($format) && is_a($format, WriterInterface::class, true)) {
            $formatter = new $format();
        } else {
            /** @var Closure(DescriptorCollection,MessageExtractorOptions):array<mixed> | null $formatter */
            $formatter = $this->file->loadClosureFromScript($format);
        }

        if (is_callable($formatter)) {
            return $formatter;
        }

        throw new InvalidArgumentException(sprintf(
            'The format provided is not a known format, an instance of '
            . '%s, or a callable of the shape `callable(%s,%s):array<mixed>`.',
            WriterInterface::class,
            DescriptorCollection::class,
            MessageExtractorOptions::class,
        ));
    }

    /**
     * @param callable(DescriptorCollection,MessageExtractorOptions):array<mixed> $formatter
     */
    private function prepareOutput(callable $formatter, DescriptorCollection $descriptors): string
    {
        $messages = $formatter($descriptors, $this->options);

        if (count($messages) === 0) {
            $messages = (object) $messages;
        }

        $output = (string) json_encode($messages, self::JSON_ENCODE_FLAGS);

        // Indent by 2 spaces instead of 4.
        $output = (string) preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $output);

        return $output . "\n";
    }

    /**
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     */
    private function writeOutput(string $output): void
    {
        if ($this->options->outFile !== null) {
            $this->file->writeContents($this->options->outFile, $output);
            $this->logger->notice(
                'Message descriptors extracted and written to {file}',
                ['file' => $this->options->outFile],
            );

            return;
        }

        $stream = fopen('php://output', 'w');
        assert(is_resource($stream));

        $this->file->writeContents($stream, $output);
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
}
