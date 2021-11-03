<?php

declare(strict_types=1);

namespace FormatPHP\Test\Writer\Formatter;

use FormatPHP\Descriptor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Test\TestCase;
use FormatPHP\Writer\Formatter\Smartling;

class SmartlingTest extends TestCase
{
    public function testFormatter(): void
    {
        $options = new MessageExtractorOptions();

        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('foo'));
        $collection->add(new Descriptor('bar', 'some message'));
        $collection->add(new Descriptor('baz', 'another message', 'a description'));

        $formatter = new Smartling();

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
                'foo' => ['message' => ''],
                'bar' => ['message' => 'some message'],
                'baz' => ['description' => 'a description', 'message' => 'another message'],
            ],
            $formatter($collection, $options),
        );
    }
}
