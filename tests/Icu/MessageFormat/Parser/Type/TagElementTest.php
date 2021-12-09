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
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);
        $children = new ElementCollection([clone $formatElement, clone $formatElement]);

        $element = new TagElement('tag name', $children, $location);

        $this->assertEquals(ElementType::Tag(), $element->type);
        $this->assertSame('tag name', $element->value);
        $this->assertSame($children, $element->children);
        $this->assertSame($location, $element->location);
    }
}
