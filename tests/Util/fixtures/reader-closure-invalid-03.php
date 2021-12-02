<?php

declare(strict_types=1);

use FormatPHP\Intl\LocaleInterface;
use FormatPHP\MessageCollection;

return function ($config, array $data, LocaleInterface $localeResolved): MessageCollection
{
    return new MessageCollection();
};
