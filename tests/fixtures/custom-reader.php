<?php

declare(strict_types=1);

use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\MessageCollection;

return fn (array $data): MessageCollection => (new FormatPHPReader())($data);
