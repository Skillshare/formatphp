<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberFormatOptions;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonToken;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonTokenCollection;
use FormatPHP\Test\TestCase;

class NumberElementTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $options = new NumberFormatOptions();
        $token = new NumberSkeletonToken('token1');
        $tokens = new NumberSkeletonTokenCollection([$token]);
        $skeleton = new NumberSkeleton($tokens, $location, $options);

        $element = new NumberElement('number value', $location, $skeleton);

        $this->assertEquals(ElementType::Number(), $element->type);
        $this->assertSame($location, $element->location);
        $this->assertSame($skeleton, $element->style);
        $this->assertSame($skeleton, $element->style);
        $this->assertSame($options, $element->style->parsedOptions);
        $this->assertCount(1, $element->style->tokens);
        $this->assertSame($token, $element->style->tokens[0]);
    }

    public function testDeepClone(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $options = new NumberFormatOptions();
        $token = new NumberSkeletonToken('token1');
        $tokens = new NumberSkeletonTokenCollection([$token]);
        $skeleton = new NumberSkeleton($tokens, $location, $options);

        $element = new NumberElement('number value', $location, $skeleton);
        $clone = clone $element;

        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($skeleton, $clone->style);
        $this->assertNotSame($skeleton, $clone->style);
        $this->assertInstanceOf(NumberSkeleton::class, $clone->style);
        $this->assertNotSame($options, $clone->style->parsedOptions);
        $this->assertCount(1, $clone->style->tokens);
        $this->assertNotSame($token, $clone->style->tokens[0]);
    }
}
