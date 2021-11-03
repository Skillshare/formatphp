<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl\Formatter;

use FormatPHP\Descriptor;
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

    /**
     * @param array<array-key, int | float | string> $replacements
     *
     * @dataProvider formatProvider
     */
    public function testFormat(
        Locale $locale,
        IntlDescriptor $descriptor,
        string $expected,
        array $replacements = [],
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
     * @return mixed[]
     */
    public function formatProvider(): array
    {
        $localeEn = new Locale('en');
        $localeEnGb = new Locale('en-GB');
        $localeFr = new Locale('fr');
        $localeFrCa = new Locale('fr-CA');
        $localeFoo = new Locale('foo');

        $descriptors = [
            'empty' => new Descriptor(),
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
            'description only' => new Descriptor(
                null,
                null,
                'This description has no default message, so it shouldn\'t find a message.',
            ),
        ];

        return [
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['empty'],
                'expected' => '',
            ],
            [
                'locale' => $localeFr,
                'descriptor' => $descriptors['full'],
                'expected' => 'Nous sommes aujourd\'hui le 25/10/2021',
                'replacements' => ['ts' => 1635204852], // Mon, 25 Oct 2021 23:34:12 +0000
            ],
            [
                'locale' => $localeFrCa,
                'descriptor' => $descriptors['id only'],
                'expected' => 'Une chaîne de traduction sans message par défaut',
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => $descriptors['id with defaultMessage'],
                'expected' => 'Howdy!',
            ],
            [
                'locale' => $localeEnGb,
                'descriptor' => $descriptors['id with description'],
                'expected' => 'I don\'t know what to write here.',
            ],
            [
                'locale' => $localeFr,
                'descriptor' => $descriptors['defaultMessage only'],
                'expected' => 'Que fais-tu ce week-end?',
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
                'locale' => $localeFrCa,
                'descriptor' => (object) $descriptors['complicated pattern'],
                'expected' => 'La dernière fois que j\'ai vérifié, elle avait 2 animaux de compagnie.',
                'replacements' => ['gender' => 'female', 'petCount' => 2],
            ],
            [
                'locale' => $localeEn,
                'descriptor' => $descriptors['description only'],
                'expected' => '',
            ],
            [
                'locale' => $localeFoo,
                'descriptor' => $descriptors['complicated pattern'],
                'expected' => 'La dernière fois que j\'ai vérifié, ils avaient no animaux.',
                'replacements' => ['gender' => 'he', 'petCount' => 0],
                'defaultLocale' => $localeFr,
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
