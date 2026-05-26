<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope;

use Envelope\Transports\TransportInterface;

/**
 * Fluent API for sending emails.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class Mailer
{
    private Message $message;

    public function __construct(
        private readonly TransportInterface $transport
    ) {
        $this->message = new Message;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function from(string|Address $address, ?string $name = null): self
    {
        $this->message->from($address, $name);

        return $this;
    }

    public function to(string|Address $address, ?string $name = null): self
    {
        $this->message->to($address, $name);

        return $this;
    }

    public function cc(string|Address $address, ?string $name = null): self
    {
        $this->message->cc($address, $name);

        return $this;
    }

    public function bcc(string|Address $address, ?string $name = null): self
    {
        $this->message->bcc($address, $name);

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->message->subject($subject);

        return $this;
    }

    public function text(string $body): self
    {
        if ($this->message->isMultipart() || $this->message->getHeaders()->get('Content-Type') !== null) {
            $part = new MimePart;
            $part->getHeaders()->set('Content-Type', 'text/plain; charset=utf-8');
            $part->setBody($body);

            if (! $this->message->isMultipart()) {
                // Move current body to a part
                $currentPart = new MimePart;
                $currentPart->getHeaders()->set('Content-Type', $this->message->getHeaders()->get('Content-Type') ?? 'text/plain; charset=utf-8');
                $currentPart->setBody($this->message->getBody() ?? '');

                $this->message->addPart($currentPart);
                $this->message->getHeaders()->set('Content-Type', 'multipart/mixed');
            }

            $this->message->addPart($part);
        } else {
            $this->message->getHeaders()->set('Content-Type', 'text/plain; charset=utf-8');
            $this->message->setBody($body);
        }

        return $this;
    }

    public function html(string $body): self
    {
        if ($this->message->isMultipart() || $this->message->getHeaders()->get('Content-Type') !== null) {
            // If it already has content, we convert it to multipart/alternative
            // This is a simplification. A real library would handle this more robustly.
            $part = new MimePart;
            $part->getHeaders()->set('Content-Type', 'text/html; charset=utf-8');
            $part->setBody($body);

            if (! $this->message->isMultipart()) {
                // Move current body to a part
                $currentPart = new MimePart;
                $currentPart->getHeaders()->set('Content-Type', $this->message->getHeaders()->get('Content-Type') ?? 'text/plain; charset=utf-8');
                $currentPart->setBody($this->message->getBody() ?? '');

                $this->message->addPart($currentPart);
                $this->message->getHeaders()->set('Content-Type', 'multipart/alternative');
            }

            $this->message->addPart($part);
        } else {
            $this->message->getHeaders()->set('Content-Type', 'text/html; charset=utf-8');
            $this->message->setBody($body);
        }

        return $this;
    }

    public function attach(string $path, ?string $filename = null, ?string $contentType = null): self
    {
        if (! file_exists($path)) {
            throw new \InvalidArgumentException("Attachment file not found: '{$path}'.");
        }

        $filename ??= basename($path);
        $contentType ??= mime_content_type($path) ?: 'application/octet-stream';

        $part = new MimePart;
        $part->getHeaders()
            ->set('Content-Type', $contentType.'; name="'.$filename.'"')
            ->set('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->set('Content-Transfer-Encoding', 'base64');

        $part->setBody(base64_encode(file_get_contents($path)));

        if (! $this->message->isMultipart()) {
            // Move current body to a part if it exists
            $currentBody = $this->message->getBody();
            if ($currentBody !== null) {
                $currentPart = new MimePart;
                $currentPart->getHeaders()->set('Content-Type', $this->message->getHeaders()->get('Content-Type') ?? 'text/plain; charset=utf-8');
                $currentPart->setBody($currentBody);
                $this->message->addPart($currentPart);
                $this->message->setBody(''); // Clear main body
            }
            $this->message->getHeaders()->set('Content-Type', 'multipart/mixed');
        }

        $this->message->addPart($part);

        return $this;
    }

    public function send(): bool
    {
        return $this->transport->send($this->message);
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
