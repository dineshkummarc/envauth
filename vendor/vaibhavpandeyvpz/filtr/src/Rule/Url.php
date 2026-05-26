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
 * Validates that a value is a valid URL.
 *
 * Uses PHP's filter_var() function with FILTER_VALIDATE_URL.
 * Null values are considered valid (optional validation).
 */
class Url extends Rule
{
    protected string $message = 'This value is not a valid URL.';

    /**
     * Validates that the value is a valid URL.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is a valid URL or null, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}
