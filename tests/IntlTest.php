<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use ArrayObject;
use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Intl;
use FormatPHP\Locale;
use FormatPHP\Message;

class IntlTest extends TestCase
{
    public function testConstructorThrowsExceptionWhenLocaleIsInvalid(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('Locale must be an instance of FormatPHP\Intl\Locale or a string locale.');

        /**
         * @psalm-suppress InvalidScalarArgument
         * @phpstan-ignore-next-line
         */
        new Intl(1234, []);
    }

    public function testConstructorThrowsExceptionWhenMessagesIsInvalid(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Messages must be an instance of FormatPHP\Intl\MessageCollection '
            . 'or an array of FormatPHP\Intl\Message objects.',
        );

        /**
         * @psalm-suppress MixedArgumentTypeCoercion
         */
        new Intl('en', new ArrayObject());
    }

    public function testConstructorThrowsExceptionWhenDefaultLocaleIsInvalid(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Default locale must be an instance of FormatPHP\Intl\Locale, a string locale, or null.',
        );

        /**
         * @psalm-suppress InvalidScalarArgument
         * @phpstan-ignore-next-line
         */
        new Intl('en', new Intl\MessageCollection(), 1234);
    }

    public function testConstructorWithInstanceObjects(): void
    {
        $locale = new Locale('fr');
        $messageCollection = new Intl\MessageCollection();
        $defaultLocale = new Locale('en');

        $intl = new Intl($locale, $messageCollection, $defaultLocale);

        $this->assertSame($locale, $intl->getLocale());
        $this->assertSame($messageCollection, $intl->getMessages());
        $this->assertSame($defaultLocale, $intl->getDefaultLocale());
    }

    public function testConstructorWithPrimitiveTypes(): void
    {
        $message = new Message(new Locale('fr'), 'foo', 'un message d\'essai');
        $intl = new Intl('fr', [$message], 'en');

        $this->assertSame('fr', $intl->getLocale()->getId());
        $this->assertCount(1, $intl->getMessages());
        $this->assertSame($message, $intl->getMessages()[0]);
        $this->assertNotNull($intl->getDefaultLocale());
        $this->assertSame('en', $intl->getDefaultLocale()->getId());
    }

    public function testFormatMessage(): void
    {
        $locale = new Locale('fr');
        $message = new Message(
            $locale,
            'myMessage',
            'Nous sommes aujourd\'hui le {ts, date, ::yyyyMMdd}',
        );

        $intl = new Intl($locale, new Intl\MessageCollection([$message]));

        $this->assertSame(
            'Nous sommes aujourd\'hui le 25/10/2021',
            $intl->formatMessage(
                [
                    'id' => 'myMessage',
                    'defaultMessage' => 'Today is {ts, date, ::yyyyMMdd}',
                ],
                [
                    'ts' => 1635204852, // Mon, 25 Oct 2021 23:34:12 +0000
                ],
            ),
        );
    }
}
