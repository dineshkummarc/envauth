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
use Filtr\RuleInterface;

/**
 * Builder class for creating validation rule chains.
 *
 * Provides a fluent interface for chaining multiple validation rules
 * together. All rules in the chain must pass for validation to succeed.
 */
class Builder extends Rule
{
    /**
     * Array of validation rules to apply.
     *
     * @var array<RuleInterface>
     */
    protected array $rules = [];

    /**
     * Adds a custom callback validation rule.
     *
     * The callback should accept one parameter (the value to validate)
     * and return true if valid, false otherwise.
     *
     * @param  callable  $callback  The validation callback function
     * @return static Returns self for method chaining
     */
    public function is(callable $callback): static
    {
        $this->rules[] = new Callback($callback);

        return $this;
    }

    /**
     * Validates that the value is blank (empty).
     *
     * @return static Returns self for method chaining
     */
    public function isBlank(): static
    {
        $this->rules[] = new Blank;

        return $this;
    }

    /**
     * Validates that the value is a boolean.
     *
     * @return static Returns self for method chaining
     */
    public function isBoolean(): static
    {
        return $this->isOfType('boolean');
    }

    /**
     * Validates that the value is a valid credit card number.
     *
     * Optionally restricts validation to specific card types.
     *
     * @param  string|array<string>|CreditCardType|array<CreditCardType>|null  $types  Optional card types to validate against
     * @return static Returns self for method chaining
     */
    public function isCreditCard(string|array|CreditCardType|null $types = null): static
    {
        $this->rules[] = new CreditCard($types);

        return $this;
    }

    /**
     * Validates that the value is a valid date/time string.
     *
     * @param  string|null  $format  Optional date format (default: 'Y-m-d H:i:s')
     * @return static Returns self for method chaining
     */
    public function isDateTime(?string $format = null): static
    {
        $this->rules[] = new DateTime($format);

        return $this;
    }

    /**
     * Validates that the value is a valid email address.
     *
     * @return static Returns self for method chaining
     */
    public function isEmailAddress(): static
    {
        $this->rules[] = new Email;

        return $this;
    }

    /**
     * Validates that the value equals the given value (loose comparison).
     *
     * @param  mixed  $value  The value to compare against
     * @return static Returns self for method chaining
     */
    public function isEqualTo(mixed $value): static
    {
        $this->rules[] = new EqualsTo($value);

        return $this;
    }

    /**
     * Validates that the value is false.
     *
     * @return static Returns self for method chaining
     */
    public function isFalse(): static
    {
        return $this->isEqualTo(false);
    }

    /**
     * Validates that the value (if countable) has the exact specified count.
     *
     * @param  int  $number  The expected count
     * @return static Returns self for method chaining
     */
    public function isHavingCount(int $number): static
    {
        $this->rules[] = new Count($number);

        return $this;
    }

    /**
     * Validates that the string length is within the specified range.
     *
     * @param  int  $min  Minimum length
     * @param  int|null  $max  Maximum length (optional)
     * @return static Returns self for method chaining
     */
    public function isHavingLength(int $min, ?int $max = null): static
    {
        $this->rules[] = new Length($min, $max);

        return $this;
    }

    /**
     * Validates that the numeric value is within the specified range.
     *
     * @param  int  $min  Minimum value (inclusive)
     * @param  int  $max  Maximum value (inclusive)
     * @return static Returns self for method chaining
     */
    public function isInRange(int $min, int $max): static
    {
        $this->rules[] = new Range($min, $max);

        return $this;
    }

    /**
     * Validates that the value is an integer.
     *
     * @return static Returns self for method chaining
     */
    public function isInteger(): static
    {
        return $this->isOfType('integer');
    }

    /**
     * Validates that the value is a valid IP address.
     *
     * @param  int  $flags  Optional filter flags (e.g., FILTER_FLAG_IPV4, FILTER_FLAG_IPV6)
     * @return static Returns self for method chaining
     */
    public function isIpAddress(int $flags = 0): static
    {
        $this->rules[] = new Ip($flags);

        return $this;
    }

