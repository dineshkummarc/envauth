<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope\Mailer;

use Envelope\Mailer;
use Envelope\Transports\SmtpTransport;
use PHPUnit\Framework\TestCase;

class SmtpMailerTest extends TestCase
{
    public function test_fluent_smtp_sending(): void
    {
        $transport = $this->getMockBuilder(SmtpTransport::class)
            ->setConstructorArgs(['localhost'])
            ->onlyMethods(['connect', 'disconnect', 'execute', 'write'])
            ->getMock();

        $transport->expects($this->once())->method('connect');
        $transport->expects($this->once())->method('disconnect');
        $transport->method('execute')->willReturn('250 OK');

        $mailer = new Mailer($transport);
        $result = $mailer->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Smtp test')
            ->text('Hello')
            ->send();

        $this->assertTrue($result);
    }
}
