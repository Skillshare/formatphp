<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor\Parser\Descriptor;

use FormatPHP\Extractor\Parser\Descriptor\PhpParser;
use FormatPHP\Intl;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\File;

class PhpParserTest extends TestCase
{
    public function testParse01(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-01.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'This is a default message',
                'description' => 'A simple description of a fixture for testing purposes.',
                'end' => 202,
                'file' => __DIR__ . '/fixtures/php-parser-01.php',
                'id' => 'aTestId',
                'line' => 3,
                'meta' => [],
                'start' => 44,
            ],
            $descriptors[0]->toArray(),
        );
    }

    public function testParse02(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage', 'bar'], 'intl');
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-02.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'How are you?',
                'description' => null,
                'end' => 635,
                'file' => __DIR__ . '/fixtures/php-parser-02.php',
                'id' => 'greeting.question',
                'line' => 28,
                'meta' => [
                    'some' => 'thing',
                    'another' => 'meta-value',
                    'another_property' => 'some_value',
                    'and-still-more' => 'a-value',
                ],
                'start' => 536,
            ],
            $descriptors[0]->toArray(),
        );
    }

    public function testParse03(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage', 'translate']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-03.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
        $this->assertSame(
            [
                'defaultMessage' => 'Hello!',
                'description' => null,
                'end' => 310,
                'file' => __DIR__ . '/fixtures/php-parser-03.php',
                'id' => 'OpKKos',
                'line' => 14,
                'meta' => [],
                'start' => 274,
            ],
            $descriptors[0]->toArray(),
        );
    }

    public function testParse04(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage', 'translate', 'translate2', 'translate3']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-04.php');

        $this->assertCount(0, $descriptors);
    }

    public function testParse05(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage'], 'invalid.pragma');
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-05.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
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
    }

    public function testParse06(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage'], 'intl');
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-06.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
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
    }

    public function testParse07WithoutPreservingWhitespace(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-07.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
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
    }

    public function testParse07PreservingWhitespace(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage'], null, true);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-07.php');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(1, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
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
    }

    public function testParse08(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-08.txt');

        $this->assertCount(0, $descriptors);
    }

    public function testParse09(): void
    {
        $parser = new PhpParser(new File(), ['formatMessage']);
        $descriptors = $parser->parse(__DIR__ . '/fixtures/php-parser-09.phtml');

        $this->assertContainsOnlyInstancesOf(Intl\Descriptor::class, $descriptors);
        $this->assertCount(2, $descriptors);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[0]);
        $this->assertInstanceOf(Intl\ExtendedDescriptor::class, $descriptors[1]);
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
    }
}
