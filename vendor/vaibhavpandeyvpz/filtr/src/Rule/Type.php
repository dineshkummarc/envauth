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
 * Validates that a value is of a specific type.
 *
 * Uses PHP's gettype() function to check the value's type.
 * Null values are considered valid (optional validation).
 */
class Type extends Rule
{
    /**
     * The expected type (e.g., 'string', 'integer', 'array', etc.).
     */
    protected readonly string $type;

    /**
     * Creates a new type validation rule.
     *
     * @param  string  $type  The expected type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Validates that the value is of the specified type.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is of the specified type or null, false otherwise
     */
    public function validate(mixed $value): bool
    {
        return $value === null || $this->type === gettype($value);
    }
}
