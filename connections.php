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
	display_connection_list($admindb);
}
elseif ($_GET['a'] == "c")
{
	print "<h5>Connection Creation</h5>";
}
else
{
	display_connection_list($admindb);
}

include_once("footer.php");

function display_connection_list($admindb)
{
	$query = "SELECT id, name FROM connections ORDER BY id ASC";
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
				<th scope="col">Delete</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>";
				print "<a class='btn btn-primary' href='connections.php?a=d&id={$row['id']}' role='button'>Delete</a>";
				print "</td></tr>";
			}
		print "</tbody>";
	print "</table>";
	
	print "<a class='btn btn-primary' href='connections.php?a=c' role='button'>Create New</a>";
}

?>