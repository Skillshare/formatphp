<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl\Formatter;

use FormatPHP\Descriptor;
use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Config;
use FormatPHP\Intl\Descriptor as IntlDescriptor;
use FormatPHP\Intl\Formatter\MessageFormatter;
use FormatPHP\Intl\MessageCollection;
use FormatPHP\Locale;
use FormatPHP\Message;
use FormatPHP\Test\TestCase;

class MessageFormatterTest extends TestCase
{
    private const MESSAGE_DESCRIPTORS = [
        'empty' => [
            // An empty message descriptor.
        ],
        'full' => [
            'id' => 'myMessage',
            'defaultMessage' => 'Today is {ts, date, ::yyyyMMdd}',
            'description' => 'This tells the user what day it is today',
        ],
        'id only' => [
            'id' => 'foo',
        ],
        'id with defaultMessage' => [
            'id' => 'bar',
            'defaultMessage' => 'Howdy!',
        ],
        'id with description' => [
            'id' => 'baz',
            'description' => 'There is not default message for this one',
        ],
        'defaultMessage only' => [
            'defaultMessage' => 'What are you doing this weekend?',
        ],
        'complicated pattern' => [
            // There are extra newlines in here to test proper trimming.
            'defaultMessage' => <<<'EOM'

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
            'description' => 'This is a more complicated message pattern.',
        ],
        'description only' => [
            'description' => 'This description has no default message, so it shouldn\'t find a message.',
        ],
    ];

    private const TRANSLATION_MESSAGES_EN = [
        'myMessage' => 'Today is {ts, date, ::yyyyMMdd}',
        'foo' => 'A translation string with no default message',
        'bar' => 'Howdy!',
        'baz' => 'I don\'t know what to write here.',
        'z2BIsL' => 'What are you doing this weekend?',
        'KBErIh' => 'Last time I checked, {gender, select, male {he had} female {she had} other {they had} } '
            . '{petCount, plural, =0 {no pets} =1 {a pet} other {# pets} }.',
    ];

    private const TRANSLATION_MESSAGES_FR = [
        'myMessage' => 'Nous sommes aujourd\'hui le {ts, date, ::yyyyMMdd}',
        'foo' => 'Une chaîne de traduction sans message par défaut',
        'bar' => 'Salut!',
        'baz' => 'Je ne sais pas quoi écrire ici.',
        'z2BIsL' => 'Que fais-tu ce week-end?',
        'KBErIh' => 'La dernière fois que j\'ai vérifié, {gender, select, male {il avait} female {elle avait} '
            . 'other {ils avaient} } {petCount, plural, =0 {no animaux} =1 {un animal de compagnie} other '
            . '{# animaux de compagnie} }.',
    ];

    private ?MessageCollection $messageCollection = null;

    protected function setUp(): void
    {
        parent::setUp();

        $messages = new MessageCollection();
        $localeEn = new Locale('en');
        $localeFr = new Locale('fr');

        foreach (self::TRANSLATION_MESSAGES_EN as $id => $value) {
            $messages[] = new Message($localeEn, $id, $value);
        }

        foreach (self::TRANSLATION_MESSAGES_FR as $id => $value) {
            $messages[] = new Message($localeFr, $id, $value);
        }

        $this->messageCollection = $messages;
    }

