<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');
header("Location: ../confirm-account.html");

session_start();
$message = '';
$fail = 0;
exit("Location: {$HOME}/confirm-account.html");

if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
    if (isset($_SESSION['page'])) {
        unset($_SESSION['page']);
    }
    require_once ($MODELS['dbConnectClass']);
    $table = "account";
    $db = DBConnect::instantiateDB('', '', '', '', true, false);
    if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $user = $db->sanitizeInput($_POST['username']);
        $pass = $db->sanitizeInput($_POST['password']);
        $passConfirm = $db->sanitizeInput($_POST['confirmpass']);
        $email = $db->sanitizeInput($_POST['email']);
        $emailConfirm = $db->sanitizeInput($_POST['confirmemail']);
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
            $result = $db->insert("INSERT INTO {$table} (username, password, email) VALUES('{$user}', SHA2('{$pass}', 512), '{$email}')");
            if ($result) {
                $_SESSION['username'] = $user;
                header("Location: {$HOME}/confirm-account.html");
            }
            $message = 'Unable to create account at this time.\nContact Support: contact@joshuaheagle.com';
            $fail = 7;
        }
    } elseif ((!empty($_POST['username']) || trim($_POST['username']) !== '') || (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $message = 'Username and Password are both required.';
        $fail = 2;
    }
}

if ($fail) {
    header("Location: {$HOME}/login.html?fail='{$fail}'");
} else {
    header("Location: {$HOME}/login.html");
}
