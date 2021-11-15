<?php

declare(strict_types=1);

use FormatPHP\Descriptor;
use FormatPHP\DescriptorCollection;
use FormatPHP\Extractor\MessageExtractorOptions;
use FormatPHP\Extractor\Parser\ParserError;
use FormatPHP\Extractor\Parser\ParserErrorCollection;

return function (
    string $filePath,
    MessageExtractorOptions $options,
    ParserErrorCollection $errors
): DescriptorCollection {
    $descriptors = new DescriptorCollection();
    $contents = (string) file_get_contents($filePath);

    preg_match_all(
        '/{{#formatMessage *\|? *(?<id>[a-z0-9\-_]*)}}(?<defaultMessage>.*){{\/formatMessage}}/i',
        $contents,
        $matches,
    );

    foreach ($matches['id'] as $index => $id) {
        if (strlen(trim($id)) === 0) {
            $errors[] = new ParserError(
                sprintf('Missing "id" in "%s"', $matches[0][$index]),
                $filePath,
            );

            continue;
        }

        $defaultMessage = $matches['defaultMessage'][$index] ?? '';
        if (strlen(trim($defaultMessage)) === 0) {
            $errors[] = new ParserError(
                sprintf('Missing "defaultMessage" in "%s"', $matches[0][$index]),
                $filePath,
            );

            continue;
        }

        $descriptors[] = new Descriptor($id, $defaultMessage);
    }

    return $descriptors;
};
