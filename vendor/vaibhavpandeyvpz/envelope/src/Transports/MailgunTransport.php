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
 * Mailgun API delivery backend.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class MailgunTransport implements TransportInterface
{
    public function __construct(
        private readonly string $domain,
        private readonly string $apiKey,
        private readonly string $region = 'us'
    ) {}

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getRegion(): string
    {
        return $this->region;
    }

    #[\Override]
    public function send(Message $message): bool
    {
        $url = $this->region === 'eu'
            ? "https://api.eu.mailgun.net/v3/{$this->domain}/messages.mime"
            : "https://api.mailgun.net/v3/{$this->domain}/messages.mime";

        $data = [];

        foreach ($message->getFrom() as $from) {
            $data[] = ['name' => 'from', 'value' => (string) $from];
        }

        foreach ($message->getTo() as $to) {
            $data[] = ['name' => 'to', 'value' => (string) $to];
        }

        foreach ($message->getCc() as $cc) {
            $data[] = ['name' => 'cc', 'value' => (string) $cc];
        }

        foreach ($message->getBcc() as $bcc) {
            $data[] = ['name' => 'bcc', 'value' => (string) $bcc];
        }

        $data[] = [
            'name' => 'message',
            'value' => $message->toString(false),
            'filename' => 'message.mime',
            'type' => 'message/rfc822',
        ];

        $boundary = '--------------------------'.bin2hex(random_bytes(16));
        $content = '';

        foreach ($data as $part) {
            $content .= "--{$boundary}\r\n";
            $content .= "Content-Disposition: form-data; name=\"{$part['name']}\"";
            if (isset($part['filename'])) {
                $content .= "; filename=\"{$part['filename']}\"";
            }
            $content .= "\r\n";
            if (isset($part['type'])) {
                $content .= "Content-Type: {$part['type']}\r\n";
            }
            $content .= "\r\n{$part['value']}\r\n";
        }

        $content .= "--{$boundary}--\r\n";

        $options = [
            'http' => [
                'header' => 'Authorization: Basic '.base64_encode("api:{$this->apiKey}")."\r\n".
                            "Content-Type: multipart/form-data; boundary={$boundary}\r\n",
                'method' => 'POST',
                'content' => $content,
                'ignore_errors' => true,
            ],
        ];

        $response = $this->request($url, $options);

        if (! isset($response['id'])) {
            throw new \RuntimeException('Mailgun API error: '.($response['message'] ?? 'Unknown error'));
        }

        return true;
    }

    protected function request(string $url, array $options): array
    {
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $error = error_get_last();
            throw new \RuntimeException('API request failed: '.($error['message'] ?? 'Unknown error'));
        }

        return json_decode($result, true) ?? [];
    }
}
