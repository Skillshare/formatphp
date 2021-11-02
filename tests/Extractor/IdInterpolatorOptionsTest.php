<?php

declare(strict_types=1);

namespace FormatPHP\Test\Extractor;

use FormatPHP\Extractor\IdInterpolatorOptions;
use FormatPHP\Test\TestCase;

class IdInterpolatorOptionsTest extends TestCase
{
    public function testDefaultInstantiation(): void
    {
        $options = new IdInterpolatorOptions();

        $this->assertSame('sha512', $options->hashingAlgorithm);
        $this->assertSame('base64', $options->encodingAlgorithm);
        $this->assertSame(6, $options->length);
    }

    public function testConstructorArgs(): void
    {
        $options = new IdInterpolatorOptions('md5', 'base64url', 10);

        $this->assertSame('md5', $options->hashingAlgorithm);
        $this->assertSame('base64url', $options->encodingAlgorithm);
        $this->assertSame(10, $options->length);
    }
}
