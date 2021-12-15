<?php

declare(strict_types=1);

namespace FormatPHP\Test\Format;

use FormatPHP\Format\Format;
use FormatPHP\Test\TestCase;

class FormatTest extends TestCase
{
    public function testConstantValues(): void
    {
        $this->assertSame('formatphp', Format::FORMATPHP);
        $this->assertSame('simple', Format::SIMPLE);
        $this->assertSame('smartling', Format::SMARTLING);
    }
}
