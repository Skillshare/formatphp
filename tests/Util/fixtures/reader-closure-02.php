<?php

declare(strict_types=1);

use FormatPHP\ConfigInterface;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\MessageCollection;

return new class implements ReaderInterface {
    public function __invoke(ConfigInterface $config, array $data): MessageCollection
    {
        return new MessageCollection();
    }
};
