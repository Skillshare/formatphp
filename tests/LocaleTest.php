<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Exception\InvalidArgument;
use FormatPHP\Intl\Locale as IntlLocale;
use FormatPHP\Locale;

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

        $this->assertInstanceOf(IntlLocale::class, $fallbackLocale);
        $this->assertSame('pt', $fallbackLocale->getId());
    }

    public function testExceptionWhenLocaleIsInvalid(): void
    {
        $this->expectException(InvalidArgument::class);
        $this->expectExceptionMessage('f-oo-bar is not valid BCP 47 formatted locale string.');

        new Locale('f-oo-bar');
    }
}
