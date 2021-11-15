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

use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Extractor\MessageExtractor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\Globber;
use LogicException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

use function array_map;
use function array_merge;
use function array_unique;
use function explode;
use function getcwd;

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

    private const LOG_FORMAT_MAPPING = [
        LogLevel::WARNING => ConsoleLogger::ERROR,
        LogLevel::NOTICE => ConsoleLogger::ERROR,
        LogLevel::INFO => ConsoleLogger::ERROR,
        LogLevel::DEBUG => ConsoleLogger::ERROR,
    ];

    private const LOG_VERBOSITY_MAPPING = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
    ];

    /**
     * @throws InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('extract')
            ->setDescription('Extract string messages from application source code')
            ->addArgument(
                'files',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'One or more paths to process for extraction. May use glob '
                    . 'patterns for file matching; supports globstar (`**`) '
                    . 'for recursive matching.',
            )
            ->addOption(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Formatter name or path to a formatter script that controls '
                    . 'the shape of the JSON produced for `--out-file`.',
            )
            ->addOption(
                'out-file',
                null,
                InputOption::VALUE_REQUIRED,
                'Target file path to save the JSON output file of all '
                    . 'translations extracted from the `files`.',
            )
            ->addOption(
                'id-interpolation-pattern',
                null,
                InputOption::VALUE_REQUIRED,
                'If message descriptors are missing the id property, we will '
                    . 'use this pattern to automatically generate IDs for '
                    . 'them. Defaults to `[sha512:contenthash:base64:6]` where '
                    . '`contenthash` represents the hash of `defaultMessage` '
                    . 'and `description`.',
            )
            ->addOption(
                'extract-source-location',
                null,
                InputOption::VALUE_NONE,
                'Whether to extract metadata for the source files. If present, '
                    . 'the extracted descriptors will each include `file`, '
                    . '`start`, `end`, `line`, and `col` properties.',
            )
            ->addOption(
                'additional-function-names',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of additional function names to search '
                    . 'for when extracting messages.',
            )
            ->addOption(
                'parser',
                'p',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Parser name or path to a parser script to apply additional '
                    . 'parsing in addition to the default PHP parsing.',
            )
            ->addOption(
                'ignore',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Glob patterns for paths to exclude from extraction.',
            )
            ->addOption(
                'throws',
                null,
                InputOption::VALUE_NONE,
                'Whether to throw an exception when failing to process any '
                    . 'file in the batch.',
            )
            ->addOption(
                'pragma',
                null,
                InputOption::VALUE_REQUIRED,
                'Allows parsing of additional custom pragma to include custom '
                    . 'metadata in the extracted messages.',
            )
            ->addOption(
                'preserve-whitespace',
                null,
                InputOption::VALUE_NONE,
                'Whether to preserve whitespace and newlines in extracted '
                    . 'messages.',
            );
    }

    /**
     * @throws InvalidArgumentException
     * @throws UnableToProcessFileException
     * @throws LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string[] $files */
        $files = $input->getArgument('files') ?: [getcwd() . '/**/*'];
        $options = $this->buildOptions($input);

        $extractor = new MessageExtractor(
            $options,
            new ConsoleLogger($output, self::LOG_VERBOSITY_MAPPING, self::LOG_FORMAT_MAPPING),
            new Globber(new FileSystemHelper()),
            new FileSystemHelper(),
        );

        $extractor->process($files);

        return self::SUCCESS;
    }

    /**
     * @throws InvalidArgumentException
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
        $idInterpolationPattern = $input->getOption('id-interpolation-pattern');
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

        /** @var string $inputFunctionNames */
        $inputFunctionNames = $input->getOption('additional-function-names') ?? '';
        $additionalFunctionNames = array_map('trim', explode(',', $inputFunctionNames));
        $options->functionNames = array_unique(array_merge($options->functionNames, $additionalFunctionNames));

        return $options;
    }
}
