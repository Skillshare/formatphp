<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Util;

use FormatPHP\Icu\MessageFormat\Parser\Util\IsPatternSyntax;
use FormatPHP\Test\TestCase;

use function range;

class IsPatternSyntaxTest extends TestCase
{
    /**
     * Loop over 65,535 code points and assert that
     * exactly 2760 code points match IsPatternSyntax
     */
    public function testMatches(): void
    {
        $isPatternSyntax = new IsPatternSyntax();

        $matches = 0;
        foreach (range(0x0000, 0xffff) as $codepoint) {
            if ($isPatternSyntax->matches($codepoint)) {
                $matches++;
            }
        }

        $this->assertSame(2760, $matches);
    }
}
