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

class MailgunTransportTest extends TestCase
{
    public function test_getters(): void
    {
        $transport = new MailgunTransport('example.com', 'key-123', 'eu');
        $this->assertEquals('example.com', $transport->getDomain());
        $this->assertEquals('key-123', $transport->getApiKey());
        $this->assertEquals('eu', $transport->getRegion());
    }

    public function test_send(): void
    {
        $transport = $this->getMockBuilder(MailgunTransport::class)
            ->setConstructorArgs(['example.com', 'key-123', 'us'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Hello')
            ->setBody('World');

        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('https://api.mailgun.net/v3/example.com/messages.mime'),
                $this->callback(function (array $options) {
                    $this->assertStringContainsString('Authorization: Basic ', $options['http']['header']);
                    $this->assertStringContainsString('Content-Type: multipart/form-data; boundary=', $options['http']['header']);
                    $this->assertStringContainsString('World', $options['http']['content']);
                    $this->assertStringContainsString('recipient@example.com', $options['http']['content']);

                    return true;
                })
            )
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_send_with_cc_and_bcc(): void
    {
        $transport = $this->getMockBuilder(MailgunTransport::class)
            ->setConstructorArgs(['example.com', 'key-123'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->to('to@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com');

        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->callback(function (array $options) {
                    $this->assertStringContainsString('name="to"', $options['http']['content']);
                    $this->assertStringContainsString('to@example.com', $options['http']['content']);
                    $this->assertStringContainsString('name="cc"', $options['http']['content']);
                    $this->assertStringContainsString('cc@example.com', $options['http']['content']);
                    $this->assertStringContainsString('name="bcc"', $options['http']['content']);
                    $this->assertStringContainsString('bcc@example.com', $options['http']['content']);

                    return true;
                })
            )
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_send_eu_region(): void
    {
        $transport = $this->getMockBuilder(MailgunTransport::class)
            ->setConstructorArgs(['example.com', 'key-123', 'eu'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->to('recipient@example.com');

        $transport->expects($this->once())
            ->method('request')
            ->with($this->equalTo('https://api.eu.mailgun.net/v3/example.com/messages.mime'))
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_send_throws_exception_on_api_error(): void
    {
        $transport = $this->getMockBuilder(MailgunTransport::class)
            ->setConstructorArgs(['example.com', 'key-123'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->to('recipient@example.com');

        $transport->method('request')
            ->willReturn(['message' => 'Invalid API key']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Mailgun API error: Invalid API key');
        $transport->send($message);
    }

    public function test_request_failure(): void
    {
        $transport = new class('example.com', 'key-123') extends MailgunTransport
        {
            public function test_request(string $url, array $options): array
            {
                return $this->request($url, $options);
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API request failed');
        // Use an invalid URI scheme to trigger file_get_contents failure
        $transport->test_request('invalid://host', []);
    }
}
