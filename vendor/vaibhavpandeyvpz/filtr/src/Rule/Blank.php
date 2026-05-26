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
 * Validates that a value is blank (empty).
 *
 * A value is considered blank if it is empty, but '0' is not considered blank.
 */
class Blank extends Rule
{
    protected string $message = 'This value should be blank.';

    /**
     * Validates that the value is blank.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is blank, false otherwise
     */
    public function validate(mixed $value): bool
    {
        return empty($value) && $value != '0';
    }
}
