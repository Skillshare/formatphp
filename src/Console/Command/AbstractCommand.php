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
    private const LOG_VERBOSITY_MAPPING = [
        LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
    ];

    protected function getConsoleLogger(OutputInterface $output): LoggerInterface
    {
        return new ConsoleLogger($output, self::LOG_VERBOSITY_MAPPING);
    }
}
