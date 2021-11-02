<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Intl;
use FormatPHP\Locale;
use FormatPHP\Message;

class IntlTest extends TestCase
{
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
