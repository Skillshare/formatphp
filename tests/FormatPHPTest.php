<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Config;
use FormatPHP\Exception\InvalidArgumentException;
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
            'myMessage',
            'Nous sommes aujourd\'hui le {ts, date, ::yyyyMMdd}',
        );

        $messageCollection = new MessageCollection([$message]);
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

    public function testFormatMessageReturnsDefaultMessage(): void
    {
        $locale = new Locale('fr');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(
            // The date is formatted according to the locale, even though the
            // default message is returned.
            'Today is 25/10/2021',
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

    public function testFormatMessageReturnsMessageIdWhenMessageNotFound(): void
    {
        $locale = new Locale('en-US');
        $config = new Config($locale);
        $collection = new MessageCollection();
        $formatphp = new FormatPHP($config, $collection);

        $this->assertSame('foobar', $formatphp->formatMessage(['id' => 'foobar']));
    }

    public function testFormatMessageThrowsExceptionWhenUnableToGenerateMessageId(): void
    {
        $locale = new Locale('en-US');
        $config = new Config($locale);
        $collection = new MessageCollection();
        $formatphp = new FormatPHP($config, $collection);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The message descriptor must have an ID or default message');

        $formatphp->formatMessage([]);
    }

    public function testFormatMessageUsesDefaultRichTextElements(): void
    {
        $locale = new Locale('en-US');
        $config = new Config($locale, null, [
            'homeLink' => fn (string $text): string => '<a href="https://example.com>' . $text . '</a>',
            'boldface' => fn ($text) => "<strong>$text</strong>",
            'italicized' => fn ($text) => "<em>$text</em>",
        ]);

        $message = new Message('myMessage', '<homeLink>Go <boldface>home</boldface></homeLink>, {name}!');
        $messageCollection = new MessageCollection([$message]);
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(
            '<a href="https://example.com>Go <strong>home</strong></a>, Sam!',
            $formatphp->formatMessage(
                [
                    'id' => 'myMessage',
                    'defaultMessage' => '<homeLink>Go <boldface>home</boldface></homeLink>, {name}!',
                ],
                [
                    'name' => 'Sam',
                ],
            ),
        );
    }
}
