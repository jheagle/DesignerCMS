<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');

session_start();
$message = '';
$fail = 0;
if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
    require_once ($MODELS['phpDBConnect']);
    $table = "account";
    $db = PHPDBConnect::instantiateDB('', '', '', '', false, true);
    if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $user = $db->sanitizeInput($_POST['username']);
        $pass = $db->sanitizeInput($_POST['password']);
        $result = $db->select_assoc("SELECT id FROM {$table} WHERE username='{$user}' AND password=SHA2('{$pass}', 512)");
        $count = count($result);
        if ($count === 1) {
            $_SESSION['username'] = $user;
            if (!empty($_SESSION['page'])) {
                list($dir, $file) = explode(':', $_SESSION['page']);
                unset($_SESSION['page']);
                header("Location: {$$dir[$file]}");
            }
            header("Location: {$HOME}/");
        } else {
            $message = 'Incorrect Username and Password';
            $fail = 1;
        }
    } elseif ((!empty($_POST['username']) || trim($_POST['username']) !== '') || (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $message = 'Username and Password are both required.';
        $fail = 2;
    }
} elseif (!empty($_POST['logout'])) {
    unset($_SESSION['username']);
    session_destroy();
    header("Location: {$HOME}/");
}
if ($fail) {
    header("Location: {$HOME}/login.html?fail={$fail}");
} else {
    header("Location: {$HOME}/login.html");
}
