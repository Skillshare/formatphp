<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\ConfigInterface;
use FormatPHP\Descriptor;
use FormatPHP\Exception\MessageNotFoundException;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;

class MessageCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $config = $this->mockery(ConfigInterface::class);
        $collection = new MessageCollection($config);

        $this->assertSame(MessageInterface::class, $collection->getType());
    }

    public function testGetMessageFindsAndReturnsMessage(): void
    {
        $locale = new Locale('en-US');

        $config = $this->mockery(ConfigInterface::class, [
            'getDefaultLocale' => null,
            'getLocale' => $locale,
        ]);

        $message = $this->mockery(MessageInterface::class, [
            'getId' => 'foobar',
            'getLocale' => new Locale('en-US'),
            'getMessage' => 'This is a message',
        ]);

        $collection = new MessageCollection($config, [$message]);

        $this->assertSame('This is a message', $collection->getMessageById('foobar'));
    }

    public function testGetMessageThrowsExceptionWhenMessageNotFound(): void
    {
        $locale = new Locale('en-US');

        $config = $this->mockery(ConfigInterface::class, [
            'getDefaultLocale' => null,
            'getLocale' => $locale,
        ]);

        $collection = new MessageCollection($config);

        $this->expectException(MessageNotFoundException::class);
        $this->expectExceptionMessage('Unable to find message with ID "foobar" for locale "en-US"');

        $collection->getMessageById('foobar');
    }

    public function testGetMessageReturnsEmptyStringWhenUnableToGenerateMessageId(): void
    {
        $config = $this->mockery(ConfigInterface::class, [
            'getDefaultLocale' => null,
            'getLocale' => new Locale('en'),
            'getIdInterpolatorPattern' => IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN,
        ]);

        $collection = new MessageCollection($config);

        $this->assertSame('', $collection->getMessageByDescriptor(new Descriptor()));
    }
}
