# Filtr

[![Latest Version](https://img.shields.io/packagist/v/vaibhavpandeyvpz/filtr.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/filtr)
[![Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/filtr.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/filtr)
[![PHP Version](https://img.shields.io/packagist/php-v/vaibhavpandeyvpz/filtr.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/filtr)
[![License](https://img.shields.io/packagist/l/vaibhavpandeyvpz/filtr.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/vaibhavpandeyvpz/filtr/tests.yml?branch=master&style=flat-square)](https://github.com/vaibhavpandeyvpz/filtr/actions)

Simple and fluent input validation library for PHP 8.2+.

## Features

- **Fluent API**: Chain validation rules together for readable, expressive code
- **Required & Optional Fields**: Distinguish between required and optional fields
- **Nested Field Access**: Use dot notation to validate nested array structures
- **Comprehensive Rules**: Built-in validators for common use cases
- **Custom Validation**: Support for custom callback functions
- **Type Safety**: Full PHP 8.2+ type declarations and strict types

## Installation

Install via Composer:

```bash
composer require vaibhavpandeyvpz/filtr
```

## Quick Start

```php
<?php

use Filtr\Validator;

$validator = new Validator();
$validator->required('email')->isNotBlank()->isEmailAddress();
$validator->required('password')->isNotBlank()->isHavingLength(8, 32);
$validator->key('remember_me')->isBoolean();

$result = $validator->validate([
    'email' => 'user@example.com',
    'password' => 'securepassword123',
    'remember_me' => true,
]);

if ($result->valid()) {
    // Validation passed
    echo "All fields are valid!";
} else {
    // Display errors
    foreach ($result->errors() as $field => $message) {
        echo "$field: $message\n";
    }
}
```

## Basic Usage

### Required Fields

Fields marked as `required()` must be present in the data, otherwise validation fails:

```php
$validator = new Validator();
$validator->required('username')->isNotBlank();

$result = $validator->validate(['username' => 'john_doe']); // ✓ Valid
$result = $validator->validate([]); // ✗ Invalid: "username: This value is required."
```

### Optional Fields

Fields marked as `key()` are only validated if they are present in the data:

```php
$validator = new Validator();
$validator->key('bio')->isString();

$result = $validator->validate(['bio' => 'Software developer']); // ✓ Valid
$result = $validator->validate([]); // ✓ Valid (field not present, so not validated)
```

### Chaining Rules

You can chain multiple validation rules together. All rules must pass for validation to succeed:

```php
$validator = new Validator();
$validator->required('email')
    ->isNotBlank()
    ->isEmailAddress()
    ->isHavingLength(5, 255);

$result = $validator->validate(['email' => 'user@example.com']); // ✓ Valid
```

### Nested Fields

Use dot notation to validate nested array structures:

```php
$validator = new Validator();
$validator->required('user.name')->isNotBlank();
$validator->required('user.email')->isEmailAddress();
$validator->key('user.address.city')->isString();

$result = $validator->validate([
    'user' => [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'address' => [
            'city' => 'New York'
        ]
    ]
]); // ✓ Valid
```

## Available Validation Rules

### Type Validation

- `isBoolean()` - Validates that the value is a boolean
- `isInteger()` - Validates that the value is an integer
- `isString()` - Validates that the value is a string
- `isNumber()` - Validates that the value is numeric
- `isOfType(string $type)` - Validates that the value is of a specific type

```php
$validator->key('age')->isInteger();
$validator->key('active')->isBoolean();
$validator->key('score')->isNumber();
```

### String Validation

- `isNotBlank()` - Validates that the value is not empty
- `isBlank()` - Validates that the value is blank
- `isHavingLength(int $min, ?int $max = null)` - Validates string length
- `isMatchingWith(string $regexp)` - Validates against a regular expression

```php
$validator->required('password')
    ->isNotBlank()
    ->isHavingLength(8, 32);

$validator->key('phone')->isMatchingWith('~^\+?[1-9]\d{1,14}$~');
```

### Email & URL Validation

- `isEmailAddress()` - Validates email addresses
- `isUrl()` - Validates URLs

```php
$validator->required('email')->isEmailAddress();
$validator->key('website')->isUrl();
```

### IP Address Validation

- `isIpAddress(int $flags = 0)` - Validates IP addresses
- `isIpv4Address()` - Validates IPv4 addresses
- `isIpv6Address()` - Validates IPv6 addresses

```php
$validator->key('ip')->isIpAddress();
$validator->key('ipv4')->isIpv4Address();
$validator->key('ipv6')->isIpv6Address();
```

### Date & Time Validation

- `isDateTime(?string $format = null)` - Validates date/time strings

```php
$validator->key('birthday')->isDateTime('Y-m-d');
$validator->key('created_at')->isDateTime('Y-m-d H:i:s');
```

### Credit Card Validation

- `isCreditCard(string|array|CreditCardType|null $types = null)` - Validates credit card numbers

```php
use Filtr\Rule\CreditCardType;

$validator->required('card_number')->isCreditCard();
$validator->required('card_number')->isCreditCard(CreditCardType::Visa);
$validator->required('card_number')->isCreditCard([CreditCardType::Visa, CreditCardType::Mastercard]);
```

### Comparison Validation

- `isEqualTo(mixed $value)` - Loose equality (==)
- `isNotEqualTo(mixed $value)` - Loose inequality (!=)
- `isSameAs(mixed $value)` - Strict equality (===)
- `isNotSameAs(mixed $value)` - Strict inequality (!==)
- `isOneOf(array $values)` - Value must be in the given array
- `isTrue()` - Value must be true
- `isFalse()` - Value must be false

```php
$validator->key('status')->isOneOf(['active', 'inactive', 'pending']);
$validator->key('confirmed')->isTrue();
$validator->key('deleted')->isFalse();
```

### Numeric Validation

- `isInRange(int $min, int $max)` - Validates numeric range

```php
$validator->key('age')->isInRange(18, 120);
$validator->key('score')->isInRange(0, 100);
```

### Count Validation

- `isHavingCount(int $number)` - Validates that countable values have exact count

```php
$validator->key('tags')->isHavingCount(3);
$validator->key('items')->isHavingCount(5);
```

### Custom Validation

- `is(callable $callback)` - Custom validation using a callback function

```php
$validator->key('username')->is(function($value) {
    return preg_match('/^[a-z0-9_]+$/', $value) === 1;
});

// Or using arrow functions (PHP 7.4+)
$validator->key('even_number')->is(fn($value) => is_numeric($value) && $value % 2 === 0);
```

### Special Validators

- `isMacAddress()` - Validates MAC addresses

```php
$validator->key('mac')->isMacAddress();
```

## Working with Results

The `validate()` method returns a `ResultInterface` instance:

```php
$result = $validator->validate($data);

// Check if validation passed
if ($result->valid()) {
    // All validations passed
}

// Get all errors
$errors = $result->errors();
// Returns: ['field_name' => 'error message', ...]

// Check for specific field errors
if (isset($errors['email'])) {
    echo $errors['email'];
}
```

## Error Messages

Each validation rule provides a default error message. You can customize the message for required fields:

```php
$validator->required('email', 'Please provide a valid email address')
    ->isEmailAddress();
```

## Examples

### User Registration Form

```php
$validator = new Validator();

// Required fields
$validator->required('username')
    ->isNotBlank()
    ->isString()
    ->isHavingLength(3, 20)
    ->isMatchingWith('~^[a-zA-Z0-9_]+$~');

$validator->required('email')
    ->isNotBlank()
    ->isEmailAddress();

$validator->required('password')
    ->isNotBlank()
    ->isHavingLength(8, 128);

$validator->required('age')
    ->isInteger()
    ->isInRange(18, 120);

// Optional fields
$validator->key('bio')->isString()->isHavingLength(0, 500);
$validator->key('website')->isUrl();
$validator->key('newsletter')->isBoolean();

$result = $validator->validate($_POST);
```

### API Request Validation

```php
$validator = new Validator();

$validator->required('api_key')->isNotBlank();
$validator->required('action')->isOneOf(['create', 'update', 'delete']);
$validator->required('data')->isOfType('array');

// Nested validation
$validator->required('data.id')->isInteger();
$validator->required('data.name')->isNotBlank();
$validator->key('data.metadata')->isOfType('array');

$result = $validator->validate($requestData);
```

## Requirements

- PHP 8.2 or higher

## License

This library is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
