<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Util;

use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidUtf8CodePointException;
use FormatPHP\Icu\MessageFormat\Parser\Util\CodePointHelper;
use FormatPHP\Test\TestCase;

use function mb_ord;
use function range;

use const PHP_INT_MAX;
use const PHP_INT_MIN;

class CodePointHelperTest extends TestCase
{
    private CodePointHelper $codePointHelper;

    protected function setUp(): void
    {
        $this->codePointHelper = new CodePointHelper();
    }

    /**
     * @dataProvider isAlphaProvider
     */
    public function testIsAlpha(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->codePointHelper->isAlpha($value));
    }

    /**
     * @return non-empty-list<array{int, bool}>
     */
    public function isAlphaProvider(): array
    {
        $values = [];

        foreach (range('a', 'z') as $char) {
            $values[] = [mb_ord($char, 'UTF-8'), true];
        }

        foreach (range('A', 'Z') as $char) {
            $values[] = [mb_ord($char, 'UTF-8'), true];
        }

        foreach (range(0, 9) as $char) {
            $values[] = [mb_ord((string) $char, 'UTF-8'), false];
        }

        foreach (range('-', '!') as $char) {
            $values[] = [mb_ord($char, 'UTF-8'), false];
        }

        return $values;
    }

    /**
     * @dataProvider isAlphaOrSlashProvider
     */
    public function testIsAlphaOrSlash(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->codePointHelper->isAlphaOrSlash($value));
    }

    /**
     * @return non-empty-list<array{int, bool}>
     */
    public function isAlphaOrSlashProvider(): array
    {
        return [
            [mb_ord('a', 'UTF-8'), true],
            [mb_ord('z', 'UTF-8'), true],
            [mb_ord('A', 'UTF-8'), true],
            [mb_ord('Z', 'UTF-8'), true],
            [mb_ord('/', 'UTF-8'), true],
            [mb_ord('\\', 'UTF-8'), false],
            [mb_ord('0', 'UTF-8'), false],
            [mb_ord('!', 'UTF-8'), false],
            [mb_ord('-', 'UTF-8'), false],
        ];
    }

    /**
     * @dataProvider isWhiteSpaceProvider
     */
    public function testIsWhiteSpace(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->codePointHelper->isWhiteSpace($value));
    }

    /**
     * @return non-empty-list<array{int, bool}>
     */
    public function isWhiteSpaceProvider(): array
    {
        return [
            [0x0009, true],
            [0x000a, true],
            [0x000b, true],
            [0x000c, true],
            [0x000d, true],
            [0x0020, true],
            [0x0085, true],
            [0x00A0, true],
            [0x1680, true],
            [0x2000, true],
            [0x2001, true],
            [0x2002, true],
            [0x2003, true],
            [0x2004, true],
            [0x2005, true],
            [0x2006, true],
            [0x2007, true],
            [0x2008, true],
            [0x2009, true],
            [0x200a, true],
            [0x200e, true],
            [0x200f, true],
            [0x2028, true],
            [0x2029, true],
            [0x202F, true],
            [0x205F, true],
            [0x3000, true],
            [mb_ord('A', 'UTF-8'), false],
            [mb_ord('Z', 'UTF-8'), false],
            [mb_ord('!', 'UTF-8'), false],
            [mb_ord('.', 'UTF-8'), false],
            [mb_ord('_', 'UTF-8'), false],
        ];
    }

    /**
     * @dataProvider isPatternSyntaxProvider
     */
    public function testIsPatternSyntax(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->codePointHelper->isPatternSyntax($value));
    }

    /**
     * @return non-empty-list<array{int, bool}>
     */
    public function isPatternSyntaxProvider(): array
    {
        $values = [];

        foreach (range(0x2190, 0x245f) as $char) {
            $values[] = [$char, true];
        }

        foreach (range(0x1000, 0x107f) as $char) {
            $values[] = [$char, false];
        }

        return $values;
    }

    /**
     * @dataProvider isPotentialElementNameCharProvider
     */
    public function testIsPotentialElementNameChar(int $value, bool $expected): void
    {
        $this->assertSame($expected, $this->codePointHelper->isPotentialElementNameChar($value));
    }

    /**
     * @return non-empty-list<array{int, bool}>
     */
    public function isPotentialElementNameCharProvider(): array
    {
        $values = [];

        foreach (range(0x00f8, 0x037d) as $char) {
            $values[] = [$char, true];
        }

        foreach (range(0xe000, 0xe07f) as $char) {
            $values[] = [$char, false];
        }

        return $values;
    }

    public function testCharCodeAt(): void
    {
        $this->assertSame(0x0062, $this->codePointHelper->charCodeAt(['a', 'b', 'c'], 1));
    }

    public function testCharCodeAtReturnsNull(): void
    {
        $this->assertNull($this->codePointHelper->charCodeAt(['a', 'b', 'c'], 3));
    }

    public function testFromCharCode(): void
    {
        $this->assertSame('ã', $this->codePointHelper->fromCharCode(0x00e3));
    }

    public function testFromCharCodeReturnsNull(): void
    {
        $this->assertNull($this->codePointHelper->fromCharCode(PHP_INT_MAX));
    }

    public function testFromCodePointThrowsException(): void
    {
        $this->expectException(InvalidUtf8CodePointException::class);
        $this->expectExceptionMessage('Code ' . PHP_INT_MAX . ' is an invalid UTF-8 code point');

        $this->codePointHelper->fromCodePoint(PHP_INT_MAX, PHP_INT_MIN);
    }

    public function testFromCodePointReturnsEmptyString(): void
    {
        $this->assertSame('', $this->codePointHelper->fromCodePoint());
    }

    public function testFromCodePoint(): void
    {
        $this->assertSame(
            'foobar',
            $this->codePointHelper->fromCodePoint(0x0066, 0x006f, 0x006f, 0x0062, 0x0061, 0x0072),
        );
    }
}
