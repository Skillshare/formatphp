<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Descriptor;

class DescriptorTest extends TestCase
{
    public function testPropertiesAreNullByDefault(): void
    {
        $descriptor = new Descriptor();

        $this->assertNull($descriptor->getDefaultMessage());
        $this->assertNull($descriptor->getDescription());
        $this->assertNull($descriptor->getId());
        $this->assertNull($descriptor->getSourceFile());
        $this->assertNull($descriptor->getSourceStartOffset());
        $this->assertNull($descriptor->getSourceEndOffset());
        $this->assertNull($descriptor->getSourceLine());
        $this->assertSame([], $descriptor->getMetadata());
        $this->assertSame(
            [
                'defaultMessage' => null,
                'description' => null,
                'end' => null,
                'file' => null,
                'id' => null,
                'line' => null,
                'meta' => [],
                'start' => null,
            ],
            $descriptor->toArray(),
        );
    }

    public function testPropertiesContainProvidedValues(): void
    {
        $descriptor = new Descriptor(
            'foobar',
            'This is the default message',
            'Translate this message using our guidelines',
            '/path/to/file.php',
            482,
            529,
            34,
        );

        $descriptor->setMetadata(['foo' => 'bar']);

        $this->assertSame('This is the default message', $descriptor->getDefaultMessage());
        $this->assertSame('Translate this message using our guidelines', $descriptor->getDescription());
        $this->assertSame('foobar', $descriptor->getId());
        $this->assertSame('/path/to/file.php', $descriptor->getSourceFile());
        $this->assertSame(482, $descriptor->getSourceStartOffset());
        $this->assertSame(529, $descriptor->getSourceEndOffset());
        $this->assertSame(34, $descriptor->getSourceLine());
        $this->assertSame(['foo' => 'bar'], $descriptor->getMetadata());
        $this->assertSame(
            [
                'defaultMessage' => 'This is the default message',
                'description' => 'Translate this message using our guidelines',
                'end' => 529,
                'file' => '/path/to/file.php',
                'id' => 'foobar',
                'line' => 34,
                'meta' => ['foo' => 'bar'],
                'start' => 482,
            ],
            $descriptor->toArray(),
        );
    }

    public function testSetId(): void
    {
        $descriptor = new Descriptor();
        $descriptor->setId('aDescriptorId');

        $this->assertSame('aDescriptorId', $descriptor->getId());
    }

    public function testSetDefaultMessage(): void
    {
        $descriptor = new Descriptor();
        $descriptor->setDefaultMessage('a default message');

        $this->assertSame('a default message', $descriptor->getDefaultMessage());
    }
}
