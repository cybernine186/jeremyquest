<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_trades)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Trades</h4>");

if (!isset($_GET['a']))
{
	display_trade_search();
}
elseif ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	display_trade_search_results($eqdb, $playername);
}
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$charid = $_GET['id'];
	
	display_player_trades($eqdb, $charid);
}
elseif ($_GET['a'] == "t")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$trade_id = $_GET['id'];
	$query = "SELECT trade_id, DATE_FORMAT(time, '%a %b %d, %Y %T') AS time, char1_id, n1.name AS n1name, char1_pp, char1_gp, char1_sp, char1_cp, char1_items, char2_id, n2.name AS n2name, char2_pp, char2_gp, char2_sp, char2_cp, char2_items FROM qs_player_trade_record LEFT JOIN character_data AS n1 ON n1.id = qs_player_trade_record.char_id LEFT JOIN character_data AS n2 ON n2.id = qs_player_trade_record.char_id WHERE trade_id = {$trade_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("<h5>Trade #{$trade_id} - {$row['n2name']} and {$row['n2name']}</h5>");
	/*
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
						<th scope="col">PP</th>
						<th scope="col">GP</th>
						<th scope="col">SP</th>
						<th scope="col">CP</th>
						<th scope="col">Items</th>
					</tr>
				</thead>
				<tbody>
					<tr>
<?php
						print "<td>{$row['handin_id']}</td><td>{$row['time']}</td>";
						print "<td>{$row['char_pp']}</td><td>{$row['char_gp']}</td>";
						print "<td>{$row['char_sp']}</td><td>{$row['char_cp']}</td>";
						print "<td>{$row['char_items']}</td></tr></tbody></table>";
						
						
		DivC();
		Col();
		DivC();
	DivC();
	
	$query = "SELECT item_id, charges, items.name FROM qs_player_handin_record_entries LEFT JOIN items ON item_id = items.id WHERE event_id = {$handin_id}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
	{
		RowText("<h6>No Items Handed In</h6>");
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
						<th scope="col">Item</th>
						<th scope="col">Charges</th>
					</tr>
				</thead>
				<tbody>
<?php
					while ($row = $result->fetch_assoc())
						print "<tr><td>{$row['name']} ({$row['item_id']})</td><td>{$row['charges']}</td></tr>";
				print "</tbody>";
			print "</table>";
		DivC();
		Col();
		DivC();
	DivC();
	*/
}
else
{
	display_trade_search();
}

include_once("footer.php");

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

function display_player_trades($eqdb, $charid)
{
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	$name = $row['name'];
	RowText("<h5>{$name} Trades</h5>");

	$days = 1000;	
	
	$query = "SELECT count(*) AS count FROM qs_player_trade_record WHERE (char1_id = {$charid} OR char2_id = {$charid}) AND time > (NOW() - INTERVAL {$days} DAY)";
	$result = $eqdb->query($query);
	$row = $result->fetch_assoc();
	
	$tradecount = $row['count'];
	
	if($tradecount < 1)
	{
		RowText("No trades found in last {$days} days.");
		include_once("footer.php");
		die;
	}
	
	// Pagination Data
	$start = 1;
	if(isset($_GET['s']))
		$start = $_GET['s'];
	
	$pagesize = 20;
	
	$pages = ceil($tradecount / $pagesize);
	
	$begin = ($start - 1) * $pagesize;	
	
	display_pagination($start, $pages, "trades.php?a=p&id={$charid}");

	$query = "SELECT trade_id, DATE_FORMAT(time, '%a %b %d, %Y %T') AS time, char1_id, n1.name AS n1name, char1_pp, char1_gp, char1_sp, char1_cp, char1_items, char2_id, n2.name AS n2name, char2_pp, char2_gp, char2_sp, char2_cp, char2_items FROM qs_player_trade_record LEFT JOIN character_data AS n1 ON n1.id = qs_player_trade_record.char1_id LEFT JOIN character_data AS n2 ON n2.id = qs_player_trade_record.char2_id WHERE (char1_id = {$charid} OR char2_id = {$charid}) LIMIT {$begin}, {$pagesize}";
	print $query;
	//$query = "SELECT handin_id, time AS timenum, DATE_FORMAT(time, '%a %b %d, %Y %T') AS time, char_pp, char_gp, char_sp, char_cp, char_items, npc_id, npc_types.name FROM qs_player_handin_record LEFT JOIN npc_types ON npc_types.id = qs_player_handin_record.npc_id WHERE char_id = {$charid} AND time > (NOW() - INTERVAL {$days} DAY) ORDER BY timenum DESC LIMIT {$begin}, {$pagesize}";

	if($result = $eqdb->query($query)){
		while ($row = $result->fetch_assoc()) {
			print_r($row);
		}
		die;
	}

	$result = $eqdb->query($query) || die($eqdb->error);;

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
<?php
			$row = $result->fetch_assoc();
			/*
			while ($row = $result->fetch_assoc())
			{
			*/
				print "<tr><td>";
				Hyperlink("trades.php?a=t&id={$row['trade_id']}", $row['handin_id']);
				print "</td><td>{$row['time']}</td><td>{$row['n1name']}</td><td>{$row['char1_pp']}</td><td>{$row['char1_gp']}</td><td>{$row['char1_sp']}</td><td>{$row['char1_cp']}</td><td>{$row['char1_items']}</td>";
				print "<td>{$row['n2name']}</td><td>{$row['char2_pp']}</td><td>{$row['char2_gp']}</td><td>{$row['char2_sp']}</td><td>{$row['char2_cp']}</td><td>{$row['char2_items']}</td>";
			//}
		print "</tbody>";
	print "</table>";
	
	display_pagination($start, $pages, "trades.php?a=p&id={$charid}");
}

function display_trade_search()
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="trades.php?a=sp" method="post">
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

function display_trade_search_results($eqdb, $playername)
{
	$query = "SELECT character_data.id AS id, character_data.name AS charname, character_data.level AS level, guild_members.guild_id, guilds.name AS gname FROM character_data LEFT JOIN guild_members ON character_data.id = guild_members.char_id LEFT JOIN guilds ON guild_members.guild_id = guilds.id WHERE character_data.name LIKE '%{$playername}%'";
	$result = $eqdb->query($query);
	
	if($result->num_rows < 1)
	{
		RowText("<h5>No Players Found</h5>");
		display_trade_search();
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
						Hyperlink("trades.php?a=p&id={$row['id']}", $row['charname']);
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