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

/**
 * Abstract base class for validation rules.
 *
 * Provides a default implementation of the RuleInterface with
 * a customizable error message.
 */
abstract class Rule implements RuleInterface
{
    /**
     * The default error message.
     */
    protected string $message = 'This value is not valid.';

    /**
     * Returns the error message for this rule.
     *
     * @return string The error message to display when validation fails
     */
    public function message(): string
    {
        return $this->message;
    }
}
