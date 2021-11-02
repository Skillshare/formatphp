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

$foo = function (string $foo): string {
    return $foo;
};

echo $foo('bar');

$translation = translate('foo');

translate2();
translate3(['foo' => '123', null, 'bar']);

echo $translation . "\n" . formatMessage('bar') . "\n";
