<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementInterface;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralOrSelectOption;
use FormatPHP\Icu\MessageFormat\Parser\Type\SelectElement;
use FormatPHP\Test\TestCase;

class SelectElementTest extends TestCase
{
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $formatElement = $this->mockery(ElementInterface::class);

        $options = [
            'one' => new PluralOrSelectOption(new ElementCollection([$formatElement]), $location),
            'two' => new PluralOrSelectOption(new ElementCollection([$formatElement]), $location),
        ];

        $element = new SelectElement('select value', $options, $location);

        $this->assertEquals(ElementType::Select(), $element->type);
        $this->assertSame('select value', $element->value);
        $this->assertSame($options, $element->options);
        $this->assertSame($location, $element->location);
    }
}
