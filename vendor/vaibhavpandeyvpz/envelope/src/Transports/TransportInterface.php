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

/**
 * Interface for mail delivery backends.
 *
 * @author Vaibhav Pandey <contact@vaibhavpandey.com>
 */
interface TransportInterface
{
    /**
     * Sends the given message using the transport.
     *
     * @param  Message  $message  The message to send.
     * @return bool True if the message was sent successfully, false otherwise.
     *
     * @throws \Exception if an error occurs during sending.
     */
    public function send(Message $message): bool;
}
