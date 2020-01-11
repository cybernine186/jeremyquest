<?php
/***************************************************************************************************
File:			resetpw.php
Description:	User password reset page
				Only accessible to user administrator
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
if (!$manage_users)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

/***************************************************************************************************
Section:		Process Password Reset
Inputs:			$_POST['password1']		- First password form field
				$_POST['password2'] 	- Second password form field
				$_POST['id']			- ID of the user whose password's being reset
***************************************************************************************************/
if ($_GET['a'] == "pr")
{
	// Check for valid ID format
	if (!IsNumber($_POST['id']))
		data_error();
	
	$id = $_POST['id'];
	
	// Check lengths of inputs - minimum length of 1 character for passwords currently
	if (strlen($_POST['password1']) < 1 || strlen($_POST['password2']) < 1)
	{
		RowText("All fields must be at least one character long.");
		// Redisplay password reset form on error
		display_password_reset($mysqli, $id);
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Check that passwords match
	if ($_POST['password1'] !== $_POST['password2'])
	{
		RowText("Passwords don't match. Please try again.");
		// Redisplay password reset form on error
		display_password_reset($mysqli, $id);
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Generate password hash from input and salt
	$cost = 10;
	$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
	$salt = sprintf("$2a$%02d$", $cost) . $salt;
	$hash = crypt($_POST['password1'], $salt);
	
	// Just in case hash contains escape characters
	$hash = $mysqli->real_escape_string($hash);

	// Query user information for page output
	$query = "SELECT firstname, lastname, userid FROM users WHERE id = {$id}";
	$result = $mysqli->query($query);
	if ($result->num_rows != 1)
		data_error();	// There should be results
	$row = $result->fetch_assoc();
	$firstname = $row['firstname'];
	$lastname = $row['lastname'];
	$username = $row['userid'];
	
	// Update password hash in database for user
	$query = "UPDATE users SET hash = '{$hash}' WHERE id = {$id}";
	$result = $mysqli->query($query);
	
	// Indicate the change in system logging
	Logging($mysqli, $uid, Logs::User, 0, "{$uname} ({$uid}) reset password for user {$username} ({$id})");
	
	// Page output
	RowText("<h4>User Management</h4>");
	RowText("Password updated for {$firstname} {$lastname}.");
	display_user_list($mysqli);
}

/***************************************************************************************************
Section:		Base Password Reset Script
Inputs:			$_GET['id']				- ID of the user whose password is being reset
***************************************************************************************************/
else
{
	// Check for valid ID format
	if (!IsNumber($_GET['id']))
		data_error();
	$id = $_GET['id'];

	RowText("<h4>Password Reset</h4>");
	
	// Invoke for output
	display_password_reset($mysqli, $id);
}

include_once("footer.php")

?>