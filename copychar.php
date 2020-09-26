<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_copychar)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Copy Character</h4>");

if (!isset($_GET['a']))
{
	display_select_origin_connection($admindb, $uid);
}
elseif ($_GET['a'] == "cn")
{
	
}
elseif ($_GET['a'] == "s")
{
	if (!IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	
	display_char_search($origin, $destination);
}
elseif ($_GET['a'] == "sd")
{
	if (!IsNumber($_POST['origin']))
		data_error();
	
	$origin = $_POST['origin'];
	display_select_destination_connection($admindb, $uid, $origin);
}
elseif ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	if (!IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	$query = "SELECT user, host, dbase, username, password FROM connections WHERE id = {$origin}";
	$result = $admindb->query($query);
	if ($result->num_rows != 1)
		data_error();
	
	$row = $result->fetch_assoc();
	
	$origindb = new mysqli($row['host'], $row['username'], $row['password'], $row['dbase']);

	if ($origindb->connect_errno)
	{
		print "Failed to connect to origin database.";
		include_once("footer.php");
		die;
	}	
	display_char_search_results($origindb, $playername, $origin, $destination);
}
else
{
	display_select_origin_connection($admindb, $uid);
}

include_once("footer.php");

function display_select_destination_connection($admindb, $uid, $origin)
{
	RowText("");
	Row();
		Col();
		DivC();
		Col(true, '', 4);
?>
			<form action="copychar.php?a=s" method="post">
				<div class="form-group">
					<label for="destination"><h6>Select Destination Server</h6></label>
					<select class="form-control" id="destination" name="destination">
<?php
						$query = "SELECT id, name FROM connections WHERE user = {$uid} AND id <> {$origin}";
						$result = $admindb->query($query);
				
						while ($row = $result->fetch_assoc())
						{
							print "<option value='{$row['id']}'>{$row['name']}</option>";
						}
?>
					</select>
				</div>
				<input type="hidden" name="origin" value="<?php print $origin; ?>">
				<button type="submit" class="btn btn-primary">Next</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

function display_select_origin_connection($admindb, $uid)
{
	RowText("");
	Row();
		Col();
		DivC();
		Col(true, '', 4);
?>
			<form action="copychar.php?a=sd" method="post">
				<div class="form-group">
					<label for="origin"><h6>Select Origin Server</h6></label>
					<select class="form-control" id="origin" name="origin">
<?php
						$query = "SELECT id, name FROM connections WHERE user = {$uid}";
						$result = $admindb->query($query);
				
						while ($row = $result->fetch_assoc())
						{
							print "<option value='{$row['id']}'>{$row['name']}</option>";
						}
?>
					</select>
				</div>
				<button type="submit" class="btn btn-primary">Next</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

function display_char_search_results($origindb, $playername, $origin, $destination)
{
	$query = "SELECT character_data.id AS id, character_data.name AS charname, character_data.level AS level, guild_members.guild_id, guilds.name AS gname FROM character_data LEFT JOIN guild_members ON character_data.id = guild_members.char_id LEFT JOIN guilds ON guild_members.guild_id = guilds.id WHERE character_data.name LIKE '%{$playername}%'";
	$result = $origindb->query($query);
	
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
						Hyperlink("copychar.php?a=cn&id={$row['id']}&o={$origin}&d={$destination}", $row['charname']);
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

function display_char_search($origin, $destination)
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="copychar.php?a=sp" method="post">
				<div class="form-group">
					<label for="playerName">Player Name</label>
					<input type="text" class="form-control" id="playerName" placeholder="Enter Player Name" name="playerName">
				</div>
				<input type="hidden" name="origin" value="<?php print $origin; ?>">
				<input type="hidden" name="destination" value="<?php print $destination; ?>">
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

?>