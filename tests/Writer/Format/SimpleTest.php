<?php

declare(strict_types=1);

namespace FormatPHP\Test\Writer\Format;

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Test\TestCase;
use FormatPHP\Writer\Format\Simple;

class SimpleTest extends TestCase
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
            (new Simple())($collection, new MessageExtractorOptions()),
        );
    }
}
