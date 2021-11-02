<?php

declare(strict_types=1);

ob_start();

$foo = "This script doesn't return anything.";
echo $foo;

ob_end_clean();
