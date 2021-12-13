<?php

declare(strict_types=1);

use FormatPHP\Format\ReaderInterface;
use FormatPHP\MessageCollection;

return new class implements ReaderInterface {
    public function __invoke(array $data): MessageCollection
    {
        return new MessageCollection();
    }
};
