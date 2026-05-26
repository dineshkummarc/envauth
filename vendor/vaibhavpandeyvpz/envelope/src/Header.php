<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope;

/**
 * Represents an email header.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final readonly class Header
{
    public function __construct(
        public string $name,
        public string $value
    ) {}

    public function __toString(): string
    {
        return "{$this->name}: {$this->value}";
    }
}
