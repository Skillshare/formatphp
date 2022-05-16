<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Console\Command;

use FormatPHP\Exception;
use FormatPHP\PseudoLocale\Converter;
use FormatPHP\PseudoLocale\ConverterOptions;
use FormatPHP\PseudoLocale\PseudoLocale;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;
use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function implode;

use const PHP_EOL;

/**
 * Provides the `formatphp pseudo-locale` command
 */
class PseudoLocaleCommand extends AbstractCommand
{
    /**
     * @throws SymfonyInvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('pseudo-locale')
            ->setDescription('Convert a locale file into a pseudo locale, for testing')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'The locale file to convert.',
            )
            ->addArgument(
                'pseudo-locale',
                InputArgument::REQUIRED,
                'The pseudo-locale tag.' . PHP_EOL
                    . 'One of: ' . implode(', ', PseudoLocale::LOCALES),
            )
            ->addOption(
                '--in-format',
                null,
                InputOption::VALUE_REQUIRED,
                'Formatter name or path to a formatter script that' . PHP_EOL
                    . 'controls the shape of the JSON read from `file`.' . PHP_EOL
                    . 'Defaults to "formatphp".',
            )
            ->addOption(
                '--out-format',
                null,
                InputOption::VALUE_REQUIRED,
                'Formatter name or path to a formatter script that' . PHP_EOL
                    . 'controls the shape of the JSON output produced.',
            )
            ->addOption(
                '--out-file',
                null,
                InputOption::VALUE_REQUIRED,
                'Target file path to save the JSON output.',
            );
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnableToWriteFileException
     * @throws Exception\UnableToProcessFileException
     * @throws Exception\ImproperContextException
     * @throws SymfonyInvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $file */
        $file = $input->getArgument('file');

        /** @var string $pseudoLocale */
        $pseudoLocale = $input->getArgument('pseudo-locale');

        $options = $this->buildOptions($input);
        $fileSystemHelper = new FileSystemHelper();

        $converter = new Converter(
            $options,
            $fileSystemHelper,
            new FormatHelper($fileSystemHelper),
            $this->getConsoleLogger($output),
        );

        $converter->convert($file, $pseudoLocale);

        return self::SUCCESS;
    }

    /**
     * @throws SymfonyInvalidArgumentException
     */
    private function buildOptions(InputInterface $input): ConverterOptions
    {
        $options = new ConverterOptions();

        /** @var string | null $inFormat */
        $inFormat = $input->getOption('in-format');
        $options->inFormat = $inFormat;

        /** @var string | null $outFormat */
        $outFormat = $input->getOption('out-format');
        $options->outFormat = $outFormat;

        /** @var string | null $outFile */
        $outFile = $input->getOption('out-file');
        $options->outFile = $outFile;

        return $options;
    }
}
