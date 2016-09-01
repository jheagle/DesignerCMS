<?php

$connection = "";
session_start();
if (empty($_SESSION['username'])) {
    header("location:../login.html");
} else {
    require_once ('../resources/dbInfo.php');
    $table = "locations";
    // Opens a connection to a mySQL server
    $connection = mysql_connect($host, $username, $password);
    if (!$connection) {
        die("Not connected : " . mysql_error());
    }
    // Set the active mySQL database
    $db_selected = mysql_select_db($database);
    if (!$db_selected) {
        die("Can\'t use db : " . mysql_error());
    }
    // if post less than 1, this is a call for select all accounts
    if (count($_POST) < 1) {
        // Start JSON file
        $json = array();
        // Search the rows in the markers table
        $query = sprintf("SELECT * FROM %s ORDER BY name", $table);
        if (isset($_GET["filter"])) {
            $filter = explode(",", sanitize($_GET["filter"]));
            if ($filter != "" && $filter != "all" && $filter[0] != "all") {
                $s = parseGetArray($filter);
                $query = sprintf("SELECT * FROM %s %s ORDER BY name", $table, $s);
            }
        }
        $result = mysql_query($query);
        if (!$result) {
            die("Invalid query: " . mysql_error() . "<br>" . $query);
        }
        header("Content-type: application/json");
        // Iterate through the rows
        while ($row = @mysql_fetch_assoc($result)) {
            $json[] = $row;
        }
        mysql_close($connection);
        echo json_encode($json);
    } else {
        $location_id = intval(sanitize($_POST["id"]));
        if (!isset($_POST["name"])) {
            header("location:../admin/");
        }
        $location_name = sanitize($_POST["name"]);
        if (!isset($_POST["lat"])) {
            header("location:../admin/");
        }
        $latitude = floatval(sanitize($_POST["lat"]));
        if (!isset($_POST["lng"])) {
            header("location:../admin/");
        }
        $longitude = floatval(sanitize($_POST["lng"]));
        if (!isset($_POST["contact"])) {
            header("location:../admin/");
        }
        $contact = sanitize($_POST["contact"]);
        if (!isset($_POST["address"])) {
            header("location:../admin/");
        }
        $address = sanitize($_POST["address"]);
        $telephone = isset($_POST["tel"]) ? sanitize(preg_replace('/\D+/', '', $_POST["tel"])) : '';
        $link = isset($_POST["link"]) ? sanitize($_POST["link"]) : '';
        $product = '';
        if (isset($_POST["product"])) {
            $product = 'product';
        }
        $info = isset($_POST["info"]) ? sanitize($_POST["info"]) : '';
        if (!empty($_POST["id"])) {
            if (isset($_POST['create'])) {
                // Search the rows in the markers table
                $query = sprintf("UPDATE %s SET name='%s', lat=%f, lng=%f, contact='%s', address='%s', tel=%s, link='%s', product='%s', info='%s' WHERE id=%d", $table, $location_name, $latitude, $longitude, $contact, $address, $telephone, $link, $product, $info, $location_id);
                if (!mysql_query($query)) {
                    die("Invalid query: " . mysql_error() . "<br>" . $query);
                }
                mysql_close($connection);
                header("location:../admin/");
                echo "A row has been modified";
            } elseif (isset($_POST['delete'])) {
                // Search the rows in the markers table
                $query = sprintf("DELETE FROM %s WHERE id=%d", $table, $location_id);
                if (!mysql_query($query)) {
                    die("Invalid query: " . mysql_error() . "<br>" . $query);
                }
                mysql_close($connection);
                header("location:../admin/");
                echo "A row has been deleted";
            }
        } else {
            if (isset($_POST['create'])) {
                // Search the rows in the markers table
                $query = sprintf("INSERT INTO %s (name, lat, lng, contact, address, tel, link, product, info) VALUES ('%s', %f, %f, '%s', '%s', '%s', '%s', '%s', '%s')", $table, $location_name, $latitude, $longitude, $contact, $address, $telephone, $link, $product, $info);
                if (!mysql_query($query)) {
                    die("Invalid query: " . mysql_error() . "<br>" . $query);
                }
                mysql_close();
                header("location:../admin/");
                echo "A new row has been added";
            } else {
                mysql_close();
                header("location:../admin/");
                echo "Invalid Request: No selection for deletion";
            }
        }
    }
}
?>
<?php

function cleanInput($input) {
    $search = array('@<script[^>]*?>.*?</script>@si', // Strip out javascript
        '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
        '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
        '@<![\s\S]*?--[ \t\n\r]*>@' // Strip multi-line comments
    );
    $output = preg_replace($search, '', $input);
    return $output;
}

?>
<?php

function sanitize($input) {
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
<?php

function parseGetArray($input) {
    $queryString = "WHERE ";
    $cnt = 0;
    if (is_array($input)) {
        foreach ($input as $val) {
            if ($val == "all") {
                $queryString = "";
                break;
            }
            $queryString .= $cnt > 0 ? sprintf(" AND FIND_IN_SET('%s',  REPLACE(product, ', ', ','))", $val) : sprintf("FIND_IN_SET('%s', REPLACE(product, ', ', ','))", $val);
            $cnt++;
        }
    } else {
        $queryString = "";
    }
    return $queryString;
}
