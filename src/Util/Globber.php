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

namespace FormatPHP\Util;

use Generator;
use Webmozart\Glob\Glob;

use function strpos;

/**
 * Find files using glob patterns
 */
class Globber
{
    private FileSystemHelper $fileUtility;

    public function __construct(FileSystemHelper $fileUtility)
    {
        $this->fileUtility = $fileUtility;
    }

    /**
     * Returns files matching the provided glob patterns, optionally
     * filtered by the "ignore" glob patterns
     *
     * @param string[] $globs
     * @param string[] $ignoreGlobs
     *
     * @return Generator<string>
     */
    public function find(array $globs, array $ignoreGlobs): Generator
    {
        foreach ($globs as $glob) {
            foreach ($this->glob($this->formatGlob($glob)) as $path) {
                if ($this->shouldIgnore($path, $ignoreGlobs)) {
                    continue;
                }

                if ($this->fileUtility->isDirectory($path)) {
                    continue;
                }

                yield $path;
            }
        }
    }

    /**
     * @return string[]
     */
    public function glob(string $glob): array
    {
        return Glob::glob($glob);
    }

    /**
     * @param string[] $ignores
     */
    private function shouldIgnore(string $path, array $ignores): bool
    {
        foreach ($ignores as $ignore) {
            if (Glob::match($path, $this->formatGlob($ignore))) {
                return true;
            }
        }

        return false;
    }

    private function formatGlob(string $glob): string
    {
        // If the path doesn't start from the root, prepend the current
        // working directory to it.
        if (strpos($glob, '/') !== 0) {
            $glob = $this->fileUtility->getCurrentWorkingDirectory() . $glob;
        }

        // If the path is a directory, append `/**/*` to recursively
        // search the directory.
        if ($this->fileUtility->isDirectory($glob)) {
            $glob .= '/**/*';
        }

        return $glob;
    }
}
