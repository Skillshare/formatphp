<?php

declare(strict_types=1);

$foo = new class {
    public function doSomething(): void
    {
    }
};

return [$foo, 'doSomething'];
