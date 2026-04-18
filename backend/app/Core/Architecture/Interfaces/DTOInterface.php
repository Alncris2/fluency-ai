<?php

namespace App\Core\Architecture\Interfaces;

use Illuminate\Validation\ValidationException;

interface DTOInterface
{
    /**
     * Fill properties of the Dto with the given params.
     */
    public function fill(array $params): self;

    /**
     * Validate the Dto.
     */
    public function validate(): bool;

    /**
     * Fill and Validate a Dto.
     *
     * @throws ValidationException
     */
    public function make(array $params, bool $toUpdate = false): self;

    /**
     * Fill properties of the Dto with the given params.
     */
    public function update(array $params): self;

    /**
     * Get the Dto as an array.
     */
    public function toArray(): array;

    /**
     * Get the Dto as a JSON string.
     */
    public function toJson(): string;
}