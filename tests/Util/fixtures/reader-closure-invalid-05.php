<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\MessageCollection;

return function (ConfigInterface $config, array $data, $localeResolved): MessageCollection
{
    return new MessageCollection();
};