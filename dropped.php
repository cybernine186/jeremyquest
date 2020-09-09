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
	
	$trade_id = $_GET['id'];
	$query = "SELECT trade_id, DATE_FORMAT(time, '%a %b %d, %Y %T') AS thetime, char1_id, n1.name AS n1name, char1_pp, char1_gp, char1_sp, char1_cp, char1_items, char2_id, n2.name AS n2name, char2_pp, char2_gp, char2_sp, char2_cp, char2_items FROM qs_player_trade_record LEFT JOIN character_data AS n1 ON n1.id = qs_player_trade_record.char1_id LEFT JOIN character_data AS n2 ON n2.id = qs_player_trade_record.char2_id WHERE trade_id = {$trade_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("<h5>Trade #{$trade_id} - {$row['n1name']} and {$row['n2name']}</h5>");

	Row();
		Col(true, '', 12);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">When</th>
						<th scope="col">Char1</th>
						<th scope="col">PP1</th>
						<th scope="col">GP1</th>
						<th scope="col">SP1</th>
						<th scope="col">CP1</th>
						<th scope="col">Items1</th>
						<th scope="col">Char2</th>
						<th scope="col">PP2</th>
						<th scope="col">GP2</th>
						<th scope="col">SP2</th>
						<th scope="col">CP2</th>
						<th scope="col">Items2</th>		
					</tr>
				</thead>
				<tbody>
					<tr>
<?php
						print "<td>";
						Hyperlink("trades.php?a=t&id={$row['trade_id']}", $row['trade_id']);
						print "</td><td>{$row['thetime']}</td><td>{$row['n1name']}</td><td>{$row['char1_pp']}</td><td>{$row['char1_gp']}</td><td>{$row['char1_sp']}</td><td>{$row['char1_cp']}</td><td>{$row['char1_items']}</td>";
						print "<td>{$row['n2name']}</td><td>{$row['char2_pp']}</td><td>{$row['char2_gp']}</td><td>{$row['char2_sp']}</td><td>{$row['char2_cp']}</td><td>{$row['char2_items']}</td></tr></tbody></table>";
		DivC();
	DivC();
	
	$idone = $row['char1_id'];
	$idtwo = $row['char2_id'];
	$nameone = $row['n1name'];
	$nametwo = $row['n2name'];
	
	$query = "SELECT event_id, from_id, to_id, item_id, charges, items.name AS itemname FROM qs_player_trade_record_entries LEFT JOIN items ON items.id = qs_player_trade_record_entries.item_id WHERE event_id = {$trade_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
	{
		RowText("<h6>No Items Traded</h6>");
		include_once("footer.php");
		die;
	}
	
	$p1items = array();
	$p1charges = array();
	$p2items = array();
	$p2charges = array();
	
	while ($row = $result->fetch_assoc())
	{
		if ($row['from_id'] == $idone)
		{
			array_push($p1items, $row['itemname'] . " (" . $row['item_id'] . ")");
			array_push($p1charges, $row['charges']);
		}
		else
		{
			array_push($p2items, $row['itemname'] . " (" . $row['item_id'] . ")");
			array_push($p2charges, $row['charges']);
		}
	}
	
	Row();
		Col();
		DivC();
		Col(true, '', 4);
			RowText("<h5>{$nameone} Trades</h5>");
			if (sizeof($p1items) <= 0)
			{
				RowText("None");
			}
			else
			{
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
						foreach ($p1items as $index => $val)
						{
							print "<tr><td>{$val}</td><td>{$p1charges[$index]}</td></tr>";
						}
					print "</tbody>";
				print "</table>";
			}
		DivC();
		Col(true, '', 4);
			RowText("<h5>{$nametwo} Trades</h5>");
			if (sizeof($p2items) <= 0)
			{
				RowText("None");
			}
			else
			{
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
						foreach ($p2items as $index => $val)
						{
							print "<tr><td>{$val}</td><td>{$p2charges[$index]}</td></tr>";
						}
					print "</tbody>";
				print "</table>";
			}
		DivC();
		Col();
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