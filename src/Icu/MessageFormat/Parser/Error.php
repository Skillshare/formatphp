<?php

/**
 * This file is part of skillshare/formatphp
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/Apache-2.0 Apache License, Version 2.0
 */

declare(strict_types=1);

namespace FormatPHP\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use ReflectionObject;
use Throwable;

use function array_flip;

/**
 * @psalm-type ErrorKind = Error::*
 */
class Error
{
    /**
     * An error that does not fit with any of the other constants on this class.
     *
     * If receiving this kind of error, check {@see getThrowable()} to see if
     * there is an associated exception.
     */
    public const OTHER = 0;

    /**
     * Argument is unclosed (e.g. `{0`)
     */
    public const EXPECT_ARGUMENT_CLOSING_BRACE = 1;

    /**
     * Argument is empty (e.g. `{}`).
     */
    public const EMPTY_ARGUMENT = 2;

    /**
     * Argument is malformed (e.g. `{foo!}``)
     */
    public const MALFORMED_ARGUMENT = 3;

    /**
     * Expect an argument type (e.g. `{foo,}`)
     */
    public const EXPECT_ARGUMENT_TYPE = 4;

    /**
     * Unsupported argument type (e.g. `{foo,foo}`)
     */
    public const INVALID_ARGUMENT_TYPE = 5;

    /**
     * Expect an argument style (e.g. `{foo, number, }`)
     */
    public const EXPECT_ARGUMENT_STYLE = 6;

    /**
     * The number skeleton is invalid.
     */
    public const INVALID_NUMBER_SKELETON = 7;

    /**
     * The date time skeleton is invalid.
     */
    public const INVALID_DATE_TIME_SKELETON = 8;

    /**
     * Expect a number skeleton following the `::` (e.g. `{foo, number, ::}`)
     */
    public const EXPECT_NUMBER_SKELETON = 9;

    /**
     * Expect a date time skeleton following the `::` (e.g. `{foo, date, ::}`)
     */
    public const EXPECT_DATE_TIME_SKELETON = 10;

    /**
     * Unmatched apostrophes in the argument style (e.g. `{foo, number, 'test`)
     */
    public const UNCLOSED_QUOTE_IN_ARGUMENT_STYLE = 11;

    /**
     * Missing select argument options (e.g. `{foo, select}`)
     */
    public const EXPECT_SELECT_ARGUMENT_OPTIONS = 12;

    /**
     * Expecting an offset value in `plural` or `selectordinal` argument
     * (e.g `{foo, plural, offset}`)
     */
    public const EXPECT_PLURAL_ARGUMENT_OFFSET_VALUE = 13;

    /**
     * Offset value in `plural` or `selectordinal` is invalid
     * (e.g. `{foo, plural, offset: x}`)
     */
    public const INVALID_PLURAL_ARGUMENT_OFFSET_VALUE = 14;

    /**
     * Expecting a selector in `select` argument (e.g `{foo, select}`)
     */
    public const EXPECT_SELECT_ARGUMENT_SELECTOR = 15;

    /**
     * Expecting a selector in `plural` or `selectordinal` argument
     * (e.g `{foo, plural}`)
     */
    public const EXPECT_PLURAL_ARGUMENT_SELECTOR = 16;

    /**
     * Expecting a message fragment after the `select` selector
     * (e.g. `{foo, select, apple}`)
     */
    public const EXPECT_SELECT_ARGUMENT_SELECTOR_FRAGMENT = 17;

    /**
     * Expecting a message fragment after the `plural` or `selectordinal`
     * selector (e.g. `{foo, plural, one}`)
     */
    public const EXPECT_PLURAL_ARGUMENT_SELECTOR_FRAGMENT = 18;

    /**
     * Selector in `plural` or `selectordinal` is malformed
     * (e.g. `{foo, plural, =x {#}}`)
     */
    public const INVALID_PLURAL_ARGUMENT_SELECTOR = 19;

    /**
     * Duplicate selectors in `plural` or `selectordinal` argument.
     * (e.g. {foo, plural, one {#} one {#}})
     */
    public const DUPLICATE_PLURAL_ARGUMENT_SELECTOR = 20;

    /**
     * Duplicate selectors in `select` argument.
     * (e.g. {foo, select, apple {apple} apple {apple}})
     */
    public const DUPLICATE_SELECT_ARGUMENT_SELECTOR = 21;

    /**
     * Plural or select argument option must have `other` clause.
     */
    public const MISSING_OTHER_CLAUSE = 22;

    /**
     * The tag is malformed. (e.g. `<bold!>foo</bold!>)
     */
    public const INVALID_TAG = 23;

    /**
     * The tag name is invalid. (e.g. `<123>foo</123>`)
     */
    public const INVALID_TAG_NAME = 25;

    /**
     * The closing tag does not match the opening tag.
     * (e.g. `<bold>foo</italic>`)
     */
    public const UNMATCHED_CLOSING_TAG = 26;

    /**
     * The opening tag has unmatched closing tag. (e.g. `<bold>foo`)
     */
    public const UNCLOSED_TAG = 27;

    /**
     * @var string[]
     */
    private static array $constants = [];

    /**
     * @var ErrorKind
     */
    public int $kind;

    public string $message;
    public Location $location;

    private ?Throwable $throwable;

    /**
     * @param ErrorKind $kind
     */
    public function __construct(
        int $kind,
        string $message,
        Location $location,
        ?Throwable $throwable = null
    ) {
        $this->kind = $kind;
        $this->message = $message;
        $this->location = $location;
        $this->throwable = $throwable;
    }

    /**
     * May return a Throwable instance if {@see $kind} is {@see OTHER}
     */
    public function getThrowable(): ?Throwable
    {
        return $this->throwable;
    }

    /**
     * Returns the name for the kind of error this represents
     */
    public function getErrorKindName(): string
    {
        if (self::$constants === []) {
            $reflection = new ReflectionObject($this);

            // @phpstan-ignore-next-line
            self::$constants = array_flip($reflection->getConstants());
        }

        return self::$constants[$this->kind] ?? '';
    }
}
