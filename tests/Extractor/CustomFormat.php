<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;

class CustomFormat implements WriterInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, WriterOptions $options): array
    {
        $format = [];
        foreach ($collection as $item) {
            $format[(string) $item->getId()] = [
                'id' => (string) $item->getId(),
                'string' => $item->getDefaultMessage(),
            ];
        }

        return $format;
    }
}
