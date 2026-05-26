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
 * Resend API delivery backend.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
class ResendTransport implements TransportInterface
{
    public function __construct(
        private readonly string $apiKey
    ) {}

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    #[\Override]
    public function send(Message $message): bool
    {
        $url = 'https://api.resend.com/emails';

        $data = [
            'from' => implode(', ', $message->getFrom()),
            'to' => array_map(fn ($addr) => $addr->email, $message->getTo()),
            'subject' => $message->getSubject() ?: '',
        ];

        if ($cc = $message->getCc()) {
            $data['cc'] = array_map(fn ($addr) => $addr->email, $cc);
        }

        if ($bcc = $message->getBcc()) {
            $data['bcc'] = array_map(fn ($addr) => $addr->email, $bcc);
        }

        $this->parseMessage($message, $data);

        $options = [
            'http' => [
                'header' => "Authorization: Bearer {$this->apiKey}\r\n".
                            "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($data),
                'ignore_errors' => true,
            ],
        ];

        $response = $this->request($url, $options);

        if (! isset($response['id'])) {
            throw new \RuntimeException('Resend API error: '.($response['message'] ?? 'Unknown error'));
        }

        return true;
    }

    private function parseMessage(\Envelope\MimePart $part, array &$data): void
    {
        $contentType = $part->getHeaders()->get('Content-Type') ?? 'text/plain';
        $contentDisposition = $part->getHeaders()->get('Content-Disposition') ?? '';

        if (str_contains($contentDisposition, 'attachment')) {
            $data['attachments'] ??= [];
            $filename = 'attachment';
            if (preg_match('/filename="(.+?)"/', $contentDisposition, $matches)) {
                $filename = $matches[1];
            } elseif (preg_match('/name="(.+?)"/', $contentType, $matches)) {
                $filename = $matches[1];
            }

            $data['attachments'][] = [
                'filename' => $filename,
                'content' => $part->getBody(),
            ];

            return;
        }

        if (str_contains($contentType, 'text/plain')) {
            $data['text'] = $part->getBody();
        } elseif (str_contains($contentType, 'text/html')) {
            $data['html'] = $part->getBody();
        }

        foreach ($part->getParts() as $subPart) {
            $this->parseMessage($subPart, $data);
        }
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
