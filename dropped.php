<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_dropped)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Dropped Items</h4>");

if (!isset($_GET['a']))
{
	display_dropped_search();
}
elseif ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	display_dropped_search_results($eqdb, $playername);
}
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$charid = $_GET['id'];
	
	display_player_dropped($eqdb, $charid);
}
elseif ($_GET['a'] == "d")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$drop_id = $_GET['id'];
	$query = "SELECT DATE_FORMAT(time, '%a %b %d, %Y %T') AS thetime, char_id, character_data.name AS name, pickup, zone.short_name, qs_player_drop_record.x AS x, qs_player_drop_record.y AS y, qs_player_drop_record.z AS z FROM qs_player_drop_record JOIN character_data ON character_data.id = qs_player_drop_record.char_id JOIN zone ON zone.zoneidnumber = qs_player_drop_record.zone_id WHERE drop_id = {$drop_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("<h5>{$row['name']} " . ($row['pickup'] ? "Pickup" : "Drop") . " - {$drop_id}</h5>");
	
	
	Row();
		Col(true, '', 12);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">Item</th>
						<th scope="col">Charges</th>
					</tr>
				</thead>
				<tbody>
<?php
					$query = "SELECT item_id, charges, items.name AS itemname FROM qs_player_drop_record_entries JOIN items ON items.id = qs_player_drop_record_entries.item_id WHERE event_id = {$drop_id}";
					$result = $eqdb->query($query);
					while ($row = $result->fetch_assoc())
					{
						print "<tr><td>{$row['itemname']} ({$row['item_id']})</td><td>{$row['charges']}</td></tr>";
					}
				print "</tbody>";
			print "</table>";
		DivC();
	DivC();
}
else
{
	display_dropped_search();
}

include_once("footer.php");

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

function display_player_dropped($eqdb, $charid)
{
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	$name = $row['name'];
	RowText("<h5>{$name} Dropped</h5>");
	$result->close();

	$days = 1000;	
	
	$query = "SELECT count(*) AS count FROM qs_player_drop_record WHERE char_id = {$charid} AND time > (NOW() - INTERVAL {$days} DAY)";
	$result = $eqdb->query($query);
	$row = $result->fetch_assoc();
	
	$dropcount = $row['count'];
	$result->close();
	
	if($dropcount < 1)
	{
		RowText("No drops found in last {$days} days.");
		include_once("footer.php");
		die;
	}
	
	// Pagination Data
	$start = 1;
	if(isset($_GET['s']))
		$start = $_GET['s'];
	
	$pagesize = 20;
	
	$pages = ceil($dropcount / $pagesize);
	
	$begin = ($start - 1) * $pagesize;	
	
	display_pagination($start, $pages, "dropped.php?a=p&id={$charid}");

	$query = "SELECT drop_id, DATE_FORMAT(time, '%a %b %d, %Y %T') AS thetime, char_id, pickup, zone_id, x, y, z, zone.short_name AS zonename FROM qs_player_drop_record JOIN zone ON zone.zoneidnumber = qs_player_drop_record.zone_id WHERE char_id = {$charid} ORDER BY time DESC LIMIT {$begin}, {$pagesize}";
	$result = $eqdb->query($query);
?>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">ID</th>
				<th scope="col">When</th>
				<th scope="col">Type</th>
				<th scope="col">Zone</th>
				<th scope="col">X</th>
				<th scope="col">Y</th>
				<th scope="col">Z</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>";
				Hyperlink("dropped.php?a=d&id={$row['drop_id']}", $row['drop_id']);
				print "</td><td>{$row['thetime']}</td><td>";
				if ($row['pickup'] == "0")
					print "Drop";
				else
					print "Pickup";
				print "</td><td>{$row['zonename']}</td><td>{$row['x']}</td><td>{$row['y']}</td><td>{$row['z']}</td></tr>";
			}
		print "</tbody>";
	print "</table>";
	
	display_pagination($start, $pages, "dropped.php?a=p&id={$charid}");
}

function display_dropped_search()
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="dropped.php?a=sp" method="post">
				<div class="form-group">
					<label for="playerName">Player Name</label>
					<input type="text" class="form-control" id="playerName" placeholder="Enter Player Name" name="playerName">
				</div>
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
		
}

function display_dropped_search_results($eqdb, $playername)
{
	$query = "SELECT character_data.id AS id, character_data.name AS charname, character_data.level AS level, guild_members.guild_id, guilds.name AS gname FROM character_data LEFT JOIN guild_members ON character_data.id = guild_members.char_id LEFT JOIN guilds ON guild_members.guild_id = guilds.id WHERE character_data.name LIKE '%{$playername}%'";
	$result = $eqdb->query($query);
	
	if($result->num_rows < 1)
	{
		RowText("<h5>No Players Found</h5>");
		display_dropped_search();
		include_once("footer.php");
		die;
	}
	Row();
		Col();
		DivC();
		Col(true, '', 6);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">Character</th>
						<th scope="col">Guild</th>
						<th scope="col">Level</th>
					</tr>
				</thead>
				<tbody>
<?php
					while ($row = $result->fetch_assoc())
					{
						print "<tr><td>";
						Hyperlink("dropped.php?a=p&id={$row['id']}", $row['charname']);
						print "</td><td>{$row['gname']}</td><td>{$row['level']}</td></tr>";
					}
?>
				</tbody>
			</table>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

?>