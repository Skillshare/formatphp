<?php

declare(strict_types=1);

namespace FormatPHP\Test;

use Ramsey\Dev\Tools\TestCase as BaseTestCase;
use Spatie\Snapshots\MatchesSnapshots;

/**
 * A base test case for common test functionality
 */
class TestCase extends BaseTestCase
{
    use MatchesSnapshots;
}
