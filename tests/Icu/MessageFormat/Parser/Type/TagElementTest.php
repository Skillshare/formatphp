<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\TagElement;
use FormatPHP\Test\TestCase;

class TagElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);
        $children = new ElementCollection([$formatElement, $formatElement]);

        $element = new TagElement('tag name', $children, $location);

        $this->assertEquals(ElementType::Tag(), $element->type);
        $this->assertSame('tag name', $element->value);
        $this->assertSame($location, $element->location);
        $this->assertSame($children, $element->children);
        $this->assertCount(2, $element->children);
        $this->assertSame($formatElement, $element->children[0]);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);
        $children = new ElementCollection([$formatElement, $formatElement]);

        $element = new TagElement('tag name', $children, $location);
        $clone = clone $element;

        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($children, $clone->children);
        $this->assertCount(2, $element->children);
        $this->assertNotSame($formatElement, $clone->children[0]);
    }
}
