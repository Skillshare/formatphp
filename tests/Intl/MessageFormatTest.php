<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Config;
use FormatPHP\Descriptor;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\UnableToFormatMessageException;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\MessageFormat;
use FormatPHP\Message;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;

class MessageFormatTest extends TestCase
{
    private const TRANSLATION_MESSAGES_EN = [
        'myMessage' => 'Today is {ts, date, ::yyyyMMdd}',
        'foo' => 'A translation string with no default message',
        'bar' => 'Howdy!',
        'baz' => 'I don\'t know what to write here.',
        'z2BIsL' => 'What are you doing this weekend?',
        'KBErIh' => 'Last time I checked, {gender, select, male {he had} female {she had} other {they had} } '
            . '{petCount, plural, =0 {no pets} =1 {a pet} other {# pets} }.',
    ];

    /**
     * @var MessageInterface[]
     */
    private array $messagesEn = [];

    protected function setUp(): void
    {
        parent::setUp();

        $localeEn = new Locale('en');

        foreach (self::TRANSLATION_MESSAGES_EN as $id => $value) {
            $this->messagesEn[] = new Message($localeEn, $id, $value);
        }
    }

    public function testFormatThrowsExceptionWhenUnableToFormatMessage(): void
    {
        $formatter = new MessageFormat(new Locale('en'));

        $this->expectException(UnableToFormatMessageException::class);
        $this->expectExceptionMessage('Unable to format message with pattern "" for locale "en"');

        $formatter->format('');
    }

    /**
     * @param array<array-key, int | float | string> $replacements
     *
     * @dataProvider formatProvider
     */
    public function testFormat(
        Locale $locale,
        DescriptorInterface $descriptor,
        string $expected,
        array $replacements = []
    ): void {
        $config = new Config($locale);
        $formatter = new MessageFormat($locale);
        $messages = new MessageCollection($config, $this->messagesEn);

        $this->assertSame(
            $expected,
            $formatter->format($messages->getMessageByDescriptor($descriptor), $replacements),
        );
    }

    /**
     * @return mixed[]
     */
    public function formatProvider(): array
    {
        $localeEn = new Locale('en');
        $localeFoo = new Locale('foo');

        $descriptors = [
            'empty' => new Descriptor('messageId'),
            'full' => new Descriptor(
                'myMessage',
                'Today is {ts, date, ::yyyyMMdd}',
                'This tells the user what day it is today',
            ),
            'id only' => new Descriptor('foo'),
            'id with defaultMessage' => new Descriptor('bar', 'Howdy!'),
            'id with description' => new Descriptor('baz', null, 'There is not default message for this one'),
            'id not found' => new Descriptor('idNotFound', 'Default message should be returned'),
            'defaultMessage only' => new Descriptor(null, 'What are you doing this weekend?'),
            'complicated pattern' => new Descriptor(
                null,
                // There are extra newlines in here to test proper trimming.
                <<<'EOM'

                    Last time I checked, {gender, select,
                        male {he had}
                        female {she had}
                        other {they had}
                    } {petCount, plural,
                        =0 {no pets}
                        =1 {a pet}
                        other {# pets}
                    }.

                    EOM,
                'This is a more complicated message pattern.',
            ),
        ];

        return [
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['empty'],
                'expected' => 'messageId',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['full'],
                'expected' => 'Today is 10/25/2021',
                'replacements' => ['ts' => 1635204852], // Mon, 25 Oct 2021 23:34:12 +0000
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['id only'],
                'expected' => 'A translation string with no default message',
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => $descriptors['id with defaultMessage'],
                'expected' => 'Howdy!',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['id with description'],
                'expected' => 'I don\'t know what to write here.',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['id not found'],
                'expected' => 'Default message should be returned',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['defaultMessage only'],
                'expected' => 'What are you doing this weekend?',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['complicated pattern'],
                'expected' => 'Last time I checked, he had no pets.',
                'replacements' => ['gender' => 'male', 'petCount' => 0],
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => $descriptors['complicated pattern'],
                'expected' => 'Last time I checked, they had a pet.',
                'replacements' => ['gender' => 'non-binary', 'petCount' => 1],
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => $descriptors['complicated pattern'],
                'expected' => 'Last time I checked, he had no pets.',
                'replacements' => ['gender' => 'male', 'petCount' => 0],
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['complicated pattern'],
                'expected' => 'Last time I checked, he had a pet.',
                'replacements' => ['gender' => 'male', 'petCount' => 1],
            ],
        ];
    }
}
