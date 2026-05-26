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
 * Validates that a countable value has the exact specified count.
 *
 * Works with arrays and objects implementing the Countable interface.
 * Null values are considered valid (optional validation).
 */
class Count extends Rule
{
    /**
     * The expected count.
     */
    protected readonly int $number;

    protected string $message = 'This should only contain %s value(s).';

    /**
     * Creates a new count validation rule.
     *
     * @param  int  $number  The expected count
     */
    public function __construct(int $number)
    {
        $this->number = $number;
    }

    /**
     * Returns the error message with the expected count.
     *
     * @return string The formatted error message
     */
    public function message(): string
    {
        return sprintf($this->message, $this->number);
    }

    /**
     * Validates that the value has the exact specified count.
     *
     * @param  mixed  $value  The value to validate (must be countable)
     * @return bool Returns true if the value has the exact count or is null, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return is_countable($value) && $this->number === count($value);
    }
}
