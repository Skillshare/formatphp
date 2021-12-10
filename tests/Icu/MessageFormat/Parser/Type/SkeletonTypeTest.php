<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\SkeletonType;
use FormatPHP\Test\TestCase;

class SkeletonTypeTest extends TestCase
{
    public function testConstantValues(): void
    {
        $this->assertSame(0, SkeletonType::Number()->getValue());
        $this->assertSame(1, SkeletonType::DateTime()->getValue());
    }
}
