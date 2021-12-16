<?php

declare(strict_types=1);

namespace FormatPHP\Test\Console\Command;

use FormatPHP\Console\Application;
use FormatPHP\Console\Command\ExtractCommand;
use FormatPHP\Test\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

use function ob_end_clean;
use function ob_get_contents;
use function ob_start;

class ExtractCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $application = new Application();

        /** @var ExtractCommand $command */
        $command = $application->find('extract');

        $commandTester = new CommandTester($command);
        $commandTester->execute(['files' => ['*.foo']]);

        $this->assertMatchesTextSnapshot($commandTester->getDisplay());
    }

    public function testExecuteWithValidation(): void
    {
        $application = new Application();

        /** @var ExtractCommand $command */
        $command = $application->find('extract');

        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            // All .php and .phtml files in the tests folder.
            'files' => [__DIR__ . '/../../**/*.ph*'],
            '--validate-messages' => true,
        ], [
            'capture_stderr_separately' => true,
        ]);
        $jsonOutput = ob_get_contents();
        ob_end_clean();

        $this->assertSame(1, $commandTester->getStatusCode());
        $this->assertSame('', $jsonOutput);

        // We can't deterministically snapshot-assert the error output because
        // it contains file paths. Boo!
        $errorOutput = $commandTester->getErrorOutput(true);

        $this->assertStringContainsString(
            '[WARNING] The following errors occurred while extracting ICU formatted',
            $errorOutput,
        );
        $this->assertStringContainsString(
            '[ERROR] Errors encountered during ICU formatted message extraction.',
            $errorOutput,
        );
        $this->assertStringContainsString(
            '32     Descriptor argument must be an array',
            $errorOutput,
        );
        $this->assertStringContainsString(
            '86     Descriptor argument must have at least one of id, defaultMessage, or',
            $errorOutput,
        );
        $this->assertStringContainsString(
            '4      Syntax Error: INVALID_TAG in message "This is a default <a href="#fo',
            $errorOutput,
        );
    }

    public function testExecuteWithValidationHasNoErrors(): void
    {
        $application = new Application();

        /** @var ExtractCommand $command */
        $command = $application->find('extract');

        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            // All .php and .phtml files in the tests folder.
            'files' => [
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-01.php',
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-05.php',
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-06.php',
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-07.php',
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-08.php',
                __DIR__ . '/../../Extractor/Parser/Descriptor/fixtures/php-parser-11.php',
            ],
            '--validate-messages' => true,
        ], [
            'capture_stderr_separately' => true,
        ]);
        $jsonOutput = ob_get_contents();
        ob_end_clean();

        $this->assertSame(0, $commandTester->getStatusCode());

        // Use a text snapshot instead of JSON so that characters aren't
        // converted to unicode escape sequences.
        $this->assertMatchesTextSnapshot($jsonOutput);

        // We can't deterministically snapshot-assert the error output because
        // it contains file paths. Boo!
        $errorOutput = $commandTester->getErrorOutput(true);

        $this->assertStringNotContainsString(
            '[WARNING] The following errors occurred while extracting ICU formatted',
            $errorOutput,
        );
        $this->assertStringNotContainsString(
            '[ERROR] Errors encountered during ICU formatted message extraction.',
            $errorOutput,
        );
    }
}
