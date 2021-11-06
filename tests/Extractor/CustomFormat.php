<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Writer\FormatInterface;

class CustomFormat implements FormatInterface
{
    /**
     * @inheritdoc
     */
    public function __invoke(DescriptorCollection $collection, MessageExtractorOptions $options): array
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
