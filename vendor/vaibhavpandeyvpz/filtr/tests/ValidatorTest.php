<?php

declare(strict_types=1);

/*
 * This file is part of vaibhavpandeyvpz/filtr package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Filtr;

use Filtr\Rule\CreditCardType;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    public function test_required(): void
    {
        $v = new Validator;
        $v->key('subject')->isNotBlank();
        $result = $v->validate([]);
        $this->assertTrue($result->valid());
        $v->required('subject')->isNotBlank();
        $result = $v->validate([]);
        $this->assertFalse($result->valid());
        $this->assertNotEmpty($result->errors());
        $this->assertArrayHasKey('subject', $result->errors());
    }

    public function test_aliases(): void
    {
        $v = new Validator;
        // isBoolean
        $v->key('subject')->isBoolean();
        $result = $v->validate(['subject' => true]);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => 'vaibhav']);
        $this->assertFalse($result->valid());
        // isInteger
        $v->key('subject')->isInteger();
        $result = $v->validate(['subject' => 60600]);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => 'vaibhav']);
        $this->assertFalse($result->valid());
        // isString
        $v->key('subject')->isString();
        $result = $v->validate(['subject' => 'vaibhav']);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => 60600]);
        $this->assertFalse($result->valid());
        // isTrue
        $v->key('subject')->isTrue();
        $result = $v->validate(['subject' => true]);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => false]);
        $this->assertFalse($result->valid());
        // isFalse
        $v->key('subject')->isFalse();
        $result = $v->validate(['subject' => false]);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => true]);
        $this->assertFalse($result->valid());
        // isIPv4Address
        $v->key('subject')->isIpv4Address();
        $result = $v->validate(['subject' => '127.0.0.1']);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334']);
        $this->assertFalse($result->valid());
        // isIPv6Address
        $v->key('subject')->isIpv6Address();
        $result = $v->validate(['subject' => '2001:0db8:85a3:08d3:1319:8a2e:0370:7334']);
        $this->assertTrue($result->valid());
        $result = $v->validate(['subject' => '127.0.0.1']);
        $this->assertFalse($result->valid());
    }

    public function test_dot_notation(): void
    {
        // Named Key
        $v = new Validator;
        $v->required('post.title')->isNotBlank()->isHavingLength(8, 128);
        $result = $v->validate(['post' => ['title' => 'Post Title']]);
        $this->assertTrue($result->valid());
        // Numeric Key
        $v = new Validator;
        $v->required('post.0.title')->isNotBlank()->isHavingLength(8, 128);
        $v->required('post.1.title')->isNotBlank()->isHavingLength(8, 128);
        $result = $v->validate([
            'post' => [
                ['title' => 'Post Title #1'],
                ['title' => 'Post Title #2'],
            ],
        ]);
        $this->assertTrue($result->valid());
    }

    /**
     * @dataProvider providesBlank
     */
    public function test_blank(mixed $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isBlank();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function providesBlank(): array
    {
        return [
            ['', true],
            [[], true],
            [0, false],
            ['0', false],
            ['one', false],
            [['one'], false],
        ];
    }

    /**
     * @dataProvider providesCallback
     */
    public function test_callback(mixed $subject, callable $callback, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->is($callback);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, callable, bool}>
     */
    public static function providesCallback(): array
    {
        return [
            ['vaibhav', fn ($value) => is_string($value), true],
            [60600, fn ($value) => is_int($value), true],
            ['vaibhav', fn ($value) => is_int($value), false],
            [60600, fn ($value) => is_string($value), false],
        ];
    }

    /**
     * @dataProvider providesCount
     */
    public function test_count(mixed $subject, int $number, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isHavingCount($number);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());

        // Test error message when validation fails
        if (! $valid && $subject !== null) {
            $v2 = new Validator;
            $v2->key('subject')->isHavingCount($number);
            $result2 = $v2->validate(['subject' => $subject]);
            $this->assertFalse($result2->valid());
            $this->assertStringContainsString((string) $number, $result2->errors()['subject']);
        }
    }

    /**
     * @return array<array{mixed, int, bool}>
     */
    public static function providesCount(): array
    {
        return [
            [['one'], 1, true],
            [['one', 'two'], 2, true],
            [['one', 'two', 'three'], 3, true],
            [['one', 'two', 'three'], 1, false],
            [['one'], 3, false],
        ];
    }

    public function test_count_message_method(): void
    {
        // Directly test Count rule's message() method for 100% coverage
        $rule = new \Filtr\Rule\Count(5);
        $message = $rule->message();
        $this->assertStringContainsString('5', $message);
        $this->assertStringContainsString('value(s)', $message);
    }

    /**
     * @dataProvider providesCreditCard
     */
    public function test_credit_card(string $subject, string|array|CreditCardType|null $type, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isCreditCard($type);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, string|array|CreditCardType|null, bool}>
     */
    public static function providesCreditCard(): array
    {
        return [
            ['340000000000009', null, true],
            ['340000000000009', CreditCardType::AMEX, true],
            ['340000000000009', CreditCardType::AMEX->value, true],
            ['6759649826438453', null, true],
            ['6759649826438453', CreditCardType::MAESTRO, true],
            ['5500000000000004', null, true],
            ['5500000000000004', CreditCardType::MASTERCARD, true],
            ['4111111111111111', null, true],
            ['4111111111111111', CreditCardType::VISA, true],
            ['4111111111111111', CreditCardType::AMEX, false],
            ['340000000000009', CreditCardType::MAESTRO, false],
            ['6759649826438453', CreditCardType::MASTERCARD, false],
            ['5500000000000004', CreditCardType::VISA, false],
        ];
    }

    /**
     * @dataProvider providesDateTime
     */
    public function test_date_time(string $subject, ?string $format, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isDateTime($format);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, string|null, bool}>
     */
    public static function providesDateTime(): array
    {
        return [
            ['2017-02-01 07:10:00', null, true],
            ['2017-02-01 07:10:00', 'Y-m-d H:i:s', true],
            ['2017-02-01', 'Y-m-d', true],
            ['2017-02-01 07:10:00', 'Y-m-d', false],
            ['2017-02-01', null, false],
        ];
    }

    /**
     * @dataProvider providesEmail
     */
    public function test_email(string $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isEmailAddress();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function providesEmail(): array
    {
        return [
            // Valid emails
            ['contact@vaibhavpandey.com', true],
            ['user.name@example.com', true],
            ['user+tag@example.co.uk', true],
            ['user_name@example-domain.com', true],
            ['user123@example123.com', true],
            ['a@b.co', true],
            ['test.email+tag@example.com', true],
            // Invalid emails
            ['vaibhavpandeyvpz', false],
            ['@example.com', false],
            ['user@', false],
            ['user @example.com', false],
            ['user@example', false],
            ['user..name@example.com', false],
            ['user@example..com', false],
            ['', true], // Empty string passes (optional validation)
            ['user@example@com', false],
            ['user@example.c', true], // PHP's filter_var allows single char TLD
        ];
    }

    /**
     * @dataProvider providesEqualsTo
     */
    public function test_equals_to(mixed $lhs, mixed $rhs, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isEqualTo($rhs);
        $result = $v->validate(['subject' => $lhs]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, mixed, bool}>
     */
    public static function providesEqualsTo(): array
    {
        return [
            [true, true, true],
            [1, 1, true],
            [true, 'true', true],
            [1, '1', true],
        ];
    }

    /**
     * @dataProvider providesIpAddress
     */
    public function test_ip_address(string $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isIpAddress();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function providesIpAddress(): array
    {
        return [
            ['127.0.0.1', true],
            ['2001:0db8:85a3:08d3:1319:8a2e:0370:7334', true],
            ['vaibhavpandey.com', false],
        ];
    }

    /**
     * @dataProvider providesLength
     */
    public function test_length(int $min, ?int $max, string $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isHavingLength($min, $max);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{int, int|null, string, bool}>
     */
    public static function providesLength(): array
    {
        return [
            [5, 10, 'Vaibhav', true],
            [8, 10, 'VPZ', false],
            [8, null, 'VPZ', false],
        ];
    }

    /**
     * @dataProvider providesMacAddress
     */
    public function test_mac_address(string $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isMacAddress();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function providesMacAddress(): array
    {
        return [
            ['00:a0:c9:14:c8:29', true],
            ['00:A0:C9:14:C8:29', true],
            ['00-1C-b3-09-85-15', true],
            ['00-1C-B3-09-85-15', true],
            ['00:a0:c9:14:c8', false],
            ['2001:0db8:85a3:08d3:1319:8a2e', false],
        ];
    }

    /**
     * @dataProvider providesNotBlank
     */
    public function test_not_blank(mixed $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isNotBlank();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function providesNotBlank(): array
    {
        return [
            [0, true],
            ['0', true],
            ['one', true],
            [['one'], true],
            ['', false],
            [[], false],
        ];
    }

    /**
     * @dataProvider providesNotEqualsTo
     */
    public function test_not_equals_to(mixed $lhs, mixed $rhs, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isNotEqualTo($rhs);
        $result = $v->validate(['subject' => $lhs]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, mixed, bool}>
     */
    public static function providesNotEqualsTo(): array
    {
        return [
            [true, '0', true],
            [1, 2, true],
            [true, '1', false],
            [1, '1', false],
        ];
    }

    /**
     * @dataProvider providesNotSameAs
     */
    public function test_not_same_as(mixed $lhs, mixed $rhs, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isNotSameAs($rhs);
        $result = $v->validate(['subject' => $lhs]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, mixed, bool}>
     */
    public static function providesNotSameAs(): array
    {
        return [
            [new \stdClass, new \stdClass, true],
            [true, 'true', true],
            [1, '1', true],
            [true, true, false],
            [1, 1, false],
        ];
    }

    /**
     * @dataProvider providesNumber
     */
    public function test_number(mixed $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isNumber();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function providesNumber(): array
    {
        return [
            ['10', true],
            [1, true],
            [0x1, true],
            ['VPZ', false],
            [false, false],
        ];
    }

    /**
     * @dataProvider providesOneOf
     */
    public function test_one_of(mixed $needle, array $haystack, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isOneOf($haystack);
        $result = $v->validate(['subject' => $needle]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, array, bool}>
     */
    public static function providesOneOf(): array
    {
        return [
            ['one', ['one', 'two', 'three'], true],
            ['four', ['one', 'two', 'three'], false],
        ];
    }

    public function test_range_message_method(): void
    {
        // Directly test Range rule's message() method for 100% coverage
        $rule = new \Filtr\Rule\Range(10, 20);
        $message = $rule->message();
        $this->assertStringContainsString('10', $message);
        $this->assertStringContainsString('20', $message);
        $this->assertStringContainsString('range', $message);
    }

    /**
     * @dataProvider providesRange
     */
    public function test_range(mixed $subject, int $min, int $max, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isInRange($min, $max);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());

        // Test error message when validation fails
        if (! $valid && $subject !== null) {
            $v2 = new Validator;
            $v2->key('subject')->isInRange($min, $max);
            $result2 = $v2->validate(['subject' => $subject]);
            $this->assertFalse($result2->valid());
            $this->assertStringContainsString((string) $min, $result2->errors()['subject']);
            $this->assertStringContainsString((string) $max, $result2->errors()['subject']);
        }
    }

    /**
     * @return array<array{int, int, int, bool}>
     */
    public static function providesRange(): array
    {
        return [
            [999, 99, 999, true],
            [69, 99, 999, false],
        ];
    }

    /**
     * @dataProvider providesRegExp
     */
    public function test_reg_exp(string $subject, string $regexp, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isMatchingWith($regexp);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, string, bool}>
     */
    public static function providesRegExp(): array
    {
        return [
            ['/user/12/posts', '~^/user/(\d+)/posts$~', true],
        ];
    }

    /**
     * @dataProvider providesSameAs
     */
    public function test_same_as(mixed $lhs, mixed $rhs, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isSameAs($rhs);
        $result = $v->validate(['subject' => $lhs]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, mixed, bool}>
     */
    public static function providesSameAs(): array
    {
        return [
            [true, true, true],
            [1, 1, true],
            [true, 'true', false],
            [1, '1', false],
        ];
    }

    /**
     * @dataProvider providesType
     */
    public function test_type(mixed $subject, string $type, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isOfType($type);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, string, bool}>
     */
    public static function providesType(): array
    {
        return [
            ['vaibhav', 'string', true],
            [true, 'boolean', true],
            [999, 'integer', true],
            [9.9, 'double', true],
            [['vpz'], 'array', true],
            ['vaibhav', 'boolean', false],
            [true, 'integer', false],
            [999, 'double', false],
            [9.9, 'array', false],
            [['vpz'], 'string', false],
        ];
    }

    /**
     * @dataProvider providesUrl
     */
    public function test_url(mixed $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isUrl();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());

        // Test error message when validation fails (covers validate method)
        if (! $valid && $subject !== null && $subject !== '') {
            $v2 = new Validator;
            $v2->key('subject')->isUrl();
            $result2 = $v2->validate(['subject' => $subject]);
            $this->assertFalse($result2->valid());
            $this->assertStringContainsString('URL', $result2->errors()['subject']);
        }
    }

    public function test_url_with_null(): void
    {
        // Test Url rule with null value (covers null path in validate)
        $v = new Validator;
        $v->key('subject')->isUrl();
        $result = $v->validate(['subject' => null]);
        $this->assertTrue($result->valid());
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function providesUrl(): array
    {
        return [
            // Valid URLs
            ['http://www.vaibhavpandey.com', true],
            ['https://github.com/vaibhapandeyvpz', true],
            ['http://example.com', true],
            ['https://example.com/path?query=value', true],
            ['http://subdomain.example.com', true],
            ['ftp://example.com', true],
            ['http://example.com:8080', true],
            // Invalid URLs
            ['/vaibhapandeyvpz?tab=repositories', false],
            ['not-a-url', false],
            ['http://', false],
            ['://example.com', false],
            ['http://.com', false],
            ['', false],
        ];
    }

    /**
     * @dataProvider providesDateTimeEdgeCases
     */
    public function test_date_time_edge_cases(mixed $subject, ?string $format, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isDateTime($format);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, string|null, bool}>
     */
    public static function providesDateTimeEdgeCases(): array
    {
        return [
            // DateTime object should pass
            [new \DateTime, null, true],
            [new \DateTime('2023-01-01'), null, true],
            // Valid formats
            ['2023-12-31 23:59:59', 'Y-m-d H:i:s', true],
            ['2023-01-01', 'Y-m-d', true],
            ['01/31/2023', 'm/d/Y', true],
            // Invalid formats
            ['invalid-date', null, false],
            ['2023-13-01', 'Y-m-d', true], // createFromFormat doesn't validate, just parses
            ['2023-02-30', 'Y-m-d', true], // createFromFormat doesn't validate, just parses
            ['25:00:00', 'H:i:s', true], // createFromFormat doesn't validate, just parses
            ['', null, true], // Empty string passes
            [null, null, true], // null passes
        ];
    }

    /**
     * @dataProvider providesLengthEdgeCases
     */
    public function test_length_edge_cases(mixed $subject, int $min, ?int $max, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isHavingLength($min, $max);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, int, int|null, bool}>
     */
    public static function providesLengthEdgeCases(): array
    {
        return [
            // Boundary conditions
            ['abc', 3, 3, true], // Exact match
            ['ab', 3, 3, false], // Too short
            ['abcd', 3, 3, false], // Too long
            ['abc', 0, null, true], // No max
            ['', 0, null, true], // Empty string with min 0
            ['', 1, null, true], // Empty string passes (optional validation)
            ['a', 1, 1, true], // Single character
            // Edge cases
            [null, 0, null, true], // null passes
            ['', 0, 0, true], // Empty with exact 0
            ['a', 1, 5, true], // Within range
            ['abcde', 1, 5, true], // At max
            ['abcdef', 1, 5, false], // Over max
        ];
    }

    /**
     * @dataProvider providesRangeEdgeCases
     */
    public function test_range_edge_cases(mixed $subject, int $min, int $max, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isInRange($min, $max);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());

        // Test error message when validation fails (covers message() method)
        if (! $valid && $subject !== null) {
            $v2 = new Validator;
            $v2->key('subject')->isInRange($min, $max);
            $result2 = $v2->validate(['subject' => $subject]);
            $this->assertFalse($result2->valid());
            $this->assertStringContainsString((string) $min, $result2->errors()['subject']);
            $this->assertStringContainsString((string) $max, $result2->errors()['subject']);
        }
    }

    /**
     * @return array<array{mixed, int, int, bool}>
     */
    public static function providesRangeEdgeCases(): array
    {
        return [
            // Boundary conditions
            [100, 100, 200, true], // At min
            [200, 100, 200, true], // At max
            [150, 100, 200, true], // In middle
            [99, 100, 200, false], // Below min
            [201, 100, 200, false], // Above max
            // Edge cases
            [0, 0, 0, true], // Same min and max
            [-10, -20, -5, true], // Negative range
            [-15, -20, -5, true], // In negative range
            [0, -10, 10, true], // Zero in range
            [null, 0, 100, true], // null passes
            ['100', 100, 200, true], // String number
            ['50', 100, 200, false], // String number below range
        ];
    }

    /**
     * @dataProvider providesCountEdgeCases
     */
    public function test_count_edge_cases(mixed $subject, int $number, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isHavingCount($number);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, int, bool}>
     */
    public static function providesCountEdgeCases(): array
    {
        return [
            // Arrays
            [[], 0, true], // Empty array
            [['a'], 1, true],
            [['a', 'b', 'c'], 3, true],
            [['a', 'b'], 3, false],
            // Strings are not countable with count() - only arrays and objects implementing Countable
            // Non-countable
            [null, 0, true], // null passes
            [123, 1, false], // Integer not countable
            [true, 1, false], // Boolean not countable
        ];
    }

    /**
     * @dataProvider providesCreditCardEdgeCases
     */
    public function test_credit_card_edge_cases(string $subject, string|array|CreditCardType|null $type, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isCreditCard($type);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, string|array|CreditCardType|null, bool}>
     */
    public static function providesCreditCardEdgeCases(): array
    {
        return [
            // Array of types
            ['4111111111111111', [CreditCardType::VISA, CreditCardType::AMEX], true],
            ['340000000000009', [CreditCardType::VISA, CreditCardType::AMEX], true],
            ['4111111111111111', [CreditCardType::AMEX], false],
            // Array of strings
            ['4111111111111111', ['visa', 'amex'], true],
            ['340000000000009', ['visa', 'amex'], true],
            // Invalid card numbers
            ['1234567890123456', null, false],
            ['411111111111111', null, false], // Too short
            ['41111111111111111', null, false], // Too long
            ['', null, true], // Empty passes
        ];
    }

    /**
     * @dataProvider providesIpAddressEdgeCases
     */
    public function test_ip_address_edge_cases(string $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isIpAddress();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, bool}>
     */
    public static function providesIpAddressEdgeCases(): array
    {
        return [
            // Valid IPv4
            ['192.168.1.1', true],
            ['10.0.0.1', true],
            ['172.16.0.1', true],
            ['0.0.0.0', true],
            ['255.255.255.255', true],
            // Valid IPv6
            ['::1', true],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334', true],
            ['2001:db8:85a3::8a2e:370:7334', true], // Compressed
            // Invalid IPs
            ['256.256.256.256', false],
            ['192.168.1', false],
            ['192.168.1.1.1', false],
            ['not.an.ip.address', false],
            ['', true], // Empty string passes (optional validation)
        ];
    }

    /**
     * @dataProvider providesRegExpEdgeCases
     */
    public function test_reg_exp_edge_cases(string $subject, string $regexp, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isMatchingWith($regexp);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{string, string, bool}>
     */
    public static function providesRegExpEdgeCases(): array
    {
        return [
            ['/user/12/posts', '~^/user/(\d+)/posts$~', true],
            ['/user/123/posts', '~^/user/(\d+)/posts$~', true],
            ['/user/abc/posts', '~^/user/(\d+)/posts$~', false],
            ['user/12/posts', '~^/user/(\d+)/posts$~', false], // Missing leading slash
            ['abc123', '~^[a-z]+\d+$~', true],
            ['ABC123', '~^[a-z]+\d+$~', false], // Case sensitive
            ['', '~^.*$~', true], // Empty matches
            ['', '~^.+$~', true], // Empty string passes (optional validation)
        ];
    }

    /**
     * @dataProvider providesOneOfEdgeCases
     */
    public function test_one_of_edge_cases(mixed $needle, array $haystack, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isOneOf($haystack);
        $result = $v->validate(['subject' => $needle]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, array, bool}>
     */
    public static function providesOneOfEdgeCases(): array
    {
        return [
            // Different types
            [1, [1, 2, 3], true],
            ['1', [1, 2, 3], false], // Strict comparison
            [true, [true, false], true],
            [false, [true, false], true],
            // Empty array
            ['value', [], false],
            // Mixed types
            [1, ['1', 1, true], true],
            [null, [null, 'value'], true], // null in array
            // Edge cases
            [0, [0, 1, 2], true],
            ['', ['', 'value'], true],
        ];
    }

    /**
     * @dataProvider providesNumberEdgeCases
     */
    public function test_number_edge_cases(mixed $subject, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isNumber();
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, bool}>
     */
    public static function providesNumberEdgeCases(): array
    {
        return [
            // Valid numbers
            ['10', true],
            ['10.5', true],
            ['-10', true],
            ['-10.5', true],
            ['0', true],
            ['0.0', true],
            [1, true],
            [1.5, true],
            [-1, true],
            [0x1, true],
            ['1e10', true], // Scientific notation
            // Invalid
            ['VPZ', false],
            ['10abc', false],
            ['abc10', false],
            [false, false],
            [true, false],
            [null, true], // null passes
            ['', true], // empty string passes
        ];
    }

    /**
     * @dataProvider providesTypeEdgeCases
     */
    public function test_type_edge_cases(mixed $subject, string $type, bool $valid): void
    {
        $v = new Validator;
        $v->key('subject')->isOfType($type);
        $result = $v->validate(['subject' => $subject]);
        $this->assertEquals($valid, $result->valid());
    }

    /**
     * @return array<array{mixed, string, bool}>
     */
    public static function providesTypeEdgeCases(): array
    {
        return [
            // Additional types
            [null, 'NULL', true],
            [null, 'string', true], // null passes (optional validation)
            [fopen('php://memory', 'r'), 'resource', true],
            [new \stdClass, 'object', true],
            // Edge cases
            [[], 'array', true],
            ['', 'string', true],
            [0, 'integer', true],
            [0.0, 'double', true],
            [false, 'boolean', true],
        ];
    }

    public function test_multiple_rules_chaining(): void
    {
        $v = new Validator;
        $v->required('email')
            ->isNotBlank()
            ->isEmailAddress()
            ->isHavingLength(5, 100);

        // Valid
        $result = $v->validate(['email' => 'test@example.com']);
        $this->assertTrue($result->valid());

        // Invalid - too short
        $result = $v->validate(['email' => 'a@b']);
        $this->assertFalse($result->valid());

        // Invalid - not email
        $result = $v->validate(['email' => 'notanemail']);
        $this->assertFalse($result->valid());

        // Invalid - blank
        $result = $v->validate(['email' => '']);
        $this->assertFalse($result->valid());
    }

    public function test_custom_required_message(): void
    {
        $v = new Validator;
        $v->required('field', 'Custom error message');
        $result = $v->validate([]);

        $this->assertFalse($result->valid());
        $this->assertArrayHasKey('field', $result->errors());
        $this->assertEquals('Custom error message', $result->errors()['field']);
    }

    public function test_error_messages(): void
    {
        $v = new Validator;
        $v->key('email')->isEmailAddress();
        $result = $v->validate(['email' => 'invalid']);

        $this->assertFalse($result->valid());
        $this->assertArrayHasKey('email', $result->errors());
        $this->assertStringContainsString('email', strtolower($result->errors()['email']));
    }

    public function test_result_class(): void
    {
        $result = new Result;

        // Initially valid
        $this->assertTrue($result->valid());
        $this->assertEmpty($result->errors());

        // Add error
        $result->error('field1', 'Error 1');
        $this->assertFalse($result->valid());
        $this->assertCount(1, $result->errors());
        $this->assertEquals('Error 1', $result->errors()['field1']);

        // Add another error
        $result->error('field2', 'Error 2');
        $this->assertFalse($result->valid());
        $this->assertCount(2, $result->errors());
        $this->assertEquals('Error 2', $result->errors()['field2']);

        // Overwrite error
        $result->error('field1', 'Updated Error');
        $this->assertEquals('Updated Error', $result->errors()['field1']);
    }

    public function test_dot_notation_edge_cases(): void
    {
        $v = new Validator;

        // Deep nesting
        $v->required('level1.level2.level3.value')->isNotBlank();
        $result = $v->validate([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'value' => 'test',
                    ],
                ],
            ],
        ]);
        $this->assertTrue($result->valid());

        // Missing intermediate level
        $result = $v->validate([
            'level1' => [
                'level2' => [],
            ],
        ]);
        $this->assertFalse($result->valid());

        // Non-array intermediate
        $result = $v->validate([
            'level1' => [
                'level2' => 'not-an-array',
            ],
        ]);
        $this->assertFalse($result->valid());
    }

    public function test_null_handling(): void
    {
        $v = new Validator;

        // Optional field with null - null passes for optional fields (not validated)
        $v->key('optional')->isNotBlank();
        $result = $v->validate(['optional' => null]);
        $this->assertTrue($result->valid()); // null is not validated for optional fields

        // Optional field missing
        $result = $v->validate([]);
        $this->assertTrue($result->valid()); // Missing optional field passes

        // Required field with null
        $v2 = new Validator;
        $v2->required('required')->isNotBlank();
        $result = $v2->validate(['required' => null]);
        $this->assertFalse($result->valid()); // Required field with null fails
    }

    public function test_equals_to_edge_cases(): void
    {
        $v = new Validator;

        // Float comparison
        $v->key('value')->isEqualTo(1.0);
        $result = $v->validate(['value' => 1]);
        $this->assertTrue($result->valid()); // Loose comparison

        // String vs number
        $v->key('value')->isEqualTo('1');
        $result = $v->validate(['value' => 1]);
        $this->assertTrue($result->valid()); // Loose comparison

        // Array comparison
        $v->key('value')->isEqualTo([1, 2, 3]);
        $result = $v->validate(['value' => [1, 2, 3]]);
        $this->assertTrue($result->valid());
    }

    public function test_same_as_edge_cases(): void
    {
        $v = new Validator;

        // String vs number (strict)
        $v->key('value')->isSameAs('1');
        $result = $v->validate(['value' => 1]);
        $this->assertFalse($result->valid()); // Strict comparison

        // Same object reference
        $obj = new \stdClass;
        $v->key('value')->isSameAs($obj);
        $result = $v->validate(['value' => $obj]);
        $this->assertTrue($result->valid());

        // Different object instances
        $v->key('value')->isSameAs(new \stdClass);
        $result = $v->validate(['value' => new \stdClass]);
        $this->assertFalse($result->valid());
    }
}
