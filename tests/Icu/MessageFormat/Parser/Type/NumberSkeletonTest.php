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
    public function testType(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $options = new NumberFormatOptions();

        $tokens = new NumberSkeletonTokenCollection([new NumberSkeletonToken('token1')]);
        $skeleton = new NumberSkeleton($tokens, $location, $options);

        $this->assertEquals(SkeletonType::Number(), $skeleton->type);
        $this->assertSame($tokens, $skeleton->tokens);
        $this->assertSame($location, $skeleton->location);
        $this->assertSame($options, $skeleton->parsedOptions);
    }
}
