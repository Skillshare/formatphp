<?php

$bar = 'default ';
$description = 'description';

$internationalization->formatMessage([
    'id' => 'message.with.variable.concat',
    // Injecting a variable here to break things.
    'defaultMessage' => 'This is a ' . $bar . 'message',
]);

$internationalization->formatMessage([
    'id' => 'message.with.variable.interpolated',
    // Injecting a variable here to break things.
    'defaultMessage' => 'This is a default message',
    'description' => "This is a $description with an interpolated variable.",
]);
