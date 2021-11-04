<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\DescriptorCollection;
use FormatPHP\DescriptorInterface;

class DescriptorCollectionTest extends TestCase
{
    public function testGetType(): void
    {
        $collection = new DescriptorCollection();

        $this->assertSame(DescriptorInterface::class, $collection->getType());
    }
}
