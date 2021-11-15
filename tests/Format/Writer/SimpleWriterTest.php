<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format\Writer;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Format\Writer\SimpleWriter;
use FormatPHP\Test\TestCase;

class SimpleWriterTest extends TestCase
{
    public function testInvoke(): void
    {
        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('aaa', 'first message', 'a description'));
        $collection->add(new Descriptor('bbb', 'second message', 'another description'));

        $this->assertSame(
            [
                'aaa' => 'first message',
                'bbb' => 'second message',
            ],
            (new SimpleWriter())($collection, new MessageExtractorOptions()),
        );
    }
}
