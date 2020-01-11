<?php

function display_user_edit($mysqli, $id)
{
	$query = "SELECT username, permission_handins, permission_trades, permission_looted, permission_dropped, permission_destroyed, permission_rollback, permission_logging, permission_users FROM users WHERE id = {$id}";
	$result = $mysqli->query($query);
	
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
				<input type="checkbox" class="form-check-input" id="rollbackCheckbox" name="rollbackCheckbox"<?php print ($row['permission_rollback'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="rollbackCheckbox">Perform Inventory Rollbacks</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="loggingCheckbox" name="loggingCheckbox"<?php print ($row['permission_logging'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="loggingCheckbox">View Logs</label>
			</div>
			<div class="form-group form-check">
				<input type="checkbox" class="form-check-input" id="usersCheckbox" name="usersCheckbox"<?php print ($row['permission_users'] ? "checked" : ""); ?>>
				<label class="form-check-label" for="usersCheckbox">Manage Users</label>
			</div>

			</div>
			<button type="submit" class="btn btn-primary">Submit</button>
			<a class="btn btn-primary" href="users.php" role="button">Cancel</a>
		</form>
<?php
	DivC();
DivC();
}

function display_user_list($mysqli)
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
	$result = $mysqli->query($query);
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

function display_password_reset($mysqli, $id)
{
	$query = "SELECT username FROM users WHERE id = {$id}";
	$result = $mysqli->query($query);
	
	if ($result->num_rows != 1)
		data_error();
	
	$row = $result->fetch_assoc();
	
	RowText("<h6>{$row['username']}</h6>");

	Row();
		Col();
?>
			<form action="resetpw.php?a=pr" method="post">
				<div class="form-group">
					<label for="password1">Password</label>
					<input type="password" class="form-control" id="password1" placeholder="Password" name="password1">
				</div>
					<div class="form-group">
					<label for="password2">Verify Password</label>
					<input type="password" class="form-control" id="password2" placeholder="Password Again" name="password2">
				</div>
				<input type="hidden" name="id" value="<?php print $id; ?>">
				<button type="submit" class="btn btn-primary">Submit</button>
				<a class="btn btn-primary" href="users.php" role="button">Cancel</a>
			</form>
<?php
		DivC();
	DivC();
}

?>