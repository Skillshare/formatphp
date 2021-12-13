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
use FormatPHP\DescriptorCollection;
use FormatPHP\Exception\ImproperContextException;
use FormatPHP\Exception\InvalidArgumentException;
use FormatPHP\Format\Format;
use FormatPHP\Format\Reader\FormatPHPReader;
use FormatPHP\Format\Reader\SimpleReader;
use FormatPHP\Format\Reader\SmartlingReader;
use FormatPHP\Format\ReaderInterface;
use FormatPHP\Format\Writer\FormatPHPWriter;
use FormatPHP\Format\Writer\SimpleWriter;
use FormatPHP\Format\Writer\SmartlingWriter;
use FormatPHP\Format\WriterInterface;
use FormatPHP\Format\WriterOptions;
use FormatPHP\MessageCollection;
use ReflectionFunction;
use Throwable;

use function assert;
use function class_exists;
use function is_a;
use function sprintf;
use function strtolower;

/**
 * Utilities for format readers and writers
 *
 * @psalm-import-type ReaderCallableType from ReaderInterface
 * @psalm-import-type ReaderType from ReaderInterface
 * @psalm-import-type WriterCallableType from WriterInterface
 * @psalm-import-type WriterType from WriterInterface
 */
class FormatHelper
{
    private const READER_FORMATS = [
        Format::FORMATPHP => FormatPHPReader::class,
        Format::SIMPLE => SimpleReader::class,
        Format::SMARTLING => SmartlingReader::class,
    ];

    private const WRITER_FORMATS = [
        Format::FORMATPHP => FormatPHPWriter::class,
        Format::SIMPLE => SimpleWriter::class,
        Format::SMARTLING => SmartlingWriter::class,
    ];

    private FileSystemHelper $fileSystemHelper;

    public function __construct(FileSystemHelper $fileSystemHelper)
    {
        $this->fileSystemHelper = $fileSystemHelper;
    }

    /**
     * Returns a format reader for the given short name, class name, or file name
     *
     * @return ReaderType
     *
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     */
    public function getReader(?string $format): callable
    {
        if ($format === null) {
            return new FormatPHPReader();
        }

        $formatter = self::READER_FORMATS[strtolower($format)] ?? null;
        if ($formatter !== null) {
            return new $formatter();
        }

        /** @var ReaderCallableType */
        return $this->loadFormatter($format, ReaderInterface::class);
    }

    /**
     * Returns a format writer for the given short name, class name, or file name
     *
     * @return WriterType
     *
     * @throws InvalidArgumentException
     * @throws ImproperContextException
     */
    public function getWriter(?string $format): callable
    {
        if ($format === null) {
            return new FormatPHPWriter();
        }

        $formatter = self::WRITER_FORMATS[strtolower($format)] ?? null;
        if ($formatter !== null) {
            return new $formatter();
        }

        /** @var WriterCallableType */
        return $this->loadFormatter($format, WriterInterface::class);
    }

    /**
     * @param class-string<ReaderInterface> | class-string<WriterInterface> $type
     *
     * @return ReaderCallableType | WriterCallableType
     *
     * @throws ImproperContextException
     * @throws InvalidArgumentException
     */
    private function loadFormatter(string $format, string $type): callable
    {
        if (class_exists($format) && is_a($format, $type, true)) {
            /** @psalm-suppress MixedMethodCall */
            return new $format(); // @phpstan-ignore-line
        }

        $formatter = $this->fileSystemHelper->loadClosureFromScript($format);

        if ($type === ReaderInterface::class) {
            $formatter = $this->validateReaderClosure($formatter);
        } else {
            $formatter = $this->validateWriterClosure($formatter);
        }

        return $formatter;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @psalm-suppress UndefinedMethod, PossiblyNullReference
     */
    private function validateReaderClosure(?Closure $formatter): Closure
    {
        try {
            assert($formatter !== null);

            $reflected = new ReflectionFunction($formatter);

            assert($reflected->getNumberOfParameters() === 1);

            $param1 = $reflected->getParameters()[0];

            assert($param1->hasType() && $param1->getType()->getName() === 'array');
            assert($reflected->hasReturnType() && $reflected->getReturnType()->getName() === MessageCollection::class);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(sprintf(
                'The format provided is not a known format, an instance of '
                    . '%s, or a callable of the shape `callable(array<mixed>):%s`.',
                ReaderInterface::class,
                MessageCollection::class,
            ));
        }

        return $formatter;
    }

    /**
     * @throws InvalidArgumentException
     *
     * @psalm-suppress UndefinedMethod, PossiblyNullReference
     */
    private function validateWriterClosure(?Closure $formatter): Closure
    {
        try {
            assert($formatter !== null);

            $reflected = new ReflectionFunction($formatter);

            assert($reflected->getNumberOfParameters() === 2);

            $param1 = $reflected->getParameters()[0];
            $param2 = $reflected->getParameters()[1];

            assert($param1->hasType() && $param1->getType()->getName() === DescriptorCollection::class);
            assert($param2->hasType() && $param2->getType()->getName() === WriterOptions::class);
            assert($reflected->hasReturnType() && $reflected->getReturnType()->getName() === 'array');
        } catch (Throwable $exception) {
            throw new InvalidArgumentException(sprintf(
                'The format provided is not a known format, an instance of '
                . '%s, or a callable of the shape `callable(%s,%s):array<mixed>`.',
                WriterInterface::class,
                DescriptorCollection::class,
                WriterOptions::class,
            ));
        }

        return $formatter;
    }
}
