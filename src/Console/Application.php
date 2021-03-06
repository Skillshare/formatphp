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

namespace FormatPHP\Console;

use FormatPHP\Console\Command\ExtractCommand;
use FormatPHP\Console\Command\PseudoLocaleCommand;
use Symfony\Component\Console\Application as SymfonyConsoleApplication;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputOption;

/**
 * FormatPHP console application
 */
class Application extends SymfonyConsoleApplication
{
    public const NAME = 'formatphp';

    /**
     * @throws LogicException
     */
    public function __construct()
    {
        parent::__construct(self::NAME);
        $this->cleanUpOptions();

        $this->add(new ExtractCommand());
        $this->add(new PseudoLocaleCommand());
    }

    private function cleanUpOptions(): void
    {
        $definition = $this->getDefaultInputDefinition();

        $helpOption = new InputOption(
            'help',
            'h',
            InputOption::VALUE_NONE,
            'Display help for the given command.',
        );
        $verboseOption = new InputOption(
            'verbose',
            'v|vv|vvv',
            InputOption::VALUE_NONE,
            'Increase the verbosity of messages.',
        );

        $newOptions = [];
        $options = $definition->getOptions();
        foreach ($options as $option) {
            if ($option->getName() === 'help') {
                $newOptions[] = $helpOption;

                continue;
            }

            if ($option->getName() === 'verbose') {
                $newOptions[] = $verboseOption;

                continue;
            }

            $newOptions[] = $option;
        }

        $definition->setOptions($newOptions);

        $this->setDefinition($definition);
    }
}
