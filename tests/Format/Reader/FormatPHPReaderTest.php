<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Reader;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;

use function sprintf;

class FormatPHPReaderTest extends TestCase
{
    public function testThrowsExceptionWhenMessageIdIsNotAString(): void
    {
        $config = new Config(new Locale('en'));
        $formatReader = new FormatPHPReader();
        $data = ['foo'];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message ID; received integer',
            FormatPHPReader::class,
        ));

        $formatReader($config, $data);
    }

    public function testThrowsExceptionWhenMessageIsNotAString(): void
    {
        $config = new Config(new Locale('en'));
        $formatReader = new FormatPHPReader();
        $data = ['foo' => ['bar']];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string defaultMessage property; defaultMessage does not exist or is not a string',
            FormatPHPReader::class,
        ));

        $formatReader($config, $data);
    }

    public function testInvoke(): void
    {
        $config = new Config(new Locale('en-US'));
        $formatReader = new FormatPHPReader();
        $data = ['foo' => ['defaultMessage' => 'I am foo'], 'bar' => ['defaultMessage' => 'I am bar']];

        $collection = $formatReader($config, $data);

        $this->assertInstanceOf(MessageCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(MessageInterface::class, $collection['foo']);
        $this->assertSame('I am foo', $collection['foo']->getMessage());
        $this->assertInstanceOf(MessageInterface::class, $collection['bar']);
        $this->assertSame('I am bar', $collection['bar']->getMessage());
    }
}
