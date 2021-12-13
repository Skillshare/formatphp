<?php

declare(strict_types=1);

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

return new class implements WriterInterface {
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        return [];
    }
};
