<?php
/***************************************************************************************************
File:			logs.php
Description:	Logging of system usage
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
if (!$permission_connections)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Connections</h4>");

if (!isset($_GET['a']))
{
	display_connection_list($admindb, $uid);
}
elseif ($_GET['a'] == "c")
{
	display_connection_creation();
}
elseif ($_GET['a'] == "cp")
{
	$cname = $admindb->real_escape_string($_POST['connectionName']);
	$chost = $admindb->real_escape_string($_POST['connectionHost']);
	$cdb = $admindb->real_escape_string($_POST['connectionDatabase']);
	$cuser = $admindb->real_escape_string($_POST['connectionUser']);
	$cpassword = $admindb->real_escape_string($_POST['connectionPassword']);
	
	$query = "UPDATE connections SET selected = 0 WHERE user = {$uid}";
	$admindb->query($query);
	
	$query = "INSERT INTO connections (user, name, host, dbase, username, password, selected) VALUES ({$uid}, '{$cname}', '{$chost}', '{$cdb}', '{$cuser}', '{$cpassword}', 1)";
	$admindb->query($query);
	
	RowText("<h6>Connection Created</h6>");
	RowText("Returning to Connection List shortly");
	header("refresh:3;url=connections.php");
}
elseif ($_GET['a'] == "u")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$cid = $_GET['id'];
	
	$query = "UPDATE connections SET selected = 0 WHERE user = {$uid}";
	$admindb->query($query);
	$query = "UPDATE connections SET selected = 1 WHERE id = {$cid}";
	$admindb->query($query);
	
	RowText("<h6>Connection Selected</h6>");
	RowText("Returning to Connection List shortly");
	header("refresh:3;url=connections.php");
}
elseif ($_GET['a'] == "d")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$cid = $_GET['id'];
	
	display_delete_confirm($admindb, $cid);
}
elseif ($_GET['a'] == "dp")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$cid = $_GET['id'];
	
	$query = "DELETE FROM connections WHERE id = {$cid}";
	$admindb->query($query);
	
	RowText("<h6>Connection Deleted</h6>");
	RowText("Returning to Connection List shortly");
	header("refresh:3;url=connections.php");
}
else
{
	display_connection_list($admindb, $uid);
}

include_once("footer.php");

function display_connection_creation()
{
	Row();
		Col();
	?>
			<form action="connections.php?a=cp" method="post">
				<div class="form-group">
					<label for="connectionName">Connection Name</label>
					<input type="text" class="form-control" id="connectionName" placeholder="Enter Connection Name" name="connectionName">
				</div>
				<div class="form-group">
					<label for="connectionHost">Host</label>
					<input type="text" class="form-control" id="connectionHost" placeholder="Host" name="connectionHost">
				</div>
				<div class="form-group">
					<label for="connectionDatabase">Database</label>
					<input type="text" class="form-control" id="connectionDatabase" placeholder="Database" name="connectionDatabase">
				</div>
				<div class="form-group">
					<label for="connectionUser">User</label>
					<input type="text" class="form-control" id="connectionUser" placeholder="User" name="connectionUser">
				</div>
				<div class="form-group">
					<label for="connectionPassword">Password</label>
					<input type="password" class="form-control" id="connectionPassword" placeholder="Password" name="connectionPassword">
				</div>
				<button type="submit" class="btn btn-primary">Submit</button>
				<a class='btn btn-primary' href='connections.php' role='button'>Cancel</a>
			</form>
	<?php
		DivC();
	DivC();
}

function display_connection_list($admindb, $uid)
{
	$query = "SELECT id, name FROM connections WHERE user = {$uid} ORDER BY id ASC";
	$result = $admindb->query($query);
	if ($result->num_rows < 1)
	{
		RowText("No Connections");
		print "<a class='btn btn-primary' href='connections.php?a=c' role='button'>Create New</a>";
		include_once("footer.php");
		die;
	}
?>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">ID</th>
				<th scope="col">Connection Name</th>
				<th scope="col">Use</th>
				<th scope="col">Delete</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>";
				print "<a class='btn btn-primary' href='connections.php?a=u&id={$row['id']}' role='button'>Use</a>";
				print "</td><td>";
				print "<a class='btn btn-primary' href='connections.php?a=d&id={$row['id']}' role='button'>Delete</a>";
				print "</td></tr>";
			}
		print "</tbody>";
	print "</table>";
	
	print "<a class='btn btn-primary' href='connections.php?a=c' role='button'>Create New</a>";
}

function display_delete_confirm($admindb, $cid)
{
	$query = "SELECT name FROM connections WHERE id = {$cid}";
	$result = $admindb->query($query);
	if ($result->num_rows != 1)
		data_error();
	
	$row = $result->fetch_assoc();
	$cname = $row['name'];
	RowText("Are you sure you want to delete connection {$cname}?");
	Row();
		Col();
		DivC();
		Col(true, '', 1);
			print "<a class='btn btn-primary' href='connections.php?a=dp&id={$cid}' role='button'>Yes</a>";
		DivC();
		Col(true, '', 1);
			print "<a class='btn btn-primary' href='connections.php' role='button'>No</a>";
		DivC();
		Col();
		DivC();
	DivC();
}
?>