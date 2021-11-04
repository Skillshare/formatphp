<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Intl\Locale;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\Test\TestCase;

class LocaleTest extends TestCase
{
    public function testGetId(): void
    {
        $locale = new Locale('pt-BR');

        $this->assertSame('pt-BR', $locale->getId());
    }

    public function testGetFallbackLocale(): void
    {
        $locale = new Locale('pt-BR');
        $fallbackLocale = $locale->getFallbackLocale();

        $this->assertInstanceOf(LocaleInterface::class, $fallbackLocale);
        $this->assertSame('pt', $fallbackLocale->getId());
    }

    public function testExceptionWhenLocaleIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('f-oo-bar is not valid BCP 47 formatted locale string.');

        new Locale('f-oo-bar');
    }
}
