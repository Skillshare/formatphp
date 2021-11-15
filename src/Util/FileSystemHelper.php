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

use Closure;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Exception\UnableToProcessFileException;
use FormatPHP\Exception\UnableToWriteFileException;
use JsonException;

use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function get_resource_type;
use function getcwd;
use function gettype;
use function is_callable;
use function is_dir;
use function is_int;
use function is_readable;
use function is_resource;
use function is_string;
use function json_decode;
use function realpath;
use function sprintf;
use function strlen;

use const JSON_BIGINT_AS_STRING;
use const JSON_INVALID_UTF8_SUBSTITUTE;
use const JSON_THROW_ON_ERROR;
use const PHP_SAPI;

/**
 * File and path utilities
 */
class FileSystemHelper
{
    private const JSON_DECODE_FLAGS = JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;

    private static ?string $currentWorkingDir = null;

    /**
     * Returns the contents of a file
     *
     * @throws UnableToProcessFileException
     */
    public function getContents(string $filePath): string
    {
        if (!$this->isReadable($filePath)) {
            throw new UnableToProcessFileException(sprintf(
                'File does not exist or you do not have permission to read it: "%s".',
                $filePath,
            ));
        }

        if ($this->isDirectory($filePath)) {
            throw new UnableToProcessFileException(sprintf('File path is a directory: "%s".', $filePath));
        }

        $contents = @file_get_contents($filePath) ?: null;

        if ($contents === null) {
            throw new UnableToProcessFileException(sprintf('Could not open file for reading: "%s".', $filePath));
        }

        return $contents;
    }

    /**
     * @return array<array-key, mixed>
     *
     * @throws UnableToProcessFileException
     */
    public function getJsonContents(string $filePath): array
    {
        $contents = $this->getContents($filePath);

        try {
            /** @var array<array-key, mixed> $parsedContents */
            $parsedContents = @json_decode($contents, true, 512, self::JSON_DECODE_FLAGS);

            return $parsedContents;
        } catch (JsonException $exception) {
            throw new UnableToProcessFileException(
                sprintf('Unable to decode the JSON in the file "%s"', $filePath),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception,
            );
        }
    }

    /**
     * @param string | resource | mixed $file
     *
     * @throws InvalidArgumentException
     * @throws UnableToWriteFileException
     */
    public function writeContents($file, string $contents): void
    {
        if (!is_string($file) && (!is_resource($file) || get_resource_type($file) !== 'stream')) {
            throw new InvalidArgumentException(sprintf(
                'File must be a string path or a stream resource; received %s.',
                gettype($file),
            ));
        }

        if (is_resource($file)) {
            $bytes = @fwrite($file, $contents);

            if ($bytes === false) {
                throw new UnableToWriteFileException('Unable to write contents to stream resource.');
            }

            return;
        }

        $bytes = @file_put_contents($file, $contents);

        if ($bytes === false) {
            throw new UnableToWriteFileException(sprintf('Unable to write contents to file "%s".', $file));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getRealPath(string $path): string
    {
        $realpath = @realpath($path);

        if ($realpath === false) {
            throw new InvalidArgumentException(sprintf(
                'Unable to find or access the path at "%s"',
                $path,
            ));
        }

        return $realpath;
    }

    /**
     * Returns `true` if the path is a directory
     */
    public function isDirectory(string $path): bool
    {
        return @is_dir($path);
    }

    /**
     * Returns `true` if the path is readable
     */
    public function isReadable(string $path): bool
    {
        return @is_readable($path);
    }

    /**
     * Returns the current working directory
     *
     * This is not necessarily the directory from which the script is running,
     * nor is it the location of this class file. It is the directory in which
     * the process is currently working, which can change if using
     * {@link https://www.php.net/chdir chdir()} or `cd` to change the working
     * directory.
     */
    public function getCurrentWorkingDirectory(): string
    {
        if (self::$currentWorkingDir === null) {
            self::$currentWorkingDir = getcwd() ?: '';
        }

        return strlen(self::$currentWorkingDir) > 0 ? self::$currentWorkingDir . '/' : '';
    }

    /**
     * Loads a PHP script and returns its Closure
     *
     * PHP needs a taint mode, so we can determine whether $path might have
     * come from external sources. If it did, we don't want to attempt to
     * include it here. As a result, we only allow calling this method from a
     * CLI SAPI context. Calling from a Web SAPI context is forbidden.
     *
     * @throws ImproperContextException
     */
    public function loadClosureFromScript(string $path): ?Closure
    {
        if ($this->getSapiName() !== 'cli') {
            throw new ImproperContextException(sprintf(
                'Method must be called from CLI SAPI context only; called from %s context.',
                $this->getSapiName(),
            ));
        }

        if (!$this->isReadable($path) || $this->isDirectory($path)) {
            return null;
        }

        /**
         * @var Closure | callable | int $closure
         * @psalm-suppress UnresolvableInclude
         */
        $closure = @include $path;

        if (!$closure instanceof Closure && is_callable($closure)) {
            $closure = Closure::fromCallable($closure);
        }

        if ($closure instanceof Closure) {
            return $closure;
        }

        return null;
    }

    /**
     * Returns the name of the current SAPI in which PHP is running
     *
     * This method exists for ease of mocking this value from tests.
     */
    protected function getSapiName(): string
    {
        return PHP_SAPI;
    }
}
