<?php

declare(strict_types=1);

namespace FormatPHP\Test\Reader\Format;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidMessageShapeException;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Reader\Format\FormatPHP;
use FormatPHP\Test\TestCase;

use function sprintf;

class FormatPHPTest extends TestCase
{
    public function testThrowsExceptionWhenMessageIdIsNotAString(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $formatReader = new FormatPHP();
        $data = ['foo'];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string message ID; received integer',
            FormatPHP::class,
        ));

        $formatReader($config, $data, $locale);
    }

    public function testThrowsExceptionWhenMessageIsNotAString(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $formatReader = new FormatPHP();
        $data = ['foo' => ['bar']];

        $this->expectException(InvalidMessageShapeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s expects a string defaultMessage property; defaultMessage does not exist or is not a string',
            FormatPHP::class,
        ));

        $formatReader($config, $data, $locale);
    }

    public function testInvoke(): void
    {
        $locale = new Locale('en-US');
        $localeResolved = new Locale('en');
        $config = new Config($locale);
        $formatReader = new FormatPHP();
        $data = ['foo' => ['defaultMessage' => 'I am foo'], 'bar' => ['defaultMessage' => 'I am bar']];

        $collection = $formatReader($config, $data, $localeResolved);

        $this->assertInstanceOf(MessageCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertInstanceOf(MessageInterface::class, $collection['foo']);
        $this->assertSame('I am foo', $collection['foo']->getMessage());
        $this->assertSame($localeResolved, $collection['foo']->getLocale());
        $this->assertInstanceOf(MessageInterface::class, $collection['bar']);
        $this->assertSame('I am bar', $collection['bar']->getMessage());
        $this->assertSame($localeResolved, $collection['bar']->getLocale());
    }
}
