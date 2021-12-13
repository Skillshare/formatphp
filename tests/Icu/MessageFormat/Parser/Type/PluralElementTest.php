<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralOrSelectOption;
use FormatPHP\Test\TestCase;

class PluralElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);

        $option = new PluralOrSelectOption(new ElementCollection([$formatElement]), $location);

        $options = [
            'one' => $option,
            'two' => $option,
        ];

        $element = new PluralElement('plural value', $options, 56, 'cardinal', $location);

        $this->assertEquals(ElementType::Plural(), $element->type);
        $this->assertSame('plural value', $element->value);
        $this->assertSame($options, $element->options);
        $this->assertSame(56, $element->offset);
        $this->assertSame('cardinal', $element->pluralType);
        $this->assertSame($location, $element->location);
        $this->assertArrayHasKey('one', $element->options);
        $this->assertSame($option, $element->options['one']);
        $this->assertSame($formatElement, $element->options['one']->value[0]);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);

        $option = new PluralOrSelectOption(new ElementCollection([$formatElement]), $location);

        $options = [
            'one' => $option,
            'two' => $option,
        ];

        $element = new PluralElement('plural value', $options, 56, 'cardinal', $location);
        $clone = clone $element;

        $this->assertNotSame($options, $clone->options);
        $this->assertNotSame($location, $clone->location);
        $this->assertArrayHasKey('one', $clone->options);
        $this->assertNotSame($option, $clone->options['one']);
        $this->assertNotSame($formatElement, $clone->options['one']->value[0]);
    }
}
