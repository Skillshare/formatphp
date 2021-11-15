<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Message;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;

class MessageCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new MessageCollection();

        $this->assertSame(MessageInterface::class, $collection->getType());
    }

    public function testGetOffsetByMessageId(): void
    {
        $message = new Message('foobar', 'This is a message');
        $collection = new MessageCollection([$message]);

        $this->assertSame($message, $collection['foobar']);
    }
}
