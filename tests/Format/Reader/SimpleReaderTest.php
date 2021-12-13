<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Reader;

use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Format\Reader\SimpleReader;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;

use function sprintf;

class SimpleReaderTest extends TestCase
{
    public function testThrowsExceptionWhenMessageIdIsNotAString(): void
    {
        $formatReader = new SimpleReader();
        $data = ['foo'];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message ID; received integer',
            SimpleReader::class,
        ));

        $formatReader($data);
    }

    public function testThrowsExceptionWhenMessageIsNotAString(): void
    {
        $formatReader = new SimpleReader();
        $data = ['foo' => ['bar']];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message; received array',
            SimpleReader::class,
        ));

        $formatReader($data);
    }

    public function testInvoke(): void
    {
        $formatReader = new SimpleReader();
        $data = ['foo' => 'I am foo', 'bar' => 'I am bar'];

        $collection = $formatReader($data);

        $this->assertInstanceOf(MessageCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(MessageInterface::class, $collection['foo']);
        $this->assertSame('I am foo', $collection['foo']->getMessage());
        $this->assertInstanceOf(MessageInterface::class, $collection['bar']);
        $this->assertSame('I am bar', $collection['bar']->getMessage());
    }
}
