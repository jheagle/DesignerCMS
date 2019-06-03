<?php

require_once('dbInfo.php');
$table = "locations";
// Start JSON file
$json = [];
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
$product = sanitize($_GET["product"]);
$query = sprintf(
  "SELECT * FROM %s WHERE FIND_IN_SET('%s', REPLACE(product, ', ', ',')) ORDER BY name",
  $table,
  $product
);
if (isset($_GET["clinic"])) {
  $clinic = sanitize($_GET["clinic"]);
  $query = sprintf(
    "SELECT * FROM %s WHERE (name LIKE '%%%s%%' OR doctor LIKE '%%%s%%') AND FIND_IN_SET('%s', REPLACE(product, ', ', ',')) ORDER BY name",
    $table,
    $clinic,
    $clinic,
    $product
  );
  if (isset($_GET["lat"]) && isset($_GET["lng"])) {
    // Get parameters from URL
    $lat = sanitize($_GET["lat"]);
    $lng = sanitize($_GET["lng"]);
    $radius = sanitize($_GET["radius"]);
    $query = sprintf(
      "SELECT *, ( 6371 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM %s WHERE FIND_IN_SET('%s', REPLACE(product, ', ', ',')) AND (name LIKE '%%%s%%' OR doctor LIKE '%%%s%%') HAVING distance < '%s' ORDER BY distance",
      $lat,
      $lng,
      $lat,
      $table,
      $product,
      $clinic,
      $clinic,
      $radius
    );
  }
}
elseif (isset($_GET["lat"]) && isset($_GET["lng"])) {
  // Get parameters from URL
  $lat = sanitize($_GET["lat"]);
  $lng = sanitize($_GET["lng"]);
  $radius = sanitize($_GET["radius"]);
  $query = sprintf(
    "SELECT *, ( 6371 * acos( cos( radians('%s') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( lat ) ) ) ) AS distance FROM %s WHERE FIND_IN_SET('%s', REPLACE(product, ', ', ',')) HAVING distance < '%s' ORDER BY distance",
    $lat,
    $lng,
    $lat,
    $table,
    $product,
    $radius
  );
}
$result = mysql_query($query);
if (!$result) {
  die("Invalid query: " . mysql_error() . "<br>" . $query);
}

/*
  $count = mysql_num_rows($result);
  if ($count < 1) {
  $query = sprintf("SELECT * FROM %s WHERE FIND_IN_SET('%s', REPLACE(product, ', ', ',')) ORDER BY name", $table, $product);
  $result = mysql_query($query);
  if (!$result) {
  die("Invalid query: " . mysql_error() . "<br>" . $query);
  }
  } */

header("Content-type: application/json");
// Iterate through the rows, adding XML nodes for each
while ($row = @mysql_fetch_assoc($result)) {
  $json[] = $row;
}
mysql_close($connection);
echo json_encode($json);
?>
<?php

function cleanInput($input) {
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

function sanitize($input) {
  if (is_array($input)) {
    foreach ($input as $var => $val) {
      $output[$var] = sanitize($val);
    }
  }
  else {
    if (get_magic_quotes_gpc()) {
      $input = stripslashes($input);
    }
    $input = cleanInput($input);
    $output = mysql_real_escape_string($input);
  }
  return $output;
}
