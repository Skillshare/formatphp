<?php

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Result;
use FormatPHP\Icu\MessageFormat\Parser\Type\ElementCollection;
use FormatPHP\Icu\MessageFormat\Parser\Type\LiteralElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use FormatPHP\Icu\MessageFormat\Parser\Type\NumberElement;
use FormatPHP\Icu\MessageFormat\Parser\Type\PluralElement;
use FormatPHP\Test\TestCase;

class ResultTest extends TestCase
{
    public function testConstructor(): void
    {
        $start = new LocationDetails(0, 1, 1);
        $end = new LocationDetails(2, 4, 6);
        $location = new Location($start, $end);

        $error = new Error(Error::EMPTY_ARGUMENT, 'a test message', $location);

        $elements = new ElementCollection([
            new LiteralElement('a value', $location),
            new NumberElement('number value', $location),
            new PluralElement('plural value', [], null, null, $location),
        ]);

        $result = new Result($elements, $error);

        $this->assertSame($elements, $result->val);
        $this->assertSame($error, $result->err);
    }
}
