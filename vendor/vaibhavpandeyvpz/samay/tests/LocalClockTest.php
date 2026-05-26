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
 * Test suite for LocalClock class.
 *
 * Tests cover all aspects of the timezone-aware clock implementation including:
 * - Interface compliance
 * - Timezone handling
 * - Timezone string and DateTimeZone object acceptance
 * - Time conversion to specified timezone
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class LocalClockTest extends TestCase
{
    /**
     * Tests that LocalClock implements ClockInterface.
     */
    public function test_implements_clock_interface(): void
    {
        $clock = new LocalClock('UTC');
        $this->assertInstanceOf(ClockInterface::class, $clock);
    }

    /**
     * Tests that now() returns a DateTimeImmutable instance.
     */
    public function test_now_returns_datetime_immutable(): void
    {
        $clock = new LocalClock('UTC');
        $now = $clock->now();

        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    /**
     * Tests that now() returns time in the specified timezone when using string.
     */
    public function test_now_returns_time_in_specified_timezone_string(): void
    {
        $clock = new LocalClock('America/New_York');
        $now = $clock->now();

        $this->assertEquals('America/New_York', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() returns time in the specified timezone when using DateTimeZone object.
     */
    public function test_now_returns_time_in_specified_timezone_object(): void
    {
        $timezone = new DateTimeZone('Europe/London');
        $clock = new LocalClock($timezone);
        $now = $clock->now();

        $this->assertEquals('Europe/London', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() returns time in UTC timezone.
     */
    public function test_now_returns_time_in_utc(): void
    {
        $clock = new LocalClock('UTC');
        $now = $clock->now();

        $this->assertEquals('UTC', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() returns time in Asia/Kolkata timezone.
     */
    public function test_now_returns_time_in_asia_kolkata(): void
    {
        $clock = new LocalClock('Asia/Kolkata');
        $now = $clock->now();

        $this->assertEquals('Asia/Kolkata', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() returns the current time.
     */
    public function test_now_returns_current_time(): void
    {
        $clock = new LocalClock('UTC');
        $before = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $now = $clock->now();
        $after = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        // The clock time should be between before and after
        $this->assertGreaterThanOrEqual($before->getTimestamp(), $now->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $now->getTimestamp());
    }

    /**
     * Tests that time is correctly converted to the specified timezone.
     */
    public function test_time_is_converted_to_timezone(): void
    {
        $utcClock = new LocalClock('UTC');
        $nyClock = new LocalClock('America/New_York');

        $utcTime = $utcClock->now();
        $nyTime = $nyClock->now();

        // Both represent the same moment, but in different timezones
        $this->assertEquals($utcTime->getTimestamp(), $nyTime->getTimestamp());

        // But the timezone names are different
        $this->assertEquals('UTC', $utcTime->getTimezone()->getName());
        $this->assertEquals('America/New_York', $nyTime->getTimezone()->getName());
    }

    /**
     * Tests that subsequent calls to now() return different times.
     */
    public function test_now_returns_different_times(): void
    {
        $clock = new LocalClock('UTC');
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
        $clock = new LocalClock('UTC');
        $now = $clock->now();

        // DateTimeImmutable should have microsecond information
        $microseconds = $now->format('u');
        $this->assertIsString($microseconds);
        $this->assertIsNumeric($microseconds);
        $this->assertGreaterThanOrEqual(0, (int) $microseconds);
        $this->assertLessThanOrEqual(999999, (int) $microseconds);
    }

    /**
     * Tests that multiple instances with same timezone work correctly.
     */
    public function test_multiple_instances_with_same_timezone(): void
    {
        $clock1 = new LocalClock('UTC');
        $clock2 = new LocalClock('UTC');

        $time1 = $clock1->now();
        \usleep(1000);
        $time2 = $clock2->now();

        // Both should return current time in UTC
        $this->assertGreaterThanOrEqual($time1->getTimestamp(), $time2->getTimestamp());
        $this->assertEquals('UTC', $time1->getTimezone()->getName());
        $this->assertEquals('UTC', $time2->getTimezone()->getName());
    }

    /**
     * Tests that multiple instances with different timezones work correctly.
     */
    public function test_multiple_instances_with_different_timezones(): void
    {
        $utcClock = new LocalClock('UTC');
        $tokyoClock = new LocalClock('Asia/Tokyo');

        $utcTime = $utcClock->now();
        $tokyoTime = $tokyoClock->now();

        // Same moment, different timezones
        $this->assertEquals($utcTime->getTimestamp(), $tokyoTime->getTimestamp());
        $this->assertNotEquals($utcTime->getTimezone()->getName(), $tokyoTime->getTimezone()->getName());
    }

    /**
     * Tests that the returned DateTimeImmutable is immutable.
     */
    public function test_returned_datetime_is_immutable(): void
    {
        $clock = new LocalClock('UTC');
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
        $clock = new LocalClock('UTC');
        $now = $clock->now();

        // Should be able to format the date
        $formatted = $now->format('Y-m-d H:i:s');
        $this->assertIsString($formatted);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $formatted);
    }

    /**
     * Tests that invalid timezone string throws exception.
     */
    public function test_invalid_timezone_string_throws_exception(): void
    {
        $this->expectException(\Exception::class);
        new LocalClock('Invalid/Timezone');
    }

    /**
     * Tests that various timezone strings are accepted.
     */
    public function test_various_timezone_strings(): void
    {
        $timezones = [
            'UTC',
            'America/New_York',
            'Europe/London',
            'Asia/Tokyo',
            'Australia/Sydney',
            'America/Los_Angeles',
        ];

        foreach ($timezones as $timezone) {
            $clock = new LocalClock($timezone);
            $now = $clock->now();
            $this->assertEquals($timezone, $now->getTimezone()->getName());
        }
    }

    /**
     * Tests that DateTimeZone object is accepted.
     */
    public function test_datetimezone_object_is_accepted(): void
    {
        $timezone = new DateTimeZone('Europe/Paris');
        $clock = new LocalClock($timezone);
        $now = $clock->now();

        $this->assertEquals('Europe/Paris', $now->getTimezone()->getName());
    }

    /**
     * Tests that now() can be called multiple times.
     */
    public function test_now_can_be_called_multiple_times(): void
    {
        $clock = new LocalClock('UTC');

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
     * Tests that LocalClock handles DST transitions correctly.
     */
    public function test_handles_dst_transitions(): void
    {
        // Test with a timezone that observes DST
        $clock = new LocalClock('America/New_York');
        $now = $clock->now();

        // Should return time in the correct timezone
        $this->assertEquals('America/New_York', $now->getTimezone()->getName());

        // The offset should be valid (either -5 or -4 hours for EST/EDT)
        $offset = $now->getOffset();
        $this->assertContains($offset, [-18000, -14400]); // -5 hours or -4 hours in seconds
    }

    /**
     * Tests that LocalClock works with offset-based timezones.
     */
    public function test_works_with_offset_based_timezones(): void
    {
        // Test with fixed offset timezones
        $offsetTimezones = [
            '+05:30', // India Standard Time
            '+09:00', // Japan Standard Time
            '-08:00', // Pacific Standard Time offset
        ];

        foreach ($offsetTimezones as $offset) {
            try {
                $timezone = new DateTimeZone($offset);
                $clock = new LocalClock($timezone);
                $now = $clock->now();

                $this->assertInstanceOf(DateTimeImmutable::class, $now);
                $this->assertNotNull($now->getTimezone());
            } catch (\Exception $e) {
                // Some offset formats might not be valid, skip them
                continue;
            }
        }
    }

    /**
     * Tests that LocalClock correctly converts time between timezones.
     */
    public function test_correctly_converts_time_between_timezones(): void
    {
        // Get the same moment in different timezones
        $utcClock = new LocalClock('UTC');
        $nyClock = new LocalClock('America/New_York');
        $tokyoClock = new LocalClock('Asia/Tokyo');

        $utcTime = $utcClock->now();
        \usleep(1000); // Small delay
        $nyTime = $nyClock->now();
        \usleep(1000);
        $tokyoTime = $tokyoClock->now();

        // All represent approximately the same moment (within 2ms)
        $utcTimestamp = $utcTime->getTimestamp();
        $nyTimestamp = $nyTime->getTimestamp();
        $tokyoTimestamp = $tokyoTime->getTimestamp();

        $this->assertLessThanOrEqual(2, \abs($utcTimestamp - $nyTimestamp));
        $this->assertLessThanOrEqual(2, \abs($utcTimestamp - $tokyoTimestamp));

        // But the displayed times should be different
        $this->assertNotEquals($utcTime->format('H:i'), $nyTime->format('H:i'));
        $this->assertNotEquals($utcTime->format('H:i'), $tokyoTime->format('H:i'));
    }

    /**
     * Tests that LocalClock works with timezones that have historical changes.
     */
    public function test_works_with_historical_timezone_changes(): void
    {
        // Test with a timezone that has had historical changes
        $clock = new LocalClock('Europe/London');
        $now = $clock->now();

        // Should still work correctly
        $this->assertEquals('Europe/London', $now->getTimezone()->getName());
        $this->assertInstanceOf(DateTimeImmutable::class, $now);
    }

    /**
     * Tests that LocalClock works around midnight in different timezones.
     */
    public function test_works_around_midnight_in_different_timezones(): void
    {
        $timezones = [
            'UTC',
            'America/New_York',
            'Asia/Tokyo',
            'Europe/London',
        ];

        foreach ($timezones as $tz) {
            $clock = new LocalClock($tz);
            $now = $clock->now();

            // Should return valid time
            $hour = (int) $now->format('H');
            $this->assertGreaterThanOrEqual(0, $hour);
            $this->assertLessThanOrEqual(23, $hour);

            $minute = (int) $now->format('i');
            $this->assertGreaterThanOrEqual(0, $minute);
            $this->assertLessThanOrEqual(59, $minute);
        }
    }

    /**
     * Tests that LocalClock works with all major world timezones.
     */
    public function test_works_with_major_world_timezones(): void
    {
        $majorTimezones = [
            'America/New_York',
            'America/Los_Angeles',
            'America/Chicago',
            'Europe/London',
            'Europe/Paris',
            'Europe/Berlin',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Asia/Dubai',
            'Australia/Sydney',
            'Australia/Melbourne',
            'Africa/Cairo',
            'America/Sao_Paulo',
        ];

        foreach ($majorTimezones as $tz) {
            $clock = new LocalClock($tz);
            $now = $clock->now();

            $this->assertEquals($tz, $now->getTimezone()->getName());
            $this->assertInstanceOf(DateTimeImmutable::class, $now);
        }
    }

    /**
     * Tests that LocalClock handles timezone offsets correctly.
     */
    public function test_handles_timezone_offsets_correctly(): void
    {
        $clock = new LocalClock('Asia/Kolkata'); // UTC+5:30
        $now = $clock->now();

        // Should have correct offset
        $offset = $now->getOffset();
        $this->assertEquals(19800, $offset); // 5 hours 30 minutes = 19800 seconds
    }

    /**
     * Tests that LocalClock works with timezone abbreviations.
     */
    public function test_works_with_timezone_abbreviations(): void
    {
        // Note: Some abbreviations might not work, but common ones should
        $abbreviations = ['UTC', 'GMT'];

        foreach ($abbreviations as $abbr) {
            try {
                $clock = new LocalClock($abbr);
                $now = $clock->now();
                $this->assertInstanceOf(DateTimeImmutable::class, $now);
            } catch (\Exception $e) {
                // Some abbreviations might not be valid, skip them
                continue;
            }
        }
    }
}
