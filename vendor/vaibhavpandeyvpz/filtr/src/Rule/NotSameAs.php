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
 * Validates that a value is not the same as a specified value (strict comparison).
 *
 * Uses the !== operator for comparison, which checks both value and type.
 */
class NotSameAs extends Rule
{
    /**
     * The value to compare against.
     */
    protected readonly mixed $value;

    /**
     * Creates a new not-same-as validation rule.
     *
     * @param  mixed  $value  The value to compare against
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Validates that the value is not the same as the specified value (strict comparison).
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the values are not the same (!==), false otherwise
     */
    public function validate(mixed $value): bool
    {
        return $this->value !== $value;
    }
}
