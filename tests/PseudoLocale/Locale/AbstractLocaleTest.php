<?php

declare(strict_types=1);

namespace FormatPHP\Test\PseudoLocale\Locale;

use FormatPHP\Icu\MessageFormat\Parser\Exception\UnableToParseMessageException;
use FormatPHP\PseudoLocale\Locale\AbstractLocale;
use FormatPHP\PseudoLocale\Locale\EnXa;
use FormatPHP\PseudoLocale\Locale\EnXb;
use FormatPHP\PseudoLocale\Locale\XxAc;
use FormatPHP\PseudoLocale\Locale\XxHa;
use FormatPHP\PseudoLocale\Locale\XxLs;
use FormatPHP\PseudoLocale\Locale\XxZa;
use FormatPHP\Test\TestCase;

class AbstractLocaleTest extends TestCase
{
    /**
     * @dataProvider provideLocales
     */
    public function testConvertThrowsExceptionForParserError(AbstractLocale $locale): void
    {
        $this->expectException(UnableToParseMessageException::class);

        $locale->convert('this opening <tag>has no end');
    }

    /**
     * @return array<string, array{locale: AbstractLocale}>
     */
    public function provideLocales(): array
    {
        return [
            'en-XA' => ['locale' => new EnXa()],
            'en-XB' => ['locale' => new EnXb()],
            'xx-AC' => ['locale' => new XxAc()],
            'xx-HA' => ['locale' => new XxHa()],
            'xx-LS' => ['locale' => new XxLs()],
            'xx-ZA' => ['locale' => new XxZa()],
        ];
    }
}
