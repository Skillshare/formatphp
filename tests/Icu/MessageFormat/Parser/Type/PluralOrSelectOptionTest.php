<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralOrSelectOption;
use FormatPHP\Test\TestCase;

class PluralOrSelectOptionTest extends TestCase
{
    public function testConstructor(): void
    {
        $element = $this->mockery(ElementInterface::class);
        $elements = new ElementCollection([$element, $element]);

        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $option = new PluralOrSelectOption($elements, $location);

        $this->assertSame($elements, $option->value);
        $this->assertSame($location, $option->location);
        $this->assertSame($element, $option->value[0]);
    }

    public function testDeepClone(): void
    {
        $element = $this->mockery(ElementInterface::class);
        $elements = new ElementCollection([$element, $element]);

        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $option = new PluralOrSelectOption($elements, $location);
        $clone = clone $option;

        $this->assertNotSame($elements, $clone->value);
        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($element, $clone->value[0]);
    }
}
