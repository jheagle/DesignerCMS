<?php
$connection = "";
session_start();
if (!isset($_SESSION['username']) && empty($_SESSION['username'])) {
	require_once ('../resources/dbInfo.php');
	$table = "account";
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
	if (!empty($_POST['username']) && !empty($_POST['password'])) {
		$user = sanitize($_POST['username']);
		$pass = sanitize($_POST['password']);
		// Search the rows in the markers table
		$query = sprintf("SELECT * FROM %s WHERE username='%s' AND password='%s'", $table, $user, $pass);
		$result = mysql_query($query);
		if (!$result) {
			die("Invalid query: " . mysql_error() . "<br>" . $query);
		}
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