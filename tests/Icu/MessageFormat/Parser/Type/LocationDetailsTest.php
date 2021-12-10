<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;

class LocationDetailsTest extends TestCase
{
    public function testConstructor(): void
    {
        $details = new LocationDetails(7, 4, 5);

        $this->assertSame(7, $details->offset);
        $this->assertSame(4, $details->line);
        $this->assertSame(5, $details->column);
    }
}
