<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');

session_start();
$message = '';
$fail = 0;
if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
    if (isset($_SESSION['page'])) {
        unset($_SESSION['page']);
    }
    require_once ($MODELS['dbConnectClass']);
    $table = "account";
    $db = DBConnect::instantiateDB('', '', '', '', false, true);
    if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $user = sanitizeInput($_POST['username']);
        $pass = sanitizeInput($_POST['password']);
        $passConfirm = sanitizeInput($_POST['confirmpass']);
        $email = sanitizeInput($_POST['email']);
        $emailConfirm = sanitizeInput($_POST['confirmemail']);
        $result = $db->select_assoc("SELECT id FROM {$table} WHERE email='{$email}'");
        $count = count($result);
        if ($count) {
            $message = 'You may only have one email per account';
            $fail = 3;
        } elseif ($email !== $emailConfirm) {
            $message = 'Your email and confirmation do not match';
            $fail = 4;
        } elseif ($pass !== $passConfirm) {
            $message = 'Your password and confirmation do not match';
            $fail = 5;
        } else {
            $result = $db->select_assoc("SELECT id FROM {$table} WHERE username='{$user}'");
            $count = count($result);
            if ($count) {
                $message = 'Please select another username';
                $fail = 6;
            }
        }
        if (!$fail) {
            $db->insert("INSERT INTO {$table} (username, password, email) VALUES('{$user}', SHA2('{$pass}', 512), '{$email}')");
            $_SESSION['username'] = $user;
            header("location: {$HOME}/confirm-account.html");
        }
    } else {
        $message = 'Username and Password are both required.';
        $fail = 2;
    }
}
header("location: {$HOME}/login.html?fail='{$fail}'");
