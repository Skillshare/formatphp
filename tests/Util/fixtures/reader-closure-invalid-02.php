<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\MessageCollection;

return function (ConfigInterface $config): MessageCollection {
    return new MessageCollection();
};
