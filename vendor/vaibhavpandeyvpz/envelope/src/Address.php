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
 * Represents an RFC 822 compliant email address.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final readonly class Address
{
    public function __construct(
        public string $email,
        public ?string $name = null
    ) {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address: '{$email}'.");
        }
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        if ($this->name === null || $this->name === '') {
            return $this->email;
        }

        $name = str_contains($this->name, '"')
            ? '"'.str_replace('"', '\"', $this->name).'"'
            : '"'.$this->name.'"';

        return "{$name} <{$this->email}>";
    }

    public static function fromString(string $address): self
    {
        if (preg_match('/^(.+) <(.+)>$/', $address, $matches)) {
            return new self(trim($matches[2]), trim($matches[1], '" '));
        }

        return new self(trim($address));
    }
}
