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

namespace FormatPHP\Util;

use Webmozart\Glob\Glob;

use function array_filter;
use function array_merge;
use function array_values;
use function strpos;

/**
 * Find files using glob patterns
 *
 * @internal
 */
class Globber
{
    private File $fileUtility;

    public function __construct(File $fileUtility)
    {
        $this->fileUtility = $fileUtility;
    }

    /**
     * Returns an array of files matching the provided glob patterns, optionally
     * filtered by the "ignore" glob patterns
     *
     * @param string[] $globs
     * @param string[] $ignoreGlobs
     *
     * @return string[]
     */
    public function find(array $globs, array $ignoreGlobs): array
    {
        $filePaths = [];

        foreach ($globs as $glob) {
            $filePaths = array_merge($filePaths, $this->glob($this->formatGlob($glob)));
        }

        if ($ignoreGlobs !== []) {
            $filePaths = $this->filterIgnores($filePaths, $ignoreGlobs);
        }

        return array_values(array_filter(
            $filePaths,
            fn (string $path): bool => !$this->fileUtility->isDirectory($path),
        ));
    }

    /**
     * @return string[]
     */
    public function glob(string $glob): array
    {
        return Glob::glob($glob);
    }

    /**
     * @param string[] $filePaths
     * @param string[] $ignores
     *
     * @return string[]
     */
    private function filterIgnores(array $filePaths, array $ignores): array
    {
        $filteredPaths = [];

        foreach ($filePaths as $path) {
            foreach ($ignores as $ignore) {
                if (Glob::match($path, $this->formatGlob($ignore))) {
                    continue 2;
                }
            }

            $filteredPaths[] = $path;
        }

        return $filteredPaths;
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
