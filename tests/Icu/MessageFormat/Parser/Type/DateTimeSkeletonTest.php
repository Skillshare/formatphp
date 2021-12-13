<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeFormatOptions;
use FormatPHP\Icu\MessageFormat\Parser\Type\DateTimeSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\SkeletonType;
use FormatPHP\Test\TestCase;

class DateTimeSkeletonTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $parsedOptions = new DateTimeFormatOptions();

        $skeleton = new DateTimeSkeleton('date pattern', $location, $parsedOptions);

        $this->assertEquals(SkeletonType::DateTime(), $skeleton->type);
        $this->assertSame('date pattern', $skeleton->pattern);
        $this->assertSame($location, $skeleton->location);
        $this->assertSame($parsedOptions, $skeleton->parsedOptions);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $parsedOptions = new DateTimeFormatOptions();

        $skeleton = new DateTimeSkeleton('date pattern', $location, $parsedOptions);
        $clone = clone $skeleton;

        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($start, $clone->location->start);
        $this->assertNotSame($end, $clone->location->end);
        $this->assertNotSame($parsedOptions, $clone->parsedOptions);
    }
}
