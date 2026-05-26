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
 * Represents an email message.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class Message extends MimePart
{
    /**
     * @var Address[]
     */
    private array $from = [];

    /**
     * @var Address[]
     */
    private array $to = [];

    /**
     * @var Address[]
     */
    private array $cc = [];

    /**
     * @var Address[]
     */
    private array $bcc = [];

    private ?string $subject = null;

    public function from(string|Address $address, ?string $name = null): self
    {
        $this->from[] = $address instanceof Address ? $address : (
            $name === null ? Address::fromString($address) : new Address($address, $name)
        );

        return $this;
    }

    /**
     * @return Address[]
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    public function to(string|Address $address, ?string $name = null): self
    {
        $this->to[] = $address instanceof Address ? $address : (
            $name === null ? Address::fromString($address) : new Address($address, $name)
        );

        return $this;
    }

    /**
     * @return Address[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    public function cc(string|Address $address, ?string $name = null): self
    {
        $this->cc[] = $address instanceof Address ? $address : (
            $name === null ? Address::fromString($address) : new Address($address, $name)
        );

        return $this;
    }

    /**
     * @return Address[]
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    public function bcc(string|Address $address, ?string $name = null): self
    {
        $this->bcc[] = $address instanceof Address ? $address : (
            $name === null ? Address::fromString($address) : new Address($address, $name)
        );

        return $this;
    }

    /**
     * @return Address[]
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    /**
     * @param  bool  $includeBcc  Whether to include Bcc header in the rendered MIME.
     */
    #[\Override]
    public function toString(bool $includeBcc = true): string
    {
        $headers = clone $this->getHeaders();

        if ($this->from !== []) {
            $headers->set('From', implode(', ', $this->from));
        }

        if ($this->to !== []) {
            $headers->set('To', implode(', ', $this->to));
        }

        if ($this->cc !== []) {
            $headers->set('Cc', implode(', ', $this->cc));
        }

        if ($this->bcc !== [] && $includeBcc) {
            $headers->set('Bcc', implode(', ', $this->bcc));
        }

        if ($this->subject !== null) {
            $headers->set('Subject', $this->subject);
        }

        if ($headers->get('MIME-Version') === null) {
            $headers->set('MIME-Version', '1.0');
        }

        if ($headers->get('Date') === null) {
            $headers->set('Date', date('r'));
        }

        if ($headers->get('Message-ID') === null) {
            $headers->set('Message-ID', '<'.bin2hex(random_bytes(16)).'@'.(gethostname() ?: 'localhost').'>');
        }

        // We temporarily replace the headers of this object to use parent's toString
        // This is a bit hacky but avoids duplicating parent's logic.
        $originalHeaders = $this->getHeaders();
        // Use Reflection to set the private headers property in MimePart
        $reflection = new \ReflectionProperty(MimePart::class, 'headers');
        $reflection->setValue($this, $headers);

        try {
            return parent::toString();
        } finally {
            $reflection->setValue($this, $originalHeaders);
        }
    }
}
