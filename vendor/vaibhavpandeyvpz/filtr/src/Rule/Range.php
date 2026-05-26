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
 * Validates that a numeric value is within the specified range.
 *
 * The value is cast to an integer before comparison.
 * Null values are considered valid (optional validation).
 */
class Range extends Rule
{
    /**
     * The minimum value (inclusive).
     */
    protected readonly int $min;

    /**
     * The maximum value (inclusive).
     */
    protected readonly int $max;

    protected string $message = 'This value should be %d to %d range.';

    /**
     * Creates a new range validation rule.
     *
     * @param  int  $min  Minimum value (inclusive)
     * @param  int  $max  Maximum value (inclusive)
     */
    public function __construct(int $min, int $max)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Returns the error message with the range constraints.
     *
     * @return string The formatted error message
     */
    public function message(): string
    {
        return sprintf($this->message, $this->min, $this->max);
    }

    /**
     * Validates that the value is within the specified range.
     *
     * @param  mixed  $value  The value to validate (will be cast to int)
     * @return bool Returns true if the value is within range or is null, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        $value = (int) $value;

        return $value >= $this->min && $value <= $this->max;
    }
}
