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
use Envelope\Transports\ResendTransport;
use PHPUnit\Framework\TestCase;

class ResendMailerTest extends TestCase
{
    public function test_fluent_resend_sending(): void
    {
        $transport = $this->getMockBuilder(ResendTransport::class)
            ->setConstructorArgs(['re_123'])
            ->onlyMethods(['request'])
            ->getMock();

        $transport->expects($this->once())
            ->method('request')
            ->willReturn(['id' => 'abc-123']);

        $mailer = new Mailer($transport);
        $result = $mailer->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Resend test')
            ->text('Hello')
            ->send();

        $this->assertTrue($result);
    }
}
