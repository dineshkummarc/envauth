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

class HeaderTest extends TestCase
{
    public function test_to_string(): void
    {
        $header = new Header('X-Test', 'Value');
        $this->assertEquals('X-Test: Value', (string) $header);
    }
}

class HeaderBagTest extends TestCase
{
    public function test_add_and_get(): void
    {
        $bag = new HeaderBag;
        $bag->add('X-Test', 'Value1');
        $bag->add('X-Test', 'Value2');

        $this->assertEquals('Value1', $bag->get('X-Test'));
        $this->assertEquals('Value1', $bag->get('x-test'));
    }

    public function test_set_and_remove(): void
    {
        $bag = new HeaderBag;
        $bag->set('X-Test', 'Value1');
        $bag->set('X-Test', 'Value2'); // Overwrites

        $this->assertEquals('Value2', $bag->get('X-Test'));

        $bag->remove('X-Test');
        $this->assertNull($bag->get('X-Test'));
    }

    public function test_iterator(): void
    {
        $bag = new HeaderBag;
        $bag->add('X-Test-1', 'Value1');
        $bag->add('X-Test-2', 'Value2');

        $headers = iterator_to_array($bag);
        $this->assertCount(2, $headers);
        $this->assertInstanceOf(Header::class, $headers[0]);
    }

    public function test_to_string(): void
    {
        $bag = new HeaderBag;
        $bag->add('X-Test-1', 'Value1');
        $bag->add('X-Test-2', 'Value2');

        $expected = "X-Test-1: Value1\r\nX-Test-2: Value2";
        $this->assertEquals($expected, $bag->toString());
    }
}
