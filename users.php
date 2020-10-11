<?php
/***************************************************************************************************
File:			users.php
Description:	Create and edit users
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
if (!$permission['users'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>User Management</h4>");

if (!isset($_GET['a']))
{
	// Page output
	display_user_list($admindb);
}
/***************************************************************************************************
Section:		Edit User - Display User Edit form
Inputs:			$_GET['id']				- ID of the user being edited
***************************************************************************************************/
elseif ($_GET['a'] == "e")
{
	// Check for valid ID format
	if (!IsNumber($_GET['id']))
		data_error();
	$id = $_GET['id'];
	
	// Page output
	display_user_edit($admindb, $id);
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
		display_user_edit($admindb, $_GET['id']);
		// Close and kill script to avoid further processing
		include_once("footer.php");
		die;
	}
	
	// Check if User ID is taken
	$uname = $admindb->real_escape_string($_POST['username']);
	$query = "SELECT id FROM users WHERE username = '{$uname}'";
	$result = $admindb->query($query);
	if ($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		if ($row['id'] != $editid)
		{
			RowText("This User ID is already taken. Please try again");
			// Redisplay user edit form on error
			display_user_edit($admindb, $_GET['id']);
			// Close and kill script to avoid further processing
			include_once("footer.php");
			die;
		}
	}
	
	// Permission variables from checkboxes on form
	$handins = "0";
	$trades = "0";
	$looted = "0";
	$dropped = "0";
	$destroyed = "0";
	$inventory = "0";
	$logging = "0";
	$users = "0";
	$connections = "0";
	$copychar = "0";
	$purgechar = "0";
	
	if (isset($_POST['handinsCheckbox']) && $_POST['handinsCheckbox'] == "on")
		$handins = "1";
	if (isset($_POST['tradesCheckbox']) && $_POST['tradesCheckbox'] == "on")
		$trades = "1";
	if (isset($_POST['lootedCheckbox']) && $_POST['lootedCheckbox'] == "on")
		$looted = "1";
	if (isset($_POST['droppedCheckbox']) && $_POST['droppedCheckbox'] == "on")
		$dropped = "1";	
	if (isset($_POST['destroyedCheckbox']) && $_POST['destroyedCheckbox'] == "on")
		$destroyed = "1";	
	if (isset($_POST['inventoryCheckbox']) && $_POST['inventoryCheckbox'] == "on")
		$inventory = "1";	
	if (isset($_POST['loggingCheckbox']) && $_POST['loggingCheckbox'] == "on")
		$logging = "1";	
	if (isset($_POST['usersCheckbox']) && $_POST['usersCheckbox'] == "on")
		$users = "1";
	if (isset($_POST['connectionsCheckbox']) && $_POST['connectionsCheckbox'] == "on")
		$connections = "1";
	if (isset($_POST['copycharCheckbox']) && $_POST['copycharCheckbox'] == "on")
		$copychar = "1";
	if (isset($_POST['purgecharCheckbox']) && $_POST['purgecharCheckbox'] == "on")
		$purgechar = "1";

	// Update the user information in database
	$query = "UPDATE users SET username='{$uname}', permission_handins={$handins}, permission_trades={$trades}, permission_looted={$looted}, permission_dropped={$dropped}, permission_destroyed={$destroyed}, permission_inventory={$inventory}, permission_logging={$logging}, permission_users={$users}, permission_connections={$connections}, permission_copychar={$copychar}, permission_purgechar={$purgechar} WHERE id={$editid}";
	$result = $admindb->query($query);
	
	RowText("User information updated.");
	
	// Indicate the change in system logging
	Logging($admindb, $uid, Logs::User, "User Info Edit - Editor: {$username} - Editee: {$uname} - " . get_client_ip());

	// Page output
	display_user_list($admindb);
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
	$uname = $admindb->real_escape_string($_POST['username']);
	$query = "SELECT id FROM users WHERE username = '{$uname}'";
	$result = $admindb->query($query);
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
	$hash = $admindb->real_escape_string($hash);

	// Insert user information into database
	$query = "INSERT INTO users (username, hash) VALUES ('{$uname}', '{$hash}')";
	$result = $admindb->query($query);
	
	// Get insert ID for logging
	$lastid = $admindb->insert_id;
	
	// Indicate the change in system logging
	//Logging($admindb, $uid, Logs::User, 0, "{$uname} ({$uid}) created user {$firstname} {$lastname} ({$userid} - {$lastid})");
	
	// Page output
	RowText("User created!");
	
	Logging($admindb, 0, Logs::User, "User Created - Creator: {$username} - Createe: {$uname} - " . get_client_ip());
	
	display_user_list($admindb);
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
	display_user_list($admindb);
}

