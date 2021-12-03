<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

return function (ConfigInterface $config, $data, LocaleInterface $localeResolved): MessageCollection
{
    return new MessageCollection();
};
