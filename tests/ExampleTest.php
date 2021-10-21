<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use FormatPHP\Example;
use Mockery\MockInterface;

class ExampleTest extends TestCase
{
    public function testGreet(): void
    {
        /** @var Example & MockInterface $example */
        $example = $this->mockery(Example::class);
        $example->shouldReceive('greet')->passthru();

        $this->assertSame('Hello, Friends!', $example->greet('Friends'));
    }
}
