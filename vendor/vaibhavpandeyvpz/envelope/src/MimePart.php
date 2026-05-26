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
 * Represents a MIME part as per RFC 2046.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class MimePart
{
    private HeaderBag $headers;

    /**
     * @var MimePart[]
     */
    private array $parts = [];

    private ?string $body = null;

    private ?string $boundary = null;

    public function __construct()
    {
        $this->headers = new HeaderBag;
    }

    public function getHeaders(): HeaderBag
    {
        return $this->headers;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function addPart(MimePart $part): self
    {
        $this->parts[] = $part;

        return $this;
    }

    /**
     * @return MimePart[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    public function isMultipart(): bool
    {
        return count($this->parts) > 0;
    }

    public function getBoundary(): string
    {
        if ($this->boundary === null) {
            $this->boundary = '=_'.md5(uniqid((string) mt_rand(), true));
        }

        return $this->boundary;
    }

    public function setBoundary(string $boundary): self
    {
        $this->boundary = $boundary;

        return $this;
    }

    public function toString(): string
    {
        $headers = clone $this->headers;
        $contentType = $headers->get('Content-Type');

        if ($this->isMultipart()) {
            $boundary = $this->getBoundary();
            if ($contentType === null) {
                $headers->set('Content-Type', "multipart/mixed; boundary=\"{$boundary}\"");
            } elseif (! str_contains($contentType, 'boundary=')) {
                $headers->set('Content-Type', "{$contentType}; boundary=\"{$boundary}\"");
            }

            $content = $headers->toString()."\r\n\r\n";
            $content .= "This is a multi-part message in MIME format.\r\n";

            foreach ($this->parts as $part) {
                $content .= "--{$boundary}\r\n";
                $content .= $part->toString()."\r\n";
            }

            $content .= "--{$boundary}--\r\n";

            return $content;
        }

        if ($contentType === null) {
            $headers->set('Content-Type', 'text/plain; charset=utf-8');
        }

        $content = $headers->toString()."\r\n\r\n";
        $content .= ($this->body ?? '')."\r\n";

        return $content;
    }
}
