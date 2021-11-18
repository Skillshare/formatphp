<?php

$internationalization->formatMessage([
    'id' => 'aTestId',
    // We're using concatenated string literals on purpose, to test this functionality.
    'defaultMessage' => 'This is a ' . 'default ' . 'message',
    'description' => 'A simple description '
        . 'of a fixture '
        . 'for testing purposes.',
]);
