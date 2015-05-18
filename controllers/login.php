<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');

session_start();
$message = '';
$fail = 0;
if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
    require_once ($MODELS['dbConnectClass']);
    $table = "account";
    $db = DBConnect::instantiateDB('', '', '', '', false, true);
    if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $user = sanitizeInput($_POST['username']);
        $pass = sanitizeInput($_POST['password']);
        $result = $db->select_assoc("SELECT id FROM {$table} WHERE username='{$user}' AND password=SHA2('{$pass}', 512)");
        $count = count($result);
        if ($count === 1) {
            $_SESSION['username'] = $user;
            if (!empty($_SESSION['page'])) {
                list($dir, $file) = explode(':', $_SESSION['page']);
                unset($_SESSION['page']);
                header("location:{$$dir[$file]}");
            }
            header("location: {$HOME}/");
        } else {
            $message = 'Incorrect Username and Password';
            $fail = 1;
        }
    } else {
        $message = 'Username and Password are both required.';
        $fail = 2;
    }
} elseif (!empty($_POST['logout'])) {
    unset($_SESSION['username']);
    session_destroy();
    header("location: {$HOME}/");
}
header("location: {$HOME}/login.html?fail='{$fail}'");
