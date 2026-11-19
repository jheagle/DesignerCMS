<?php

use Core\Database\PhpDbConnect;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env.example', __DIR__ . '/.env');

// Connection settings come from config (core/config/database.php, so from .env)
$db = PhpDbConnect::instantiateDB();
