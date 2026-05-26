# Envelope

[![Latest Version](https://img.shields.io/packagist/v/vaibhavpandeyvpz/envelope.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/envelope)
[![Downloads](https://img.shields.io/packagist/dt/vaibhavpandeyvpz/envelope.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/envelope)
[![PHP Version](https://img.shields.io/packagist/php-v/vaibhavpandeyvpz/envelope.svg?style=flat-square)](https://packagist.org/packages/vaibhavpandeyvpz/envelope)
[![License](https://img.shields.io/packagist/l/vaibhavpandeyvpz/envelope.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/github/actions/workflow/status/vaibhavpandeyvpz/envelope/tests.yml?branch=master&style=flat-square)](https://github.com/vaibhavpandeyvpz/envelope/actions)

A lightweight, flexible PHP library for sending RFC 2046 & 822 compliant emails with a fluent API and multiple transport backends.

> **Envelope** - A wrapper for your email messages, ensuring they're properly formatted and delivered through your preferred channel.

## Features

- ✅ **RFC 822 Compliant** - Full implementation of RFC 822 email address handling
- ✅ **RFC 2046 Compliant** - Proper multipart MIME message composition
- ✅ **Fluent API** - Chainable methods for building emails intuitively
- ✅ **Multiple Transports** - Support for SMTP, Mailgun, and Resend backends
- ✅ **SMTP Support** - Direct SMTP delivery with TLS/SSL encryption
- ✅ **Mailgun Integration** - Native Mailgun API support (US & EU regions)
- ✅ **Resend Integration** - Native Resend API support
- ✅ **Address Handling** - RFC 822 compliant address parsing and formatting
- ✅ **Multipart Messages** - Automatic handling of text/html alternatives and attachments
- ✅ **Attachment Support** - Easy file attachments with automatic MIME type detection
- ✅ **Header Management** - Flexible header manipulation via HeaderBag
- ✅ **Modern PHP** - Built for PHP 8.2+ with modern language features
- ✅ **Zero Dependencies** - No external dependencies required (except for testing)

## Installation

Install via Composer:

```bash
composer require vaibhavpandeyvpz/envelope
```

## Quick Start

```php
<?php

use Envelope\Mailer;
use Envelope\Transports\SmtpTransport;

// Create a transport and mailer
$transport = new SmtpTransport('smtp.example.com', 587, 'tls', 'user', 'pass');
$mailer = new Mailer($transport);

// Build and send an email
$mailer->from('sender@example.com', 'Sender Name')
    ->to('recipient@example.com', 'Recipient Name')
    ->subject('Hello from Envelope')
    ->text('This is a plain text message.')
    ->html('<h1>Hello!</h1><p>This is an HTML message.</p>')
    ->send();
```

## Usage

### Basic Email Sending

The `Mailer` class provides a fluent interface for building and sending emails:

```php
use Envelope\Mailer;
use Envelope\Transports\SmtpTransport;

$transport = new SmtpTransport('smtp.example.com', 587, 'tls', 'user', 'pass');
$mailer = new Mailer($transport);

$mailer->from('sender@example.com', 'Sender Name')
    ->to('recipient@example.com', 'Recipient Name')
    ->subject('Hello World')
    ->text('Plain text content')
    ->send();
```

### Address Handling

Envelope supports RFC 822 compliant email addresses. You can use strings or `Address` objects:

```php
use Envelope\Address;

// Using strings
$mailer->to('user@example.com', 'User Name');

// Using Address objects
$address = new Address('user@example.com', 'User Name');
$mailer->to($address);

// Parsing from string format
$address = Address::fromString('"User Name" <user@example.com>');
$mailer->to($address);
```

### Multiple Recipients

You can add multiple recipients using `to()`, `cc()`, and `bcc()`:

```php
$mailer->from('sender@example.com')
    ->to('recipient1@example.com', 'Recipient 1')
    ->to('recipient2@example.com', 'Recipient 2')
    ->cc('cc@example.com', 'CC Recipient')
    ->bcc('bcc@example.com', 'BCC Recipient')
    ->subject('Multiple Recipients')
    ->text('This email has multiple recipients.')
    ->send();
```

### Text and HTML Content

Envelope automatically handles both plain text and HTML content:

```php
// Plain text only
$mailer->text('This is plain text content.');

// HTML only
$mailer->html('<h1>Hello</h1><p>This is HTML content.</p>');

// Both text and HTML (creates multipart/alternative)
$mailer->text('Plain text version')
    ->html('<h1>HTML version</h1>');
```

When both text and HTML are provided, Envelope automatically creates a `multipart/alternative` message, allowing email clients to display the appropriate version.

### Attachments

Attach files to your emails:

```php
$mailer->from('sender@example.com')
    ->to('recipient@example.com')
    ->subject('Email with Attachment')
    ->text('Please find the attached file.')
    ->attach('/path/to/file.pdf')
    ->send();
```

You can also specify a custom filename and content type:

```php
$mailer->attach('/path/to/file.pdf', 'document.pdf', 'application/pdf');
```

When attachments are added, Envelope automatically converts the message to `multipart/mixed` format.

### Custom Headers

Access and modify headers directly through the message:

```php
$message = $mailer->getMessage();
$message->getHeaders()
    ->set('X-Custom-Header', 'Custom Value')
    ->add('X-Another-Header', 'Another Value');
```

### Transport System

Envelope uses a contract-based transport system that allows you to choose how emails are delivered.

#### SMTP Transport

Send emails directly via SMTP:

```php
use Envelope\Mailer;
use Envelope\Transports\SmtpTransport;

// Basic SMTP (no encryption)
$transport = new SmtpTransport('smtp.example.com', 25);

// SMTP with TLS
$transport = new SmtpTransport('smtp.example.com', 587, 'tls', 'username', 'password');

// SMTP with SSL
$transport = new SmtpTransport('smtp.example.com', 465, 'ssl', 'username', 'password');

// With custom timeout
$transport = new SmtpTransport('smtp.example.com', 587, 'tls', 'user', 'pass', 60);

$mailer = new Mailer($transport);
```

**Constructor Parameters:**

- `string $host` - SMTP server hostname
- `int $port` - SMTP server port (default: 25)
- `?string $encryption` - Encryption type: `'tls'`, `'ssl'`, or `null` (default: `null`)
- `?string $username` - SMTP username for authentication (default: `null`)
- `?string $password` - SMTP password for authentication (default: `null`)
- `int $timeout` - Connection timeout in seconds (default: 30)

**Methods:**

- `getHost(): string` - Get the SMTP host
- `getPort(): int` - Get the SMTP port
- `getEncryption(): ?string` - Get the encryption type
- `getUsername(): ?string` - Get the SMTP username
- `getPassword(): ?string` - Get the SMTP password
- `getTimeout(): int` - Get the connection timeout

#### Mailgun Transport

Send emails via Mailgun API:

```php
use Envelope\Mailer;
use Envelope\Transports\MailgunTransport;

// US region (default)
$transport = new MailgunTransport('your-domain.com', 'your-api-key');

// EU region
$transport = new MailgunTransport('your-domain.com', 'your-api-key', 'eu');

$mailer = new Mailer($transport);
```

**Constructor Parameters:**

- `string $domain` - Your Mailgun domain
- `string $apiKey` - Your Mailgun API key
- `string $region` - Region: `'us'` or `'eu'` (default: `'us'`)

**Methods:**

- `getDomain(): string` - Get the Mailgun domain
- `getApiKey(): string` - Get the API key
- `getRegion(): string` - Get the region

#### Resend Transport

Send emails via Resend API:

```php
use Envelope\Mailer;
use Envelope\Transports\ResendTransport;

$transport = new ResendTransport('your-api-key');
$mailer = new Mailer($transport);
```

**Constructor Parameters:**

- `string $apiKey` - Your Resend API key

**Methods:**

- `getApiKey(): string` - Get the API key

#### Custom Transports

Create your own transport by implementing `TransportInterface`:

```php
use Envelope\Transports\TransportInterface;
use Envelope\Message;

class CustomTransport implements TransportInterface
{
    public function __construct(
        private readonly string $apiEndpoint,
        private readonly string $apiKey
    ) {}

    public function send(Message $message): bool
    {
        // Your custom sending logic
        // Use $message->toString() to get the RFC-compliant email string
        // Or access message properties directly

        return true; // Return true on success, false on failure
    }
}

$mailer = new Mailer(new CustomTransport('https://api.example.com', 'key'));
```

## API Reference

### Mailer

The main class for building and sending emails.

#### Constructor

```php
public function __construct(TransportInterface $transport)
```

Creates a new `Mailer` instance with the specified transport.

#### Methods

##### `from(string|Address $address, ?string $name = null): self`

Sets the sender address. Can be called multiple times to add multiple senders.

```php
$mailer->from('sender@example.com', 'Sender Name');
```

##### `to(string|Address $address, ?string $name = null): self`

Adds a recipient address. Can be called multiple times to add multiple recipients.

```php
$mailer->to('recipient@example.com', 'Recipient Name');
```

##### `cc(string|Address $address, ?string $name = null): self`

Adds a CC recipient address. Can be called multiple times.

```php
$mailer->cc('cc@example.com', 'CC Recipient');
```

##### `bcc(string|Address $address, ?string $name = null): self`

Adds a BCC recipient address. Can be called multiple times.

```php
$mailer->bcc('bcc@example.com', 'BCC Recipient');
```

##### `subject(string $subject): self`

Sets the email subject.

```php
$mailer->subject('Hello World');
```

##### `text(string $body): self`

Sets the plain text body. If HTML content already exists, creates a `multipart/alternative` message.

```php
$mailer->text('Plain text content');
```

##### `html(string $body): self`

Sets the HTML body. If text content already exists, creates a `multipart/alternative` message.

```php
$mailer->html('<h1>HTML content</h1>');
```

##### `attach(string $path, ?string $filename = null, ?string $contentType = null): self`

Attaches a file to the email. Automatically converts the message to `multipart/mixed` if needed.

- `$path` - Path to the file to attach
- `$filename` - Optional custom filename (defaults to basename of `$path`)
- `$contentType` - Optional MIME type (auto-detected if not provided)

```php
$mailer->attach('/path/to/file.pdf', 'document.pdf', 'application/pdf');
```

**Throws:** `InvalidArgumentException` if the file doesn't exist.

##### `send(): bool`

Sends the email using the configured transport. Returns `true` on success, `false` on failure.

```php
$result = $mailer->send();
```

**Throws:** `RuntimeException` if the transport fails to send the email.

##### `getMessage(): Message`

Gets the underlying `Message` object for direct manipulation.

```php
$message = $mailer->getMessage();
```

##### `getTransport(): TransportInterface`

Gets the configured transport instance.

```php
$transport = $mailer->getTransport();
```

### Message

Represents an email message with headers, body, and MIME parts.

#### Methods

##### `from(string|Address $address, ?string $name = null): self`

Adds a sender address.

##### `getFrom(): Address[]`

Returns an array of sender addresses.

##### `to(string|Address $address, ?string $name = null): self`

Adds a recipient address.

##### `getTo(): Address[]`

Returns an array of recipient addresses.

##### `cc(string|Address $address, ?string $name = null): self`

Adds a CC recipient address.

##### `getCc(): Address[]`

Returns an array of CC recipient addresses.

##### `bcc(string|Address $address, ?string $name = null): self`

Adds a BCC recipient address.

##### `getBcc(): Address[]`

Returns an array of BCC recipient addresses.

##### `subject(string $subject): self`

Sets the email subject.

##### `getSubject(): ?string`

Returns the email subject, or `null` if not set.

##### `getHeaders(): HeaderBag`

Returns the `HeaderBag` instance for header manipulation.

##### `getBody(): ?string`

Returns the message body, or `null` if not set.

##### `setBody(string $body): self`

Sets the message body.

##### `addPart(MimePart $part): self`

Adds a MIME part to the message.

##### `getParts(): MimePart[]`

Returns an array of MIME parts.

##### `isMultipart(): bool`

Returns `true` if the message has multiple parts.

##### `toString(bool $includeBcc = true): string`

Converts the message to an RFC-compliant string representation.

- `$includeBcc` - Whether to include BCC headers in the output (default: `true`)

### Address

Represents an RFC 822 compliant email address.

#### Constructor

```php
public function __construct(string $email, ?string $name = null)
```

Creates a new `Address` instance.

**Throws:** `InvalidArgumentException` if the email address is invalid.

#### Static Methods

##### `fromString(string $address): self`

Parses an address string in the format `"Name" <email@example.com>` or `email@example.com`.

```php
$address = Address::fromString('"John Doe" <john@example.com>');
```

#### Properties

- `string $email` - The email address
- `?string $name` - The display name (optional)

#### Methods

##### `getEmail(): string`

Returns the email address.

##### `getName(): ?string`

Returns the display name, or `null` if not set.

##### `__toString(): string`

Returns the address in RFC 822 format: `"Name" <email@example.com>` or `email@example.com`.

### HeaderBag

Collection of email headers.

#### Methods

##### `add(string $name, string $value): self`

Adds a header (allows multiple headers with the same name).

```php
$headers->add('X-Custom', 'Value 1')->add('X-Custom', 'Value 2');
```

##### `set(string $name, string $value): self`

Sets a header (replaces existing headers with the same name).

```php
$headers->set('Content-Type', 'text/html');
```

##### `get(string $name): ?string`

Gets the first header value with the given name, or `null` if not found.

```php
$contentType = $headers->get('Content-Type');
```

##### `remove(string $name): self`

Removes all headers with the given name.

```php
$headers->remove('X-Custom');
```

##### `toString(): string`

Converts all headers to a string representation.

##### `getIterator(): \Traversable`

Returns an iterator over all headers.

### MimePart

Represents a MIME part as per RFC 2046.

#### Methods

##### `getHeaders(): HeaderBag`

Returns the header bag for this MIME part.

##### `setBody(string $body): self`

Sets the body content.

##### `getBody(): ?string`

Returns the body content, or `null` if not set.

##### `addPart(MimePart $part): self`

Adds a child MIME part.

##### `getParts(): MimePart[]`

Returns an array of child MIME parts.

##### `isMultipart(): bool`

Returns `true` if this part has child parts.

##### `getBoundary(): string`

Returns the boundary string for multipart messages.

##### `setBoundary(string $boundary): self`

Sets a custom boundary string.

##### `toString(): string`

Converts the MIME part to a string representation.

### TransportInterface

Interface for mail delivery backends.

#### Methods

##### `send(Message $message): bool`

Sends the given message using the transport. Returns `true` on success, `false` on failure.

**Throws:** `Exception` if an error occurs during sending.

## Requirements

- PHP 8.2 or higher

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/vaibhavpandeyvpz/envelope).
