# Samay

[![Latest Version](https://img.shields.io/packagist/v/vaibhavpandeyvpz/samay.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/samay)
[![Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/samay.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/samay)
[![PHP Version](https://img.shields.io/packagist/php-v/vaibhavpandeyvpz/samay.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/samay)
[![License](https://img.shields.io/packagist/l/vaibhavpandeyvpz/samay.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/vaibhavpandeyvpz/samay/tests.yml?branch=master&style=flat-square)](https://github.com/vaibhavpandeyvpz/samay/actions)

A lightweight, [PSR-20](https://www.php-fig.org/psr/psr-20/) compliant clock library with zero dependencies other than the PSR-20 interface.

> **Samay** (`समय`) - Sanskrit for "Time", representing the flow of moments and the measurement of temporal events.

## Features

- ✅ **PSR-20 Compliant** - Full implementation of the PSR-20 ClockInterface specification
- ✅ **Zero Dependencies** - Only requires PSR-20 interface (psr/clock)
- ✅ **System Clock** - Get current system time with microsecond precision
- ✅ **Local Clock** - Timezone-aware clock for specific timezones
- ✅ **UTC Clock** - Convenient UTC timezone clock
- ✅ **Frozen Clock** - Deterministic clock for testing scenarios
- ✅ **Modern PHP** - Built for PHP 8.2+ with modern language features
- ✅ **100% Test Coverage** - Fully tested with comprehensive test suite

## Installation

Install via Composer:

```bash
composer require vaibhavpandeyvpz/samay
```

## Quick Start

```php
<?php

use Samay\SystemClock;

// Create a new clock instance
$clock = new SystemClock();

// Get the current time
$now = $clock->now();

echo $now->format('Y-m-d H:i:s'); // Output: 2024-01-15 10:30:45
```

## Usage

### SystemClock

The `SystemClock` class provides access to the current system time. It implements the PSR-20 `ClockInterface` and returns a `DateTimeImmutable` instance representing the current moment.

```php
use Samay\SystemClock;

$clock = new SystemClock();
$now = $clock->now();

// $now is a DateTimeImmutable instance
echo $now->format('Y-m-d H:i:s');
echo $now->getTimestamp();
```

#### Features

- Returns the current system time with microsecond precision
- Each call to `now()` returns the current time (time progresses)
- Uses the system's default timezone
- Returns immutable `DateTimeImmutable` instances

#### Example: Using SystemClock in a Service

```php
use Samay\SystemClock;
use Psr\Clock\ClockInterface;

class OrderService
{
    public function __construct(
        private readonly ClockInterface $clock = new SystemClock()
    ) {
    }

    public function createOrder(array $items): Order
    {
        $order = new Order();
        $order->setCreatedAt($this->clock->now());
        $order->setItems($items);

        return $order;
    }
}
```

### FrozenClock

The `FrozenClock` class provides a deterministic clock that always returns the same time. This is extremely useful for testing scenarios where you need predictable, reproducible results.

```php
use Samay\FrozenClock;
use DateTimeImmutable;

// Freeze at a specific time
$frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
$clock = new FrozenClock($frozenTime);

$now = $clock->now();
echo $now->format('Y-m-d H:i:s'); // Always: 2024-01-15 10:30:45

// Even after waiting, it returns the same time
sleep(5);
$later = $clock->now();
echo $later->format('Y-m-d H:i:s'); // Still: 2024-01-15 10:30:45
```

#### Construction Options

```php
use Samay\FrozenClock;
use DateTimeImmutable;

// Freeze at a specific time
$clock1 = new FrozenClock(new DateTimeImmutable('2024-01-15 10:30:45'));

// Freeze at current time (captures the moment of construction)
$clock2 = new FrozenClock();

// Freeze at a time with timezone
$clock3 = new FrozenClock(
    new DateTimeImmutable('2024-01-15 10:30:45', new \DateTimeZone('UTC'))
);
```

#### Example: Using FrozenClock in Tests

```php
use PHPUnit\Framework\TestCase;
use Samay\FrozenClock;
use Samay\SystemClock;
use DateTimeImmutable;

class OrderServiceTest extends TestCase
{
    public function test_order_created_at_time(): void
    {
        // Freeze time for deterministic testing
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);

        $service = new OrderService($clock);
        $order = $service->createOrder(['item1', 'item2']);

        // Assert exact time
        $this->assertEquals($frozenTime, $order->getCreatedAt());
    }

    public function test_order_expires_after_30_days(): void
    {
        $createdAt = new DateTimeImmutable('2024-01-01 00:00:00');
        $clock = new FrozenClock($createdAt);

        $service = new OrderService($clock);
        $order = $service->createOrder(['item1']);

        // Fast-forward time
        $expiryTime = $createdAt->modify('+30 days');
        $expiredClock = new FrozenClock($expiryTime);

        $this->assertTrue($order->isExpired($expiredClock->now()));
    }
}
```

### LocalClock

The `LocalClock` class provides a timezone-aware clock that returns the current time in a specific timezone. It accepts either a timezone string or a `DateTimeZone` object.

```php
use Samay\LocalClock;
use DateTimeZone;

// Using timezone string
$clock = new LocalClock('America/New_York');
$now = $clock->now();

echo $now->getTimezone()->getName(); // "America/New_York"
echo $now->format('Y-m-d H:i:s'); // Current time in New York timezone

// Using DateTimeZone object
$timezone = new DateTimeZone('Europe/London');
$clock = new LocalClock($timezone);
$now = $clock->now();

echo $now->getTimezone()->getName(); // "Europe/London"
```

#### Features

- Returns the current system time converted to the specified timezone
- Accepts timezone as string or `DateTimeZone` object
- Each call to `now()` returns the current time (time progresses)
- Returns immutable `DateTimeImmutable` instances with the correct timezone

#### Example: Using LocalClock for Regional Services

```php
use Samay\LocalClock;
use Psr\Clock\ClockInterface;

class RegionalService
{
    public function __construct(
        private readonly ClockInterface $clock
    ) {
    }

    public function getLocalTime(): string
    {
        return $this->clock->now()->format('Y-m-d H:i:s T');
    }
}

// For New York office
$nyService = new RegionalService(new LocalClock('America/New_York'));

// For Tokyo office
$tokyoService = new RegionalService(new LocalClock('Asia/Tokyo'));
```

### UtcClock

The `UtcClock` class extends `LocalClock` and provides a convenient way to always get the current time in UTC. It's equivalent to using `LocalClock` with `'UTC'` timezone.

```php
use Samay\UtcClock;

$clock = new UtcClock();
$now = $clock->now();

echo $now->getTimezone()->getName(); // "UTC"
echo $now->format('Y-m-d H:i:s'); // Current time in UTC
```

#### Features

- Always returns time in UTC timezone
- Extends `LocalClock` for consistency
- No constructor parameters required
- Ideal for applications that need UTC time

#### Example: Using UtcClock for Timestamp Generation

```php
use Samay\UtcClock;
use Psr\Clock\ClockInterface;

class ApiService
{
    public function __construct(
        private readonly ClockInterface $clock = new UtcClock()
    ) {
    }

    public function createTimestamp(): string
    {
        // Always use UTC for API timestamps
        return $this->clock->now()->format('Y-m-d\TH:i:s\Z');
    }
}

$service = new ApiService();
echo $service->createTimestamp(); // e.g., "2024-01-15T10:30:45Z"
```

### Dependency Injection

Since all clock implementations (`SystemClock`, `LocalClock`, `UtcClock`, and `FrozenClock`) implement `Psr\Clock\ClockInterface`, you can easily inject them into your services:

```php
use Psr\Clock\ClockInterface;
use Samay\SystemClock;

class EventLogger
{
    public function __construct(
        private readonly ClockInterface $clock = new SystemClock()
    ) {
    }

    public function log(string $message): void
    {
        $timestamp = $this->clock->now()->format('Y-m-d H:i:s');
        echo "[$timestamp] $message\n";
    }
}

// In production
$logger = new EventLogger(new SystemClock());

// In tests
$logger = new EventLogger(new FrozenClock(new DateTimeImmutable('2024-01-15 10:30:45')));
```

### Timezone Handling

All clock implementations handle timezones appropriately:

```php
use Samay\SystemClock;
use Samay\LocalClock;
use Samay\UtcClock;
use DateTimeImmutable;
use DateTimeZone;

// SystemClock uses system default timezone
$systemClock = new SystemClock();
$now = $systemClock->now();
echo $now->getTimezone()->getName(); // e.g., "America/New_York" (system default)

// LocalClock uses specified timezone
$localClock = new LocalClock('Asia/Tokyo');
$tokyoTime = $localClock->now();
echo $tokyoTime->getTimezone()->getName(); // "Asia/Tokyo"

// UtcClock always uses UTC
$utcClock = new UtcClock();
$utcTime = $utcClock->now();
echo $utcTime->getTimezone()->getName(); // "UTC"

// FrozenClock preserves timezone from the frozen time
$frozenTime = new DateTimeImmutable('2024-01-15 10:30:45', new DateTimeZone('UTC'));
$frozenClock = new FrozenClock($frozenTime);
$frozen = $frozenClock->now();
echo $frozen->getTimezone()->getName(); // "UTC"
```

### Microsecond Precision

All clocks support microsecond precision:

```php
use Samay\SystemClock;
use Samay\LocalClock;
use Samay\UtcClock;

$systemClock = new SystemClock();
$now = $systemClock->now();
echo $now->format('Y-m-d H:i:s.u'); // e.g., "2024-01-15 10:30:45.123456"

$localClock = new LocalClock('UTC');
$utcNow = $localClock->now();
echo $utcNow->format('Y-m-d H:i:s.u'); // e.g., "2024-01-15 10:30:45.123456"

$utcClock = new UtcClock();
$utcTime = $utcClock->now();
echo $utcTime->format('Y-m-d H:i:s.u'); // e.g., "2024-01-15 10:30:45.123456"
```

## PSR-20 Compliance

This library fully implements the PSR-20 `ClockInterface` specification:

```php
namespace Psr\Clock;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
```

All clock implementations (`SystemClock`, `LocalClock`, `UtcClock`, and `FrozenClock`) implement this interface, ensuring compatibility with any PSR-20 compliant code.

## Requirements

- PHP 8.2 or higher
- PSR-20 interface (psr/clock)

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/vaibhavpandeyvpz/samay).
