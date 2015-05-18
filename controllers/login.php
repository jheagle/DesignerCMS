<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/global_include.php');

$connection = "";
session_start();
if (empty($_SESSION['username']) || trim($_SESSION['username']) === '') {
    require_once ($MODELS['dbConnectClass']);
    $table = "account";
    $db = DBConnect::instantiateDB('', '', '', '', false, true);
    if ((!empty($_POST['username']) || trim($_POST['username']) !== '') && (!empty($_POST['password']) || trim($_POST['password']) !== '')) {
        $user = sanitizeInput($_POST['username']);
        $pass = sanitizeInput($_POST['password']);
        $db->select("SELECT id FROM {$table} WHERE username='{$user}' AND password='{$pass}'");
        $count = mysql_num_rows($result);
        if ($count == 1) {
            $_SESSION['username'] = $user;
            $row = @mysql_fetch_assoc($result);
            $_SESSION['admin'] = $row['type'];
            header("location:../admin/");
            echo "Logged In as " . $_SESSION['username'];
        } else {
            header("location:../login.html");
            echo "Incorrect Username and Password";
        }
    } else {
        mysql_close($connection);
        header("location:../login.html");
        echo "Username and Password are both required.";
    }
} else {
    unset($_SESSION['username']);
    unset($_SESSION['admin']);
    session_destroy();
    header("location:../login.html");
    echo "Logged Out";
}