<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\MessageCollection;

return function (ConfigInterface $config, array $data)
{
    return new MessageCollection();
};
