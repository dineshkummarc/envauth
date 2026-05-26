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
 * Collection of email headers.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
final class HeaderBag implements \IteratorAggregate
{
    /**
     * @var array<string, Header[]>
     */
    private array $headers = [];

    public function __clone()
    {
        foreach ($this->headers as $name => $headers) {
            foreach ($headers as $i => $header) {
                $this->headers[$name][$i] = clone $header;
            }
        }
    }

    public function add(string $name, string $value): self
    {
        $normalized = strtolower($name);
        if (! isset($this->headers[$normalized])) {
            $this->headers[$normalized] = [];
        }
        $this->headers[$normalized][] = new Header($name, $value);

        return $this;
    }

    public function set(string $name, string $value): self
    {
        $normalized = strtolower($name);
        $this->headers[$normalized] = [new Header($name, $value)];

        return $this;
    }

    public function get(string $name): ?string
    {
        $normalized = strtolower($name);
        if (isset($this->headers[$normalized]) && count($this->headers[$normalized]) > 0) {
            return $this->headers[$normalized][0]->value;
        }

        return null;
    }

    public function remove(string $name): self
    {
        unset($this->headers[strtolower($name)]);

        return $this;
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        foreach ($this->headers as $headers) {
            foreach ($headers as $header) {
                yield $header;
            }
        }
    }

    public function toString(): string
    {
        $lines = [];
        foreach ($this->headers as $headers) {
            foreach ($headers as $header) {
                $lines[] = (string) $header;
            }
        }

        return implode("\r\n", $lines);
    }
}
