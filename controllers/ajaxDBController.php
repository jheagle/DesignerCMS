<?php

use DesignerCms\Models\Database\AjaxDBConnect;

header('Content-Type: application/json');

$db = AjaxDBConnect::instantiateDB('', '', '', '', true, false);

if (empty($_POST['DBQueries'])) {
    unset($db);
    exit();
}

$queries = $_POST['DBQueries'];
$echoAll = false;
if (isset($_POST['DBEchoAll'])) {
    $echoAll = true;
}

if (!is_array($queries) && $db->requestValidation($queries)) {
    list($type, $query) = explode(':', $queries, 2);
    echo $db->$$type($query);
} elseif (is_array($queries)) {
    foreach ($queries as $queryItem) {
        if (!$db->requestValidation($queryItem)) {
            unset($db);
            exit();
        }
        list($type, $query) = explode(':', $queries, 2);
        if ($type === 'select' || $echoAll) {
            echo $db->$$type($query);
        } else {
            $db->$$type($query);
        }
    }
} else {
    unset($db);
    exit();
}
