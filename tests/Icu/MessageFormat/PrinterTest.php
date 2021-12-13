<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Options;
use FormatPHP\Icu\MessageFormat\Printer;
use FormatPHP\Test\TestCase;

class PrinterTest extends TestCase
{
    /**
     * @dataProvider messageProvider
     */
    public function testPrintAst(string $message, ?Options $options = null): void
    {
        $parser = new Parser($message, $options);
        $parsed = $parser->parse();

        $printer = new Printer();

        $this->assertNotNull($parsed->val);
        $this->assertMatchesTextSnapshot($printer->printAst($parsed->val));
    }

    /**
     * @return array<string, array{message: string, options?: Options}>
     */
    public function messageProvider(): array
    {
        return [
            'basic_argument_1' => ['message' => '{a}'],
            'basic_argument_2' => ['message' => "a {b} \nc"],
            'date_arg_skeleton_1' => ['message' => "{0, date, ::yyyy.MM.dd G 'at' HH:mm:ss vvvv}"],
            'date_arg_skeleton_2' => ['message' => "{0, date, ::EEE, MMM d, ''yy}"],
            'date_arg_skeleton_3' => ['message' => '{0, date, ::h:mm a}'],
            'escaped_pound_1' => [
                'message' => "{numPhotos, plural, =0{no photos} =1{one photo} other{'#' photos}}",
            ],
            'ignore_tag_number_arg_1' => [
                'message' => 'I have <foo>{numCats, number}</foo> cats.',
                'options' => (function (): Options {
                    $options = new Options();
                    $options->ignoreTag = true;

                    return $options;
                })(),
            ],
            'ignore_tags_1' => [
                'message' => '<test-tag></test-tag>',
                'options' => (function (): Options {
                    $options = new Options();
                    $options->ignoreTag = true;

                    return $options;
                })(),
            ],
            'left_angle_bracket_1' => ['message' => 'I <3 cats.'],
            'less_than_sign_1' => [
                // See: https://github.com/formatjs/formatjs/issues/1845
                'message' => '< {level, select, A {1} 4 {2} 3 {3} 2{6} 1{12}} hours',
            ],
            'negative_offset_1' => [
                'message' => '{c, plural, offset:-2 =-1 { {text} project} other { {text} projects}}',
            ],
            'nested_1' => [
                'message' => <<<'EOD'

                    {gender_of_host, select,
                      female {
                        {num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to her party.}
                          =2 {{host} invites {guest} and one other person to her party.}
                          other {{host} invites {guest} and # other people to her party.}}}
                      male {
                        {num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to his party.}
                          =2 {{host} invites {guest} and one other person to his party.}
                          other {{host} invites {guest} and # other people to his party.}}}
                      other {
                        {num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to their party.}
                          =2 {{host} invites {guest} and one other person to their party.}
                          other {{host} invites {guest} and # other people to their party.}}}}
                EOD,
            ],
            'nested_tags_1' => ['message' => 'this is <a>nested <b>{placeholder}</b></a>'],
            'not_escaped_pound_1' => ['message' => "'#'"],
            'not_quoted_string_2' => ['message' => "I don't know"],
            'not_self_closing_tag_1' => ['message' => '< test-tag />'],
            'number_arg_skeleton_2' => ['message' => '{0, number, :: currency/GBP}'],
            'number_arg_skeleton_3' => ['message' => '{0, number, ::currency/GBP compact-short}'],
            'number_arg_style_1' => ['message' => '{0, number, percent}'],
            'numeric_tag_1' => ['message' => '<i0>foo</i0>'],
            'open_close_tag_1' => ['message' => '<test-tag></test-tag>'],
            'open_close_tag_2' => ['message' => '<test-tag>foo</test-tag>'],
            'open_close_tag_3' => ['message' => '<test-tag>foo {0} bar</test-tag>'],
            'open_close_tag_with_args' => [
                'message' => 'I <b>have</b> <foo>{numCats, number} some string {placeholder}</foo> cats.',
            ],
            'open_close_tag_with_nested_arg' => [
                'message' => <<<'EOD'
                    <bold>You have {
                            count, plural,
                            one {<italic>#</italic> apple}
                            other {<italic>#</italic> apples}
                        }.</bold>
                    EOD,
            ],
            'plural_arg_1' => [
                'message' => <<<'EOD'

                    Cart: {itemCount} {itemCount, plural,
                        one {item}
                        other {items}
                    }
                EOD,
            ],
            'plural_arg_2' => [
                'message' => <<<'EOD'

                    You have {itemCount, plural,
                        =0 {no items}
                        one {1 item}
                        other {{itemCount} items}
                    }.
                EOD,
            ],
            'plural_arg_with_escaped_nested_message' => [
                'message' => <<<'EOD'

                    {itemCount, plural,
                        one {item'}'}
                        other {items'}'}
                    }
                EOD,
            ],
            'plural_arg_with_offset_1' => [
                'message' => <<<'EOD'
                    You have {itemCount, plural, offset: 2
                            =0 {no items}
                            one {1 item}
                            other {{itemCount} items}
                        }.
                    EOD,
            ],
            'quoted_pound_sign_1' => [
                'message' => "You {count, plural, one {worked for '#' hour} other {worked for '#' hours}} today.",
            ],
            'quoted_string_3' => ['message' => "aaa'{'"],
            'quoted_string_4' => ['message' => "aaa'}'"],
            'select_arg_1' => [
                'message' => <<<'EOD'

                    {gender, select,
                        male {He}
                        female {She}
                        other {They}
                    } will respond shortly.
                EOD,
            ],
            'select_arg_with_nested_arguments' => [
                'message' => <<<'EOD'

                    {taxableArea, select,
                        yes {An additional {taxRate, number, percent} tax will be collected.}
                        other {No taxes apply.}
                    }
                EOD,
            ],
            'selectordinal_1' => [
                'message' => '{floor, selectordinal, =0{ground} one{#st} two{#nd} few{#rd} other{#th}} floor',
            ],
            'self_closing_tag_1' => ['message' => '<test-tag />'],
            'self_closing_tag_2' => ['message' => '<test-tag/>'],
            'simple_argument_1' => ['message' => 'My name is {0}'],
            'simple_argument_2' => ['message' => 'My name is { name }'],
            'simple_date_and_time_arg_1' => [
                'message' => 'Your meeting is scheduled for the {dateVal, date} at {timeVal, time}',
            ],
            'simple_number_arg_1' => ['message' => 'I have {numCats, number} cats.'],
            'time_arg_skeleton_1' => ['message' => '{0, time, ::h:mm a}'],
            'treat_unicode_nbsp_as_whitespace' => [
                // phpcs:ignore SlevomatCodingStandard.PHP.RequireNowdoc.RequiredNowdoc
                'message' => <<<EOD

                    {gender, select,
                    \u{200E}male {
                        {He}}
                    \u{200E}female {
                        {She}}
                    \u{200E}other{
                        {They}}}
                EOD,
            ],
            'trivial_1' => ['message' => 'a'],
            'trivial_2' => ['message' => '中文'],
            'unescaped_string_literal_1' => ['message' => '}'],
            'uppercase_tag_1' => ['message' => 'this is <a>nested <Button>{placeholder}</Button></a>'],
        ];
    }
}
