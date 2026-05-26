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
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;

/**
 * Test suite for UtcClock class.
 *
 * Tests cover all aspects of the UTC clock implementation including:
 * - Interface compliance
 * - Inheritance from LocalClock
 * - UTC timezone behavior
 * - Constructor behavior
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class UtcClockTest extends TestCase
{
    /**
     * Tests that UtcClock implements ClockInterface.
     */
    public function test_implements_clock_interface(): void
    {
        $clock = new UtcClock;
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    /**
     * Tests that UtcClock extends LocalClock.
     */
    public function test_extends_local_clock(): void
    {
        $clock = new UtcClock;
        $this->assertInstanceOf(LocalClock::class, $clock);
    }

    /**
     * Tests that now() returns a DateTimeImmutable instance.
     */
    public function test_now_returns_datetime_immutable(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    /**
     * Tests that now() returns time in UTC timezone.
     */
    public function test_now_returns_time_in_utc(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();

        $this->assertEquals('UTC', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() returns the current time.
     */
    public function test_now_returns_current_time(): void
    {
        $clock = new UtcClock;
        $before = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $now = $clock->now();
        $after = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        // The clock time should be between before and after
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $now->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $now->getTimestamp());
    }

    /**
     * Tests that UtcClock is equivalent to LocalClock with UTC timezone.
     */
    public function test_equivalent_to_local_clock_utc(): void
    {
        $utcClock = new UtcClock;
        $localClock = new LocalClock('UTC');

        $utcTime = $utcClock->now();
        \usleep(1000);
        $localTime = $localClock->now();

        // Both should return time in UTC
        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('UTC', $localTime->getTimezone()->getName());
    }

    /**
     * Tests that subsequent calls to now() return different times.
     */
    public function test_now_returns_different_times(): void
    {
        $clock = new UtcClock;
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
        $clock = new UtcClock;
        $now = $clock->now();

        // DateTimeImmutable should have microsecond information
        $microseconds = $now->format('u');
        $this->assertIsString($microseconds);
        $this->assertIsNumeric($microseconds);
        $this->assertGreaterThanOrEqual(0, (int) $microseconds);
        $this->assertLessThanOrEqual(999999, (int) $microseconds);
    }

    /**
     * Tests that multiple instances work correctly.
     */
    public function test_multiple_instances_are_independent(): void
    {
        $clock1 = new UtcClock;
        $clock2 = new UtcClock;

        $time1 = $clock1->now();
        \usleep(1000);
        $time2 = $clock2->now();

        // Both should return current time in UTC
        $this->assertGreaterThanOrEqual($time1->getTimestamp(), $time2->getTimestamp());
        $this->assertEquals('UTC', $time1->getTimezone()->getName());
        $this->assertEquals('UTC', $time2->getTimezone()->getName());
    }

    /**
     * Tests that the returned DateTimeImmutable is immutable.
     */
    public function test_returned_datetime_is_immutable(): void
    {
        $clock = new UtcClock;
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
        $clock = new UtcClock;
        $now = $clock->now();

        // Should be able to format the date
        $formatted = $now->format('Y-m-d H:i:s');
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $formatted);
    }

    /**
     * Tests that now() can be called multiple times.
     */
    public function test_now_can_be_called_multiple_times(): void
    {
        $clock = new UtcClock;

        $times = [];
        for ($i = 0; $i < 10; $i++) {
            $times[] = $clock->now();
            \usleep(100);
        }

        $this->assertCount(10, $times);
        foreach ($times as $time) {
            $this->assertInstanceOf(DateTimeImmutable::class, $time);
            $this->assertEquals('UTC', $time->getTimezone()->getName());
        }
    }

    /**
     * Tests that constructor accepts no parameters.
     */
    public function test_constructor_accepts_no_parameters(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
        $this->assertEquals('UTC', $now->getTimezone()->getName());
    }

    /**
     * Tests that UtcClock always returns UTC regardless of system timezone.
     */
    public function test_always_returns_utc_regardless_of_system_timezone(): void
    {
        $originalTimezone = \date_default_timezone_get();

        try {
            // Change system timezone
            \date_default_timezone_set('America/New_York');
            $clock1 = new UtcClock;
            $time1 = $clock1->now();
            $this->assertEquals('UTC', $time1->getTimezone()->getName());

            \date_default_timezone_set('Asia/Tokyo');
            $clock2 = new UtcClock;
            $time2 = $clock2->now();
            $this->assertEquals('UTC', $time2->getTimezone()->getName());

            \date_default_timezone_set('Europe/London');
            $clock3 = new UtcClock;
            $time3 = $clock3->now();
            $this->assertEquals('UTC', $time3->getTimezone()->getName());
        } finally {
            // Restore original timezone
            \date_default_timezone_set($originalTimezone);
        }
    }

    /**
     * Tests that UtcClock is truly equivalent to LocalClock('UTC').
     */
    public function test_truly_equivalent_to_local_clock_utc(): void
    {
        $utcClock = new UtcClock;
        $localClock = new LocalClock('UTC');

        // Get times at the same moment
        $utcTime = $utcClock->now();
        $localTime = $localClock->now();

        // Should have same timezone
        $this->assertEquals($utcTime->getTimezone()->getName(), $localTime->getTimezone()->getName());

        // Should have same offset (UTC is always 0)
        $this->assertEquals($utcTime->getOffset(), $localTime->getOffset());
        $this->assertEquals(0, $utcTime->getOffset());
    }

    /**
     * Tests that UtcClock works correctly around UTC midnight.
     */
    public function test_works_correctly_around_utc_midnight(): void
    {
        $clock = new UtcClock;

        // Get multiple times to potentially cross midnight
        $times = [];
        for ($i = 0; $i < 10; $i++) {
            $times[] = $clock->now();
            \usleep(100000); // 100ms delay
        }

        foreach ($times as $time) {
            $this->assertEquals('UTC', $time->getTimezone()->getName());
            $hour = (int) $time->format('H');
            $this->assertGreaterThanOrEqual(0, $hour);
            $this->assertLessThanOrEqual(23, $hour);
        }
    }

    /**
     * Tests that UtcClock can be used for ISO 8601 timestamp generation.
     */
    public function test_can_be_used_for_iso8601_timestamp_generation(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();

        // Should format correctly as ISO 8601
        $iso8601 = $now->format('c');
        $this->assertStringEndsWith('+00:00', $iso8601); // UTC offset

        // Should also work with 'Y-m-d\TH:i:s\Z' format
        $iso8601Z = $now->format('Y-m-d\TH:i:s\Z');
        $this->assertStringEndsWith('Z', $iso8601Z);
    }

    /**
     * Tests that UtcClock works correctly with Unix timestamp generation.
     */
    public function test_works_correctly_with_unix_timestamp_generation(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();
        $timestamp = $now->getTimestamp();

        // Should be a valid Unix timestamp
        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);

        // Should match current UTC time
        $currentUtcTimestamp = \time();
        $difference = \abs($timestamp - $currentUtcTimestamp);
        $this->assertLessThanOrEqual(1, $difference);
    }

    /**
     * Tests that UtcClock works correctly for database timestamp storage.
     */
    public function test_works_correctly_for_database_timestamp_storage(): void
    {
        $clock = new UtcClock;
        $now = $clock->now();

        // Common database formats
        $mysqlFormat = $now->format('Y-m-d H:i:s');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $mysqlFormat);

        $postgresFormat = $now->format('Y-m-d H:i:s.u');
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}$/', $postgresFormat);

        // All should be in UTC
        $this->assertEquals('UTC', $now->getTimezone()->getName());
    }
}
