<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission['inventory'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Inventory Snapshots</h4>");

if (!isset($_GET['a']))
{
	Row();
		Col();
		DivC();
		Col(true, '', 2);
?>
			<form action="inventory.php?a=sp" method="post">
				<div class="form-group">
					<label for="playername">Player Name</label>
					<input type="text" class="form-control" id="playername" placeholder="Enter Player Name" name="playername">
				</div>
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}
elseif ($_GET['a'] == "ri")
{
	$charid = preg_replace("/[^0-9]/", "", $_GET['id']);
	$snaptime = preg_replace("/[^0-9]/", "", $_GET['ti']);

	RowText("CharID: " . $charid . " - Snap Time: " . $snaptime . " - Rolling Back");
		
	$query = "SELECT slotid, itemid, charges, color FROM inventory_snapshots WHERE charid = {$charid} AND time_index = {$snaptime}";
	$result = $eqdb->query($query);
		
	print $query;
		
	if($result->num_rows < 1)
	{
		RowText("No Backup");
		include_once("footer.php");
		die;
	}
	
	$items = array();
		
	while($row = $result->fetch_assoc())
	{
		array_push($items, array('slotid' => $row['slotid'], 'itemid' => $row['itemid'], 'charges' => $row['charges'], 'color' => $row['color']));
	}
		
	$query = "DELETE FROM inventory WHERE charid = {$charid}";
	$eqdb->query($query);
		
	foreach($items as $key => $value)
	{
		$query = "INSERT INTO inventory (charid, slotid, itemid, charges, color) VALUES ('" . $charid . "', '" . $items[$key]['slotid'] . "', '" . $items[$key]['itemid'] . "', '" . $items[$key]['charges'] . "', '" . $items[$key]['color'] . "'); ";
		print "<br />" . $query;
		$eqdb->query($query);
	}
		
	RowText("<h4>Rollback Complete</h4>");
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	if ($result->num_rows != 1)
		data_error();
	$row = $result->fetch_assoc();
	$playername = $row['name'];
	Logging($admindb, $uid, Logs::Rollback, "Inventory Rollback Performed - User: {$username} - Player: {$playername} - Time Index: {$snaptime} - " . get_client_ip());
}
elseif ($_GET['a'] == "sp")
{
	$name = $eqdb->real_escape_string($_POST['playername']);
		
	RowText("<h3>Players Matching: {$name}</h3>");
		
	$query = "SELECT id, name, level FROM character_data WHERE name LIKE '%{$name}%'";
	$result = $eqdb->query($query);
		
	Row();
		Col();
		DivC();
		Col(true, '', 4);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Name</th>
						<th scope="col">Level</th>
						<th scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php
					while ($row = $result->fetch_assoc())
					{
						print "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['level']}</td><td><a href='inventory.php?a=sr&id={$row['id']}' class='btn btn-primary btn-sm' role='button' aria-disabled='true'>Go</a></td></tr>";
					}
				print "</tbody>";
			print "</table>";
		DivC();
		Col();
		DivC();
	DivC();
}
elseif ($_GET['a'] == "sr")
{
	$charid = preg_replace("/[^0-9]/", "", $_GET['id']);
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	$row = $result->fetch_assoc();
	$name = $row['name'];
	RowText("<h3>Rollbacks for {$name} ({$charid})</h3>");
	$query = "SELECT DISTINCT time_index, from_unixtime((time_index), '%Y %D %M %h:%i:%s %p') AS timef FROM inventory_snapshots WHERE charid = {$charid} ORDER BY time_index DESC";
	$result = $eqdb->query($query);
	$times = array();
	while ($row = $result->fetch_assoc())
	{
		$times[$row['time_index']] = "";
	}
	
	Row();
		Col();
		DivC();
		Col(true, '', 6);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">Rollback Time</th>
						<th scope="col">Items</th>
						<th scope="col">&nbsp;</th>
					</tr>
				</thead>
				<tbody>
<?php
					foreach($times as $key => $value)
					{
						$query = "SELECT count(*) AS mycount, from_unixtime(time_index - 3600 * 4, '%Y %D %M %h:%i:%s %p') AS timef FROM inventory_snapshots WHERE time_index = {$key} AND charid = {$charid}";
						$result = $eqdb->query($query);
						$row = $result->fetch_assoc();
						print "<tr><td>{$row['timef']}</td><td>{$row['mycount']}</td><td><a href='inventory.php?a=ri&id={$charid}&ti={$key}' class='btn btn-primary btn-sm' role='button' aria-disabled='true'>ROLL IT</a></td></tr>";
					}
				print "</tbody>";
			print "</table>";
		DivC();
		Col();
		DivC();
	DivC();
	}
else
{
	Row();
		Col();
		DivC();
		Col(true, '', 2);
?>
			<form action="inventory.php?a=sp" method="post">
				<div class="form-group">
					<label for="playername">Player Name</label>
					<input type="text" class="form-control" id="playername" placeholder="Enter Player Name" name="playername">
				</div>
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}
	
	
include_once("footer.php");

?>
