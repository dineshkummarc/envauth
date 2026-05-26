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

use Filtr\Rule\Builder;

/**
 * Interface for input validators.
 *
 * Provides a fluent API for defining validation rules and validating data.
 */
interface ValidatorInterface
{
    /**
     * Defines an optional validation rule for a given key.
     *
     * Optional fields are only validated if they are present in the data.
     *
     * @param  string  $name  The name of the field to validate
     * @return Builder A builder instance for chaining validation rules
     */
    public function key(string $name): Builder;

    /**
     * Defines a required validation rule for a given key.
     *
     * Required fields must be present in the data, otherwise validation fails.
     *
     * @param  string  $key  The name of the field that is required
     * @param  string  $message  The error message to display if the field is missing
     * @return Builder A builder instance for chaining validation rules
     */
    public function required(string $key, string $message = 'This value is required.'): Builder;

    /**
     * Validates the provided data against all defined rules.
     *
     * @param  array<string, mixed>|null  $data  The data to validate. If null, an empty array is used.
     * @return ResultInterface The validation result containing errors if any
     */
    public function validate(?array $data = null): ResultInterface;
}
