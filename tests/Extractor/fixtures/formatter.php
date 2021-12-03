<?php

/**
 * This is a test formatter
 */

declare(strict_types=1);

use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;

/**
 * @return array<string, array{translation: string}>
 *
 * @psalm-suppress UnusedClosureParam
 */
return function (DescriptorCollection $collection, MessageExtractorOptions $options): array {
    $format = [];
    foreach ($collection as $item) {
        $format[(string) $item->getId()] = [
            'translation' => $item->getDefaultMessage(),
        ];
    }

    return $format;
};
