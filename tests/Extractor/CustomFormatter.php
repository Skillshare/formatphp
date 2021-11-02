<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Writer\Formatter\Formatter;

class CustomFormatter implements Formatter
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
