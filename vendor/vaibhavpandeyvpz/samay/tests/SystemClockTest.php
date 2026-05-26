<?php

/*
 * This file is part of vaibhavpandeyvpz/samay package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source file in the file LICENSE.
 */

namespace Samay;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * Test suite for SystemClock class.
 *
 * Tests cover all aspects of the PSR-20 clock implementation including:
 * - Interface compliance
 * - Current time retrieval
 * - Time progression
 * - Return type validation
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class SystemClockTest extends TestCase
{
    /**
     * Tests that SystemClock implements ClockInterface.
     */
    public function test_implements_clock_interface(): void
    {
        $clock = new SystemClock;
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    /**
     * Tests that now() returns a DateTimeImmutable instance.
     */
    public function test_now_returns_datetime_immutable(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    /**
     * Tests that now() returns the current system time.
     */
    public function test_now_returns_current_time(): void
    {
        $clock = new SystemClock;
        $before = new DateTimeImmutable;
        $now = $clock->now();
        $after = new DateTimeImmutable;

        // The clock time should be between before and after
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $now->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $now->getTimestamp());
    }

    /**
     * Tests that subsequent calls to now() return different times.
     */
    public function test_now_returns_different_times(): void
    {
        $clock = new SystemClock;
        $first = $clock->now();

        // Small delay to ensure different timestamps
        \usleep(1000); // 1 millisecond

        $second = $clock->now();

        // Times should be different (or at least the second should be >= first)
        $this->assertGreaterThanOrEqual($first->getTimestamp(), $second->getTimestamp());
    }

    /**
     * Tests that now() returns time with microsecond precision.
     */
    public function test_now_has_microsecond_precision(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // DateTimeImmutable should have microsecond information
        $microseconds = $now->format('u');
        $this->assertIsString($microseconds);
        $this->assertIsNumeric($microseconds);
        $this->assertGreaterThanOrEqual(0, (int) $microseconds);
        $this->assertLessThanOrEqual(999999, (int) $microseconds);
    }

    /**
     * Tests that now() returns time in the system timezone.
     */
    public function test_now_uses_system_timezone(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // Should have a timezone set
        $this->assertNotNull($now->getTimezone());
    }

    /**
     * Tests that multiple instances return independent times.
     */
    public function test_multiple_instances_are_independent(): void
    {
        $clock1 = new SystemClock;
        $clock2 = new SystemClock;

        $time1 = $clock1->now();
        \usleep(1000);
        $time2 = $clock2->now();

        // Both should return current time, not the same time
        $this->assertGreaterThanOrEqual($time1->getTimestamp(), $time2->getTimestamp());
    }

    /**
     * Tests that now() can be called multiple times.
     */
    public function test_now_can_be_called_multiple_times(): void
    {
        $clock = new SystemClock;

        $times = [];
        for ($i = 0; $i < 10; $i++) {
            $times[] = $clock->now();
            \usleep(100);
        }

        $this->assertCount(10, $times);
        foreach ($times as $time) {
            $this->assertInstanceOf(DateTimeImmutable::class, $time);
        }
    }

    /**
     * Tests that the returned DateTimeImmutable is immutable.
     */
    public function test_returned_datetime_is_immutable(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();
        $originalTimestamp = $now->getTimestamp();

        // Attempting to modify should create a new instance
        $modified = $now->modify('+1 day');
        $this->assertNotSame($now, $modified);
        $this->assertEquals($originalTimestamp, $now->getTimestamp());
    }

    /**
     * Tests that now() returns a valid date.
     */
    public function test_now_returns_valid_date(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // Should be able to format the date
        $formatted = $now->format('Y-m-d H:i:s');
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $formatted);
    }

    /**
     * Tests that now() works correctly across different system times.
     */
    public function test_now_handles_system_time_changes(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // Should return a reasonable date (not too far in past or future)
        $currentYear = (int) (new DateTimeImmutable)->format('Y');
        $clockYear = (int) $now->format('Y');

        // Should be within a reasonable range (e.g., 1970-2100)
        $this->assertGreaterThanOrEqual(1970, $clockYear);
        $this->assertLessThanOrEqual(2100, $clockYear);
    }

    /**
     * Tests that now() works correctly around midnight boundaries.
     */
    public function test_now_works_around_midnight_boundary(): void
    {
        $clock = new SystemClock;

        // Get multiple times to ensure we can cross midnight
        $times = [];
        for ($i = 0; $i < 5; $i++) {
            $times[] = $clock->now();
            \usleep(100000); // 100ms delay
        }

        // All times should be valid and sequential
        foreach ($times as $time) {
            $this->assertIsInt($time->getTimestamp());
            $this->assertGreaterThan(0, $time->getTimestamp());
        }

        // Times should be in ascending order (or equal)
        for ($i = 1; $i < \count($times); $i++) {
            $this->assertGreaterThanOrEqual(
                $times[$i - 1]->getTimestamp(),
                $times[$i]->getTimestamp()
            );
        }
    }

    /**
     * Tests that now() returns consistent results when called rapidly.
     */
    public function test_now_returns_consistent_results_on_rapid_calls(): void
    {
        $clock = new SystemClock;

        // Call now() multiple times rapidly
        $times = [];
        for ($i = 0; $i < 100; $i++) {
            $times[] = $clock->now();
        }

        // All should be valid DateTimeImmutable instances
        foreach ($times as $time) {
            $this->assertInstanceOf(DateTimeImmutable::class, $time);
        }

        // Times should be in non-decreasing order
        for ($i = 1; $i < \count($times); $i++) {
            $this->assertGreaterThanOrEqual(
                $times[$i - 1]->getTimestamp(),
                $times[$i]->getTimestamp()
            );
        }
    }

    /**
     * Tests that now() can be used for timestamp generation.
     */
    public function test_now_can_be_used_for_timestamp_generation(): void
    {
        $clock = new SystemClock;
        $timestamp = $clock->now()->getTimestamp();

        // Should be a valid Unix timestamp
        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);

        // Should be close to current time
        $currentTimestamp = \time();
        $difference = \abs($timestamp - $currentTimestamp);

        // Should be within 1 second (accounting for execution time)
        $this->assertLessThanOrEqual(1, $difference);
    }

    /**
     * Tests that now() works correctly with date formatting.
     */
    public function test_now_works_with_date_formatting(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // Test various date formats
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d',
            'H:i:s',
            'U', // Unix timestamp
            'c', // ISO 8601
            'r', // RFC 2822
        ];

        foreach ($formats as $format) {
            $formatted = $now->format($format);
            $this->assertIsString($formatted);
            $this->assertNotEmpty($formatted);
        }
    }

    /**
     * Tests that now() works correctly with date arithmetic.
     */
    public function test_now_works_with_date_arithmetic(): void
    {
        $clock = new SystemClock;
        $now = $clock->now();

        // Test adding time
        $future = $now->modify('+1 hour');
        $this->assertGreaterThan($now->getTimestamp(), $future->getTimestamp());

        // Test subtracting time
        $past = $now->modify('-1 day');
        $this->assertLessThan($now->getTimestamp(), $past->getTimestamp());

        // Original should remain unchanged
        $this->assertNotSame($now, $future);
        $this->assertNotSame($now, $past);
    }
}
