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
 * Validates that a value is numeric.
 *
 * Uses PHP's is_numeric() function to check if a value is numeric.
 * Null and empty string values are considered valid (optional validation).
 */
class Number extends Rule
{
    protected string $message = 'This value must be a number.';

    /**
     * Validates that the value is numeric.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is numeric or null/empty, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return is_numeric($value);
    }
}
