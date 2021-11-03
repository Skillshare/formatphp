<?php

function translate(string $foo): string {
    return $foo;
}

function formatMessage(string $foo): string {
    return $foo;
}

function translate2(): void {
    // do nothing
}

function translate3(array $something): void {
    // do nothing
}

function translate4(array $descriptor): void {
    // do nothing
}

$foo = function (string $foo): string {
    return $foo;
};

echo $foo('bar');

$translation = translate('foo');

translate2();
translate3(['foo' => '123', null, 'bar']);

translate4([
    'id' => 'aTestId',
    'defaultMessage' => 'This supposed to look like a formatMessage call, but it is not',
    'description' => 'Sample text',
]);

echo $translation . "\n" . formatMessage('bar') . "\n";
