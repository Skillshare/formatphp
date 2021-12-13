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
        $defaultRichTextElements = [
            'em' => fn (string $text): string => '<em class="bar">' . $text . '</em>',
            'strong' => fn (string $text): string => '<strong class="foo">' . $text . '</strong>',
        ];

        $config = new Config($locale, $defaultLocale, $defaultRichTextElements);

        $this->assertSame($locale, $config->getLocale());
        $this->assertSame($defaultLocale, $config->getDefaultLocale());
        $this->assertSame($defaultRichTextElements, $config->getDefaultRichTextElements());
        $this->assertSame(IdInterpolator::DEFAULT_ID_INTERPOLATION_PATTERN, $config->getIdInterpolatorPattern());
    }
}
