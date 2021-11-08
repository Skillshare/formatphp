<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Config;
use FormatPHP\FormatPHP;
use FormatPHP\Intl\Locale;
use FormatPHP\Message;
use FormatPHP\MessageCollection;

class FormatPHPTest extends TestCase
{
    public function testFormatMessage(): void
    {
        $locale = new Locale('fr');
        $config = new Config($locale);

        $message = new Message(
            $locale,
            'myMessage',
            'Nous sommes aujourd\'hui le {ts, date, ::yyyyMMdd}',
        );

        $messageCollection = new MessageCollection($config, [$message]);
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(
            'Nous sommes aujourd\'hui le 25/10/2021',
            $formatphp->formatMessage(
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
