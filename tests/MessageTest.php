<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Intl\LocaleInterface;
use FormatPHP\Message;

class MessageTest extends TestCase
{
    public function testGetId(): void
    {
        $locale = $this->mockery(LocaleInterface::class);
        $message = new Message($locale, 'foo', 'Hello, there!');

        $this->assertSame('foo', $message->getId());
    }

    public function testGetLocale(): void
    {
        $locale = $this->mockery(LocaleInterface::class);
        $message = new Message($locale, 'bar', 'Goodbye, then.');

        $this->assertSame($locale, $message->getLocale());
    }

    public function testGetMessage(): void
    {
        $locale = $this->mockery(LocaleInterface::class);
        $message = new Message($locale, 'baz', 'Hello, again.');

        $this->assertSame('Hello, again.', $message->getMessage());
    }
}
