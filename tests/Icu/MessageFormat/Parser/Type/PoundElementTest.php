<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\PoundElement;
use FormatPHP\Test\TestCase;

class PoundElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $element = new PoundElement($location);

        $this->assertEquals(ElementType::Pound(), $element->type);
        $this->assertSame($location, $element->location);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $element = new PoundElement($location);
        $clone = clone $element;

        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($start, $clone->location->start);
        $this->assertNotSame($end, $clone->location->end);
    }
}
