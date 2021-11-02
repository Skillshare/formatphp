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

namespace FormatPHP;

/**
 * FormatPHP descriptor
 */
class Descriptor implements Intl\ExtendedDescriptor
{
    private ?string $defaultMessage;
    private ?string $description;
    private ?string $id;
    private ?string $sourceFile;
    private ?int $sourceStartOffset;
    private ?int $sourceEndOffset;
    private ?int $sourceLine;

    /**
     * @var array<string, string>
     */
    private array $metadata = [];

    /**
     * @param string | null $id The descriptor identifier
     * @param string | null $defaultMessage The default message for the descriptor
     * @param string | null $description A description to give translators more context/information about this message
     * @param string | null $sourceFile The source file in which the descriptor appears
     * @param int | null $sourceStartOffset The string offset in the source file where the descriptor begins
     * @param int | null $sourceEndOffset The string offset in the source file where the descriptor ends
     * @param int | null $sourceLine The line number in the source file where the descriptor begins
     */
    public function __construct(
        ?string $id = null,
        ?string $defaultMessage = null,
        ?string $description = null,
        ?string $sourceFile = null,
        ?int $sourceStartOffset = null,
        ?int $sourceEndOffset = null,
        ?int $sourceLine = null
    ) {
        $this->id = $id;
        $this->defaultMessage = $defaultMessage;
        $this->description = $description;
        $this->sourceFile = $sourceFile;
        $this->sourceStartOffset = $sourceStartOffset;
        $this->sourceEndOffset = $sourceEndOffset;
        $this->sourceLine = $sourceLine;
    }

    public function getDefaultMessage(): ?string
    {
        return $this->defaultMessage;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getSourceFile(): ?string
    {
        return $this->sourceFile;
    }

    public function getSourceStartOffset(): ?int
    {
        return $this->sourceStartOffset;
    }

    public function getSourceEndOffset(): ?int
    {
        return $this->sourceEndOffset;
    }

    public function getSourceLine(): ?int
    {
        return $this->sourceLine;
    }

    /**
     * @return array{defaultMessage: string | null, description: string | null, id: string | null, file: string | null, start: int | null, end: int | null, line: int | null, meta: array<string, string>}
     */
    public function toArray(): array
    {
        return [
            'defaultMessage' => $this->defaultMessage,
            'description' => $this->description,
            'end' => $this->sourceEndOffset,
            'file' => $this->sourceFile,
            'id' => $this->id,
            'line' => $this->sourceLine,
            'meta' => $this->metadata,
            'start' => $this->sourceStartOffset,
        ];
    }

    /**
     * @inheritdoc
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
