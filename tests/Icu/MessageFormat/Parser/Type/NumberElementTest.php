<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonToken;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonTokenCollection;
use FormatPHP\Test\TestCase;

class NumberElementTest extends TestCase
{
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $tokens = new NumberSkeletonTokenCollection([new NumberSkeletonToken('token1')]);
        $skeleton = new NumberSkeleton($tokens, $location);

        $element = new NumberElement('number value', $location, $skeleton);

        $this->assertEquals(ElementType::Number(), $element->type);
        $this->assertSame($location, $element->location);
        $this->assertSame($skeleton, $element->style);
    }
}
