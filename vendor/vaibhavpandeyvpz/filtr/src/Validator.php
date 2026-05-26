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
 * Default implementation of ValidatorInterface.
 *
 * Provides a fluent API for defining and executing validation rules.
 * Supports both optional and required fields, as well as nested field
 * access using dot notation.
 */
class Validator implements ValidatorInterface
{
    /**
     * Array of validation rule builders indexed by field name.
     *
     * @var array<string, Builder>
     */
    protected array $assertions = [];

    /**
     * Array indicating which fields are required.
     * The value is false for optional fields, or a string error message for required fields.
     *
     * @var array<string, string|false>
     */
    protected array $required = [];

    /**
     * Fetches a value from an array using dot notation for nested keys.
     *
     * @param  array<string, mixed>  $haystack  The array to search in
     * @param  string  $needle  The key to search for (supports dot notation for nested keys)
     * @return mixed The value if found, null otherwise
     */
    protected static function fetch(array $haystack, string $needle): mixed
    {
        if (isset($haystack[$needle])) {
            return $haystack[$needle];
        }
        $segments = explode('.', $needle);
        foreach ($segments as $segment) {
            if (! is_array($haystack) || ! array_key_exists($segment, $haystack)) {
                return null;
            }
            $haystack = $haystack[$segment];
        }

        return $haystack;
    }

    /**
     * Defines an optional validation rule for a given key.
     *
     * Optional fields are only validated if they are present in the data.
     *
     * @param  string  $name  The name of the field to validate
     * @return Builder A builder instance for chaining validation rules
     */
    public function key(string $name): Builder
    {
        $assertion = new Builder;
        $this->assertions[$name] = $assertion;
        $this->required[$name] = false;

        return $assertion;
    }

    /**
     * Defines a required validation rule for a given key.
     *
     * Required fields must be present in the data, otherwise validation fails.
     *
     * @param  string  $key  The name of the field that is required
     * @param  string  $message  The error message to display if the field is missing
     * @return Builder A builder instance for chaining validation rules
     */
    public function required(string $key, string $message = 'This value is required.'): Builder
    {
        $assertion = new Builder;
        $this->assertions[$key] = $assertion;
        $this->required[$key] = $message;

        return $assertion;
    }

    /**
     * Validates the provided data against all defined rules.
     *
     * @param  array<string, mixed>|null  $data  The data to validate. If null, an empty array is used.
     * @return ResultInterface The validation result containing errors if any
     */
    public function validate(?array $data = null): ResultInterface
    {
        $data ??= [];
        $result = new Result;

        foreach ($this->assertions as $key => $assertion) {
            $value = self::fetch($data, $key);
            if ($value !== null) {
                if (! $assertion->validate($value)) {
                    $result->error($key, $assertion->message());
                }
            } else {
                $message = $this->required[$key];
                if ($message !== false) {
                    $result->error($key, $message);
                }
            }
        }

        return $result;
    }
}
