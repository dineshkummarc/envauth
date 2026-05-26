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

namespace Filtr\Rule;

use Filtr\Rule;

/**
 * Validates that a value is one of the specified allowed values.
 *
 * Uses strict comparison (===) to check if the value is in the allowed list.
 * Null values are considered valid (optional validation).
 */
class OneOf extends Rule
{
    /**
     * Array of allowed values.
     *
     * @var array<mixed>
     */
    protected readonly array $values;

    protected string $message = 'This value should be one of %s.';

    /**
     * Creates a new one-of validation rule.
     *
     * @param  array<mixed>  $values  Array of allowed values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Returns the error message with the allowed values.
     *
     * @return string The formatted error message
     */
    public function message(): string
    {
        return sprintf($this->message, implode(', ', $this->values));
    }

    /**
     * Validates that the value is one of the allowed values.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is in the allowed list or is null, false otherwise
     */
    public function validate(mixed $value): bool
    {
        return $value === null || in_array($value, $this->values, true);
    }
}
