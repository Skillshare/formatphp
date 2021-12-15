<?php

declare(strict_types=1);

namespace FormatPHP\Test\Console\Command;

use FormatPHP\Console\Application;
use FormatPHP\Console\Command\PseudoLocaleCommand;
use FormatPHP\Test\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

class PseudoLocaleCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();

        /** @var PseudoLocaleCommand $command */
        $command = $application->find('pseudo-locale');

        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            'file' => __DIR__ . '/../../fixtures/locales/en.json',
            'pseudo-locale' => 'en-XA',
        ]);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertMatchesTextSnapshot($output);
    }
}
