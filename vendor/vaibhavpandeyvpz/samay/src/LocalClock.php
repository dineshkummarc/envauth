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
use Psr\Clock\ClockInterface;

/**
 * Local clock implementation that returns the current time in a specific timezone.
 *
 * This class provides a timezone-aware implementation of the PSR-20 ClockInterface
 * that returns the current system time converted to the specified timezone.
 * The timezone can be provided as a string (e.g., 'America/New_York') or
 * as a DateTimeZone instance.
 *
 * @author  Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * @implements ClockInterface
 */
class LocalClock implements ClockInterface
{
    /**
     * The timezone for this clock.
     */
    private readonly DateTimeZone $timezone;

    /**
     * Creates a new local clock instance.
     *
     * @param  DateTimeZone|string  $timezone  The timezone (DateTimeZone instance or timezone string)
     *
     * @throws \Exception If the timezone is invalid
     */
    public function __construct(DateTimeZone|string $timezone)
    {
        $this->timezone = $timezone instanceof DateTimeZone
            ? $timezone
            : new DateTimeZone($timezone);
    }

    /**
     * Returns the current system time in the configured timezone.
     *
     * @return DateTimeImmutable The current date and time in the specified timezone
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
