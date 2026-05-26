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
use Psr\Clock\ClockInterface;

/**
 * Frozen clock implementation that always returns a fixed time.
 *
 * This class is useful for testing scenarios where you need predictable,
 * deterministic time values. The time is set during construction and
 * remains constant across all calls to now(). This makes it ideal for
 * unit tests that require time-based assertions.
 *
 * @author  Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * @implements ClockInterface
 */
final readonly class FrozenClock implements ClockInterface
{
    /**
     * The frozen time value.
     */
    private DateTimeImmutable $time;

    /**
     * Creates a new frozen clock instance.
     *
     * If no time is provided, the current system time is used as the frozen value.
     *
     * @param  DateTimeImmutable|null  $time  The time to freeze (defaults to current time)
     */
    public function __construct(?DateTimeImmutable $time = null)
    {
        $this->time = $time ?? new DateTimeImmutable;
    }

    /**
     * Returns the frozen time value.
     *
     * This method always returns the same time that was set during construction,
     * making it ideal for testing scenarios that require deterministic behavior.
     *
     * @return DateTimeImmutable The frozen date and time
     */
    public function now(): DateTimeImmutable
    {
        return $this->time;
    }
}
