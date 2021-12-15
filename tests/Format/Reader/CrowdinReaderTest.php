<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Reader;

use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Format\Reader\CrowdinReader;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;

use function sprintf;

class CrowdinReaderTest extends TestCase
{
    public function testThrowsExceptionWhenMessageIdIsNotAString(): void
    {
        $formatReader = new CrowdinReader();
        $data = ['foo'];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message ID; received integer',
            CrowdinReader::class,
        ));

        $formatReader($data);
    }

    public function testThrowsExceptionWhenMessageIsNotAString(): void
    {
        $formatReader = new CrowdinReader();
        $data = ['foo' => ['bar']];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message property; message does not exist or is not a string',
            CrowdinReader::class,
        ));

        $formatReader($data);
    }

    public function testInvoke(): void
    {
        $formatReader = new CrowdinReader();
        $data = ['foo' => ['message' => 'I am foo'], 'bar' => ['message' => 'I am bar']];

        $collection = $formatReader($data);

        $this->assertInstanceOf(MessageCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(MessageInterface::class, $collection['foo']);
        $this->assertSame('I am foo', $collection['foo']->getMessage());
        $this->assertInstanceOf(MessageInterface::class, $collection['bar']);
        $this->assertSame('I am bar', $collection['bar']->getMessage());
    }
}
