<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\TimeElement;
use FormatPHP\Test\TestCase;

class TimeElementTest extends TestCase
{
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $skeleton = new DateTimeSkeleton('time pattern', $location);

        $element = new TimeElement('time element', $location, $skeleton);

        $this->assertEquals(ElementType::Time(), $element->type);
        $this->assertSame('time element', $element->value);
        $this->assertSame($location, $element->location);
        $this->assertSame($skeleton, $element->style);
    }
}
