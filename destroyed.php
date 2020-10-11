<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission['destroyed'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Destroyed Items</h4>");

if (!isset($_GET['a']))
{
	display_destroyed_search();
}
elseif ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	display_destroyed_search_results($eqdb, $playername);
}
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$charid = $_GET['id'];
	
	display_player_destroyed($eqdb, $charid);
}
elseif ($_GET['a'] == "d")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$destroyed_id = $_GET['id'];

	$query = "SELECT DATE_FORMAT(time, '%a %b %d, %Y %T') AS thetime, char_id, char_items, character_data.name AS name FROM qs_player_delete_record JOIN character_data ON character_data.id = qs_player_delete_record.char_id WHERE delete_id = {$destroyed_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("<h5>{$row['name']} Item Destroy - #{$destroyed_id}</h5>");
	
	Row();
		Col();
		DivC();
		Col(true, '', 8);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">When</th>
						<th scope="col">Items</th>
					</tr>
				</thead>
				<tbody>
<?php
					print "<tr><td>{$destroyed_id}</td><td>{$row['thetime']}</td><td>{$row['char_items']}</td></tr>";
				print "</tbody>";
			print "</table>";
		DivC();
		Col();
		DivC();
	DivC();
	
	Row();
		Col();
		DivC();
		Col(true, '', 6);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">Item</th>
						<th scope="col">Charges</th>
						<th scope="col">Slot</th>
					</tr>
				</thead>
				<tbody>
<?php
					$query = "SELECT char_slot, item_id, charges, items.name AS itemname FROM qs_player_delete_record_entries JOIN items ON items.id = qs_player_delete_record_entries.item_id WHERE event_id = {$destroyed_id}";
					$result = $eqdb->query($query);
					while ($row = $result->fetch_assoc())
					{
						print "<tr><td>{$row['itemname']} ({$row['item_id']})</td><td>{$row['charges']}</td></tr>";
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
	display_destroyed_search();
}

include_once("footer.php");

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

function display_player_destroyed($eqdb, $charid)
{
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	$name = $row['name'];
	RowText("<h5>{$name} Doug - Destroyed Items</h5>");

	$days = 1000;	
	
	$query = "SELECT count(*) AS count FROM qs_player_delete_record WHERE char_id = {$charid} AND time > (NOW() - INTERVAL {$days} DAY)";
	$result = $eqdb->query($query);
	$row = $result->fetch_assoc();
	
	$destroyedcount = $row['count'];
	
	if($destroyedcount < 1)
	{
		RowText("No item destroys found in last {$days} days.");
		include_once("footer.php");
		die;
	}
	
	// Pagination Data
	$start = 1;
	if(isset($_GET['s']))
		$start = $_GET['s'];
	
	$pagesize = 20;
	
	$pages = ceil($destroyedcount / $pagesize);
	
	$begin = ($start - 1) * $pagesize;
	
	display_pagination($start, $pages, "destroyed.php?a=p&id={$charid}");

	$query = "SELECT delete_id, DATE_FORMAT(time, '%a %b %d, %Y %T') AS thetime, char_items FROM qs_player_delete_record WHERE char_id = {$charid} ORDER BY time DESC LIMIT {$begin}, {$pagesize}";
	$result = $eqdb->query($query);
?>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">ID</th>
				<th scope="col">When</th>
				<th scope="col">Items</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>";
				Hyperlink("destroyed.php?a=d&id={$row['delete_id']}", $row['delete_id']);
				print "</td><td>{$row['thetime']}</td><td>{$row['char_items']}</td></tr>";
			}
		print "</tbody>";
	print "</table>";
	
	display_pagination($start, $pages, "destroyed.php?a=p&id={$charid}");
}

function display_destroyed_search()
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="destroyed.php?a=sp" method="post">
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

function display_destroyed_search_results($eqdb, $playername)
{
	$query = "SELECT character_data.id AS id, character_data.name AS charname, character_data.level AS level, guild_members.guild_id, guilds.name AS gname FROM character_data LEFT JOIN guild_members ON character_data.id = guild_members.char_id LEFT JOIN guilds ON guild_members.guild_id = guilds.id WHERE character_data.name LIKE '%{$playername}%'";
	$result = $eqdb->query($query);
	
	if($result->num_rows < 1)
	{
		RowText("<h5>No Players Found</h5>");
		display_destroyed_search();
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
						Hyperlink("destroyed.php?a=p&id={$row['id']}", $row['charname']);
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