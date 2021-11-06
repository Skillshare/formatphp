<?php

/**
 * This is a test formatter
 */

declare(strict_types=1);

use FormatPHP\DescriptorCollection;

/**
 * @return array<string, array{translation: string}>
 */
return function (DescriptorCollection $collection): array {
    $format = [];
    foreach ($collection as $item) {
        $format[(string) $item->getId()] = [
            'translation' => $item->getDefaultMessage(),
        ];
    }

    return $format;
};
