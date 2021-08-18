<?php

use Core\Adaptors\Config;
use Core\Database\PhpDbConnect;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env.example', __DIR__ . '/.env');

$db = PhpDbConnect::instantiateDB('', '', '', '', Config::get('system.testing'), Config::get('system.production'));
