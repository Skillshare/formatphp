<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser;
use FormatPHP\Icu\MessageFormat\Parser\Exception\IllegalParserUsageException;
use FormatPHP\Icu\MessageFormat\Parser\Options;
use FormatPHP\Test\TestCase;

use function json_encode;

use const JSON_INVALID_UTF8_IGNORE;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class ParserTest extends TestCase
{
    private const JSON_ENCODE_FLAGS = JSON_INVALID_UTF8_IGNORE
        | JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_THROW_ON_ERROR;

    /**
     * @dataProvider parserProvider
     */
    public function testParser(string $message, ?Options $options = null): void
    {
        $parser = new Parser($message, $options);
        $parsed = (string) json_encode($parser->parse(), self::JSON_ENCODE_FLAGS);

        $this->assertMatchesJsonSnapshot($parsed);
    }

    /**
     * @dataProvider numberSkeletonProvider
     */
    public function testNumberSkeleton(string $skeleton): void
    {
        $message = "{0, number, ::$skeleton}";

        $options = new Options();
        $options->shouldParseSkeletons = true;

        $parser = new Parser($message, $options);
        $parsed = (string) json_encode($parser->parse(), self::JSON_ENCODE_FLAGS);

        $this->assertMatchesJsonSnapshot($parsed);
    }

    public function testParserCannotRunMoreThanOnce(): void
    {
        $parser = new Parser('foo');
        $parser->parse();

        $this->expectException(IllegalParserUsageException::class);
        $this->expectExceptionMessage('The parser may only be used once');

        $parser->parse();
    }

    /**
     * We want to ensure that FormatPHP follows the same parsing rules as
     * FormatJS, so we've borrowed all test cases from FormatJS and have
     * verified that our snapshot files match.
     *
     * @link https://github.com/formatjs/formatjs/blob/main/packages/icu-messageformat-parser/tests/parser.test.ts
     * @link https://github.com/formatjs/formatjs/blob/main/packages/icu-messageformat-parser/tests/__snapshots__/parser.test.ts.snap
     *
     * @return array<string, array{message: string, options?: Options | null}>
     */
    public function parserProvider(): array
    {
        return [
            'basic_argument_1' => ['message' => '{a}'],
            'basic_argument_2' => ['message' => "a {b} \nc"],
            'date_arg_skeleton_1' => ['message' => "{0, date, ::yyyy.MM.dd G 'at' HH:mm:ss vvvv}"],
            'date_arg_skeleton_2' => ['message' => "{0, date, ::EEE, MMM d, ''yy}"],
            'date_arg_skeleton_3' => ['message' => '{0, date, ::h:mm a}'],
            'double_apostrophes_1' => ['message' => "a''b"],
            'duplicate_plural_selectors' => [
                'message' => 'You have {count, plural, one {# hot dog} one {# hamburger} one '
                    . '{# sandwich} other {# snacks}} in your lunch bag.',
            ],
            'duplicate_select_selectors' => [
                'message' => 'You have {count, select, one {# hot dog} one {# hamburger} one '
                    . '{# sandwich} other {# snacks}} in your lunch bag.',
            ],
            'empty_argument_1' => ['message' => 'My name is { }'],
            'empty_argument_2' => ['message' => "My name is {\n}"],
            'escaped_multiple_tags_1' => ['message' => "I '<'3 cats. '<a>foo</a>' '<b>bar</b>'"],
            'escaped_pound_1' => [
                'message' => "{numPhotos, plural, =0{no photos} =1{one photo} other{'#' photos}}",
            ],
            'expect_arg_closing_brace_1' => ['message' => '{0,  '],
            'expect_arg_format_1' => ['message' => 'My name is {0, }'],
            'expect_date_skeleton_1' => ['message' => '{0, date, ::}'],
            'expect_number_arg_skeleton_token_1' => ['message' => '{0, number, ::}'],
            'expect_number_arg_skeleton_token_option_1' => ['message' => '{0, number, ::currency/}'],
            'expect_number_arg_style_1' => ['message' => '{0, number, }'],
            'expect_plural_arg_offset_1' => ['message' => '{foo, plural, offset}'],
            'expect_select_args_1' => ['message' => '{foo, select}'],
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
            'incomplete_nested_message_in_tag' => ['message' => '<a>{a, plural, other {</a>}}'],
            'invalid_arg_format_1' => ['message' => 'My name is {0, foo}'],
            'invalid_close_tag_1' => ['message' => '<a></ b>'],
            'invalid_closing_tag_1' => ['message' => '<test>a</123>'],
            'invalid_closing_tag_2' => ['message' => '<test>a</'],
            'invalid_closing_tag_3' => ['message' => '<test>a</test'],
            'invalid_closing_tag_4' => ['message' => '<test>a'],
            'invalid_tag_1' => ['message' => '<test! />'],
            'invalid_tag_2' => ['message' => '<test / >'],
            'invalid_tag_3' => ['message' => '<test foo />'],
            'left_angle_bracket_1' => ['message' => 'I <3 cats.'],
            'less_than_sign_1' => [
                // See: https://github.com/formatjs/formatjs/issues/1845
                'message' => '< {level, select, A {1} 4 {2} 3 {3} 2{6} 1{12}} hours',
            ],
            'malformed_argument_1' => ['message' => 'My name is {0!}'],
            'malformed_argument_2' => ['message' => 'My name is { .'],
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
            'not_quoted_string_1' => ['message' => "'aa''b'"],
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
            'quoted_pound_sign_2' => [
                'message' => "You {count, plural, one {worked for '# hour} other {worked for '# hours}} today.",
            ],
            'quoted_string_1' => ['message' => "'{a''b}'"],
            'quoted_string_2' => ['message' => "'}a''b{'"],
            'quoted_string_3' => ['message' => "aaa'{'"],
            'quoted_string_4' => ['message' => "aaa'}'"],
            'quoted_string_5' => [
                // See: https://unicode-org.github.io/icu/userguide/format_parse/messages/#quotingescaping
                // See: https://github.com/formatjs/formatjs/issues/97
                'message' => "This '{isn''t}' obvious",
            ],
            'quoted_tag_1' => ['message' => "'<a>"],
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
            'unclosed_argument_1' => ['message' => 'My name is { 0'],
            'unclosed_argument_2' => ['message' => 'My name is { '],
            'unclosed_number_arg_1' => ['message' => '{0, number'],
            'unclosed_number_arg_2' => ['message' => '{0, number, percent'],
            'unclosed_number_arg_3' => ['message' => '{0, number, ::percent'],
            'unclosed_quote_in_arg_style_1' => ['message' => "{foo, number, 'test"],
            'unclosed_quoted_string_1' => ['message' => "a '{a{ {}{}{} ''bb"],
            'unclosed_quoted_string_2' => ['message' => "a 'a {}{}"],
            'unclosed_quoted_string_3' => ['message' => "a '{a{ {}{}{}}}''' \n {}"],
            'unclosed_quoted_string_4' => ['message' => "You have '{count'"],
            'unclosed_quoted_string_5' => ['message' => "You have '{count"],
            'unclosed_quoted_string_6' => ['message' => "You have '{count}"],
            'unescaped_string_literal_1' => ['message' => '}'],
            'unmatched_closing_tag_1' => ['message' => 'foo</test>'],
            'unmatched_open_close_tag_1' => ['message' => '<a></b>'],
            'unmatched_open_close_tag_2' => ['message' => '<a></ab>'],
            'uppercase_tag_1' => ['message' => 'this is <a>nested <Button>{placeholder}</Button></a>'],
        ];
    }

    /**
     * @return array<string, string[]>
     */
    public function numberSkeletonProvider(): array
    {
        return [
            'number_skeleton_1' => ['compact-short currency/GBP'],
            'number_skeleton_2' => ['@@#'],
            'number_skeleton_3' => ['currency/CAD unit-width-narrow'],
            'number_skeleton_4' => ['percent .##'],
            'number_skeleton_5' => ['percent .000*'],
            'number_skeleton_6' => ['percent .0###'],
            'number_skeleton_7' => ['percent .00/@##'],
            'number_skeleton_8' => ['percent .00/@@@'],
            'number_skeleton_9' => ['percent .00/@@@@*'],
            'number_skeleton_10' => ['currency/GBP .00##/@@@ unit-width-full-name'],
            'number_skeleton_11' => ['measure-unit/length-meter .00##/@@@ unit-width-full-name'],
            'number_skeleton_12' => ['scientific/+ee/sign-always'],
        ];
    }
}
