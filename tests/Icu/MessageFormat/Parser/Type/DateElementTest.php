<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\DateElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeFormatOptions;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;

class DateElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $parsedOptions = new DateTimeFormatOptions();
        $skeleton = new DateTimeSkeleton('date pattern', $location, $parsedOptions);

        $element = new DateElement('date element value', $location, $skeleton);

        $this->assertEquals(ElementType::Date(), $element->type);
        $this->assertSame('date element value', $element->value);
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
        $skeleton = new DateTimeSkeleton('date pattern', $location, $parsedOptions);

        $element = new DateElement('date element value', $location, $skeleton);
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
