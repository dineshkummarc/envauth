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

class AddressTest extends TestCase
{
    public function test_constructor(): void
    {
        $address = new Address('vpz@example.com', 'Vaibhav');
        $this->assertEquals('vpz@example.com', $address->getEmail());
        $this->assertEquals('Vaibhav', $address->getName());
    }

    public function test_constructor_with_invalid_email(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Address('invalid-email');
    }

    public function test_to_string(): void
    {
        $address = new Address('vpz@example.com', 'Vaibhav');
        $this->assertEquals('"Vaibhav" <vpz@example.com>', (string) $address);

        $address = new Address('vpz@example.com', 'Vaibhav "The User" Pandey');
        $this->assertEquals('"Vaibhav \"The User\" Pandey" <vpz@example.com>', (string) $address);

        $address = new Address('vpz@example.com');
        $this->assertEquals('vpz@example.com', (string) $address);
    }

    public function test_from_string(): void
    {
        $address = Address::fromString('"Vaibhav" <vpz@example.com>');
        $this->assertEquals('vpz@example.com', $address->email);
        $this->assertEquals('Vaibhav', $address->name);

        $address = Address::fromString('Vaibhav Pandey <vpz@example.com>');
        $this->assertEquals('vpz@example.com', $address->email);
        $this->assertEquals('Vaibhav Pandey', $address->name);

        $address = Address::fromString('vpz@example.com');
        $this->assertEquals('vpz@example.com', $address->email);
        $this->assertNull($address->name);
    }
}
