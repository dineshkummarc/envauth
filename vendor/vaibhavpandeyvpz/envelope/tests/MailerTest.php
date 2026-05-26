<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope;

use Envelope\Transports\SmtpTransport;
use Envelope\Transports\TransportInterface;
use PHPUnit\Framework\TestCase;

class MailerTest extends TestCase
{
    public function test_fluent_api(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(Message::class))
            ->willReturn(true);

        $mailer = new Mailer($transport);
        $result = $mailer->from('sender@example.com', 'Sender Name')
            ->to('recipient@example.com', 'Recipient Name')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->subject('Hello from Envelope')
            ->text('This is a plain text message.')
            ->html('<h1>Hello!</h1>')
            ->send();

        $this->assertTrue($result);

        $message = $mailer->getMessage();
        $this->assertEquals('sender@example.com', $message->getFrom()[0]->email);
        $this->assertEquals('Hello from Envelope', $message->getSubject());
        $this->assertCount(1, $message->getTo());
        $this->assertEquals('recipient@example.com', $message->getTo()[0]->email);
        $this->assertCount(1, $message->getCc());
        $this->assertCount(1, $message->getBcc());
        $this->assertTrue($message->isMultipart());
        $this->assertStringContainsString('multipart/alternative', $message->getHeaders()->get('Content-Type'));
    }

    public function test_attach(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);

        $tempFile = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tempFile, 'attachment content');

        try {
            $mailer->attach($tempFile, 'test.txt', 'text/plain');
            $mailer->text('hello');
            $message = $mailer->getMessage();

            $this->assertTrue($message->isMultipart());
            $this->assertStringContainsString('multipart/mixed', $message->getHeaders()->get('Content-Type'));
            $parts = $message->getParts();
            $this->assertCount(2, $parts);
            $this->assertEquals('attachment content', base64_decode($parts[0]->getBody()));
            $this->assertEquals('hello', $parts[1]->getBody());
        } finally {
            unlink($tempFile);
        }
    }

    public function test_text_only(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->text('plain text');

        $message = $mailer->getMessage();
        $this->assertFalse($message->isMultipart());
        $this->assertEquals('plain text', $message->getBody());
    }

    public function test_html_only(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->html('<b>html</b>');

        $message = $mailer->getMessage();
        $this->assertFalse($message->isMultipart());
        $this->assertEquals('<b>html</b>', $message->getBody());
    }

    public function test_text_after_text(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->text('text1')->text('text2');

        $message = $mailer->getMessage();
        $this->assertTrue($message->isMultipart());
        $parts = $message->getParts();
        $this->assertCount(2, $parts);
        $this->assertEquals('text1', $parts[0]->getBody());
        $this->assertEquals('text2', $parts[1]->getBody());
    }

    public function test_html_after_html(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->html('html1')->html('html2');

        $message = $mailer->getMessage();
        $this->assertTrue($message->isMultipart());
        $parts = $message->getParts();
        $this->assertCount(2, $parts);
        $this->assertEquals('html1', $parts[0]->getBody());
        $this->assertEquals('html2', $parts[1]->getBody());
    }

    public function test_html_after_text_creates_alternative(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->text('plain')->html('<b>html</b>');

        $message = $mailer->getMessage();
        $this->assertTrue($message->isMultipart());
        $this->assertStringContainsString('multipart/alternative', $message->getHeaders()->get('Content-Type'));
        $parts = $message->getParts();
        $this->assertCount(2, $parts);
        $this->assertEquals('plain', $parts[0]->getBody());
        $this->assertEquals('<b>html</b>', $parts[1]->getBody());
    }

    public function test_text_after_html_creates_alternative(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $mailer->html('<b>html</b>')->text('plain');

        $message = $mailer->getMessage();
        $this->assertTrue($message->isMultipart());
        $this->assertStringContainsString('multipart/mixed', $message->getHeaders()->get('Content-Type'));
        $parts = $message->getParts();
        $this->assertCount(2, $parts);
        $this->assertEquals('<b>html</b>', $parts[0]->getBody());
        $this->assertEquals('plain', $parts[1]->getBody());
    }

    public function test_smtp_transport_getters(): void
    {
        $transport = new SmtpTransport('localhost', 1025, 'tls', 'user', 'pass', 15);

        $this->assertEquals('localhost', $transport->getHost());
        $this->assertEquals(1025, $transport->getPort());
        $this->assertEquals('tls', $transport->getEncryption());
        $this->assertEquals('user', $transport->getUsername());
        $this->assertEquals('pass', $transport->getPassword());
        $this->assertEquals(15, $transport->getTimeout());
    }

    public function test_attach_without_body(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);

        $tempFile = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tempFile, 'attachment content');

        try {
            $mailer->attach($tempFile, 'test.txt', 'text/plain');
            $message = $mailer->getMessage();

            $this->assertTrue($message->isMultipart());
            $this->assertStringContainsString('multipart/mixed', $message->getHeaders()->get('Content-Type'));
            $parts = $message->getParts();
            $this->assertCount(1, $parts);
            $this->assertEquals('attachment content', base64_decode($parts[0]->getBody()));
            $this->assertNull($message->getBody());
        } finally {
            unlink($tempFile);
        }
    }

    public function test_attach_after_html(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);

        $tempFile = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tempFile, 'attachment content');

        try {
            $mailer->html('<b>html</b>')->attach($tempFile, 'test.txt', 'text/plain');
            $message = $mailer->getMessage();

            $this->assertTrue($message->isMultipart());
            $this->assertStringContainsString('multipart/mixed', $message->getHeaders()->get('Content-Type'));
            $parts = $message->getParts();
            $this->assertCount(2, $parts);
            $this->assertEquals('<b>html</b>', $parts[0]->getBody());
            $this->assertEquals('attachment content', base64_decode($parts[1]->getBody()));
            $this->assertEmpty($message->getBody());
        } finally {
            unlink($tempFile);
        }
    }

    public function test_mailer_get_transport(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);
        $this->assertSame($transport, $mailer->getTransport());
        $this->assertInstanceOf(Message::class, $mailer->getMessage());
    }

    public function test_attach_non_existent_file_throws_exception(): void
    {
        $transport = $this->createMock(TransportInterface::class);
        $mailer = new Mailer($transport);

        $this->expectException(\InvalidArgumentException::class);
        $mailer->attach('/non/existent/file');
    }
}
