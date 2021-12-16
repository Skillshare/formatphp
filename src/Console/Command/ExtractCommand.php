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

namespace FormatPHP\Console\Command;

use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Extractor\MessageExtractor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidMessageException;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use FormatPHP\Util\Globber;
use LogicException;
use Ramsey\Collection\Exception\CollectionMismatchException;
use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function explode;
use function getcwd;
use function ksort;
use function strlen;
use function substr;

use const PHP_EOL;

/**
 * Provides the `formatphp extract` command
 */
class ExtractCommand extends AbstractCommand
{
    private const STANDARD_IGNORES = [
        '.arch-params',
        '.bzr',
        '.git',
        '.hg',
        '.idea',
        '.monotone',
        '.svn',
        '.vscode',
        '_darcs',
        '_svn',
        'CVS',
        'node_modules',
        'vendor',
    ];

    /**
     * @throws SymfonyInvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('extract')
            ->setDescription('Extract string messages from application source code')
            ->addArgument(
                'files',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'One or more paths to process for extraction.' . PHP_EOL
                    . 'May use glob patterns for file matching;' . PHP_EOL
                    . 'supports globstar (**) for recursive matching.',
            )
            ->addOption(
                '--format',
                null,
                InputOption::VALUE_REQUIRED,
                'Formatter name or path to a formatter script' . PHP_EOL
                    . 'that controls the shape of the JSON produced' . PHP_EOL
                    . 'for `--out-file`.',
            )
            ->addOption(
                '--out-file',
                null,
                InputOption::VALUE_REQUIRED,
                'Target file path to save the JSON output file' . PHP_EOL
                    . 'of all translations extracted from the `files`.',
            )
            ->addOption(
                '--ignore',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Glob patterns for paths to exclude from' . PHP_EOL
                    . 'extraction.',
            )
            ->addOption(
                '--flatten',
                null,
                InputOption::VALUE_NONE,
                'Whether to hoist selectors & flatten sentences' . PHP_EOL
                    . 'as much as possible, e.g: "I have {count,' . PHP_EOL
                    . 'plural, one{a dog} other{many dogs}}" becomes' . PHP_EOL
                    . '"{count, plural, one{I have a dog} other{I have' . PHP_EOL
                    . 'many dogs}}". The goal is to provide as many' . PHP_EOL
                    . 'full sentences as possible, since fragmented' . PHP_EOL
                    . 'sentences are not translator-friendly.',
            )
            ->addOption(
                '--validate-messages',
                null,
                InputOption::VALUE_NONE,
                'Whether to validate messages as proper ICU' . PHP_EOL
                    . 'message syntax. If any messages fail, this' . PHP_EOL
                    . 'will respond with a non-zero exit code and' . PHP_EOL
                    . 'print the error messages to stderr.',
            )
            ->addOption(
                '--extract-source-location',
                null,
                InputOption::VALUE_NONE,
                'Whether to extract source file metadata. If' . PHP_EOL
                    . 'present, the extracted descriptors will each' . PHP_EOL
                    . 'include `file`, `start`, `end`, `line`, and' . PHP_EOL
                    . '`col` properties.',
            )
            ->addOption(
                '--addl-func',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of additional function' . PHP_EOL
                    . 'names to search for when extracting messages.',
            )
            ->addOption(
                '--pragma',
                null,
                InputOption::VALUE_REQUIRED,
                'Allows parsing of additional custom pragma to' . PHP_EOL
                    . 'include custom metadata in the extracted' . PHP_EOL
                    . 'messages.',
            )
            ->addOption(
                '--preserve-whitespace',
                null,
                InputOption::VALUE_NONE,
                'Whether to preserve whitespace and newlines in' . PHP_EOL
                    . 'extracted messages.',
            )
            ->addOption(
                '--parser',
                '-p',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Parser name or path to a parser script to apply' . PHP_EOL
                    . 'additional parsing in addition to the default' . PHP_EOL
                    . 'PHP parsing.',
            )
            ->addOption(
                '--throws',
                null,
                InputOption::VALUE_NONE,
                'Whether to throw an exception when failing to' . PHP_EOL
                    . 'process any file in the batch.',
            )
            ->addOption(
                '--id-pattern',
                null,
                InputOption::VALUE_REQUIRED,
                'If message descriptors are missing the id' . PHP_EOL
                    . 'property, we will use this to autogenerate IDs.' . PHP_EOL
                    . 'Defaults to `[sha512:contenthash:base64:6]`' . PHP_EOL
                    . 'where `contenthash` represents the hash of' . PHP_EOL
                    . '`defaultMessage` and `description`.',
            )
            ->addOption(
                '--add-missing-ids',
                null,
                InputOption::VALUE_NONE,
                'Whether to update the source code in place with' . PHP_EOL
                    . 'autogenerated descriptor IDs. Descriptors that' . PHP_EOL
                    . 'already have IDs will not change.',
            );
    }

    /**
     * @throws SymfonyInvalidArgumentException
     * @throws UnableToProcessFileException
     * @throws UnableToWriteFileException
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     * @throws LogicException
     * @throws CollectionMismatchException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $files */
        $files = $input->getArgument('files') ?: [getcwd() . '/**/*'];
        $options = $this->buildOptions($input);
        $fileSystemHelper = new FileSystemHelper();

