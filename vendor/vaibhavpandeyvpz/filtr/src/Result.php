<?php

declare(strict_types=1);

/*
 * This file is part of vaibhavpandeyvpz/filtr package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Filtr;

/**
 * Default implementation of ResultInterface.
 *
 * Stores validation errors and provides methods to check
 * if validation was successful.
 */
class Result implements ResultInterface
{
    /**
     * Array of validation errors.
     *
     * @var array<string, string>
     */
    protected array $errors = [];

    /**
     * Adds an error message for a specific field.
     *
     * @param  string  $field  The name of the field that has an error
     * @param  string  $message  The error message
     */
    public function error(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Returns all validation errors.
     *
     * @return array<string, string> An associative array mapping field names to error messages
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Checks if the validation result is valid (no errors).
     *
     * @return bool Returns true if there are no errors, false otherwise
     */
    public function valid(): bool
    {
        return empty($this->errors);
    }
}
