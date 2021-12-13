<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeFormatOptions;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\TimeElement;
use FormatPHP\Test\TestCase;

class TimeElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $parsedOptions = new DateTimeFormatOptions();
        $skeleton = new DateTimeSkeleton('time pattern', $location, $parsedOptions);

        $element = new TimeElement('time element', $location, $skeleton);

        $this->assertEquals(ElementType::Time(), $element->type);
        $this->assertSame('time element', $element->value);
        $this->assertSame($location, $element->location);
        $this->assertSame($start, $element->location->start);
        $this->assertSame($end, $element->location->end);
        $this->assertSame($skeleton, $element->style);
        $this->assertSame($parsedOptions, $element->style->parsedOptions);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $parsedOptions = new DateTimeFormatOptions();
        $skeleton = new DateTimeSkeleton('time pattern', $location, $parsedOptions);

        $element = new TimeElement('time element', $location, $skeleton);
        $clone = clone $element;

        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($start, $clone->location->start);
        $this->assertNotSame($end, $clone->location->end);
        $this->assertNotSame($skeleton, $clone->style);
        $this->assertNotNull($clone->style);
        $this->assertInstanceOf(DateTimeSkeleton::class, $clone->style);
        $this->assertNotSame($location, $clone->style->location);
        $this->assertNotSame($parsedOptions, $clone->style->parsedOptions);
    }
}
