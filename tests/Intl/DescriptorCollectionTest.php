<?php

declare(strict_types=1);

namespace FormatPHP\Test\Intl;

use FormatPHP\Intl\Descriptor;
use FormatPHP\Intl\DescriptorCollection;
use FormatPHP\Test\TestCase;

class DescriptorCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new DescriptorCollection();

        $this->assertSame(Descriptor::class, $collection->getType());
    }
}
