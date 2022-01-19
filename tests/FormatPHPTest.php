<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use DateTimeImmutable;
use FormatPHP\Config;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\FormatPHP;
use FormatPHP\Intl\DateTimeFormatOptions;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\NumberFormatOptions;
use FormatPHP\Message;
use FormatPHP\MessageCollection;
use Locale as PhpLocale;

use function date;

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

    public function testFormatDateUsesCurrentDateWhenNoValuePassed(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(date('n/j/y'), $formatphp->formatDate());
    }

    public function testFormatDateWithUnixTimestamp(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $this->assertSame(
            '10/25/21',
            $formatphp->formatDate(1635204852),
        );
    }

    public function testFormatDateWithOptions(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $this->assertSame(
            'Monday, October 25, 2021 at 11:34:12 PM UTC',
            $formatphp->formatDate(1635204852, new DateTimeFormatOptions([
                'dateStyle' => 'full',
                'timeStyle' => 'long',
            ])),
        );
    }

    public function testFormatDateWithDateTimeInstance(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $date = new DateTimeImmutable('@' . 1635204852);

        $this->assertSame(
            '10/25/21',
            $formatphp->formatDate($date),
        );
    }

    public function testFormatDateWithString(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(
            '10/25/21',
            $formatphp->formatDate('Mon, 25 Oct 2021 23:34:12 +0000'),
        );
    }

    public function testFormatDateThrowsExceptionForInvalidArgument(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Value must be a string, integer, or instance of DateTimeInterface; received \'boolean\'',
        );

        // @phpstan-ignore-next-line
        $formatphp->formatDate(false);
    }

    public function testFormatTime(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame(
            '11:34 PM',
            $formatphp->formatTime('Mon, 25 Oct 2021 23:34:12 +0000'),
        );
    }

    public function testFormatTimeWithOptions(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $this->assertSame(
            '11:34:12 PM',
            $formatphp->formatTime(1635204852, new DateTimeFormatOptions([
                'second' => 'numeric',
            ])),
        );
    }

    public function testFormatTimeWithTimeStyle(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $this->assertSame(
            '11:34:12 PM Coordinated Universal Time',
            $formatphp->formatTime(1635204852, new DateTimeFormatOptions([
                'timeStyle' => 'full',
            ])),
        );
    }

    public function testFormatTimeWithDateStyle(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // Mon, 25 Oct 2021 23:34:12 +0000
        $this->assertSame(
            'Monday, October 25, 2021',
            $formatphp->formatTime(1635204852, new DateTimeFormatOptions([
                'dateStyle' => 'full',
            ])),
        );
    }

    public function testFormatTimeDoesNotModifyPassedDateTimeFormatOptionsInstance(): void
    {
        $time = 1642708984; // Thu, 20 Jan 2022 20:03:04 +0000
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $options = new DateTimeFormatOptions();

        // These should be null before passing them to formatTime().
        $this->assertNull($options->hour);
        $this->assertNull($options->minute);

        $this->assertSame('8:03 PM', $formatphp->formatTime($time, $options));

        // These should still be null after passing them to formatTime().
        $this->assertNull($options->hour);
        $this->assertNull($options->minute);
    }

    public function testFormatTimeUsesProvidedHourMinuteOptions(): void
    {
        $time = 1642708984; // Thu, 20 Jan 2022 20:03:04 +0000
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $options = new DateTimeFormatOptions([
            'hour' => '2-digit',
            'minute' => '2-digit',
        ]);

        $this->assertSame('8:03 PM', $formatphp->formatTime($time, $options));

        // These should not change after being passed to formatTime().
        $this->assertSame('2-digit', $options->hour);
        $this->assertSame('2-digit', $options->minute);
    }

    public function testConstructorWithoutConfiguration(): void
    {
        $date = 1635204852; // Mon, 25 Oct 2021 23:34:12 +0000

        $systemLocale = PhpLocale::getDefault();
        $locale = new Locale($systemLocale);
        $config = new Config($locale);
        $formatphpForComparison = new FormatPHP($config);

        $formatphpWithoutConfig = new FormatPHP();

        $this->assertSame(
            $formatphpForComparison->formatDate($date),
            $formatphpWithoutConfig->formatDate($date),
        );
    }

    public function testConstructorWithoutMessages(): void
    {
        $date = 1635204852; // Mon, 25 Oct 2021 23:34:12 +0000

        $systemLocale = PhpLocale::getDefault();
        $locale = new Locale($systemLocale);
        $config = new Config($locale);
        $formatphpForComparison = new FormatPHP($config);

        $expectedMessageResponse = $formatphpForComparison->formatMessage(
            ['defaultMessage' => 'Today is {ts, date, ::yyyyMMdd}'],
            ['ts' => $date],
        );

        $formatphpWithoutMessages = new FormatPHP();

        $this->assertSame(
            $expectedMessageResponse,
            $formatphpWithoutMessages->formatMessage(
                ['id' => 'myMessage', 'defaultMessage' => 'Today is {ts, date, ::yyyyMMdd}'],
                ['ts' => $date],
            ),
        );
    }

    public function testFormatNumber(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame('1,234', $formatphp->formatNumber(1234));
    }

    public function testFormatNumberWithOptions(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame('1.234E3', $formatphp->formatNumber(1234, new NumberFormatOptions([
            'notation' => 'scientific',
        ])));
    }

    public function testFormatCurrency(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        $this->assertSame('$1,234.00', $formatphp->formatCurrency(1234, 'USD'));
    }

    public function testFormatCurrencyWithOptions(): void
    {
        $locale = new Locale('en');
        $config = new Config($locale);
        $messageCollection = new MessageCollection();
        $formatphp = new FormatPHP($config, $messageCollection);

        // The following string has a non-breaking space encoded in it.
        $expected = "EUR\xC2\xA01,234";

        $this->assertSame($expected, $formatphp->formatCurrency(1234, 'EUR', new NumberFormatOptions([
            'currencyDisplay' => 'code',
            'trailingZeroDisplay' => 'stripIfInteger',
        ])));
    }
}
