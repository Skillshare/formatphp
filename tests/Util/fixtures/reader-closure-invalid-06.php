<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

return function (ConfigInterface $config, array $data, LocaleInterface $localeResolved)
{
    return new MessageCollection();
};
