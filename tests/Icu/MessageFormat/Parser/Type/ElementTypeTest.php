<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser\Type;

use FormatPHP\Icu\MessageFormat\Parser\Type\ElementType;
use FormatPHP\Test\TestCase;

class ElementTypeTest extends TestCase
{
    public function testConstantValues(): void
    {
        $this->assertSame(0, ElementType::Literal()->getValue());
        $this->assertSame(1, ElementType::Argument()->getValue());
        $this->assertSame(2, ElementType::Number()->getValue());
        $this->assertSame(3, ElementType::Date()->getValue());
        $this->assertSame(4, ElementType::Time()->getValue());
        $this->assertSame(5, ElementType::Select()->getValue());
        $this->assertSame(6, ElementType::Plural()->getValue());
        $this->assertSame(7, ElementType::Pound()->getValue());
        $this->assertSame(8, ElementType::Tag()->getValue());
    }
}
