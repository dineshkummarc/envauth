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

class ResendTransportTest extends TestCase
{
    public function test_getters(): void
    {
        $transport = new ResendTransport('re_123');
        $this->assertEquals('re_123', $transport->getApiKey());
    }

    public function test_send(): void
    {
        $transport = $this->getMockBuilder(ResendTransport::class)
            ->setConstructorArgs(['re_123'])
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
                $this->equalTo('https://api.resend.com/emails'),
                $this->callback(function (array $options) {
                    $this->assertStringContainsString('Authorization: Bearer re_123', $options['http']['header']);
                    $this->assertStringContainsString('Content-Type: application/json', $options['http']['header']);
                    $data = json_decode($options['http']['content'], true);
                    $this->assertEquals('sender@example.com', $data['from']);
                    $this->assertEquals(['recipient@example.com'], $data['to']);
                    $this->assertEquals('Hello', $data['subject']);

                    return true;
                })
            )
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_send_with_cc_and_bcc(): void
    {
        $transport = $this->getMockBuilder(ResendTransport::class)
            ->setConstructorArgs(['re_123'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->subject('Hello');

        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->callback(function (array $options) {
                    $data = json_decode($options['http']['content'], true);
                    $this->assertEquals(['cc@example.com'], $data['cc']);
                    $this->assertEquals(['bcc@example.com'], $data['bcc']);

                    return true;
                })
            )
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_send_throws_exception_on_api_error(): void
    {
        $transport = $this->getMockBuilder(ResendTransport::class)
            ->setConstructorArgs(['re_123'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->to('recipient@example.com');

        $transport->method('request')
            ->willReturn(['message' => 'Invalid API key']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Resend API error: Invalid API key');
        $transport->send($message);
    }

    public function test_send_complex(): void
    {
        $transport = $this->getMockBuilder(ResendTransport::class)
            ->setConstructorArgs(['re_123'])
            ->onlyMethods(['request'])
            ->getMock();

        $message = new Message;
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->subject('Hello');

        $part1 = new \Envelope\MimePart;
        $part1->getHeaders()->set('Content-Type', 'text/html');
        $part1->setBody('<b>HTML</b>');
        $message->addPart($part1);

        $part2 = new \Envelope\MimePart;
        $part2->getHeaders()->set('Content-Type', 'application/pdf');
        $part2->getHeaders()->set('Content-Disposition', 'attachment; filename="test.pdf"');
        $part2->setBody('BASE64');
        $message->addPart($part2);

        $transport->expects($this->once())
            ->method('request')
            ->with(
                $this->anything(),
                $this->callback(function (array $options) {
                    $data = json_decode($options['http']['content'], true);
                    $this->assertEquals('<b>HTML</b>', $data['html']);
                    $this->assertCount(1, $data['attachments']);
                    $this->assertEquals('test.pdf', $data['attachments'][0]['filename']);
                    $this->assertEquals('BASE64', $data['attachments'][0]['content']);

                    return true;
                })
            )
            ->willReturn(['id' => 'abc-123']);

        $this->assertTrue($transport->send($message));
    }

    public function test_request_failure(): void
    {
        $transport = new class('key-123') extends ResendTransport
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
