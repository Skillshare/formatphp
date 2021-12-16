<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidMessageException;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidSkeletonOption;
use FormatPHP\Icu\MessageFormat\Validator;
use FormatPHP\Test\TestCase;

class ValidatorTest extends TestCase
{
    public function testValidate(): void
    {
        $validator = new Validator();

        $this->assertTrue($validator->validate('A simple {test} message'));
    }

    public function testValidateThrowsExceptionOnInvalidMessage(): void
    {
        $validator = new Validator();
        $exception = null;

        try {
            $validator->validate('This is an <invalid>message');
        } catch (InvalidMessageException $exception) {
        }

        $this->assertInstanceOf(InvalidMessageException::class, $exception);
        $this->assertSame(Error::UNCLOSED_TAG, $exception->getParserError()->kind);
        $this->assertNull($exception->getParserError()->getThrowable());
    }

    public function testValidateThrowsExceptionOnParserError(): void
    {
        $validator = new Validator();
        $exception = null;

        try {
            $validator->validate('{0, date, ::eee}');
        } catch (InvalidMessageException $exception) {
        }

        $this->assertInstanceOf(InvalidMessageException::class, $exception);
        $this->assertSame(Error::OTHER, $exception->getParserError()->kind);
        $this->assertInstanceOf(InvalidSkeletonOption::class, $exception->getPrevious());
        $this->assertSame(
            '"e..eee" (weekday) patterns are not supported',
            $exception->getPrevious()->getMessage(),
        );
    }
}
