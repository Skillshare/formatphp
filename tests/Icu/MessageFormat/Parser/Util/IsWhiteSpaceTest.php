<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Util;

use FormatPHP\Icu\MessageFormat\Parser\Util\IsWhiteSpace;
use FormatPHP\Test\TestCase;

use function range;

class IsWhiteSpaceTest extends TestCase
{
    /**
     * Loop over 65,535 code points and assert that
     * exactly 27 code points match IsWhiteSpace
     */
    public function testMatches(): void
    {
        $isWhiteSpace = new IsWhiteSpace();

        $matches = 0;
        foreach (range(0x0000, 0xffff) as $codepoint) {
            if ($isWhiteSpace->matches($codepoint)) {
                $matches++;
            }
        }

        $this->assertSame(27, $matches);
    }
}
