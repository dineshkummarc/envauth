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
 * Validates that a string value has a length within the specified range.
 *
 * Null and empty string values are considered valid (optional validation).
 */
class Length extends Rule
{
    /**
     * The minimum length.
     */
    protected readonly int $min;

    /**
     * The maximum length (optional).
     */
    protected readonly ?int $max;

    protected string $message = 'This value should contain a minimum of %d characters.';

    protected string $message2 = 'This value should contain a minimum of %d and a maximum of %d characters.';

    /**
     * Creates a new length validation rule.
     *
     * @param  int  $min  Minimum length
     * @param  int|null  $max  Maximum length (optional)
     */
    public function __construct(int $min, ?int $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Returns the error message with the length constraints.
     *
     * @return string The formatted error message
     */
    public function message(): string
    {
        return match ($this->max !== null) {
            true => sprintf($this->message2, $this->min, $this->max),
            false => sprintf($this->message, $this->min),
        };
    }

    /**
     * Validates that the value's length is within the specified range.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the length is within range or value is null/empty, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }
        $length = strlen((string) $value);

        return $length >= $this->min && ($this->max === null || $length <= $this->max);
    }
}
