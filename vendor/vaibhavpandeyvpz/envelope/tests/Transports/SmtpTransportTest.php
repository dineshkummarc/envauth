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

class SmtpTransportTest extends TestCase
{
    public function test_getters(): void
    {
        $transport = new SmtpTransport('smtp.example.com', 587, 'tls', 'user', 'pass', 10);
        $this->assertEquals('smtp.example.com', $transport->getHost());
        $this->assertEquals(587, $transport->getPort());
        $this->assertEquals('tls', $transport->getEncryption());
        $this->assertEquals('user', $transport->getUsername());
        $this->assertEquals('pass', $transport->getPassword());
        $this->assertEquals(10, $transport->getTimeout());
    }

    public function test_send_with_mocks(): void
    {
        $transport = $this->getMockBuilder(SmtpTransport::class)
            ->setConstructorArgs(['localhost', 25, null, 'user', 'pass'])
            ->onlyMethods(['connect', 'disconnect', 'execute', 'write'])
            ->getMock();

        $message = new Message;
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Hello')
            ->setBody('World');

        $transport->expects($this->once())->method('connect');
        $transport->expects($this->once())->method('disconnect');

        $transport->expects($this->exactly(9))
            ->method('execute')
            ->willReturnMap([
                ['HELO '.(gethostname() ?: 'localhost'), 250, '250 OK'],
                ['AUTH LOGIN', 334, '334 base64'],
                [base64_encode('user'), 334, '334 base64'],
                [base64_encode('pass'), 235, '235 OK'],
                ['MAIL FROM: <sender@example.com>', 250, '250 OK'],
                ['RCPT TO: <recipient@example.com>', 250, '250 OK'],
                ['DATA', 354, '354 GO'],
                ["\r\n.", 250, '250 OK'],
                ['QUIT', 221, '221 BYE'],
            ]);

        $transport->expects($this->once())
            ->method('write')
            ->with($this->stringContains('World'));

        $this->assertTrue($transport->send($message));
    }

    public function test_connection_failure(): void
    {
        $transport = new SmtpTransport('localhost', 9999, null, null, null, 1);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not connect to SMTP host');
        $transport->send(new Message);
    }

    public function test_unexpected_response_code(): void
    {
        $transport = $this->getMockBuilder(SmtpTransport::class)
            ->setConstructorArgs(['localhost'])
            ->onlyMethods(['connect', 'disconnect', 'execute'])
            ->getMock();

        $transport->expects($this->once())->method('connect');
        $transport->method('execute')->willThrowException(new \RuntimeException('SMTP error: Expected response code 250 but got 500'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('SMTP error: Expected response code 250 but got 500');

        $message = new Message;
        $message->to('recipient@example.com');
        $transport->send($message);
    }
}
