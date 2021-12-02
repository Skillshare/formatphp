<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use Closure;
use FormatPHP\ConfigInterface;
use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\Reader\SimpleReader;
use FormatPHP\Format\Reader\SmartlingReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Format\Writer\FormatPHPWriter;
use FormatPHP\Format\Writer\SimpleWriter;
use FormatPHP\Format\Writer\SmartlingWriter;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\FileSystemHelper;
use FormatPHP\Util\FormatHelper;

use function sprintf;

class FormatHelperTest extends TestCase
{
    public function testGetReaderWithNullParameter(): void
    {
        $helper = new FormatHelper(new FileSystemHelper());

        $this->assertInstanceOf(FormatPHPReader::class, $helper->getReader(null));
    }

    /**
     * @param class-string $expectedType
     *
     * @dataProvider validReaderProvider
     */
    public function testGetReader(string $reader, string $expectedType): void
    {
        $helper = new FormatHelper(new FileSystemHelper());

        $this->assertInstanceOf($expectedType, $helper->getReader($reader));
    }

    /**
     * @return array<string, array{reader: string, expectedType: string}>
     */
    public function validReaderProvider(): array
    {
        return [
            'simple' => [
                'reader' => 'simple',
                'expectedType' => SimpleReader::class,
            ],
            'smartling' => [
                'reader' => 'smartling',
                'expectedType' => SmartlingReader::class,
            ],
            'formatphp' => [
                'reader' => 'formatphp',
                'expectedType' => FormatPHPReader::class,
            ],
            'reader class' => [
                'reader' => MockFormatReader::class,
                'expectedType' => MockFormatReader::class,
            ],
            'loaded closure' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-01.php',
                'expectedType' => Closure::class,
            ],
            'loaded anonymous class' => [
                // Even though this implements ReaderInterface, our closure
                // loader method (@see FileSystemHelper::loadClosureFromScript())
                // wraps it in a Closure, so its type is Closure.
                'reader' => __DIR__ . '/fixtures/reader-closure-02.php',
                'expectedType' => Closure::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidReaderProvider
     */
    public function testInvalidReader(string $reader): void
    {
        $helper = new FormatHelper(new FileSystemHelper());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The format provided is not a known format, an instance of '
                . '%s, or a callable of the shape `callable(%s,array<mixed>,%s):%s`.',
            ReaderInterface::class,
            ConfigInterface::class,
            LocaleInterface::class,
            MessageCollection::class,
        ));

        $helper->getReader($reader);
    }

    /**
     * @return array<array{reader: string}>
     */
    public function invalidReaderProvider(): array
    {
        return [
            'non-existent class' => [
                'reader' => '\\This\\Class\\Does\\Not\\Exist',
            ],
            'existing class not a ReaderInterface' => [
                'reader' => self::class,
            ],
            'not a closure' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-01.php',
            ],
            'not enough parameters' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-02.php',
            ],
            'first param is not ConfigInterface' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-03.php',
            ],
            'second param is not array' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-04.php',
            ],
            'second param is not LocaleInterface' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-05.php',
            ],
            'return type is not Message Collection' => [
                'reader' => __DIR__ . '/fixtures/reader-closure-invalid-06.php',
            ],
        ];
    }

    /**
     * @param class-string $expectedType
     *
     * @dataProvider validWriterProvider
     */
    public function testGetWriter(string $writer, string $expectedType): void
    {
        $helper = new FormatHelper(new FileSystemHelper());

        $this->assertInstanceOf($expectedType, $helper->getWriter($writer));
    }

    /**
     * @return array<string, array{writer: string, expectedType: string}>
     */
    public function validWriterProvider(): array
    {
        return [
            'simple' => [
                'writer' => 'simple',
                'expectedType' => SimpleWriter::class,
            ],
            'smartling' => [
                'writer' => 'smartling',
                'expectedType' => SmartlingWriter::class,
            ],
            'formatphp' => [
                'writer' => 'formatphp',
                'expectedType' => FormatPHPWriter::class,
            ],
            'writer class' => [
                'writer' => MockFormatWriter::class,
                'expectedType' => MockFormatWriter::class,
            ],
            'loaded closure' => [
                'writer' => __DIR__ . '/fixtures/writer-closure-01.php',
                'expectedType' => Closure::class,
            ],
            'loaded anonymous class' => [
                // Even though this implements WriterInterface, our closure
                // loader method (@see FileSystemHelper::loadClosureFromScript())
                // wraps it in a Closure, so its type is Closure.
                'writer' => __DIR__ . '/fixtures/writer-closure-02.php',
                'expectedType' => Closure::class,
            ],
        ];
    }

    /**
     * @dataProvider invalidWriterProvider
     */
    public function testInvalidWriter(string $writer): void
    {
        $helper = new FormatHelper(new FileSystemHelper());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The format provided is not a known format, an instance of '
                . '%s, or a callable of the shape `callable(%s,%s):array<mixed>`.',
            WriterInterface::class,
            DescriptorCollection::class,
            MessageExtractorOptions::class,
        ));

        $helper->getWriter($writer);
    }

    /**
     * @return array<array{reader: string}>
     */
    public function invalidWriterProvider(): array
    {
        return [
            'not a closure' => [
                'reader' => __DIR__ . '/fixtures/writer-closure-invalid-01.php',
            ],
            'not enough parameters' => [
                'reader' => __DIR__ . '/fixtures/writer-closure-invalid-02.php',
            ],
            'first param is not DescriptorCollection' => [
                'reader' => __DIR__ . '/fixtures/writer-closure-invalid-03.php',
            ],
            'second param is not MessageExtractorOptions' => [
                'reader' => __DIR__ . '/fixtures/writer-closure-invalid-04.php',
            ],
            'return type is not array' => [
                'reader' => __DIR__ . '/fixtures/writer-closure-invalid-05.php',
            ],
        ];
    }
}