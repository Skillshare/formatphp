<?php

use FormatPHP\FormatPHP;

function translate(array $descriptor): string {
    $intl = new FormatPHP('en', []);

    return $intl->formatMessage($descriptor);
}

/**
 * @intl name:value we didn't pass a pragma in the test, so this shouldn't show up
 */
$translation = translate([
    'defaultMessage' => 'Hello!',
]);

echo $translation . "\n";
