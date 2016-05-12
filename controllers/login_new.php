<?php

if (!empty($_POST)) {
    if (!isset($ROOT)) {
        $ROOT = dirname(__DIR__);
    }
    require_once $ROOT.'/global_include.php';

    session_start();
    $message = '';
    $fail = 0;

    if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
        if (isset($_SESSION['page'])) {
            unset($_SESSION['page']);
        }
        require_once $MODELS['phpDBConnect'];
        $table = 'access_tracking';
        $db = PHPDBConnect::instantiateDB('', '', '', '', $testing, $production);
        if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
            $user = $db->sanitizeInput($_POST['username']);
            $pass = $db->sanitizeInput($_POST['password']);
            $passConfirm = $db->sanitizeInput($_POST['confirmpass']);
            $email = $db->sanitizeInput($_POST['email']);
            $emailConfirm = $db->sanitizeInput($_POST['confirmemail']);
            $result = $db->select_assoc("SELECT id FROM {$table} WHERE access_email='{$email}'");
            $count = count($result);
            if ($count) {
                $fail = 4; // 'You may only have one account per email.'
            } elseif ($email !== $emailConfirm) {
                $fail = 5; // 'Your email and confirmation do not match.'
            } elseif ($pass !== $passConfirm) {
                $fail = 6; // 'Your password and confirmation do not match.'
            } else {
                $result = $db->select_assoc("SELECT id FROM {$table} WHERE username='{$user}'");
                $count = count($result);
                if ($count) {
                    $fail = 7; // 'Please select another username.'
                }
            }
            if (!$fail) {
                $result = $db->insert("INSERT INTO account (username, password) VALUES('{$user}', SHA2('{$pass}', 512))");
                $accountId = $db->lastInsertId();
                $result = $db->insert("INSERT INTO {$table} (username, password, email) VALUES('{$user}', SHA2('{$pass}', 512), '{$email}')");
                if ($result) {
                    $_SESSION['username'] = $user;
                    header("Location: {$HOME}/confirm-account.html");
                }
                $fail = 8; // 'Unable to create account at this time.\nContact Support: contact@joshuaheagle.com'
            }
        } elseif ((!empty($_POST['username']) || trim($_POST['username']) !== '') || (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
            $fail = 2; // 'Username and Password are both required.'
        } else {
            $fail = 3; // 'Please Enter Username and Password.'
        }
    }
}

if ($fail) {
    header("Location: {$HOME}/login.html?fail={$fail}");
} else {
    header("Location: {$HOME}/login.html");
}
