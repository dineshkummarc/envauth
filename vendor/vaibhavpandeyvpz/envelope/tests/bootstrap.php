<?php

/*
 * This file is part of vaibhavpandeyvpz/envelope package.
 *
 * (c) Vaibhav Pandey <contact@vaibhavpandey.com>
 *
 * This source file is subject to the MIT license that is bundled with this source code in the LICENSE file.
 */

require_once __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__.'/..')->load();
}