    /**
     * Validates that the value is a valid IPv4 address.
     *
     * @return static Returns self for method chaining
     */
    public function isIpv4Address(): static
    {
        return $this->isIpAddress(FILTER_FLAG_IPV4);
    }

    /**
     * Validates that the value is a valid IPv6 address.
     *
     * @return static Returns self for method chaining
     */
    public function isIpv6Address(): static
    {
        return $this->isIpAddress(FILTER_FLAG_IPV6);
    }

    /**
     * Validates that the value is a valid MAC address.
     *
     * @return static Returns self for method chaining
     */
    public function isMacAddress(): static
    {
        return $this->isMatchingWith('~^(?:[a-fA-F0-9]{2}[:-]?){6}$~');
    }

    /**
     * Validates that the value matches the given regular expression.
     *
     * @param  string  $regexp  The regular expression pattern
     * @return static Returns self for method chaining
     */
    public function isMatchingWith(string $regexp): static
    {
        $this->rules[] = new RegExp($regexp);

        return $this;
    }

    /**
     * Validates that the value is not blank (not empty).
     *
     * @return static Returns self for method chaining
     */
    public function isNotBlank(): static
    {
        $this->rules[] = new NotBlank;

        return $this;
    }

    /**
     * Validates that the value does not equal the given value (loose comparison).
     *
     * @param  mixed  $value  The value to compare against
     * @return static Returns self for method chaining
     */
    public function isNotEqualTo(mixed $value): static
    {
        $this->rules[] = new NotEqualsTo($value);

        return $this;
    }

    /**
     * Validates that the value is not the same as the given value (strict comparison).
     *
     * @param  mixed  $value  The value to compare against
     * @return static Returns self for method chaining
     */
    public function isNotSameAs(mixed $value): static
    {
        $this->rules[] = new NotSameAs($value);

        return $this;
    }

    /**
     * Validates that the value is numeric.
     *
     * @return static Returns self for method chaining
     */
    public function isNumber(): static
    {
        $this->rules[] = new Number;

        return $this;
    }

    /**
     * Validates that the value is of the specified type.
     *
     * @param  string  $type  The expected type (e.g., 'string', 'integer', 'array', etc.)
     * @return static Returns self for method chaining
     */
    public function isOfType(string $type): static
    {
        $this->rules[] = new Type($type);

        return $this;
    }

    /**
     * Validates that the value is one of the specified values.
     *
     * @param  array<mixed>  $values  Array of allowed values
     * @return static Returns self for method chaining
     */
    public function isOneOf(array $values): static
    {
        $this->rules[] = new OneOf($values);

        return $this;
    }

    /**
     * Validates that the value is the same as the given value (strict comparison).
     *
     * @param  mixed  $value  The value to compare against
     * @return static Returns self for method chaining
     */
    public function isSameAs(mixed $value): static
    {
        $this->rules[] = new SameAs($value);

        return $this;
    }

    /**
     * Validates that the value is a string.
     *
     * @return static Returns self for method chaining
     */
    public function isString(): static
    {
        return $this->isOfType('string');
    }

    /**
     * Validates that the value is true.
     *
     * @return static Returns self for method chaining
     */
    public function isTrue(): static
    {
        return $this->isEqualTo(true);
    }

    /**
     * Validates that the value is a valid URL.
     *
     * @return static Returns self for method chaining
     */
    public function isUrl(): static
    {
        $this->rules[] = new Url;

        return $this;
    }

    /**
     * Validates the given value against all rules in the chain.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if all rules pass, false otherwise
     */
    public function validate(mixed $value): bool
    {
        foreach ($this->rules as $rule) {
            if (! $rule->validate($value)) {
                $this->message = $rule->message();

                return false;
            }
        }

        return true;
    }
}
