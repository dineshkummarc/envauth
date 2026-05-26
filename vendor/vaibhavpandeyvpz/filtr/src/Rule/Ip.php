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
 * Validates that a value is a valid IP address.
 *
 * Uses PHP's filter_var() function with FILTER_VALIDATE_IP.
 * Supports optional flags to restrict validation to IPv4 or IPv6.
 * Null and empty string values are considered valid (optional validation).
 */
class Ip extends Rule
{
    /**
     * Filter flags for IP validation (e.g., FILTER_FLAG_IPV4, FILTER_FLAG_IPV6).
     */
    protected readonly int $flags;

    protected string $message = 'This value is not a valid IP address.';

    /**
     * Creates a new IP validation rule.
     *
     * @param  int  $flags  Optional filter flags (default: 0, accepts both IPv4 and IPv6)
     */
    public function __construct(int $flags = 0)
    {
        $this->flags = $flags;
    }

    /**
     * Validates that the value is a valid IP address.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is a valid IP or null/empty, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_IP, $this->flags) !== false;
    }
}
