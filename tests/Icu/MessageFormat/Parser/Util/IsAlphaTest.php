<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Util;

use FormatPHP\Icu\MessageFormat\Parser\Util\IsAlpha;
use FormatPHP\Test\TestCase;

use function range;

class IsAlphaTest extends TestCase
{
    /**
     * Loop over 65,535 code points and assert that
     * exactly 52 code points match IsAlpha
     */
    public function testMatches(): void
    {
        $isAlpha = new IsAlpha();

        $matches = 0;
        foreach (range(0x0000, 0xffff) as $codepoint) {
            if ($isAlpha->matches($codepoint)) {
                $matches++;
            }
        }

        $this->assertSame(52, $matches);
    }
}
