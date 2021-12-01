<?php

$intl->formatMessage([
    'defaultMessage' => 'This is a default message',
    'description' => 'A simple description of a fixture for testing purposes.',
]);

// Duplicated on purpose without a description to test that the generated ID is different.
$intl->formatMessage([
    'defaultMessage' => 'This is a default message',
]);

?>

<div>
    <p><?php echo $intl->formatMessage(['defaultMessage' => 'Welcome!']); ?></p>
</div>

<!-- This is duplicated on purpose to test adding the same ID to source code. -->
<div>
    <p><?php echo $intl->formatMessage(['defaultMessage' => 'Welcome!']); ?></p>
</div>
