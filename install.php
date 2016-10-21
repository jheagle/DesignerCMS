<?php

require_once 'global_include.php';
require_once $DATABASE['DBConnect__PHP'];
require_once $ENTITY['Entity'];

$db = PHPDBConnect::instantiateDB($testing, $production);

$selectTables = "SELECT CONCAT(TABLE_SCHEMA, '.', TABLE_NAME) as name FROM information_schema.TABLES WHERE TABLE_SCHEMA <> 'mysql' AND ENGINE = 'MyISAM' AND TABLE_TYPE = 'BASE TABLE'";

$alterQuery = [];
while ($table = $db->select_assoc($selectTables)) {
    $alterQuery[] = "ALTER TABLE {$table['name']} engine=InnoDB";
}

if (count($alterQuery)) {
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

    $query = implode(";\n", $alterQuery);
    var_dump($query);

    $result = $db->alter($query);
}

$selectTables = "SELECT TABLE_SCHEMA AS dname, CONCAT(TABLE_SCHEMA, '.', TABLE_NAME) AS tname FROM information_schema.TABLES WHERE TABLE_SCHEMA <> 'mysql' AND TABLE_TYPE = 'BASE TABLE' ORDER BY dname";

// create an automated script to convert to utf8mb4, follow tips from https://mathiasbynens.be/notes/mysql-utf8mb4

$alterQuery = [];
$database = '';
while ($fields = $db->select_assoc($selectTables)) {
    if ($database !== $fields['dname']) {
        $database = $table['dname'];
        $alterQuery[] = "ALTER DATABASE {$fields['dname']} CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci";
    }
    $alterQuery[] = "ALTER TABLE {$fields['tname']} CONVERT TO CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci";
}

if (count($alterQuery)) {
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

    $query = implode(";\n", $alterQuery);
    var_dump($query);

    $result = $db->alter($query);
}

