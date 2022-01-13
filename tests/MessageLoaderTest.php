<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\LocaleNotFoundException;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Intl\Locale;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\MessageLoader;

use function sprintf;

/**
 * @psalm-import-type ReaderType from ReaderInterface
 */
class MessageLoaderTest extends TestCase
{
    public function testExceptionWhenDirectoryIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'Messages directory "%s" is not a valid directory',
            __FILE__,
        ));

        /** @var ReaderInterface $reader */
        $reader = $this->mockery(ReaderInterface::class);

        new MessageLoader(
            __FILE__,
            new Config(new Locale('en')),
            $reader,
        );
    }

    public function testExceptionWhenUnableToFindSuitableLocale(): void
    {
        // Esperanto, Latin script, US region.
        $locale = new Locale('eo-Latn-US');

        $messagesDirectory = __DIR__ . '/fixtures/locales';

        /** @var ReaderInterface $reader */
        $reader = $this->mockery(ReaderInterface::class);

        $loader = new MessageLoader(
            $messagesDirectory,
            new Config($locale),
            $reader,
        );

        $this->expectException(LocaleNotFoundException::class);
        $this->expectExceptionMessage(
            'Unable to find a suitable locale for "eo-Latn-US" in ' . $messagesDirectory
            . '; please set a default locale',
        );

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

        $this->assertGreaterThanOrEqual(1, $collection->count());
        $this->assertNotNull($collection['about.inspire']);
        $this->assertInstanceOf(MessageInterface::class, $collection['about.inspire']);
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
        $this->assertSame(
            'في Skillshare ، نقوم بتمكين الأعضاء للحصول على الإلهام.',
            $collection['about.inspire']->getMessage(),
        );
    }

    /**
     * @param ReaderType $customReader
     *
     * @dataProvider provideCustomReader
     */
    public function testLoadMessagesWithCustomReader($customReader): void
    {
        $locale = new Locale('ar');

        $loader = new MessageLoader(
            __DIR__ . '/fixtures/locales',
            new Config($locale),
            $customReader,
        );

        $collection = $loader->loadMessages();

        $this->assertCount(1, $collection);
        $this->assertNotNull($collection['about.inspire']);
        $this->assertInstanceOf(MessageInterface::class, $collection['about.inspire']);
        $this->assertSame(
            'في Skillshare ، نقوم بتمكين الأعضاء للحصول على الإلهام.',
            $collection['about.inspire']->getMessage(),
        );
    }

    /**
     * @return mixed[]
     */
    public function provideCustomReader(): array
    {
        $customReader = new CustomMessageLoaderReader();

        return [
            ['customReader' => CustomMessageLoaderReader::class],
            ['customReader' => $customReader],
            ['customReader' => fn (array $data): MessageCollection => (new FormatPHPReader())($data)],
            ['customReader' => [$customReader, '__invoke']],
            ['customReader' => __DIR__ . '/fixtures/custom-reader.php'],
            ['customReader' => null],
        ];
    }

    public function testLoadMessagesNormalizesFilenames(): void
    {
        $locale = new Locale('en-XB');
        $defaultLocale = new Locale('en');

        $loader = new MessageLoader(
            __DIR__ . '/fixtures/locales',
            new Config($locale, $defaultLocale),
            new FormatPHPReader(),
        );

        $collection = $loader->loadMessages();

        $this->assertGreaterThanOrEqual(1, $collection->count());
        $this->assertNotNull($collection['about.inspire']);
        $this->assertInstanceOf(MessageInterface::class, $collection['about.inspire']);
        $this->assertSame(
            '[!! Ḁṭ Ṡǩíííĺĺśśśḫâŕŕŕè, ẘè èṁṗṗṗŏẘèèèŕ ṁṁṁèṁḃḃḃèŕśśś ṭŏŏŏ ĝèèèṭ íííńśṗṗṗíŕèèèḋ. !!]',
            $collection['about.inspire']->getMessage(),
        );
    }
}
