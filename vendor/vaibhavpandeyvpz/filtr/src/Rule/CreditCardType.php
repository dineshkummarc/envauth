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

namespace Filtr\Rule;

/**
 * Enumeration of supported credit card types.
 *
 * Used to restrict credit card validation to specific card types.
 */
enum CreditCardType: string
{
    /** American Express */
    case AMEX = 'amex';

    /** Maestro */
    case MAESTRO = 'maestro';

    /** Mastercard */
    case MASTERCARD = 'mc';

    /** Visa */
    case VISA = 'visa';
}
