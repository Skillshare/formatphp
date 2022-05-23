<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Config;
use FormatPHP\ConfigInterface;
use FormatPHP\Descriptor;
use FormatPHP\DescriptorInterface;
use FormatPHP\Exception\UnableToFormatMessageException;
use FormatPHP\Icu\MessageFormat\Parser\Exception\UnableToParseMessageException;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\MessageFormat;
use FormatPHP\Message;
use FormatPHP\MessageCollection;
use FormatPHP\MessageInterface;
use FormatPHP\Test\TestCase;
use FormatPHP\Util\DescriptorIdBuilder;

class MessageFormatTest extends TestCase
{
    use DescriptorIdBuilder;

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

    private ConfigInterface $config;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (self::TRANSLATION_MESSAGES_EN as $id => $value) {
            $this->messagesEn[] = new Message($id, $value);
        }
    }

    protected function getConfig(): ConfigInterface
    {
        return $this->config;
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
        $this->config = new Config($locale);
        $formatter = new MessageFormat($locale);
        $messages = new MessageCollection($this->messagesEn);
        $messageId = $this->buildMessageId($descriptor);

        /** @var MessageInterface $message */
        $message = $messages[$messageId];

        $this->assertSame($expected, $formatter->format($message->getMessage(), $replacements));
    }

    /**
     * @return mixed[]
     */
    public function formatProvider(): array
    {
        $localeEn = new Locale('en');
        $localeFoo = new Locale('foo');

        $descriptors = [
            'full' => new Descriptor(
                'myMessage',
                'Today is {ts, date, ::yyyyMMdd}',
                'This tells the user what day it is today',
            ),
            'id only' => new Descriptor('foo'),
            'id with defaultMessage' => new Descriptor('bar', 'Howdy!'),
            'id with description' => new Descriptor('baz', null, 'There is not default message for this one'),
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

    public function testTags(): void
    {
        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $formatted = $formatter->format(
            'Hi, <profileLink><boldface>{name}</boldface>, our <italicized>great friend</italicized></profileLink>!',
            [
                'name' => 'Samwise',
                'profileLink' => fn ($text) => '<a href="https://example.com">' . $text . '</a>',
                'boldface' => fn ($text) => "<strong>$text</strong>",
                'italicized' => fn ($text) => "<em>$text</em>",
            ],
        );

        $this->assertSame(
            'Hi, <a href="https://example.com"><strong>Samwise</strong>, our <em>great friend</em></a>!',
            $formatted,
        );
    }

    public function testMixedTags(): void
    {
        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $formatted = $formatter->format(
            'Hi, <profileLink><boldface>{name}</boldface>, <foo /> our '
                . '<italicized>great friend</italicized></profileLink>!',
            [
                'name' => 'Pippin',
                'profileLink' => fn ($text) => '<a href="https://example.com">' . $text . '</a>',
            ],
        );

        $this->assertSame(
            'Hi, <a href="https://example.com"><boldface>Pippin</boldface>, '
                . '<foo/> our <italicized>great friend</italicized></a>!',
            $formatted,
        );
    }

    public function testSelectAndPluralOptionsWithTags(): void
    {
        $message = <<<'EOD'
            Last time I checked, {gender, select,
                male {<italicized>he</italicized> had}
                female {<italicized>she</italicized> had}
                other {<italicized>they</italicized> had}
            } {petCount, plural,
                =0 {<bold>no</bold> pets}
                =1 {<bold>a</bold> pet}
                other {<bold>#</bold> pets}
            }.
            EOD;

        $expected = 'Last time I checked, <em>she</em> had <strong>4</strong> pets.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $formatted = $formatter->format($message, [
            'gender' => 'female',
            'petCount' => 4,
            'italicized' => fn ($text) => "<em>$text</em>",
            'bold' => fn ($text) => "<strong>$text</strong>",
        ]);

        $this->assertSame($expected, $formatted);
    }

    public function testThrowsExceptionWhenUnableToParseMessage(): void
    {
        $message = 'Hello, <link>{name}';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        // We're not using expectException() because we want to actually
        // inspect the exception object as part of this test.
        try {
            $formatter->format($message, [
                'name' => 'Bilbo',
                'link' => fn ($text) => "<a>$text</a>",
            ]);
        } catch (UnableToFormatMessageException $exception) {
            $this->assertSame(
                'Unable to format message with pattern "Hello, <link>{name}" for locale "en-US"',
                $exception->getMessage(),
            );
            $this->assertInstanceOf(UnableToParseMessageException::class, $exception->getPrevious());
            $this->assertSame(
                'Syntax error UNCLOSED_TAG found while parsing message "Hello, <link>{name}"',
                $exception->getPrevious()->getMessage(),
            );
        }
    }

    public function testReplacesEmptyTagWithCallbackResultValue(): void
    {
        $message = 'Sometimes a <foobar></foobar> might not have a body';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $formatted = $formatter->format($message, [
            'foobar' => fn () => '<em>tag</em>',
        ]);

        $this->assertSame('Sometimes a <em>tag</em> might not have a body', $formatted);
    }

    public function testReplacesSelfClosingTagWithCallbackResultValue(): void
    {
        $message = 'Sometimes a <foobar /> might be self-closing';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $formatted = $formatter->format($message, [
            'foobar' => fn () => '<strong>tag</strong>',
        ]);

        $this->assertSame('Sometimes a <strong>tag</strong> might be self-closing', $formatted);
    }

    public function testThrowsExceptionForIllegalArgumentError(): void
    {
        $message = 'Today is {value, date}.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        // We're not using expectException() because we want to actually
        // inspect the exception object as part of this test.
        try {
            $formatter->format($message, ['value' => 'not a valid date/time']);
        } catch (UnableToFormatMessageException $exception) {
            $this->assertSame(
                'Unable to format message with pattern "Today is {value, date}." for locale "en-US"',
                $exception->getMessage(),
            );
            $this->assertInstanceOf(UnableToFormatMessageException::class, $exception->getPrevious());
            $this->assertSame(
                "The argument for key 'value' cannot be used as a date or time: U_ILLEGAL_ARGUMENT_ERROR",
                $exception->getPrevious()->getMessage(),
            );
        }
    }

    public function testProcessesPercentagesAccordingToEcma402(): void
    {
        $message = 'Your discount is {discount, number, ::percent} off the retail value.';
        $expected = 'Your discount is 25% off the retail value.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format($message, ['discount' => 0.25]);

        $this->assertSame($expected, $result);
    }

    public function testProcessesPercentagesAccordingToEcma402WithScaleAt100(): void
    {
        $message = 'Your discount is {discount, number, ::percent scale/100} off the retail value.';
        $expected = 'Your discount is 2,500% off the retail value.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format($message, ['discount' => 0.25]);

        $this->assertSame($expected, $result);
    }

    public function testProcessesPercentagesAccordingToEcma402WithScaleAt1(): void
    {
        $message = 'Your discount is {discount, number, ::percent scale/1} off the retail value.';
        $expected = 'Your discount is 25% off the retail value.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format($message, ['discount' => 0.25]);

        $this->assertSame($expected, $result);
    }

    public function testProcessesNumberWithoutStyle(): void
    {
        $message = 'Your discount is {discount, number} off the retail value.';
        $expected = 'Your discount is 25 off the retail value.';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format($message, ['discount' => 25]);

        $this->assertSame($expected, $result);
    }

    public function testArrayCallablesAndClosures(): void
    {
        $message = 'Hello, <firstName></firstName> <lastName></lastName>!';
        $expected = 'Hello, Jane Doe!';

        $user = new class {
            public function getFirstName(): string
            {
                return 'Jane';
            }

            public function getLastName(): string
            {
                return 'Doe';
            }
        };

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format(
            $message,
            [
                'firstName' => [$user, 'getFirstName'],
                'lastName' => fn (): string => $user->getLastName(),
            ],
        );

        $this->assertSame($expected, $result);
    }

    public function testStringsMustNotEvaluateAsCallables(): void
    {
        $message = 'Hello, {firstName} {lastName}!';
        $expected = 'Hello, Ceil Floor!';

        $locale = new Locale('en-US');
        $formatter = new MessageFormat($locale);

        $result = $formatter->format(
            $message,
            [
                'firstName' => 'Ceil',
                'lastName' => 'Floor',
            ],
        );

        $this->assertSame($expected, $result);
    }
}
