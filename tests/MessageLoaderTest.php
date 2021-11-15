<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\LocaleNotFoundException;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageInterface;
use FormatPHP\MessageLoader;

use function sprintf;

class MessageLoaderTest extends TestCase
{
    public function testExceptionWhenDirectoryIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Messages directory "%s" is not a valid directory',
            __FILE__,
        ));

        new MessageLoader(
            __FILE__,
            new Config(new Locale('en')),
            $this->mockery(ReaderInterface::class),
        );
    }

    public function testExceptionWhenUnableToFindSuitableLocale(): void
    {
        // Esperanto, Latin script, US region.
        $locale = new Locale('eo-Latn-US');

        $loader = new MessageLoader(
            __DIR__ . '/fixtures/locales',
            new Config($locale),
            $this->mockery(ReaderInterface::class),
        );

        $this->expectException(LocaleNotFoundException::class);
        $this->expectExceptionMessage('Unable to find a suitable locale for "eo-Latn-US"; please set a default locale');

        $loader->loadMessages();
    }

    public function testLoadMessagesFallsBackToDefaultLocale(): void
    {
        // Esperanto, Latin script, US region.
        $locale = new Locale('eo-Latn-US');

        $defaultLocale = new Locale('en');

        $loader = new MessageLoader(
            __DIR__ . '/fixtures/locales',
            new Config($locale, $defaultLocale),
            new FormatPHPReader(),
        );

        $collection = $loader->loadMessages();

        $this->assertCount(1, $collection);
        $this->assertNotNull($collection['about.inspire']);
        $this->assertInstanceOf(MessageInterface::class, $collection['about.inspire']);
        $this->assertSame('en', $collection['about.inspire']->getLocale()->toString());
    }

    public function testLoadMessagesWithFallback(): void
    {
        $locale = new Locale('ar-Arab-AE-VARIANT1');

        $defaultLocale = new Locale('en');

        $loader = new MessageLoader(
            __DIR__ . '/fixtures/locales',
            new Config($locale, $defaultLocale),
            new FormatPHPReader(),
        );

        $collection = $loader->loadMessages();

        $this->assertCount(1, $collection);
        $this->assertNotNull($collection['about.inspire']);
        $this->assertInstanceOf(MessageInterface::class, $collection['about.inspire']);
        $this->assertSame('ar', $collection['about.inspire']->getLocale()->toString());
        $this->assertSame(
            'في Skillshare ، نقوم بتمكين الأعضاء للحصول على الإلهام.',
            $collection['about.inspire']->getMessage(),
        );
    }
}
