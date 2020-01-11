<?php
/***************************************************************************************************
File:			header.php
Description:	Handles user/login information
				Opening HTML tags
				Navbar menu system (based on permissions)
				Included at the top of every script
***************************************************************************************************/

// Database Connection
include_once("dbconn.php");

// Globals
$cookie_name = 'jeremyquest';
// User Information
$uid = -1;
$uname = "";
$first_name = "";
$last_name = "";
// Allowed Permissions
$manage_users = false;
$logging = false;
$types = false;

// Handle logging-in process so user-specific data can be in header on login/logout
if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login" && $_GET['a'] == "login") {
	// Process login
	$userid = $mysqli->real_escape_string($_POST['userid']);
	$password = $_POST['password'];

	// Look for entry for indicated UserID
	$query = "SELECT hash, firstname, lastname, id, manage_users, logging, types FROM users WHERE userid = '" . $userid . "'";
	$result = $mysqli->query($query);
	
	// Login good until otherwise indicated bad
	$login_good = true;

	if($result->num_rows == 0) {
		// UserID not found
		$login_good = false;
	}
	else {
		// UserID found, now check hash
		$row = $result->fetch_assoc();
		$hash = $row['hash'];
		$input_hash = crypt($password, $hash);

		if ($hash != $input_hash) {
			// Bad password
			$login_good = false;
		}
		else {
			// Login is good - set user information and permissions
			$first_name = $row['firstname'];
			$last_name = $row['lastname'];
			$uid = $row['id'];
			$uname = $userid;
			if ($row['manage_users'])
				$manage_users = true;
			if ($row['logging'])
				$logging = true;
			if ($row['types'])
				$types = true;
		}
	}
	
	$ip = get_client_ip();

	if($login_good) {
		// Generate a hash for cookie
		$cost = 10;
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		$salt = sprintf("$2a$%02d$", $cost) . $salt;
		$hash = crypt(($first_name . $uid . time()), $salt);
		setcookie($cookie_name, $hash, time() + (86400 * 9999));
		$query = "INSERT INTO cookiehashes (userid, cookiehash, created, used) VALUES ({$uid}, '{$hash}', NOW(), NOW())";
		$mysqli->query($query);
		//Logging($mysqli, $uid, Logs::User, 0, "{$uname} ({$uid}) logged in from {$ip}");
	}
	else
		//Logging($mysqli, $uid, Logs::User, 0, "Failed login attempt for {$userid} from {$ip}");
}

// Process logout if applicable
if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login" && $_GET['a'] == "logout") {
	setcookie($cookie_name, "0", time() - 86400);
	$uid = -1;
}

// Cookie bad until confirmed good
$badcookie = false;

if(!isset($_COOKIE[$cookie_name])) {
    // Cookie Absent - do nothing
} else {
	// Cookie Present - see if it's legit
	$cookievalue = $_COOKIE[$cookie_name];
	$query = "SELECT cookiehashes.id AS id, cookiehashes.userid AS uid, users.id AS uid2, users.firstname AS firstname, users.lastname AS lastname, users.manage_users AS manage_users, users.logging AS logging, users.types AS types, users.userid AS userid FROM cookiehashes LEFT JOIN users ON cookiehashes.userid=users.id  WHERE cookiehashes.cookiehash='{$cookievalue}'";
	$result = $mysqli->query($query);
	if($result->num_rows == 0) {
		// No cookies found server side - clear client cookie
		setcookie($cookie_name, $hash, time() - 86400);
		$badcookie = true;
	}
	else {
		// Cookie found - set user information and permissions
		$row = $result->fetch_assoc();
		$uid = $row['uid'];
		$uname = $row['userid'];
		$first_name = $row['firstname'];
		$last_name = $row['lastname'];
		if ($row['manage_users'])
			$manage_users = true;
		if ($row['logging'])
			$logging = true;
		if ($row['types'])
			$types = true;
		$cookieid = $row['id'];
		// Touch cookie to keep alive after proper use
		$query = "UPDATE cookiehashes SET used = NOW() WHERE id={$cookieid}";
		$mysqli->query($query);
	}
}

// Start HTML
?>
<!doctype html>
<html lang="en">
	<head>
		<!-- Required meta tags -->
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

		<!-- Bootstrap CSS -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

		<title>JeremyQuest</title>
	</head>
	<body>
<?php
// Wrap page in container
Container();
// Navbar data
?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
	<a class="navbar-brand" href="index.php">Stoney Inventory</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbar">
		<ul class="navbar-nav mr-auto">
			<li class="nav-item<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "index") print " active"; ?>">
				<a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
			</li>
<?php
		// Only show these menu options if user is logged in
		if ($uid >= 0)
		{
?>
			<li class="nav-item<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "view") print " active"; ?>">
				<a class="nav-link" href="view.php">View</a>
			</li>

			<li class="nav-item<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "reports") print " active"; ?>">
				<a class="nav-link" href="reports.php">Reports</a>
			</li>
<?php
			// Check for permission to modify types
			if ($types || $manage_users)
			{
?>
				<li class="nav-item dropdown<?php $basename = basename($_SERVER["SCRIPT_FILENAME"], '.php'); if ($basename == "users" || $basename == "locations" || $basename == "vendors" || $basename == "items" || $basename == "categories") print " active"; ?>">
					<a class="nav-link dropdown-toggle" href="#" id="navbarMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Manage
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarMenuLink">
<?php
					if ($types)
					{
?>
						<a class="dropdown-item" href="items.php">Item Types</a>
						<a class="dropdown-item" href="categories.php">Item Categories</a>
						<a class="dropdown-item" href="locations.php">Locations</a>
						<a class="dropdown-item" href="vendors.php">Vendors</a>
<?php
					}
					// Check for permission to manage users
					if ($manage_users)
					{
?>
						<a class="dropdown-item" href="users.php">Users</a>
<?php
					}
?>
				</div>
			</li>
<?php
			}
			// Check for permission to view logging data
			if ($logging)
			{
?>
				<li class="nav-item<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "logs") print " active"; ?>">
					<a class="nav-link" href="logs.php">Logs</a>
				</li>
<?php
			}
			// Logged in, so display logout link
?>
			<li class="nav-item<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login") print " active"; ?>">
				<a class="nav-link" href="login.php?a=logout">Logout</a>
			</li>
<?php
		}
		// Not logged in, so display login link.
		else
		{
?>
			<li class="nav-item">
				<a class="nav-link<?php if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login") print " active"; ?>" href="login.php">Login</a>
			</li>
<?php
		}
?>
		</ul>
<?php
		// Show search field if user is logged in
		if ($uid >= 0)
		{
?>
		<form class="form-inline my-2 my-lg-0" action="search.php?a=s" method="post">
			<input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search" id="search" name="search">
			<button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
		</form>
<?php
		}
?>
	</div>
</nav>

<?php
if ($badcookie)
{
	RowText("Expired Session - Please login again");

	header("refresh:3;url=login.php");
	include_once("footer.php");
	die;
}

?>