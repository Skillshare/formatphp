<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberFormatOptions;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeleton;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonToken;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonTokenCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\SkeletonType;
use FormatPHP\Test\TestCase;

class NumberSkeletonTest extends TestCase
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

        $this->assertEquals(SkeletonType::Number(), $skeleton->type);
        $this->assertSame($tokens, $skeleton->tokens);
        $this->assertSame($location, $skeleton->location);
        $this->assertSame($options, $skeleton->parsedOptions);
        $this->assertCount(1, $skeleton->tokens);
        $this->assertSame($token, $skeleton->tokens[0]);
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

        $clone = clone $skeleton;

        $this->assertNotSame($tokens, $clone->tokens);
        $this->assertNotSame($location, $clone->location);
        $this->assertNotSame($options, $clone->parsedOptions);
        $this->assertCount(1, $clone->tokens);
        $this->assertNotSame($token, $clone->tokens[0]);
    }
}
