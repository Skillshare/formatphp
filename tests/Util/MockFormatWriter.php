<?php

declare(strict_types=1);

namespace FormatPHP\Test\Util;

use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Format\WriterInterface;

class MockFormatWriter implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, MessageExtractorOptions $options): array
    {
        return [];
    }
}
