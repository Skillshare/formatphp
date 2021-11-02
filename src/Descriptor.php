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
    private ?string $file;
    private ?int $start;
    private ?int $end;
    private ?int $line;
    private ?int $col;

    /**
     * @var array<string, string>
     */
    private array $metadata = [];

    public function __construct(
        ?string $id = null,
        ?string $defaultMessage = null,
        ?string $description = null,
        ?string $file = null,
        ?int $start = null,
        ?int $end = null,
        ?int $line = null,
        ?int $col = null
    ) {
        $this->id = $id;
        $this->defaultMessage = $defaultMessage;
        $this->description = $description;
        $this->file = $file;
        $this->start = $start;
        $this->end = $end;
        $this->line = $line;
        $this->col = $col;
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

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function getStart(): ?int
    {
        return $this->start;
    }

    public function getEnd(): ?int
    {
        return $this->end;
    }

    public function getLine(): ?int
    {
        return $this->line;
    }

    public function getCol(): ?int
    {
        return $this->col;
    }

    /**
     * @return array{defaultMessage: string | null, description: string | null, id: string | null, file: string | null, start: int | null, end: int | null, line: int | null, meta: array<string, string>, col: int | null}
     */
    public function toArray(): array
    {
        return [
            'col' => $this->col,
            'defaultMessage' => $this->defaultMessage,
            'description' => $this->description,
            'end' => $this->end,
            'file' => $this->file,
            'id' => $this->id,
            'line' => $this->line,
            'meta' => $this->metadata,
            'start' => $this->start,
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
