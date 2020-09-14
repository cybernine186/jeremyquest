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
$username = "";
// Allowed Permissions
$permission_handins = false;
$permission_trades = false;
$permission_looted = false;
$permission_dropped = false;
$permission_destroyed = false;
$permission_inventory = false;
$permission_logging = false;
$permission_users = false;
$permission_connections = false;

abstract class Logs
{
	const Session = 0;
	const User = 1;
	const Rollback = 2;
	const Transfer = 3;
}


// $uname is what user is trying to use to login
// $username is what is loaded as handle after verified, and used for content


// Handle logging-in process so user-specific data can be in header on login/logout
if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login" && isset($_GET['a']) && $_GET['a'] == "login") {
	// Process login
	$uname = $admindb->real_escape_string($_POST['uname']);
	$password = $_POST['password'];

	// Look for entry for indicated UserID
	$query = "SELECT hash, id, permission_handins, permission_trades, permission_looted, permission_dropped, permission_destroyed, permission_inventory, permission_logging, permission_users, permission_connections FROM users WHERE username = '" . $uname . "'";
	$result = $admindb->query($query);
	
	// Login good until otherwise indicated bad
	$login_good = true;

	if($result->num_rows == 0) {
		// UserID not found
		$login_good = false;
		Logging($admindb, 0, Logs::Session, "Failed Login Attempt - Unknown User Name: {$uname} - " . get_client_ip());
	}
	else
	{
		// UserID found, now check hash
		$row = $result->fetch_assoc();
		$hash = $row['hash'];
		$input_hash = crypt($password, $hash);

		if ($hash != $input_hash) {
			// Bad password
			$login_good = false;
			Logging($admindb, 0, Logs::Session, "Failed Login Attempt - Bad Password for User: {$uname} - " . get_client_ip());
		}
		else
		{
			// Login is good - set user information and permissions
			$uid = $row['id'];
			$username = $uname;
			if ($row['permission_handins'])
				$permission_handins = true;
			if ($row['permission_trades'])
				$permission_trades = true;
			if ($row['permission_looted'])
				$permission_looted = true;
			if ($row['permission_dropped'])
				$permission_dropped = true;			
			if ($row['permission_destroyed'])
				$permission_destroyed = true;
			if ($row['permission_inventory'])
				$permission_inventory = true;
			if ($row['permission_logging'])
				$permission_logging = true;
			if ($row['permission_users'])
				$permission_users = true;
			if ($row['permission_connections'])
				$permission_connections = true;
			
			Logging($admindb, 0, Logs::Session, "Successful Login - User: {$uname} - " . get_client_ip());
		}
	}
	
	$ip = get_client_ip();

	if($login_good)
	{
		// Generate a hash for cookie
		$cost = 10;
		$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
		$salt = sprintf("$2a$%02d$", $cost) . $salt;
		$hash = crypt(($uname . $uid . time()), $salt);
		setcookie($cookie_name, $hash, time() + (86400 * 9999));
		$query = "INSERT INTO cookiehashes (userid, cookiehash, created, used) VALUES ({$uid}, '{$hash}', NOW(), NOW())";
		$admindb->query($query);
		//Logging($admindb, $uid, Logs::User, 0, "{$uname} ({$uid}) logged in from {$ip}");
	}
	//else
		//Logging($admindb, $uid, Logs::User, 0, "Failed login attempt for {$userid} from {$ip}");
}

// Process logout if applicable
if(basename($_SERVER["SCRIPT_FILENAME"], '.php') == "login" && isset($_GET['a']) && $_GET['a'] == "logout") {
	setcookie($cookie_name, "0", time() - 86400);
	$uid = -1;
}

// Cookie bad until confirmed good
$badcookie = false;

if(!isset($_COOKIE[$cookie_name]))
{
    // Cookie Absent - do nothing
}
else
{
	// Cookie Present - see if it's legit
	
	$cookievalue = $_COOKIE[$cookie_name];
	$query = "SELECT cookiehashes.id AS id, cookiehashes.userid AS uid, users.id AS uid2, users.permission_handins AS permission_handins, users.permission_trades AS permission_trades, users.permission_looted AS permission_looted, users.permission_dropped AS permission_dropped, users.permission_destroyed AS permission_destroyed, users.permission_inventory AS permission_inventory, users.permission_logging AS permission_logging, users.permission_users AS permission_users, users.permission_connections AS permission_connections, users.username AS username FROM cookiehashes LEFT JOIN users ON cookiehashes.userid=users.id  WHERE cookiehashes.cookiehash='{$cookievalue}'";
	$result = $admindb->query($query);
	if($result->num_rows == 0)
	{
		// No cookies found server side - clear client cookie
		setcookie($cookie_name, "", time() - 86400);
		$badcookie = true;
	}
	else {
		// Cookie found - set user information and permissions
		$row = $result->fetch_assoc();
		$uid = $row['uid'];
		$username = $row['username'];
		if ($row['permission_handins'])
			$permission_handins = true;
		
		if ($row['permission_trades'])
			$permission_trades = true;
		
		if ($row['permission_looted'])
			$permission_looted = true;
		
		if ($row['permission_dropped'])
			$permission_dropped = true;
		
		if ($row['permission_destroyed'])
			$permission_destroyed = true;
		
		if ($row['permission_inventory'])
			$permission_inventory = true;
		
		if ($row['permission_logging'])
			$permission_logging = true;
		
		if ($row['permission_users'])
			$permission_users = true;
		
		if ($row['permission_connections'])
			$permission_connections = true;
		$cookieid = $row['id'];
		// Touch cookie to keep alive after proper use
		$query = "UPDATE cookiehashes SET used = NOW() WHERE id={$cookieid}";
		$admindb->query($query);
	}
}

