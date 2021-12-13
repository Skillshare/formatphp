<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;

class LocationTest extends TestCase
{
    public function testConstructor(): void
    {
        $details1 = new LocationDetails(7, 4, 5);
        $details2 = new LocationDetails(12, 4, 10);
        $location = new Location($details1, $details2);

        $this->assertSame($details1, $location->start);
        $this->assertSame($details2, $location->end);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $clone = clone $location;

        $this->assertNotSame($start, $clone->start);
        $this->assertNotSame($end, $clone->end);
    }
}
