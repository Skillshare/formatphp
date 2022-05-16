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

namespace FormatPHP\Icu\MessageFormat;

use FormatPHP\Icu\MessageFormat\Parser\Error;
use FormatPHP\Icu\MessageFormat\Parser\Exception\InvalidMessageException;
use FormatPHP\Icu\MessageFormat\Parser\Options;
use FormatPHP\Icu\MessageFormat\Parser\Result;
use FormatPHP\Icu\MessageFormat\Parser\Type\Location;
use FormatPHP\Icu\MessageFormat\Parser\Type\LocationDetails;
use Throwable;

/**
 * A validator to use with the ICU message format syntax
 */
class Validator
{
    /**
     * Returns true if the message is valid ICU message syntax
     *
     * @throws InvalidMessageException
     */
    public function validate(string $message): bool
    {
        $throwable = null;

        $options = new Options();
        $options->shouldParseSkeletons = true;

        try {
            $parser = new Parser($message, $options);
            $result = $parser->parse();
        } catch (Throwable $throwable) {
            // Convert exceptions to errors.
            $position = new LocationDetails(-1, $throwable->getLine(), -1);
            $result = new Result(
                null,
                new Error(Error::OTHER, $message, new Location($position, $position), $throwable),
            );
        }

        if ($result->err !== null) {
            throw new InvalidMessageException($result->err, $throwable);
        }

        return true;
    }
}