$eqcgood = false;
$cname = "No Connection";

$query = "SELECT name, host, dbase, username, password FROM connections WHERE user = {$uid} AND selected = 1";
$result = $admindb->query($query);

if ($result->num_rows < 1)
{
	$eqcgood = false;
}
elseif ($result->num_rows > 1)
{
	data_error();
}
else
{
	$row = $result->fetch_assoc();

	$eqdb = new mysqli($row['host'], $row['username'], $row['password'], $row['dbase']);

	if ($eqdb->connect_errno)
	{
		RowText("Failed to connect to database.");
		$eqcgood = false;
	}
	else
	{
		$eqcgood = true;
		$cname = $row['name'];
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
	<a class="navbar-brand" href="index.php">JeremyQuest</a>
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
			// The READ list of menu options
			if ($eqcgood && ($permission_handins || $permission_trades || $permission_looted || $permission_dropped || $permission_destroyed))
			{
?>
				<li class="nav-item dropdown<?php $basename = basename($_SERVER["SCRIPT_FILENAME"], '.php'); if ($basename == "handins" || $basename == "trades" || $basename == "looted" || $basename == "dropped" || $basename == "destroyed") print " active"; ?>">
					<a class="nav-link dropdown-toggle" href="#" id="navbarMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						View
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarMenuLink">
<?php
					if ($permission_handins)
						print "<a class='dropdown-item' href='handins.php'>Handins</a>";
					if ($permission_trades)
						print "<a class='dropdown-item' href='trades.php'>Trades</a>";
					if ($permission_looted)
						print "<a class='dropdown-item' href='looted.php'>Looted</a>";
					if ($permission_dropped)
						print "<a class='dropdown-item' href='dropped.php'>Dropped</a>";
					if ($permission_destroyed)
						print "<a class='dropdown-item' href='destroyed.php'>Destroyed</a>";
?>
					</div>
				</li>
<?php
			}
			// The ALTER list of menu options
			if ($eqcgood && $permission_inventory)
			{
?>
				<li class="nav-item dropdown<?php $basename = basename($_SERVER["SCRIPT_FILENAME"], '.php'); if ($basename == "inventory") print " active"; ?>">
					<a class="nav-link dropdown-toggle" href="#" id="navbarMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Alter
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarMenuLink">
<?php
					if ($permission_inventory)
						print "<a class='dropdown-item' href='inventory.php'>Inventory</a>";
?>
					</div>
				</li>
<?php
			}
			// Check for permission to view logging data
			if ($permission_logging || $permission_users || $permission_connections)
			{
?>
				<li class="nav-item dropdown<?php $basename = basename($_SERVER["SCRIPT_FILENAME"], '.php'); if ($basename == "logs" || $basename == "users") print " active"; ?>">
					<a class="nav-link dropdown-toggle" href="#" id="navbarMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						Admin
					</a>
					<div class="dropdown-menu" aria-labelledby="navbarMenuLink">
<?php
					if ($permission_logging)
						print "<a class='dropdown-item' href='logs.php'>Logs</a>";
					if ($permission_users)
						print "<a class='dropdown-item' href='users.php'>Users</a>";
					if ($permission_connections)
						print "<a class='dropdown-item' href='connections.php'>Connections</a>";
?>
					</div>
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
			<li class="nav-item">
				<a class="nav-link" href="connections.php"><i><b><?php print $cname; ?></b></i></a>
			</li>
		</ul>
<?php
		// Show search field if user is logged in
		if ($eqcgood && $uid >= 0)
		{
?>
		<form class="form-inline my-2 my-lg-0" action="search.php?a=s" method="post">
			<input class="form-control mr-sm-2" type="search" placeholder="Search Players" aria-label="Search" id="search" name="search">
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