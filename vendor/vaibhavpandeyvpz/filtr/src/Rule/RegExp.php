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
 * Validates that a value matches a regular expression pattern.
 *
 * Uses PHP's preg_match() function to check if the value matches the pattern.
 * Null and empty string values are considered valid (optional validation).
 */
class RegExp extends Rule
{
    /**
     * The regular expression pattern.
     */
    protected readonly string $pattern;

    /**
     * Creates a new regular expression validation rule.
     *
     * @param  string  $pattern  The regular expression pattern
     */
    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Validates that the value matches the regular expression pattern.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value matches the pattern or is null/empty, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return preg_match($this->pattern, (string) $value) === 1;
    }
}
