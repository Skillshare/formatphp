<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Writer;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Format\Writer\ChromeWriter;
use FormatPHP\Format\WriterOptions;
use FormatPHP\Test\TestCase;

class ChromeWriterTest extends TestCase
{
    public function testFormatter(): void
    {
        $options = new WriterOptions();

        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('foo'));
        $collection->add(new Descriptor('bar', 'some message'));
        $collection->add(new Descriptor('baz', 'another message', 'a description'));

        $formatter = new ChromeWriter();

        $this->assertSame(
            [
                'foo' => ['message' => ''],
                'bar' => ['message' => 'some message'],
                'baz' => ['description' => 'a description', 'message' => 'another message'],
            ],
            $formatter($collection, $options),
        );
    }
}
