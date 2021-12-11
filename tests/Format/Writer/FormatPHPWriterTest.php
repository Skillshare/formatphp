<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Writer;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Format\Writer\FormatPHPWriter;
use FormatPHP\Format\WriterOptions;
use FormatPHP\Test\TestCase;

class FormatPHPWriterTest extends TestCase
{
    public function testFormatterBasic(): void
    {
        $options = new WriterOptions();

        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('foo'));
        $collection->add(new Descriptor('bar', 'some message'));
        $collection->add(new Descriptor('baz', 'another message', 'a description'));

        $formatter = new FormatPHPWriter();

        $this->assertSame(
            [
                'foo' => ['defaultMessage' => ''],
                'bar' => ['defaultMessage' => 'some message'],
                'baz' => ['defaultMessage' => 'another message', 'description' => 'a description'],
            ],
            $formatter($collection, $options),
        );
    }

    public function testFormatterFull(): void
    {
        $options = new WriterOptions();
        $options->includesSourceLocation = true;
        $options->includesPragma = true;

        $descriptor = new Descriptor(
            'foo',
            'my default message',
            'my description',
            '/path/to/file.php',
            45,
            96,
            13,
        );

        $descriptor->setMetadata([
            'aaa' => 'some-value',
            'bbb' => 'another-value',
        ]);

        $collection = new DescriptorCollection();
        $collection->add($descriptor);

        $formatter = new FormatPHPWriter();

        $this->assertSame(
            [
                'foo' => [
                    'defaultMessage' => 'my default message',
                    'description' => 'my description',
                    'end' => 96,
                    'file' => '/path/to/file.php',
                    'line' => 13,
                    'meta' => [
                        'aaa' => 'some-value',
                        'bbb' => 'another-value',
                    ],
                    'start' => 45,
                ],
            ],
            $formatter($collection, $options),
        );
    }
}
