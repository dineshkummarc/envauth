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

namespace Filtr;

/**
 * Interface for validation rules.
 *
 * All validation rules must implement this interface to provide
 * validation logic and error messages.
 */
interface RuleInterface
{
    /**
     * Returns the error message for this rule.
     *
     * @return string The error message to display when validation fails
     */
    public function message(): string;

    /**
     * Validates the given input value.
     *
     * @param  mixed  $input  The value to validate
     * @return bool Returns true if the value is valid, false otherwise
     */
    public function validate(mixed $input): bool;
}
