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
 * Validates a value using a custom callback function.
 *
 * The callback should accept one parameter (the value to validate)
 * and return true if valid, false otherwise.
 */
class Callback extends Rule
{
    /**
     * The validation callback function.
     *
     * @var callable
     */
    protected readonly mixed $callback;

    /**
     * Creates a new callback validation rule.
     *
     * @param  callable  $callback  The validation callback function
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * Validates the value using the callback function.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the callback returns true, false otherwise
     */
    public function validate(mixed $value): bool
    {
        return ($this->callback)($value) === true;
    }
}
