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
use PHPUnit\Framework\TestCase;

class MailgunIntegrationTest extends TestCase
{
    private ?MailgunTransport $transport = null;

    private ?string $domain = null;

    protected function setUp(): void
    {
        $this->domain = getenv('MAILGUN_DOMAIN') ?: ($_ENV['MAILGUN_DOMAIN'] ?? null);
        $apiKey = getenv('MAILGUN_API_KEY') ?: ($_ENV['MAILGUN_API_KEY'] ?? null);
        $region = getenv('MAILGUN_REGION') ?: ($_ENV['MAILGUN_REGION'] ?? 'us');

        if (! $this->domain || ! $apiKey) {
            $this->markTestSkipped('Mailgun credentials not found. Skipping integration test.');
        }

        $this->transport = new MailgunTransport($this->domain, $apiKey, $region);
    }

    public function test_send_real_email(): void
    {
        $message = new Message;
        $message->from("Envelope Test <sender@{$this->domain}>")
            ->to('contact@vaibhavpandey.com')
            ->subject('Mailgun Integration Test - Simple')
            ->setBody('This is a real email sent via Mailgun API.');

        try {
            $result = $this->transport->send($message);
            $this->assertTrue($result);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Sandbox subdomains are for test purposes only')) {
                $this->markTestSkipped('Mailgun sandbox domain detected. Recipient must be authorized.');
            }
            throw $e;
        }
    }

    public function test_send_complex_real_email(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tempFile, 'This is an attachment for Mailgun.');

        try {
            $mailer = new \Envelope\Mailer($this->transport);
            $result = $mailer->from("Envelope Test <sender@{$this->domain}>")
                ->to('contact@vaibhavpandey.com')
                ->cc('contact@vaibhavpandey.com')
                ->subject('Mailgun Integration Test - Complex')
                ->text('Plain text body')
                ->html('<h1>HTML body</h1><p>With attachment</p>')
                ->attach($tempFile, 'mailgun-test.txt', 'text/plain')
                ->send();

            $this->assertTrue($result);
        } catch (\RuntimeException $e) {
            if (str_contains($e->getMessage(), 'Sandbox subdomains are for test purposes only')) {
                $this->markTestSkipped('Mailgun sandbox domain detected. Recipient must be authorized.');
            }
            throw $e;
        } finally {
            unlink($tempFile);
        }
    }

    public function test_invalid_credentials(): void
    {
        $transport = new MailgunTransport('example.com', 'key-invalid');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailgun API error');

        $message = new Message;
        $message->to('recipient@example.com');
        $transport->send($message);
    }
}
