<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Test\TestCase;

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
    }

    public function testConstantValues(): void
    {
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
}
