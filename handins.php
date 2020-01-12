<?php


include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_handins)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Handins</h4>");

if ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	display_handin_search_results($eqdb, $playername);
}
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$charid = $_GET['id'];
	
	display_player_handins($eqdb, $charid);
}
else
{
	display_handin_search();
}

include_once("footer.php");

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

function display_player_handins($eqdb, $charid)
{
	$query = "SELECT name FROM character_data WHERE id = {$charid}";
	$result = $eqdb->query($query);
	if($result->num_rows < 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("<h5>{$row['name']} Quest Handins</h5>");

	$days = 1000;	
	
	$query = "SELECT count(*) AS count FROM qs_player_handin_record WHERE char_id = {$charid} AND time > (NOW() - INTERVAL {$days} DAY)";
	$result = $eqdb->query($query);
	$row = $result->fetch_assoc();
	
	$handincount = $row['count'];
	
	if($handincount < 1)
	{
		RowText("No handins found in last {$days} days.");
		include_once("footer.php");
		die;
	}
	
	// Pagination Data
	$start = 1;
	if(isset($_GET['s']))
		$start = $_GET['s'];
	
	$pagesize = 10;
	
	$pages = ceil($handincount / $pagesize);
	
	$begin = ($start - 1) * $pagesize;	
	
	display_pagination($start, $pages, "handins.php?a=p&id={$charid}");

	$query = "SELECT handin_id, time AS timenum, DATE_FORMAT(time, '%a %b %d, %Y %T') AS time, char_pp, char_gp, char_sp, char_cp, char_items, npc_id, npc_types.name FROM qs_player_handin_record LEFT JOIN npc_types ON npc_types.id = qs_player_handin_record.npc_id WHERE char_id = {$charid} AND time > (NOW() - INTERVAL {$days} DAY) ORDER BY timenum DESC LIMIT {$begin}, {$pagesize}";
	$result = $eqdb->query($query);

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
				<th scope="col">NPC</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>";
				Hyperlink("handins.php?a=h&id={$row['handin_id']}", $row['handin_id']);
				print "</td><td>{$row['time']}</td><td>{$row['char_pp']}</td><td>{$row['char_gp']}</td><td>{$row['char_sp']}</td><td>{$row['char_cp']}</td>";
				print "<td>{$row['char_items']}</td><td>{$row['name']} ({$row['npc_id']})</td></tr>";
			}
		print "</tbody>";
	print "</table>";
}

function display_handin_search()
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="handins.php?a=sp" method="post">
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

function display_handin_search_results($eqdb, $playername)
{
	$query = "SELECT character_data.id AS id, character_data.name AS charname, character_data.level AS level, guild_members.guild_id, guilds.name AS gname FROM character_data LEFT JOIN guild_members ON character_data.id = guild_members.char_id LEFT JOIN guilds ON guild_members.guild_id = guilds.id WHERE character_data.name LIKE '%{$playername}%'";
	$result = $eqdb->query($query);
	
	if($result->num_rows < 1)
	{
		RowText("<h5>No Players Found</h5>");
		display_handin_search();
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
						Hyperlink("handins.php?a=p&id={$row['id']}", $row['charname']);
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