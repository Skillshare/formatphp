<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Writer;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Format\Writer\SmartlingWriter;
use FormatPHP\Format\WriterOptions;
use FormatPHP\Test\TestCase;

class SmartlingWriterTest extends TestCase
{
    public function testFormatter(): void
    {
        $options = new WriterOptions();

        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('foo'));
        $collection->add(new Descriptor('bar', 'some message'));
        $collection->add(new Descriptor('baz', 'another message', 'a description'));

        $formatter = new SmartlingWriter();

        $this->assertSame(
            [
                'smartling' => [
                    'string_format' => 'icu',
                    'translate_paths' => [
                        [
                            'instruction' => '*/description',
                            'key' => '{*}/message',
                            'path' => '*/message',
                        ],
                    ],
                    'variants_enabled' => true,
                ],
                'bar' => ['message' => 'some message'],
                'baz' => ['description' => 'a description', 'message' => 'another message'],
                'foo' => ['message' => ''],
            ],
            $formatter($collection, $options),
        );
    }
}