    public function testFormatterThrowsExceptionForInvalidDescriptor(): void
    {
        $config = $this->mockery(Config::class, [
            'getLocale->getId' => 'en',
        ]);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Descriptor must be a FormatPHP\Intl\Descriptor, array, or object with public properties.',
        );

        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        MessageFormatter::format($config, 'this should cause exception');
    }

    public function testFormatterThrowsExceptionForInvalidValues(): void
    {
        $messages = new MessageCollection([new Message(new Locale('en'), 'foo', 'bar')]);

        $config = $this->mockery(Config::class, [
            'getLocale->getId' => 'en',
            'getDefaultLocale' => null,
            'getMessages' => $messages,
            'getIdInterpolatorPattern' => IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN,
        ]);

        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage(
            'Values must be an array, an object with public properties, or null.',
        );

        /**
         * @psalm-suppress InvalidArgument
         * @phpstan-ignore-next-line
         */
        MessageFormatter::format($config, new Descriptor('foo'), 'this should cause exception');
    }

    /**
     * @param IntlDescriptor | array{id?: string, defaultMessage?: string, description?: string} $descriptor
     * @param object | array<array-key, int | float | string> | null $replacements
     *
     * @dataProvider formatProvider
     *
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function testFormat(
        Locale $locale,
        $descriptor,
        string $expected,
        $replacements = null,
        ?Locale $defaultLocale = null
    ): void {
        $config = $this->mockery(Config::class, [
            'getDefaultLocale' => $defaultLocale,
            'getLocale' => $locale,
            'getMessages' => $this->messageCollection,
            'getIdInterpolatorPattern' => IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN,
        ]);

        $this->assertSame(
            $expected,
            MessageFormatter::format($config, $descriptor, $replacements),
        );
    }

    /**
     * @return array<array{locale: Locale, descriptor: mixed, expected: string, replacements?: mixed}>
     */
    public function formatProvider(): array
    {
        $localeEn = new Locale('en');
        $localeEnGb = new Locale('en-GB');
        $localeFr = new Locale('fr');
        $localeFrCa = new Locale('fr-CA');
        $localeFoo = new Locale('foo');

        return [
            [
                'locale' => $localeEn,
                'descriptor' => self::MESSAGE_DESCRIPTORS['empty'],
                'expected' => '',
            ],
            [
                'locale' => $localeFr,
                'descriptor' => self::MESSAGE_DESCRIPTORS['full'],
                'expected' => 'Nous sommes aujourd\'hui le 25/10/2021',
                'replacements' => ['ts' => 1635204852], // Mon, 25 Oct 2021 23:34:12 +0000
            ],
            [
                'locale' => $localeFrCa,
                'descriptor' => self::MESSAGE_DESCRIPTORS['id only'],
                'expected' => 'Une chaîne de traduction sans message par défaut',
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => self::MESSAGE_DESCRIPTORS['id with defaultMessage'],
                'expected' => 'Howdy!',
            ],
            [
                'locale' => $localeEnGb,
                'descriptor' => self::MESSAGE_DESCRIPTORS['id with description'],
                'expected' => 'I don\'t know what to write here.',
            ],
            [
                'locale' => $localeFr,
                'descriptor' => self::MESSAGE_DESCRIPTORS['defaultMessage only'],
                'expected' => 'Que fais-tu ce week-end?',
            ],
            [
                'locale' => $localeEn,
                'descriptor' => self::MESSAGE_DESCRIPTORS['complicated pattern'],
                'expected' => 'Last time I checked, he had no pets.',
                'replacements' => ['gender' => 'male', 'petCount' => 0],
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => self::MESSAGE_DESCRIPTORS['complicated pattern'],
                'expected' => 'Last time I checked, they had a pet.',
                'replacements' => ['gender' => 'non-binary', 'petCount' => 1],
            ],
            [
                'locale' => $localeFrCa,
                'descriptor' => (object) self::MESSAGE_DESCRIPTORS['complicated pattern'],
                'expected' => 'La dernière fois que j\'ai vérifié, elle avait 2 animaux de compagnie.',
                'replacements' => ['gender' => 'female', 'petCount' => 2],
            ],
            [
                'locale' => $localeEn,
                'descriptor' => self::MESSAGE_DESCRIPTORS['description only'],
                'expected' => '',
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => self::MESSAGE_DESCRIPTORS['complicated pattern'],
                'expected' => 'La dernière fois que j\'ai vérifié, ils avaient no animaux.',
                'replacements' => ['gender' => 'he', 'petCount' => 0],
                'defaultLocale' => $localeFr,
            ],
            [
                'locale' => $localeEn,
                'descriptor' => self::MESSAGE_DESCRIPTORS['complicated pattern'],
                'expected' => 'Last time I checked, he had a pet.',
                'replacements' => (object) ['gender' => 'male', 'petCount' => 1],
            ],
        ];
    }
}
