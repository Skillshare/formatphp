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
        $elements = new ElementCollection([
            $this->mockery(ElementInterface::class),
            $this->mockery(ElementInterface::class),
            $this->mockery(ElementInterface::class),
        ]);

        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $option = new PluralOrSelectOption($elements, $location);

        $this->assertSame($elements, $option->value);
        $this->assertSame($location, $option->location);
    }
}
