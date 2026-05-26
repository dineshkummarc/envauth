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

class ResendIntegrationTest extends TestCase
{
    private ?ResendTransport $transport = null;

    protected function setUp(): void
    {
        $apiKey = getenv('RESEND_API_KEY') ?: ($_ENV['RESEND_API_KEY'] ?? null);

        if (! $apiKey) {
            $this->markTestSkipped('Resend API key not found. Skipping integration test.');
        }

        $this->transport = new ResendTransport($apiKey);
    }

    public function test_send_real_email(): void
    {
        $message = new Message;
        $message->from('Envelope <onboarding@resend.dev>')
            ->to('contact@vaibhavpandey.com')
            ->subject('Resend Integration Test - Simple')
            ->setBody('This is a real email sent via Resend API.');

        $result = $this->transport->send($message);
        $this->assertTrue($result);
    }

    public function test_send_complex_real_email(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tempFile, 'This is an attachment for Resend.');

        try {
            $mailer = new \Envelope\Mailer($this->transport);
            $result = $mailer->from('Envelope <onboarding@resend.dev>')
                ->to('contact@vaibhavpandey.com')
                ->cc('contact@vaibhavpandey.com')
                ->subject('Resend Integration Test - Complex')
                ->text('Plain text body')
                ->html('<h1>HTML body</h1><p>With attachment</p>')
                ->attach($tempFile, 'resend-test.txt', 'text/plain')
                ->send();

            $this->assertTrue($result);
        } finally {
            unlink($tempFile);
        }
    }

    public function test_invalid_credentials(): void
    {
        $transport = new ResendTransport('re_invalid_key');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resend API error');

        $message = new Message;
        $message->to('recipient@example.com');
        $transport->send($message);
    }
}
