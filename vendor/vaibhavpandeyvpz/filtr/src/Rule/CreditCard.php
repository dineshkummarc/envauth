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
 * Validates that a value is a valid credit card number.
 *
 * Supports validation for multiple card types: AMEX, Maestro, Mastercard, and Visa.
 * Optionally restricts validation to specific card types.
 * Null and empty string values are considered valid (optional validation).
 */
class CreditCard extends Rule
{
    protected string $message = 'This value is not a valid card number.';

    /**
     * Regular expression patterns for each card type.
     *
     * @var array<string, array<string>>
     */
    private readonly array $types;

    /**
     * Array of card types to validate against (empty means all types).
     *
     * @var array<string>
     */
    private readonly array $types2;

    /**
     * Creates a new credit card validation rule.
     *
     * @param  string|array<string>|CreditCardType|array<CreditCardType>|null  $types  Optional card types to validate against
     */
    public function __construct(string|array|CreditCardType|null $types = null)
    {
        $this->types = [
            'amex' => ['~^3[47][0-9]{13}$~'],
            'maestro' => [
                '~^(6759[0-9]{2})[0-9]{6,13}$~',
                '~^(50[0-9]{4})[0-9]{6,13}$~',
                '~^5[6-9][0-9]{10,17}$~',
                '~^6[0-9]{11,18}$~',
            ],
            'mc' => [
                '~^5[1-5][0-9]{14}$~',
                '~^2(22[1-9][0-9]{12}|2[3-9][0-9]{13}|[3-6][0-9]{14}|7[0-1][0-9]{13}|720[0-9]{12})$~',
            ],
            'visa' => ['~^4([0-9]{12}|[0-9]{15})$~'],
        ];

        $this->types2 = match (true) {
            $types === null => [],
            $types instanceof CreditCardType => [$types->value],
            is_string($types) => [$types],
            default => array_map(
                fn ($type) => $type instanceof CreditCardType ? $type->value : $type,
                (array) $types
            ),
        };
    }

    /**
     * Validates that the value is a valid credit card number.
     *
     * @param  mixed  $value  The value to validate
     * @return bool Returns true if the value is a valid card number or is null/empty, false otherwise
     */
    public function validate(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $value = (string) $value;
        $typesToCheck = $this->types2 === []
            ? $this->types
            : array_intersect_key($this->types, array_flip($this->types2));

        foreach ($typesToCheck as $regexps) {
            foreach ($regexps as $regexp) {
                if (preg_match($regexp, $value) === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
