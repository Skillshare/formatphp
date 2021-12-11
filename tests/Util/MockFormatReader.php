<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use FormatPHP\ConfigInterface;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\MessageCollection;

class MockFormatReader implements ReaderInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(ConfigInterface $config, array $data): MessageCollection
    {
        return new MessageCollection();
    }
}
