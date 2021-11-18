<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor\Parser\Descriptor;

use FormatPHP\DescriptorInterface;
use FormatPHP\ExtendedDescriptorInterface;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\Descriptor\PhpParser;
use FormatPHP\Extractor\Parser\ParserErrorCollection;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;

use function sprintf;

class PhpParserTest extends TestCase
{
    public function testParse01(): void
    {
        $errors = new ParserErrorCollection();
        $options = new MessageExtractorOptions();
        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-01.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'This is a default message',
                'description' => 'A simple description of a fixture for testing purposes.',
                'end' => 326,
                'file' => __DIR__ . '/fixtures/php-parser-01.php',
                'id' => 'aTestId',
                'line' => 3,
                'meta' => [],
                'start' => 44,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame([], $receivedErrors);
    }

    public function testParse02(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->functionNames = ['formatMessage', 'bar'];
        $options->pragma = 'intl';

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-02.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'How are you?',
                'description' => null,
                'end' => 839,
                'file' => __DIR__ . '/fixtures/php-parser-02.php',
                'id' => 'greeting.question',
                'line' => 37,
                'meta' => [
                    'some' => 'thing',
                    'another' => 'meta-value',
                    'more' => 'details',
                    'and' => 'more',
                    'another_property' => 'some_value',
                    'and-still-more' => 'a-value',
                ],
                'start' => 740,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame(
            [
                'Pragma contains data that could not be parsed: "some:thing this should not be captured '
                    . 'another:meta-value also not captured" on line 2 in ' . __DIR__ . '/fixtures/php-parser-02.php',
                'Pragma found without a value on line 8 in ' . __DIR__ . '/fixtures/php-parser-02.php',
                'Descriptor argument must be an array on line 32 in ' . __DIR__ . '/fixtures/php-parser-02.php',
            ],
            $receivedErrors,
        );
    }

    public function testParse03(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->functionNames = ['formatMessage', 'translate'];

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-03.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'Hello!',
                'description' => null,
                'end' => 320,
                'file' => __DIR__ . '/fixtures/php-parser-03.php',
                'id' => 'OpKKos',
                'line' => 14,
                'meta' => [],
                'start' => 284,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame(
            [
                'Descriptor argument must be an array on line 8 in ' . __DIR__ . '/fixtures/php-parser-03.php',
            ],
            $receivedErrors,
        );
    }

    public function testParse04(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->functionNames = ['formatMessage', 'translate', 'translate3'];

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-04.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertCount(0, $descriptors);
        $this->assertSame(
            [
                'Descriptor argument must be an array on line 29 in ' . __DIR__ . '/fixtures/php-parser-04.php',
                'Descriptor argument must have at least one of id, defaultMessage, or description on line 32 in '
                    . __DIR__ . '/fixtures/php-parser-04.php',
                'Descriptor argument must be an array on line 40 in ' . __DIR__ . '/fixtures/php-parser-04.php',
            ],
            $receivedErrors,
        );
    }

    public function testParse05(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->pragma = 'invalid.pragma';

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-05.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'This is a default message',
                'description' => 'A simple description of a fixture for testing purposes.',
                'end' => 237,
                'file' => __DIR__ . '/fixtures/php-parser-05.php',
                'id' => 'aTestId',
                'line' => 6,
                'meta' => [],
                'start' => 79,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame([], $receivedErrors);
    }

    public function testParse06(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->pragma = 'intl';

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-06.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'This is a default message',
                'description' => 'A simple description of a fixture for testing purposes.',
                'end' => 241,
                'file' => __DIR__ . '/fixtures/php-parser-06.php',
                'id' => 'aTestId',
                'line' => 6,
                'meta' => [],
                'start' => 83,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame([], $receivedErrors);
    }

    public function testParse07WithoutPreservingWhitespace(): void
    {
        $errors = new ParserErrorCollection();
        $options = new MessageExtractorOptions();
        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-07.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'You have {numPhotos, plural, =0 {no photos.} =1 {one photo.} other {# photos.} }',
                'description' => 'A description with multiple lines and extra whitespace.',
                'end' => 394,
                'file' => __DIR__ . '/fixtures/php-parser-07.php',
                'id' => 'photos.count',
                'line' => 4,
                'meta' => [],
                'start' => 49,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame([], $receivedErrors);
    }

    public function testParse07PreservingWhitespace(): void
    {
        $errors = new ParserErrorCollection();

        $options = new MessageExtractorOptions();
        $options->preserveWhitespace = true;

        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-07.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => "\nYou have {numPhotos, plural,\n    =0 {no photos.}\n"
                    . "    =1 {one photo.}\n    other {# photos.}\n}\n",
                'description' => "  A description with \n multiple lines    \n   and extra whitespace.   ",
                'end' => 394,
                'file' => __DIR__ . '/fixtures/php-parser-07.php',
                'id' => 'photos.count',
                'line' => 4,
                'meta' => [],
                'start' => 49,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame([], $receivedErrors);
    }

    public function testParse08(): void
    {
        $errors = new ParserErrorCollection();
        $options = new MessageExtractorOptions();
        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-08.txt', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertCount(0, $descriptors);
        $this->assertSame([], $receivedErrors);
    }

    public function testParse09(): void
    {
        $errors = new ParserErrorCollection();
        $options = new MessageExtractorOptions();
        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-09.phtml', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(2, $descriptors);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[0]);
        $this->assertInstanceOf(ExtendedDescriptorInterface::class, $descriptors[1]);
        $this->assertSame(
            [
                'defaultMessage' => 'Welcome!',
                'description' => null,
                'end' => 277,
                'file' => __DIR__ . '/fixtures/php-parser-09.phtml',
                'id' => 'welcome',
                'line' => 11,
                'meta' => [],
                'start' => 227,
            ],
            $descriptors[0]->toArray(),
        );
        $this->assertSame(
            [
                'defaultMessage' => 'Goodbye!',
                'description' => null,
                'end' => 419,
                'file' => __DIR__ . '/fixtures/php-parser-09.phtml',
                'id' => 'goodbye',
                'line' => 15,
                'meta' => [],
                'start' => 369,
            ],
            $descriptors[1]->toArray(),
        );
        $this->assertSame(
            [
                'Descriptor argument must be present on line 18 in ' . __DIR__ . '/fixtures/php-parser-09.phtml',
            ],
            $receivedErrors,
        );
    }

    public function testParse10(): void
    {
        $errors = new ParserErrorCollection();
        $options = new MessageExtractorOptions();
        $parser = new PhpParser(new FileSystemHelper());
        $descriptors = $parser(__DIR__ . '/fixtures/php-parser-10.php', $options, $errors);
        $receivedErrors = $this->compileErrors($errors);

        $this->assertContainsOnlyInstancesOf(DescriptorInterface::class, $descriptors);
        $this->assertCount(0, $descriptors);
        $this->assertSame(
            [
                'The descriptor must not contain values other than string literals; '
                    . 'encountered Expr_Variable on line 6 in '
                    . __DIR__ . '/fixtures/php-parser-10.php',
                'The descriptor must not contain values other than string literals; '
                    . 'encountered Scalar_Encapsed on line 12 in '
                    . __DIR__ . '/fixtures/php-parser-10.php',
            ],
            $receivedErrors,
        );
    }

    /**
     * @return string[]
     */
    private function compileErrors(ParserErrorCollection $errors): array
    {
        $receivedErrors = [];

        foreach ($errors as $error) {
            $receivedErrors[] = sprintf('%s on line %d in %s', $error->message, $error->sourceLine, $error->sourceFile);
        }

        return $receivedErrors;
    }
}
