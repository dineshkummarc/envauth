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
 * System clock implementation that returns the current system time.
 *
 * This class provides a simple implementation of the PSR-20 ClockInterface
 * that returns the current system time as a DateTimeImmutable instance.
 * The time returned uses the system's default timezone.
 *
 * @author  Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * @implements ClockInterface
 */
final readonly class SystemClock implements ClockInterface
{
    /**
     * Returns the current system time.
     *
     * @return DateTimeImmutable The current date and time
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable;
    }
}
