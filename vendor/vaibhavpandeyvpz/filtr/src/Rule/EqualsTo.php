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
 * Validates that a value equals a specified value (loose comparison).
 *
 * Uses the == operator for comparison, which performs type coercion.
 */
class EqualsTo extends Rule
{
    /**
     * The value to compare against.
     */
    protected readonly mixed $value;

    /**
     * Creates a new equals-to validation rule.
     *
     * @param  mixed  $value  The value to compare against
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Validates that the value equals the specified value (loose comparison).
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the values are equal (==), false otherwise
     */
    public function validate(mixed $value): bool
    {
        return $this->value == $value;
    }
}
