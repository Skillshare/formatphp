<?php

declare(strict_types=1);

namespace FormatPHP\Test\Console\Command;

use FormatPHP\Console\Application;
use FormatPHP\Console\Command\ExtractCommand;
use FormatPHP\Test\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ExtractCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();

        /** @var ExtractCommand $command */
        $command = $application->find('extract');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['files' => ['*.foo']]);

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString(
            '[warning] Could not find files',
            $output,
        );
    }
}
