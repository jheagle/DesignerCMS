<?php

use Core\Database\PhpDbConnect;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
// .env is optional: a server may set the variables in its environment instead (later files win over earlier ones)
$dotenv->load(...array_filter([__DIR__ . '/.env.example', __DIR__ . '/.env'], 'is_file'));

// Connection settings come from config (core/config/database.php, so from .env)
$db = PhpDbConnect::instantiateDB();
