<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Util;

use FormatPHP\Icu\MessageFormat\Parser\Util\IsPotentialElementNameChar;
use FormatPHP\Test\TestCase;

use function range;

class IsPotentialElementNameCharTest extends TestCase
{
    /**
     * Loop over 1,048,575 code points and assert that
     * exactly 971,632 code points match IsPotentialElementNameChar
     */
    public function testMatches(): void
    {
        $isPotentialElementNameChar = new IsPotentialElementNameChar();

        $matches = 0;
        foreach (range(0x0000, 0xfffff) as $codepoint) {
            if ($isPotentialElementNameChar->matches($codepoint)) {
                $matches++;
            }
        }

        $this->assertSame(971_632, $matches);
    }
}
