<?php

$connection = "";
session_start();
if (empty($_SESSION['username'])) {
    header("location:../login.html");
} else {
    require_once('dbInfo.php');
    $table = "account";
    // Opens a connection to a mySQL server
    $connection = mysql_connect($host, $username, $password);
    if (!$connection) {
        die("Not connected : ".mysql_error());
    }
    // Set the active mySQL database
    $db_selected = mysql_select_db($database);
    if (!$db_selected) {
        die("Can\'t use db : ".mysql_error());
    }
    // if post less than 1, this is a call for select all accounts
    if (count($_POST) < 1) {
        // Start JSON file
        $json = [];
        // Search the rows in the markers table
        $query = sprintf(
          "SELECT * FROM %s WHERE username='%s'",
          $table,
          $_SESSION['username']
        );
        if ($_SESSION['admin'] == 0) {
            $query = sprintf("SELECT * FROM %s ORDER BY username", $table);
        }
        $result = mysql_query($query);
        if (!$result) {
            die("Invalid query: ".mysql_error()."<br>".$query);
        }
        header("Content-type: application/json");
        // Iterate through the rows
        while ($row = @mysql_fetch_assoc($result)) {
            $json[] = $row;
        }
        mysql_close($connection);
        // signal that it is admin by adding an empty row
        echo $_SESSION['admin'] == 0 ? rtrim(
            json_encode($json),
            ']'
          ).', {"id":"0", "username":"", "password":"", "type":"0"}]' : json_encode(
          $json
        );
    } else {// if post greater than 1, perfrom crud
        $account_id = intval(sanitize($_POST['id']));
        $user = $_SESSION['admin'] == 0 ? sanitize(
          $_POST['username']
        ) : $_SESSION['username'];
        $type = intval(sanitize($_POST["type"]));
        if (!empty($_POST['id'])) {// if there is an id, then this means that a row will be altered (update or destroy)
            if (isset($_POST['create'])) {
                // Search the rows in the markers table
                $query = sprintf(
                  "UPDATE %s SET username='%s' WHERE id=%d",
                  $table,
                  $user,
                  $account_id
                );
                if (isset($_POST['password']) && !empty($_POST['password'])) {
                    $pass = sanitize($_POST['password']);
                    $query = sprintf(
                      "UPDATE %s SET username='%s', password='%s' WHERE id=%d",
                      $table,
                      $user,
                      $pass,
                      $account_id
                    );
                }
                if (!mysql_query($query)) {
                    die("Invalid query: ".mysql_error()."<br>".$query);
                }
                if ($_SESSION['admin'] == 0) {
                    $query = sprintf(
                      "UPDATE %s SET username='%s', type=%d WHERE id=%d",
                      $table,
                      $user,
                      $type,
                      $account_id
                    );
                    if (isset($_POST['password']) && !empty($_POST['password'])) {
                        $pass = sanitize($_POST['password']);
                        $query = sprintf(
                          "UPDATE %s SET username='%s', password='%s', type=%d WHERE id=%d",
                          $table,
                          $user,
                          $pass,
                          $type,
                          $account_id
                        );
                    }
                    if (!mysql_query($query)) {
                        die("Invalid query: ".mysql_error()."<br>".$query);
                    }
                }

                $query = sprintf(
                  "SELECT * FROM %s WHERE username='%s' AND type=%d",
                  $table,
                  $_SESSION['username'],
                  $_SESSION['admin']
                );
                $result = mysql_query($query);
                if (!$result) {
                    die("Invalid query: ".mysql_error()."<br>".$query);
                }
                $count = mysql_num_rows($result);

                mysql_close($connection);

                if ($count == 1) {
                    header("location:../account/");
                    echo "A row has been modified";
                } else {
                    header("location:login.php");
                    echo "Account has changed";
                }
            } elseif (isset($_POST['delete'])) {
                $query = sprintf("DELETE FROM %s WHERE id=%d", $table,
                  $account_id);
                if (!mysql_query($query)) {
                    die("Invalid query: ".mysql_error()."<br>".$query);
                }

                if ($_SESSION['admin'] == 0) {
                    $query = sprintf(
                      "SELECT * FROM %s WHERE username='%s' AND type=%d",
                      $table,
                      $_SESSION['username'],
                      $_SESSION['admin']
                    );
                    $result = mysql_query($query);
                    if (!$result) {
                        die("Invalid query: ".mysql_error()."<br>".$query);
                    }
                    $count = mysql_num_rows($result);

                    mysql_close($connection);

                    if ($count == 1) {
                        header("location:../account/");
                    } else {
                        header("location:login.php");
                        echo "Account has changed";
                    }
                } else {
                    header("location:login.php");
                }
                echo "A row has been deleted";
            }
        } else {// if there is no id, then create the row
            if (isset($_POST['create'])) {
                if ($_SESSION['admin'] == 0) {
                    if (isset($_POST['password']) && !empty($_POST['password'])) {
                        $pass = sanitize($_POST['password']);
                        $query = sprintf(
                          "INSERT INTO %s (username, password, type) VALUES ('%s', '%s', %d)",
                          $table,
                          $user,
                          $pass,
                          $type
                        );
                        if (!mysql_query($query)) {
                            die("Invalid query: ".mysql_error()."<br>".$query);
                        }
                        mysql_close($connection);
                        header("location:../account/");
                        echo "A new row has been added";
                    } else {
                        header("location:../account/");
                        echo "Invalid Request: Password required.";
                    }
                } else {
                    header("location:login.php");
                    echo "Access Denied: You do not have this privilidge";
                }
            } else {
                mysql_close($connection);
                header("location:../account/");
                echo "Invalid Request: No selection for deletion";
            }
        }
    }
}
?>
<?php

function cleanInput($input)
{
    $search = [
      '@<script[^>]*?>.*?</script>@si', // Strip out javascript
      '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
      '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
      '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments
    ];
    $output = preg_replace($search, '', $input);

    return $output;
}

?>
<?php

function sanitize($input)
{
    if (is_array($input)) {
        foreach ($input as $var => $val) {
            $output[$var] = sanitize($val);
        }
    } else {
        if (get_magic_quotes_gpc()) {
            $input = stripslashes($input);
        }
        $input = cleanInput($input);
        $output = mysql_real_escape_string($input);
    }

    return $output;
}

?>