<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\Locale;
use FormatPHP\Intl\Message;
use FormatPHP\Intl\MessageCollection;
use FormatPHP\Test\TestCase;

class MessageCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new MessageCollection();

        $this->assertSame(Message::class, $collection->getType());
    }

    public function testGetMessageFindsAndReturnsMessage(): void
    {
        $locale = $this->mockery(Locale::class, [
            'getId' => 'en-US',
        ]);

        $message = $this->mockery(Message::class, [
            'getId' => 'foobar',
            'getLocale' => $this->mockery(Locale::class, [
                'getId' => 'en-US',
            ]),
            'getMessage' => 'This is a message',
        ]);

        $collection = new MessageCollection([$message]);

        $this->assertSame(
            'This is a message',
            $collection->getMessage('foobar', $locale),
        );
    }

    public function testGetMessageReturnsNullWhenMessageNotFound(): void
    {
        $locale = $this->mockery(Locale::class, [
            'getId' => 'en-US',
        ]);

        $collection = new MessageCollection();

        $this->assertNull($collection->getMessage('foobar', $locale));
    }
}