include_once("footer.php");

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

/*******************************************************************************
Function:	display_user_list
Purpose:	Display user list
*******************************************************************************/
function display_user_list($admindb)
{
?>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">Name</th>
				<th scope="col">Edit User</th>
				<th scope="col">Password</th>
			</tr>
		</thead>
		<tbody>
<?php
	$query = "SELECT id, username FROM users";
	$result = $admindb->query($query);
	while ($row = $result->fetch_assoc())
	{
		print "<tr>";
		print "<td>{$row['username']}</td>";
		print "<td><a class='btn btn-primary' href='users.php?a=e&id={$row['id']}' role='button'>Edit</a></td>";
		print "<td><a class='btn btn-primary' href='resetpw.php?id={$row['id']}' role='button'>Reset</a></td>";
		print "</tr>";
	}
	print "</tbody></table>";
	print "<a class='btn btn-primary' href='users.php?a=c' role='button'>Create New</a>";
}

/*******************************************************************************
Function:	display_user_creation
Purpose:	Display user creation form
*******************************************************************************/
function display_user_creation()
{
Row();
	Col();
?>
		<form action="users.php?a=cp" method="post">
			<div class="form-group">
				<label for="userid">User Name</label>
				<input type="text" class="form-control" id="username" placeholder="Enter User Name" name="username">
			</div>
			<div class="form-group">
				<label for="password1">Password</label>
				<input type="password" class="form-control" id="password1" placeholder="Password" name="password1">
			</div>
				<div class="form-group">
				<label for="password2">Verify Password</label>
				<input type="password" class="form-control" id="password2" placeholder="Password Again" name="password2">
			</div>
			<button type="submit" class="btn btn-primary">Submit</button>
			<a class='btn btn-primary' href='users.php' role='button'>Cancel</a>
		</form>
<?php
	DivC();
DivC();
}

/*******************************************************************************
Function:	display_user_edit
Purpose:	Display user edit form
In:			$id - id of user for editing
*******************************************************************************/
function display_user_edit($admindb, $id)
{
	$query = "SELECT username, permission_handins, permission_trades, permission_looted, permission_dropped, permission_destroyed, permission_inventory, permission_logging, permission_users, permission_connections, permission_copychar, permission_purgechar FROM users WHERE id = {$id}";
	$result = $admindb->query($query);
	
	// Check results exist
	if ($result->num_rows != 1)
		data_error();
	
	$row = $result->fetch_assoc();	
	
Row();
	Col();
?>
		<form action="users.php?a=pe&id=<?php print $id; ?>" method="post">
			<div class="form-group">
				<label for="userid">User Name</label>
				<input type="text" class="form-control" id="username" name="username" value="<?php print $row['username']; ?>">
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="handinsCheckbox" name="handinsCheckbox"<?php print ($row['permission_handins'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="handinsCheckbox">View Handins</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="tradesCheckbox" name="tradesCheckbox"<?php print ($row['permission_trades'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="tradesCheckbox">View Trades</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="lootedCheckbox" name="lootedCheckbox"<?php print ($row['permission_looted'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="lootedCheckbox">View Looted Items</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="droppedCheckbox" name="droppedCheckbox"<?php print ($row['permission_dropped'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="droppedCheckbox">View Dropped Items</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="destroyedCheckbox" name="destroyedCheckbox"<?php print ($row['permission_destroyed'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="destroyedCheckbox">View Destroyed Items</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="inventoryCheckbox" name="inventoryCheckbox"<?php print ($row['permission_inventory'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="inventoryCheckbox">Perform Inventory Stuff</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="loggingCheckbox" name="loggingCheckbox"<?php print ($row['permission_logging'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="loggingCheckbox">View Logs</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="usersCheckbox" name="usersCheckbox"<?php print ($row['permission_users'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="usersCheckbox">Manage Users</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="connectionsCheckbox" name="connectionsCheckbox"<?php print ($row['permission_connections'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="usersCheckbox">Manage Connections</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="copycharCheckbox" name="copycharCheckbox"<?php print ($row['permission_copychar'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="copycharCheckbox">Copy Characters</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="purgecharCheckbox" name="purgecharCheckbox"<?php print ($row['permission_purgechar'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="purgecharCheckbox">Purge Character Data</label>
			</div>

			<button type="submit" class="btn btn-primary">Submit</button>
			<a class="btn btn-primary" href="users.php" role="button">Cancel</a>
		</form>
<?php
	DivC();
DivC();
}

?>