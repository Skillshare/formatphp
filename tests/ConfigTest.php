<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Config;
use FormatPHP\Extractor\IdInterpolator;
use FormatPHP\Intl\Locale;

class ConfigTest extends TestCase
{
    public function testInstanceConstruction(): void
    {
        $locale = new Locale('en-US');
        $defaultLocale = new Locale('en');
        $config = new Config($locale, $defaultLocale);

        $this->assertSame($locale, $config->getLocale());
        $this->assertSame($defaultLocale, $config->getDefaultLocale());
        $this->assertSame(IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN, $config->getIdInterpolatorPattern());
    }
}