        $extractor = new MessageExtractor(
            $options,
            $this->getConsoleLogger($output),
            new Globber($fileSystemHelper),
            $fileSystemHelper,
            new FormatHelper($fileSystemHelper),
        );

        $extractor->process($files);

        if ($options->validateMessages && $this->printErrors($extractor->getErrors(), $input, $output)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * @throws SymfonyInvalidArgumentException
     */
    private function buildOptions(InputInterface $input): MessageExtractorOptions
    {
        $options = new MessageExtractorOptions();

        /** @var string | null $format */
        $format = $input->getOption('format');
        $options->format = $format;

        /** @var string | null $outFile */
        $outFile = $input->getOption('out-file');
        $options->outFile = $outFile;

        /** @var string | null $idInterpolationPattern */
        $idInterpolationPattern = $input->getOption('id-pattern');
        $options->idInterpolationPattern = $idInterpolationPattern ?? IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN;

        /** @var string[] $parsers */
        $parsers = (array) $input->getOption('parser');
        $options->parsers = array_unique(array_merge($options->parsers, $parsers));

        /** @var string[] $ignore */
        $ignore = (array) $input->getOption('ignore');
        $options->ignore = array_merge(self::STANDARD_IGNORES, $ignore);

        /** @var string | null $pragma */
        $pragma = $input->getOption('pragma');
        $options->pragma = $pragma;

        $options->extractSourceLocation = (bool) $input->getOption('extract-source-location');
        $options->throws = (bool) $input->getOption('throws');
        $options->preserveWhitespace = (bool) $input->getOption('preserve-whitespace');
        $options->flatten = (bool) $input->getOption('flatten');
        $options->addGeneratedIdsToSourceCode = (bool) $input->getOption('add-missing-ids');
        $options->validateMessages = (bool) $input->getOption('validate-messages');

        /** @var string $inputFunctionNames */
        $inputFunctionNames = $input->getOption('addl-func') ?? '';
        $additionalFunctionNames = array_map('trim', explode(',', $inputFunctionNames));
        $options->functionNames = array_unique(array_merge($options->functionNames, $additionalFunctionNames));

        return $options;
    }

    /**
     * @throws LogicException
     * @throws SymfonyInvalidArgumentException
     */
    private function printErrors(ParserErrorCollection $errors, InputInterface $input, OutputInterface $output): bool
    {
        $tableErrors = [];
        foreach ($errors as $error) {
            $message = $error->message;
            if ($error->exception instanceof InvalidMessageException) {
                $message = 'Syntax Error: '
                    . $error->exception->getParserError()->getErrorKindName()
                    . ' in message "' . $error->exception->getParserError()->message . '"';
            }

            $tableErrors[$error->sourceFile][] = [$error->sourceLine, $message];
        }

        if (count($tableErrors) === 0) {
            return false;
        }

        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $style = new SymfonyStyle($input, $output);
        $style->warning('The following errors occurred while extracting ICU formatted messages.');

        ksort($tableErrors);
        foreach ($tableErrors as $file => $fileErrors) {
            $this->renderTable($file, $fileErrors, $output);
        }

        $style->error('Errors encountered during ICU formatted message extraction.');

        return true;
    }

    /**
     * @param non-empty-array<array{int | null, string}> $errors
     *
     * @throws LogicException
     * @throws SymfonyInvalidArgumentException
     */
    private function renderTable(string $file, array $errors, OutputInterface $output): void
    {
        $fileHeader = strlen($file) > 68 ? '...' . substr($file, -65) : $file;

        $style = Table::getStyleDefinition('borderless');
        $style->setHorizontalBorderChars('-');

        $table = new Table($output);
        $table->setStyle($style);
        $table->setColumnMaxWidth(0, 4);
        $table->setColumnMaxWidth(1, 68);
        $table->setHeaders(['Line', $fileHeader]);
        $table->setRows($errors);

        $table->render();

        $output->write(PHP_EOL);
    }
}
