<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\NumberSkeletonToken;
use FormatPHP\Test\TestCase;

class NumberSkeletonTokenTest extends TestCase
{
    public function testConstructor(): void
    {
        $token = new NumberSkeletonToken('a stem', ['option 1', 'option 2', 'option 2']);

        $this->assertSame('a stem', $token->stem);
        $this->assertSame(['option 1', 'option 2', 'option 2'], $token->options);
    }
}
