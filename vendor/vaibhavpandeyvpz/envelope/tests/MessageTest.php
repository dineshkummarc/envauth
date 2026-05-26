<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

namespace Envelope;

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function test_plain_text_rendering(): void
    {
        $message = new Message;
        $message->from('vpz@example.com', 'Vaibhav')
            ->to('recipient@example.com', 'Recipient')
            ->subject('Hello')
            ->setBody('Hello World');

        $rendered = $message->toString();

        $this->assertStringContainsString('From: "Vaibhav" <vpz@example.com>', $rendered);
        $this->assertStringContainsString('To: "Recipient" <recipient@example.com>', $rendered);
        $this->assertStringContainsString('Subject: Hello', $rendered);
        $this->assertStringContainsString('Content-Type: text/plain; charset=utf-8', $rendered);
        $this->assertStringContainsString('Hello World', $rendered);
    }

    public function test_multipart_mixed_rendering(): void
    {
        $message = new Message;
        $message->from('vpz@example.com')
            ->to('recipient@example.com')
            ->cc('cc@example.com')
            ->bcc('bcc@example.com')
            ->subject('Hello With Attachment');

        $bodyPart = new MimePart;
        $bodyPart->getHeaders()->set('Content-Type', 'text/plain; charset=utf-8');
        $bodyPart->setBody('Hello World');
        $message->addPart($bodyPart);

        $attachmentPart = new MimePart;
        $attachmentPart->getHeaders()
            ->set('Content-Type', 'text/plain; name="test.txt"')
            ->set('Content-Disposition', 'attachment; filename="test.txt"');
        $attachmentPart->setBody('Attachment Content');
        $message->addPart($attachmentPart);

        $rendered = $message->toString();

        $this->assertStringContainsString('From: vpz@example.com', $rendered);
        $this->assertStringContainsString('To: recipient@example.com', $rendered);
        $this->assertStringContainsString('Cc: cc@example.com', $rendered);
        $this->assertStringContainsString('Bcc: bcc@example.com', $rendered);
        $this->assertStringContainsString('Content-Type: multipart/mixed; boundary=', $rendered);
        $this->assertStringContainsString('Hello World', $rendered);
        $this->assertStringContainsString('Attachment Content', $rendered);
        $this->assertStringContainsString('Content-Disposition: attachment; filename="test.txt"', $rendered);
    }

    public function test_default_headers(): void
    {
        $message = new Message;
        $rendered = $message->toString();

        $this->assertStringContainsString('MIME-Version: 1.0', $rendered);
        $this->assertStringContainsString('Date: ', $rendered);
        $this->assertStringContainsString('Message-ID: <', $rendered);
    }

    public function test_set_boundary(): void
    {
        $part = new MimePart;
        $part->setBoundary('custom-boundary');
        $this->assertEquals('custom-boundary', $part->getBoundary());

        $part->addPart((new MimePart)->setBody('content'));
        $rendered = $part->toString();
        $this->assertStringContainsString('boundary="custom-boundary"', $rendered);
    }

    public function test_bcc_stripping(): void
    {
        $message = new Message;
        $message->from('sender@example.com')
            ->to('recipient@example.com')
            ->bcc('bcc@example.com');

        $renderedWithBcc = $message->toString(true);
        $this->assertStringContainsString('Bcc: bcc@example.com', $renderedWithBcc);

        $renderedWithoutBcc = $message->toString(false);
        $this->assertStringNotContainsString('Bcc: bcc@example.com', $renderedWithoutBcc);
    }

    public function test_header_bag_immutability(): void
    {
        $message = new Message;
        $message->from('sender@example.com');

        $this->assertNull($message->getHeaders()->get('Date'));

        $message->toString();

        // Date header should NOT be present in original headers after toString()
        $this->assertNull($message->getHeaders()->get('Date'));
    }
}
