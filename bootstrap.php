<?php

use Core\Adaptors\Config;
use Core\Database\PhpDbConnect;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env.example', __DIR__ . '/.env');

$connection = Config::get('database.connection', 'mysql');
$hostname = Config::get("database.connections.$connection.hostname");
$database = Config::get("database.connections.$connection.database");
$username = Config::get("database.connections.$connection.username");
$password = Config::get("database.connections.$connection.password");


$db = PhpDbConnect::instantiateDB($connection, $hostname, $database, $username, $password, Config::get('system.testing'), Config::get('system.production'));
