<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

class MockFormatWriter implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        return [];
    }
}
