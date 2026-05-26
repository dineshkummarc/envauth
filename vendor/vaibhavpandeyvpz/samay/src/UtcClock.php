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

use DateTimeZone;

/**
 * UTC clock implementation that always returns the current time in UTC.
 *
 * This class extends LocalClock and provides a convenient way to get
 * the current time in UTC timezone. It's equivalent to using
 * LocalClock with DateTimeZone('UTC').
 *
 * @author  Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * @extends LocalClock
 */
final class UtcClock extends LocalClock
{
    /**
     * Creates a new UTC clock instance.
     *
     * The clock will always return the current time in UTC timezone,
     * regardless of the system's default timezone.
     */
    public function __construct()
    {
        parent::__construct(new DateTimeZone('UTC'));
    }
}
