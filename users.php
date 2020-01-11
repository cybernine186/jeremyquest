<?php
/***************************************************************************************************
File:			users.php
Description:	Create, edit, and reset passwords for users in the system
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
/*
if (!$permission_users)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}
*/

RowText("<h4>User Management</h4>");

/***************************************************************************************************
Section:		Edit User - Display User Edit form
Inputs:			$_GET['id']				- ID of the user being edited
***************************************************************************************************/
if ($_GET['a'] == "e")
{
	// Check for valid ID format
	if (!IsNumber($_GET['id']))
		data_error();
	$id = $_GET['id'];
	
	// Page output
	display_user_edit($mysqli, $id);
}

/***************************************************************************************************
Section:		Edit User Process - Check and update User information
Inputs:			$_GET['id']						- ID of the user being edited
				$_POST['username']				- User Name (login name)
***************************************************************************************************/
elseif ($_GET['a'] == "pe")
{
	$editid = $_GET['id'];
	
	if (!IsNumber($editid))
		data_error();
	
	// Check lengths of inputs
	if (strlen($_POST['username']) < 1)
	{
		RowText("All fields must be at least one character long.");
		// Redisplay user edit form on error
		display_user_edit($mysqli, $_GET['id']);
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Check if User ID is taken
	$uname = $mysqli->real_escape_string($_POST['username']);
	$query = "SELECT id FROM users WHERE username = '{$uname}'";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		if ($row['id'] != $editid)
		{
			RowText("This User ID is already taken. Please try again");
			// Redisplay user edit form on error
			display_user_edit($mysqli, $_GET['id']);
			// Close and kill script to avoid further processing
			include_once("footer.php");
			die;
		}
	}
	
	// Permission variables from checkboxes on form
	$handins = $_POST['handinsCheckbox'] == "on" ? "1" : "0";
	$trades = $_POST['tradesCheckbox'] == "on" ? "1" : "0";
	$looted = $_POST['lootedCheckbox'] == "on" ? "1" : "0";
	$dropped = $_POST['droppedCheckbox'] == "on" ? "1" : "0";
	$destroyed = $_POST['destroyedCheckbox'] == "on" ? "1" : "0";
	$rollback = $_POST['rollbackCheckbox'] == "on" ? "1" : "0";
	$logging = $_POST['loggingCheckbox'] == "on" ? "1" : "0";
	$users = $_POST['usersCheckbox'] == "on" ? "1" : "0";
	
	// Update the user information in database
	$query = "UPDATE users SET username='{$uname}', permission_handins={$handins}, permission_trades={$trades}, permission_looted={$looted}, permission_dropped={$dropped}, permission_destroyed={$destroyed}, permission_rollback={$rollback}, permission_logging={$login}, permission_users={$users} WHERE id={$editid}";
	print $query;
	$result = $mysqli->query($query);
	
	// Indicate the change in system logging
	//Logging($mysqli, $uid, Logs::User, 0, "{$uname} ({$uid}) edited user info for user {$firstname} {$lastname} ({$userid})");
	
	RowText("User information updated.");

	// Page output
	display_user_list($mysqli);
}

/***************************************************************************************************
Section:		Create User Process - Check and Process the User creation
Inputs:			$_POST['userid']		- User ID (login name)
				$_POST['password1']		- First password form field
				$_POST['password2']		- Second password form field
***************************************************************************************************/
elseif ($_GET['a'] == "cp")
{
	// Check lengths of inputs
	if (strlen($_POST['username']) < 1 || strlen($_POST['password1']) < 1 || strlen($_POST['password2']) < 1)
	{
		RowText("All fields must be at least one character long.");
		// Redisplay user creation form on error
		display_user_creation();
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Check passwords match
	if ($_POST['password1'] !== $_POST['password2'])
	{
		RowText("Passwords don't match. Please try again.");
		// Redisplay user creation form on error
		display_user_creation();
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// $uname is user name attempted to be created
	// $username is user name set for user by login
	
	// Check if User ID is taken
	$uname = $mysqli->real_escape_string($_POST['username']);
	$query = "SELECT id FROM users WHERE username = '{$uname}'";
	$result = $mysqli->query($query);
	if ($result->num_rows > 0)
	{
		RowText("This User ID is already taken. Please try again");
		// Redisplay user creation form on error
		display_user_creation();
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Everything ok, process creation
	
	// Create password hash
	$cost = 10;
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
	$salt = sprintf("$2a$%02d$", $cost) . $salt;
	$hash = crypt($_POST['password1'], $salt);
	
	// Escape the escapes!
	$hash = $mysqli->real_escape_string($hash);

	// Insert user information into database
	$query = "INSERT INTO users (username, hash) VALUES ('{$uname}', '{$hash}')";
	$result = $mysqli->query($query);
	
	// Get insert ID for logging
	$lastid = $mysqli->insert_id;
	
	// Indicate the change in system logging
	//Logging($mysqli, $uid, Logs::User, 0, "{$uname} ({$uid}) created user {$firstname} {$lastname} ({$userid} - {$lastid})");
	
	// Page output
	RowText("User created!");
	display_user_list($mysqli);
}

/***************************************************************************************************
Section:		Create User - Display User Create form
Inputs:			None
***************************************************************************************************/
elseif ($_GET['a'] == "c")
{
	// Page output
	display_user_creation();
}

/***************************************************************************************************
Section:		Base User Script - Display list of users with options to edit and reset password
Inputs:			None
***************************************************************************************************/
else
{
	// Page output
	display_user_list($mysqli);
}

include_once("footer.php")

?>