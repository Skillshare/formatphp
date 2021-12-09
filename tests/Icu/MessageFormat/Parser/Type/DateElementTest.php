<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\DateElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;

class DateElementTest extends TestCase
{
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $skeleton = new DateTimeSkeleton('date pattern', $location);

        $element = new DateElement('date element value', $location, $skeleton);

        $this->assertEquals(ElementType::Date(), $element->type);
        $this->assertSame('date element value', $element->value);
        $this->assertSame($location, $element->location);
        $this->assertSame($skeleton, $element->style);
    }
}
