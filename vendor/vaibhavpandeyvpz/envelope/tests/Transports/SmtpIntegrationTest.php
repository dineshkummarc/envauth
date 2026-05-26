<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope\Transports;

use Envelope\Mailer;
use Envelope\Message;
use PHPUnit\Framework\TestCase;

class SmtpIntegrationTest extends TestCase
{
    private ?SmtpTransport $transport = null;

    private string $apiBaseUrl;

    protected function setUp(): void
    {
        $host = getenv('SMTP_HOST') ?: '127.0.0.1';
        $port = (int) (getenv('SMTP_PORT') ?: 1025);
        $apiPort = 8025;

        // Check if MailCatcher is running
        $fp = @fsockopen($host, $port, $errno, $errstr, 1);
        if (! $fp) {
            $this->markTestSkipped("MailCatcher is not running on {$host}:{$port}. Skipping integration test.");
        }
        fclose($fp);

        $this->transport = new SmtpTransport($host, $port);
        $this->apiBaseUrl = "http://{$host}:{$apiPort}";

        // Clear previous messages if possible
        @$this->httpDelete("{$this->apiBaseUrl}/messages");
    }

    public function test_send_email_to_mail_catcher(): void
    {
        $message = new Message;
        $message->from('sender@envelope.test', 'Envelope Test')
            ->to('contact@vaibhavpandey.com', 'Vaibhav Pandey')
            ->subject('Integration Test Support')
            ->setBody('This is a test email sent to MailCatcher.');

        $result = $this->transport->send($message);

        $this->assertTrue($result);

        // Verify via MailCatcher API
        $messagesJson = @file_get_contents("{$this->apiBaseUrl}/messages");
        $this->assertNotFalse($messagesJson, "Failed to connect to MailCatcher API at {$this->apiBaseUrl}");

        $messages = json_decode($messagesJson, true);
        $this->assertNotEmpty($messages);
        $latest = end($messages);

        $this->assertEquals('<sender@envelope.test>', $latest['sender']);
        $this->assertContains('<contact@vaibhavpandey.com>', $latest['recipients']);
        $this->assertEquals('Integration Test Support', $latest['subject']);

        // Thoroughly test message content
        $id = $latest['id'];
        $detailsJson = file_get_contents("{$this->apiBaseUrl}/messages/{$id}.json");
        $details = json_decode($detailsJson, true);

        $this->assertEquals('text/plain', $details['type']);

        $plainContent = file_get_contents("{$this->apiBaseUrl}/messages/{$id}.plain");
        $this->assertEquals('This is a test email sent to MailCatcher.', trim($plainContent));
    }

    public function test_send_multipart_email_to_mail_catcher(): void
    {
        $mailer = new Mailer($this->transport);
        $mailer->from('sender@envelope.test')
            ->to('contact@vaibhavpandey.com')
            ->subject('Multipart Test')
            ->text('Plain text body')
            ->html('<h1>HTML body</h1>');

        $result = $mailer->send();
        $this->assertTrue($result);

        $messagesJson = file_get_contents("{$this->apiBaseUrl}/messages");
        $messages = json_decode($messagesJson, true);
        $latest = end($messages);
        $id = $latest['id'];

        $plainContent = file_get_contents("{$this->apiBaseUrl}/messages/{$id}.plain");
        $this->assertEquals('Plain text body', trim($plainContent));

        $htmlContent = file_get_contents("{$this->apiBaseUrl}/messages/{$id}.html");
        $this->assertEquals('<h1>HTML body</h1>', trim($htmlContent));
    }

    public function test_fluent_smtp_sending(): void
    {
        $mailer = new Mailer($this->transport);
        $result = $mailer->from('sender@envelope.test', 'Mailer Test')
            ->to('contact@vaibhavpandey.com', 'Vaibhav Pandey')
            ->subject('SmtpMailerTest Subject')
            ->text('Hello from SmtpMailerTest')
            ->send();

        $this->assertTrue($result);

        $messagesJson = file_get_contents("{$this->apiBaseUrl}/messages");
        $messages = json_decode($messagesJson, true);
        $latest = end($messages);

        $this->assertEquals('SmtpMailerTest Subject', $latest['subject']);
        $this->assertContains('<contact@vaibhavpandey.com>', $latest['recipients']);

        $plain = file_get_contents("{$this->apiBaseUrl}/messages/{$latest['id']}.plain");
        $this->assertEquals('Hello from SmtpMailerTest', trim($plain));
    }

    private function httpDelete(string $url): void
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
