<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Message;

class MessageTest extends TestCase
{
    public function testGetId(): void
    {
        $message = new Message('foo', 'Hello, there!');

        $this->assertSame('foo', $message->getId());
    }

    public function testGetMessage(): void
    {
        $message = new Message('baz', 'Hello, again.');

        $this->assertSame('Hello, again.', $message->getMessage());
    }
}
