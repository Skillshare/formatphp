<?php

declare(strict_types=1);

namespace FormatPHP\Test\Writer\Formatter;

use FormatPHP\Descriptor;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Test\TestCase;
use FormatPHP\Writer\Formatter\FormatPHP;

class FormatPHPTest extends TestCase
{
    public function testFormatterBasic(): void
    {
        $options = new MessageExtractorOptions();

        $collection = new DescriptorCollection();
        $collection->add(new Descriptor('foo'));
        $collection->add(new Descriptor('bar', 'some message'));
        $collection->add(new Descriptor('baz', 'another message', 'a description'));

        $formatter = new FormatPHP();

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
        $options = new MessageExtractorOptions();
        $options->extractSourceLocation = true;
        $options->pragma = 'intl';

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

        $formatter = new FormatPHP();

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
