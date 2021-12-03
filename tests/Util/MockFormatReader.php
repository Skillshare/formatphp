<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use FormatPHP\ConfigInterface;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

class MockFormatReader implements ReaderInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ConfigInterface $config, array $data, LocaleInterface $localeResolved): MessageCollection
    {
        return new MessageCollection();
    }
}
