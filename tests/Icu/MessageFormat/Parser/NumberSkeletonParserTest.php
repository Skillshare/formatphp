<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\NumberSkeletonParser;
use FormatPHP\Test\TestCase;

use function json_encode;

use const JSON_INVALID_UTF8_IGNORE;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class NumberSkeletonParserTest extends TestCase
{
    private const JSON_ENCODE_FLAGS = JSON_INVALID_UTF8_IGNORE
        | JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_THROW_ON_ERROR;

    /**
     * @dataProvider numberSkeletonProvider
     */
    public function testParseNumberSkeleton(string $skeleton): void
    {
        $parser = new NumberSkeletonParser();
        $tokens = $parser->parseTokens($skeleton);

        $parsed = (string) json_encode($parser->parseOptions($tokens), self::JSON_ENCODE_FLAGS);

        $this->assertMatchesJsonSnapshot($parsed);
    }

    /**
     * @return array<string[]>
     */
    public function numberSkeletonProvider(): array
    {
        return [
            ['percent .##'],
            ['.##'],
            ['.##/w'],
            ['.'],
            ['% .##'],
            ['.##/@##r'],
            ['.##/@##s'],
            ['percent .000*'],
            ['percent .0###'],
            ['percent .00/@##'],
            ['percent .00/@@@'],
            ['percent .00/@@@@*'],
            ['percent scale/0.01'],
            ['currency/CAD .'],
            ['currency/GBP .0*/@@@'],
            ['currency/GBP .00##/@@@'],
            ['currency/GBP .00##/@@@ unit-width-full-name'],
            ['measure-unit/length-meter .00##/@@@'],
            ['measure-unit/length-meter .00##/@@@ unit-width-full-name'],
            ['compact-short'],
            ['compact-long'],
            ['scientific'],
            ['scientific/sign-always'],
            ['scientific/+ee/sign-always'],
            ['engineering'],
            ['engineering/sign-except-zero'],
            ['notation-simple'],
            ['sign-auto'],
            ['sign-always'],
            ['+!'],
            ['sign-never'],
            ['+_'],
            ['sign-accounting'],
            ['()'],
            ['sign-accounting-always'],
            ['()!'],
            ['sign-except-zero'],
            ['+?'],
            ['sign-accounting-except-zero'],
            ['()?'],
            ['000'],
            ['integer-width/*000'],
            ['E0'],
            ['E+!00'],
            ['EE+?000'],
            ['%x100'],
            ['group-off'],
            [',_'],
            ['unit-width-short'],
            ['unit-width-iso-code'],
        ];
    }
}
