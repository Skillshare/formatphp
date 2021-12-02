<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

return new class implements ReaderInterface {
    public function __invoke(ConfigInterface $config, array $data, LocaleInterface $localeResolved): MessageCollection
    {
        return new MessageCollection();
    }
};
