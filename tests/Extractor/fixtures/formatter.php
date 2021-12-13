<?php

/**
 * This is a test formatter
 */

declare(strict_types=1);

use FormatPHP\DescriptorCollection;
use FormatPHP\Format\WriterOptions;

/**
 * @return array<string, array{translation: string}>
 */
return function (DescriptorCollection $collection, WriterOptions $options): array {
    $format = [];
    foreach ($collection as $item) {
        $format[(string) $item->getId()] = [
            'translation' => $item->getDefaultMessage(),
        ];
    }

    return $format;
};
