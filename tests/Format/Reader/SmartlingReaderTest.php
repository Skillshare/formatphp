<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Reader;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Format\Reader\SmartlingReader;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;

use function sprintf;

class SmartlingReaderTest extends TestCase
{
    public function testThrowsExceptionWhenMessageIdIsNotAString(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $formatReader = new SmartlingReader();
        $data = ['smartling' => [], 'foo'];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message ID; received integer',
            SmartlingReader::class,
        ));

        $formatReader($config, $data, $locale);
    }

    public function testThrowsExceptionWhenMessageIsNotAString(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $formatReader = new SmartlingReader();
        $data = ['smartling' => [], 'foo' => ['bar']];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message property; message does not exist or is not a string',
            SmartlingReader::class,
        ));

        $formatReader($config, $data, $locale);
    }

    public function testInvoke(): void
    {
        $locale = new Locale('en-US');
        $localeResolved = new Locale('en');
        $config = new Config($locale);
        $formatReader = new SmartlingReader();
        $data = ['smartling' => [], 'foo' => ['message' => 'I am foo'], 'bar' => ['message' => 'I am bar']];

        $collection = $formatReader($config, $data, $localeResolved);

        $this->assertInstanceOf(MessageCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(MessageInterface::class, $collection['foo']);
        $this->assertSame('I am foo', $collection['foo']->getMessage());
        $this->assertInstanceOf(MessageInterface::class, $collection['bar']);
        $this->assertSame('I am bar', $collection['bar']->getMessage());
    }
}