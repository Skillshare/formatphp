<?php

// This message should fail validation.
$internationalization->formatMessage([
    'id' => 'aTestId',
    'defaultMessage' => 'This is a default <a href="#foo">message</a>',
    'description' => 'A simple description of a fixture for testing purposes.',
]);
