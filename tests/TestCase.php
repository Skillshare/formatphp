<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * A base test case for common test functionality
 */
abstract class TestCase extends PHPUnitTestCase
{
    use MatchesSnapshots;
    use MockeryPHPUnitIntegration;

    /**
     * Configures and returns a mock object
     *
     * @param class-string<T> $class
     * @param mixed ...$arguments
     *
     * @return T & MockInterface
     *
     * @template T
     *
     * phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    public function mockery(string $class, ...$arguments)
    {
        /** @var T & MockInterface $mock */
        $mock = Mockery::mock($class, ...$arguments);

        return $mock;
    }
}
