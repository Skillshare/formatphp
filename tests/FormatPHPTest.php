<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\FormatPHP;
use FormatPHP\Intl\Locale;
use FormatPHP\Message;
use FormatPHP\MessageCollection;

class FormatPHPTest extends TestCase
{
    public function testConstructorWithInstanceObjects(): void
    {
        $locale = new Locale('fr');
        $messageCollection = new MessageCollection();
        $defaultLocale = new Locale('en');

        $intl = new FormatPHP($locale, $messageCollection, $defaultLocale);

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

        $intl = new FormatPHP($locale, new MessageCollection([$message]));

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
