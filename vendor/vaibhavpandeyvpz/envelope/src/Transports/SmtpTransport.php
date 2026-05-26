<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope\Transports;

use Envelope\Message;

/**
 * SMTP delivery backend.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class SmtpTransport implements TransportInterface
{
    private $socket;

    public function __construct(
        private readonly string $host,
        private readonly int $port = 25,
        private readonly ?string $encryption = null,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly int $timeout = 30
    ) {}

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getEncryption(): ?string
    {
        return $this->encryption;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    #[\Override]
    public function send(Message $message): bool
    {
        $this->connect();

        try {
            $this->execute('HELO '.(gethostname() ?: 'localhost'), 250);

            if ($this->username !== null && $this->password !== null) {
                $this->execute('AUTH LOGIN', 334);
                $this->execute(base64_encode($this->username), 334);
                $this->execute(base64_encode($this->password), 235);
            }

            $fromEmails = array_map(fn ($addr) => $addr->email, $message->getFrom());
            foreach ($fromEmails as $email) {
                $this->execute("MAIL FROM: <{$email}>", 250);
            }

            $recipients = array_merge($message->getTo(), $message->getCc(), $message->getBcc());
            foreach ($recipients as $recipient) {
                $this->execute("RCPT TO: <{$recipient->email}>", 250);
            }

            $this->execute('DATA', 354);
            $this->write($message->toString(false));
            $this->execute("\r\n.", 250);

            $this->execute('QUIT', 221);

            return true;
        } finally {
            $this->disconnect();
        }
    }

    protected function connect(): void
    {
        $remote = $this->host.':'.$this->port;
        if ($this->encryption === 'ssl') {
            $remote = 'ssl://'.$remote;
        }

        $this->socket = @stream_socket_client($remote, $errno, $errstr, $this->timeout);

        if (! $this->socket) {
            throw new \RuntimeException("Could not connect to SMTP host '{$this->host}': {$errstr} ({$errno})");
        }

        $this->read(220);

        if ($this->encryption === 'tls') {
            $this->execute('STARTTLS', 220);
            if (! stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \RuntimeException('Could not enable TLS encryption.');
            }
        }
    }

    protected function disconnect(): void
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    protected function execute(string $command, int $expectedResponse): string
    {
        $this->write($command."\r\n");

        return $this->read($expectedResponse);
    }

    protected function write(string $data): void
    {
        fwrite($this->socket, $data);
    }

    protected function read(int $expectedResponse): string
    {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        $code = (int) substr($response, 0, 3);
        if ($code !== $expectedResponse) {
            throw new \RuntimeException("SMTP error: Expected response code {$expectedResponse} but got {$code}. Response: ".trim($response));
        }

        return $response;
    }
}
