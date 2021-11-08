<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;

class MessageCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new MessageCollection();

        $this->assertSame(MessageInterface::class, $collection->getType());
    }

    public function testGetMessageFindsAndReturnsMessage(): void
    {
        $locale = $this->mockery(LocaleInterface::class, [
            'baseName' => 'en-US',
        ]);

        $message = $this->mockery(MessageInterface::class, [
            'getId' => 'foobar',
            'getLocale' => $this->mockery(LocaleInterface::class, [
                'baseName' => 'en-US',
            ]),
            'getMessage' => 'This is a message',
        ]);

        $collection = new MessageCollection([$message]);

        $this->assertSame(
            'This is a message',
            $collection->getMessage('foobar', $locale),
        );
    }

    public function testGetMessageThrowsExceptionWhenMessageNotFound(): void
    {
        $locale = $this->mockery(LocaleInterface::class, [
            'baseName' => 'en-US',
        ]);

        $collection = new MessageCollection();

        $this->expectException(MessageNotFoundException::class);
        $this->expectExceptionMessage('Could not find message with ID "foobar".');

        $collection->getMessage('foobar', $locale);
    }
}
