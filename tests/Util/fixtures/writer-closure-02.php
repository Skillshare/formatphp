<?php

declare(strict_types=1);

use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Format\WriterInterface;

return new class implements WriterInterface {
    public function __invoke(DescriptorCollection $collection, MessageExtractorOptions $options): array
    {
        return [];
    }
};
