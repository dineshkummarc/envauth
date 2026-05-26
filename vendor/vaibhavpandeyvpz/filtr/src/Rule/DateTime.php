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
 * Validates that a value is a valid date/time string.
 *
 * Uses PHP's DateTime::createFromFormat() to validate the format.
 * Null, empty string, and DateTime objects are considered valid (optional validation).
 */
class DateTime extends Rule
{
    /**
     * The expected date/time format.
     */
    protected readonly string $format;

    protected string $message = 'This value is not a valid date or time.';

    /**
     * Creates a new date/time validation rule.
     *
     * @param  string|null  $format  Optional date format (default: 'Y-m-d H:i:s')
     */
    public function __construct(?string $format = null)
    {
        $this->format = $format ?? 'Y-m-d H:i:s';
    }

    /**
     * Validates that the value matches the specified date/time format.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value matches the format or is null/empty/DateTime, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '' || $value instanceof \DateTime) {
            return true;
        }

        return \DateTime::createFromFormat($this->format, (string) $value) !== false;
    }
}
