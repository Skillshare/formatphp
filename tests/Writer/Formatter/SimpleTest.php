<?php

declare(strict_types=1);

namespace FormatPHP\Test\Writer\Formatter;

use FormatPHP\Descriptor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Test\TestCase;
use FormatPHP\Writer\Formatter\Simple;

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
