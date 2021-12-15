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

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command as SymfonyConsoleCommand;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Common command functionality for FormatPHP console commands
 */
abstract class AbstractCommand extends SymfonyConsoleCommand
{
    private const LOG_FORMAT_MAPPING = [
        LogLevel::WARNING => ConsoleLogger::ERROR,
        LogLevel::NOTICE => ConsoleLogger::ERROR,
        LogLevel::INFO => ConsoleLogger::ERROR,
        LogLevel::DEBUG => ConsoleLogger::ERROR,
    ];

    private const LOG_VERBOSITY_MAPPING = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
    ];

    protected function getConsoleLogger(OutputInterface $output): LoggerInterface
    {
        return new ConsoleLogger($output, self::LOG_VERBOSITY_MAPPING, self::LOG_FORMAT_MAPPING);
    }
}
