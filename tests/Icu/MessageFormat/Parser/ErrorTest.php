<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;
use RuntimeException;

/**
 * @psalm-import-type ErrorKind from Error
 */
class ErrorTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $error = new Error(Error::EMPTY_ARGUMENT, 'a test message', $location);

        $this->assertSame(Error::EMPTY_ARGUMENT, $error->kind);
        $this->assertSame('a test message', $error->message);
        $this->assertSame($location, $error->location);
        $this->assertNull($error->getThrowable());
    }

    public function testConstantValues(): void
    {
        $this->assertSame(0, Error::OTHER);
        $this->assertSame(1, Error::EXPECT_ARGUMENT_CLOSING_BRACE);
        $this->assertSame(2, Error::EMPTY_ARGUMENT);
        $this->assertSame(3, Error::MALFORMED_ARGUMENT);
        $this->assertSame(4, Error::EXPECT_ARGUMENT_TYPE);
        $this->assertSame(5, Error::INVALID_ARGUMENT_TYPE);
        $this->assertSame(6, Error::EXPECT_ARGUMENT_STYLE);
        $this->assertSame(7, Error::INVALID_NUMBER_SKELETON);
        $this->assertSame(8, Error::INVALID_DATE_TIME_SKELETON);
        $this->assertSame(9, Error::EXPECT_NUMBER_SKELETON);
        $this->assertSame(10, Error::EXPECT_DATE_TIME_SKELETON);
        $this->assertSame(11, Error::UNCLOSED_QUOTE_IN_ARGUMENT_STYLE);
        $this->assertSame(12, Error::EXPECT_SELECT_ARGUMENT_OPTIONS);
        $this->assertSame(13, Error::EXPECT_PLURAL_ARGUMENT_OFFSET_VALUE);
        $this->assertSame(14, Error::INVALID_PLURAL_ARGUMENT_OFFSET_VALUE);
        $this->assertSame(15, Error::EXPECT_SELECT_ARGUMENT_SELECTOR);
        $this->assertSame(16, Error::EXPECT_PLURAL_ARGUMENT_SELECTOR);
        $this->assertSame(17, Error::EXPECT_SELECT_ARGUMENT_SELECTOR_FRAGMENT);
        $this->assertSame(18, Error::EXPECT_PLURAL_ARGUMENT_SELECTOR_FRAGMENT);
        $this->assertSame(19, Error::INVALID_PLURAL_ARGUMENT_SELECTOR);
        $this->assertSame(20, Error::DUPLICATE_PLURAL_ARGUMENT_SELECTOR);
        $this->assertSame(21, Error::DUPLICATE_SELECT_ARGUMENT_SELECTOR);
        $this->assertSame(22, Error::MISSING_OTHER_CLAUSE);
        $this->assertSame(23, Error::INVALID_TAG);
        $this->assertSame(25, Error::INVALID_TAG_NAME);
        $this->assertSame(26, Error::UNMATCHED_CLOSING_TAG);
        $this->assertSame(27, Error::UNCLOSED_TAG);
    }

    public function testConstructorAcceptsThrowable(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);
        $exception = new RuntimeException();

        $error = new Error(Error::EMPTY_ARGUMENT, 'a test message', $location, $exception);

        $this->assertSame($exception, $error->getThrowable());
    }

    /**
     * @param ErrorKind $kind
     *
     * @dataProvider provideErrorKind
     */
    public function testGetErrorKindName(int $kind, string $expected): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $error = new Error($kind, 'A test message', $location);

        $this->assertSame($expected, $error->getErrorKindName());
    }

    /**
     * @return array<array{kind: ErrorKind, expected: string}>
     */
    public function provideErrorKind(): array
    {
        return [
            ['kind' => 0, 'expected' => 'OTHER'],
            ['kind' => 1, 'expected' => 'EXPECT_ARGUMENT_CLOSING_BRACE'],
            ['kind' => 2, 'expected' => 'EMPTY_ARGUMENT'],
            ['kind' => 3, 'expected' => 'MALFORMED_ARGUMENT'],
            ['kind' => 4, 'expected' => 'EXPECT_ARGUMENT_TYPE'],
            ['kind' => 5, 'expected' => 'INVALID_ARGUMENT_TYPE'],
            ['kind' => 6, 'expected' => 'EXPECT_ARGUMENT_STYLE'],
            ['kind' => 7, 'expected' => 'INVALID_NUMBER_SKELETON'],
            ['kind' => 8, 'expected' => 'INVALID_DATE_TIME_SKELETON'],
            ['kind' => 9, 'expected' => 'EXPECT_NUMBER_SKELETON'],
            ['kind' => 10, 'expected' => 'EXPECT_DATE_TIME_SKELETON'],
            ['kind' => 11, 'expected' => 'UNCLOSED_QUOTE_IN_ARGUMENT_STYLE'],
            ['kind' => 12, 'expected' => 'EXPECT_SELECT_ARGUMENT_OPTIONS'],
            ['kind' => 13, 'expected' => 'EXPECT_PLURAL_ARGUMENT_OFFSET_VALUE'],
            ['kind' => 14, 'expected' => 'INVALID_PLURAL_ARGUMENT_OFFSET_VALUE'],
            ['kind' => 15, 'expected' => 'EXPECT_SELECT_ARGUMENT_SELECTOR'],
            ['kind' => 16, 'expected' => 'EXPECT_PLURAL_ARGUMENT_SELECTOR'],
            ['kind' => 17, 'expected' => 'EXPECT_SELECT_ARGUMENT_SELECTOR_FRAGMENT'],
            ['kind' => 18, 'expected' => 'EXPECT_PLURAL_ARGUMENT_SELECTOR_FRAGMENT'],
            ['kind' => 19, 'expected' => 'INVALID_PLURAL_ARGUMENT_SELECTOR'],
            ['kind' => 20, 'expected' => 'DUPLICATE_PLURAL_ARGUMENT_SELECTOR'],
            ['kind' => 21, 'expected' => 'DUPLICATE_SELECT_ARGUMENT_SELECTOR'],
            ['kind' => 22, 'expected' => 'MISSING_OTHER_CLAUSE'],
            ['kind' => 23, 'expected' => 'INVALID_TAG'],
            ['kind' => 25, 'expected' => 'INVALID_TAG_NAME'],
            ['kind' => 26, 'expected' => 'UNMATCHED_CLOSING_TAG'],
            ['kind' => 27, 'expected' => 'UNCLOSED_TAG'],
        ];
    }
}
