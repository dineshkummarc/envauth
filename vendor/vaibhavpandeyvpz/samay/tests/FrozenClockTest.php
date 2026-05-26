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
 * Test suite for FrozenClock class.
 *
 * Tests cover all aspects of the frozen clock implementation including:
 * - Interface compliance
 * - Fixed time behavior
 * - Deterministic time values
 * - Construction with and without time parameter
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class FrozenClockTest extends TestCase
{
    /**
     * Tests that FrozenClock implements ClockInterface.
     */
    public function test_implements_clock_interface(): void
    {
        $clock = new FrozenClock;
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    /**
     * Tests that now() returns a DateTimeImmutable instance.
     */
    public function test_now_returns_datetime_immutable(): void
    {
        $clock = new FrozenClock;
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    /**
     * Tests that now() returns the same time when no time is provided in constructor.
     */
    public function test_now_returns_same_time_when_no_time_provided(): void
    {
        $clock = new FrozenClock;
        $first = $clock->now();
        \usleep(1000);
        $second = $clock->now();

        $this->assertEquals($first->getTimestamp(), $second->getTimestamp());
    }

    /**
     * Tests that now() returns the frozen time when time is provided in constructor.
     */
    public function test_now_returns_frozen_time(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);
        $now = $clock->now();

        $this->assertEquals($frozenTime->getTimestamp(), $now->getTimestamp());
        $this->assertEquals($frozenTime->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
    }

    /**
     * Tests that multiple calls to now() return the same time.
     */
    public function test_multiple_calls_return_same_time(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);

        $times = [];
        for ($i = 0; $i < 10; $i++) {
            $times[] = $clock->now();
            \usleep(100);
        }

        foreach ($times as $time) {
            $this->assertEquals($frozenTime->getTimestamp(), $time->getTimestamp());
        }
    }

    /**
     * Tests that different instances with same time return same time.
     */
    public function test_different_instances_with_same_time(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock1 = new FrozenClock($frozenTime);
        $clock2 = new FrozenClock($frozenTime);

        $this->assertEquals($clock1->now()->getTimestamp(), $clock2->now()->getTimestamp());
    }

    /**
     * Tests that different instances with different times return different times.
     */
    public function test_different_instances_with_different_times(): void
    {
        $time1 = new DateTimeImmutable('2024-01-15 10:30:45');
        $time2 = new DateTimeImmutable('2024-01-16 11:30:45');
        $clock1 = new FrozenClock($time1);
        $clock2 = new FrozenClock($time2);

        $this->assertNotEquals($clock1->now()->getTimestamp(), $clock2->now()->getTimestamp());
    }

    /**
     * Tests that the frozen time can be in the past.
     */
    public function test_frozen_time_can_be_in_past(): void
    {
        $pastTime = new DateTimeImmutable('2000-01-01 00:00:00');
        $clock = new FrozenClock($pastTime);
        $now = $clock->now();

        $this->assertLessThan((new DateTimeImmutable)->getTimestamp(), $now->getTimestamp());
        $this->assertEquals($pastTime->getTimestamp(), $now->getTimestamp());
    }

    /**
     * Tests that the frozen time can be in the future.
     */
    public function test_frozen_time_can_be_in_future(): void
    {
        $futureTime = new DateTimeImmutable('2100-01-01 00:00:00');
        $clock = new FrozenClock($futureTime);
        $now = $clock->now();

        $this->assertGreaterThan((new DateTimeImmutable)->getTimestamp(), $now->getTimestamp());
        $this->assertEquals($futureTime->getTimestamp(), $now->getTimestamp());
    }

    /**
     * Tests that the frozen time preserves timezone information.
     */
    public function test_frozen_time_preserves_timezone(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45', new \DateTimeZone('UTC'));
        $clock = new FrozenClock($frozenTime);
        $now = $clock->now();

        $this->assertEquals($frozenTime->getTimezone()->getName(), $now->getTimezone()->getName());
    }

    /**
     * Tests that the frozen time preserves microsecond precision.
     */
    public function test_frozen_time_preserves_microseconds(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45.123456');
        $clock = new FrozenClock($frozenTime);
        $now = $clock->now();

        $this->assertEquals($frozenTime->format('u'), $now->format('u'));
    }

    /**
     * Tests that now() returns a DateTimeImmutable instance with the same time.
     */
    public function test_now_returns_datetime_with_same_time(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);

        $first = $clock->now();
        $second = $clock->now();

        // Should have same timestamp (may be same instance due to readonly property)
        $this->assertEquals($first->getTimestamp(), $second->getTimestamp());
        $this->assertEquals($frozenTime->getTimestamp(), $first->getTimestamp());
        $this->assertEquals($frozenTime->getTimestamp(), $second->getTimestamp());
    }

    /**
     * Tests that the returned DateTimeImmutable is immutable.
     */
    public function test_returned_datetime_is_immutable(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);
        $now = $clock->now();
        $originalTimestamp = $now->getTimestamp();

        // Attempting to modify should create a new instance
        $modified = $now->modify('+1 day');
        $this->assertNotSame($now, $modified);
        $this->assertEquals($originalTimestamp, $now->getTimestamp());
    }

    /**
     * Tests that construction without time uses current time as frozen value.
     */
    public function test_construction_without_time_uses_current_time(): void
    {
        $before = new DateTimeImmutable;
        $clock = new FrozenClock;
        $frozen = $clock->now();
        $after = new DateTimeImmutable;

        // The frozen time should be between before and after
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $frozen->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $frozen->getTimestamp());
    }

    /**
     * Tests that the frozen time remains constant even after system time changes.
     */
    public function test_frozen_time_remains_constant(): void
    {
        $frozenTime = new DateTimeImmutable('2024-01-15 10:30:45');
        $clock = new FrozenClock($frozenTime);

        $first = $clock->now();
        \sleep(1); // Wait 1 second
        $second = $clock->now();

        // Should still return the same frozen time
        $this->assertEquals($first->getTimestamp(), $second->getTimestamp());
        $this->assertEquals($frozenTime->getTimestamp(), $second->getTimestamp());
    }

    /**
     * Tests that now() works correctly with edge case dates.
     */
    public function test_now_works_with_edge_case_dates(): void
    {
        $edgeCases = [
            new DateTimeImmutable('1970-01-01 00:00:00'), // Unix epoch
            new DateTimeImmutable('2038-01-19 03:14:07'), // 32-bit Unix timestamp limit
            new DateTimeImmutable('9999-12-31 23:59:59'), // Far future
        ];

        foreach ($edgeCases as $edgeCase) {
            $clock = new FrozenClock($edgeCase);
            $now = $clock->now();

            $this->assertEquals($edgeCase->getTimestamp(), $now->getTimestamp());
        }
    }

    /**
     * Tests that frozen clock works with leap year dates.
     */
    public function test_frozen_clock_works_with_leap_years(): void
    {
        $leapYearDates = [
            new DateTimeImmutable('2024-02-29 12:00:00'), // 2024 is a leap year
            new DateTimeImmutable('2000-02-29 12:00:00'), // 2000 is a leap year
            new DateTimeImmutable('2020-02-29 12:00:00'), // 2020 is a leap year
        ];

        foreach ($leapYearDates as $leapDate) {
            $clock = new FrozenClock($leapDate);
            $now = $clock->now();

            $this->assertEquals($leapDate->format('Y-m-d'), $now->format('Y-m-d'));
            $this->assertEquals('29', $now->format('d'));
        }
    }

    /**
     * Tests that frozen clock works with DST transition dates.
     */
    public function test_frozen_clock_works_with_dst_transitions(): void
    {
        // Spring forward in US (2 AM becomes 3 AM)
        $springForward = new DateTimeImmutable('2024-03-10 07:00:00', new \DateTimeZone('America/New_York'));
        $clock1 = new FrozenClock($springForward);
        $this->assertEquals($springForward->getTimestamp(), $clock1->now()->getTimestamp());

        // Fall back in US (3 AM becomes 2 AM)
        $fallBack = new DateTimeImmutable('2024-11-03 06:00:00', new \DateTimeZone('America/New_York'));
        $clock2 = new FrozenClock($fallBack);
        $this->assertEquals($fallBack->getTimestamp(), $clock2->now()->getTimestamp());
    }

    /**
     * Tests that frozen clock preserves timezone offsets correctly.
     */
    public function test_frozen_clock_preserves_timezone_offsets(): void
    {
        $timezones = [
            new \DateTimeZone('UTC'),
            new \DateTimeZone('America/New_York'),
            new \DateTimeZone('Asia/Tokyo'),
            new \DateTimeZone('Europe/London'),
        ];

        foreach ($timezones as $tz) {
            $frozenTime = new DateTimeImmutable('2024-06-15 12:00:00', $tz);
            $clock = new FrozenClock($frozenTime);
            $now = $clock->now();

            $this->assertEquals($frozenTime->getTimezone()->getName(), $now->getTimezone()->getName());
            $this->assertEquals($frozenTime->getOffset(), $now->getOffset());
        }
    }

    /**
     * Tests that frozen clock works with very precise microsecond values.
     */
    public function test_frozen_clock_works_with_precise_microseconds(): void
    {
        $preciseTimes = [
            new DateTimeImmutable('2024-01-15 10:30:45.123456'),
            new DateTimeImmutable('2024-01-15 10:30:45.000001'),
            new DateTimeImmutable('2024-01-15 10:30:45.999999'),
        ];

        foreach ($preciseTimes as $preciseTime) {
            $clock = new FrozenClock($preciseTime);
            $now = $clock->now();

            $this->assertEquals($preciseTime->format('u'), $now->format('u'));
            $this->assertEquals($preciseTime->getTimestamp(), $now->getTimestamp());
        }
    }

    /**
     * Tests that frozen clock works with year boundaries.
     */
    public function test_frozen_clock_works_with_year_boundaries(): void
    {
        $yearBoundaries = [
            new DateTimeImmutable('2023-12-31 23:59:59'),
            new DateTimeImmutable('2024-01-01 00:00:00'),
            new DateTimeImmutable('2024-12-31 23:59:59'),
            new DateTimeImmutable('2025-01-01 00:00:00'),
        ];

        foreach ($yearBoundaries as $boundary) {
            $clock = new FrozenClock($boundary);
            $now = $clock->now();

            $this->assertEquals($boundary->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));
        }
    }

    /**
     * Tests that frozen clock works with month boundaries.
     */
    public function test_frozen_clock_works_with_month_boundaries(): void
    {
        $monthBoundaries = [
            new DateTimeImmutable('2024-01-31 23:59:59'), // January to February
            new DateTimeImmutable('2024-02-01 00:00:00'),
            new DateTimeImmutable('2024-02-29 23:59:59'), // Leap year February
            new DateTimeImmutable('2024-03-01 00:00:00'),
        ];

        foreach ($monthBoundaries as $boundary) {
            $clock = new FrozenClock($boundary);
            $now = $clock->now();

            $this->assertEquals($boundary->format('Y-m-d'), $now->format('Y-m-d'));
        }
    }
}
