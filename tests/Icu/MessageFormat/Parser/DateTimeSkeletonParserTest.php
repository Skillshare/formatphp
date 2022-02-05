<?php

/**
 * This file is part of skillshare/formatphp
 *
 * skillshare/formatphp is open source software: you can distribute
 * it and/or modify it under the terms of the MIT License
 * (the "License"). You may not use this file except in
 * compliance with the License.
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright Copyright (c) Skillshare, Inc. <https://www.skillshare.com>
 * @license https://opensource.org/licenses/MIT MIT License
 */

declare(strict_types=1);

namespace FormatPHP\Test\Icu\MessageFormat\Parser;

use FormatPHP\Icu\MessageFormat\Parser\DateTimeSkeletonParser;
use FormatPHP\Test\TestCase;

use function json_encode;

use const JSON_INVALID_UTF8_IGNORE;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;

class DateTimeSkeletonParserTest extends TestCase
{
    private const JSON_ENCODE_FLAGS = JSON_INVALID_UTF8_IGNORE
        | JSON_PRETTY_PRINT
        | JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_THROW_ON_ERROR;

    /**
     * @dataProvider dateTimeSkeletonProvider
     */
    public function testParseDateTimeSkeleton(string $skeleton): void
    {
        $parser = new DateTimeSkeletonParser();
        $parsed = (string) json_encode($parser->parse($skeleton), self::JSON_ENCODE_FLAGS);

        $this->assertMatchesJsonSnapshot($parsed);
    }

    /**
     * @return array<string[]>
     */
    public function dateTimeSkeletonProvider(): array
    {
        return [
            ["yyyy.MM.dd G 'at' HH:mm:ss zzzz"],
            ["EEE, MMM d, ''yy"],
            ['h:mm a'],
            [''],
            ['eeeee'],
            ['cccccc'],
            ['KK'],
            ['k'],
            ['hb'],
            ['hB'],
            ['hbbbbb'],
        ];
    }
}
