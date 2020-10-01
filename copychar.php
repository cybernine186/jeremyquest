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

// Select Origin Connection - Step 1
if (!isset($_GET['a']))
{
	display_select_origin_connection($admindb, $uid);
}
// Process Copy
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_POST['sn']) || !IsNumber($_POST['sa']) || !IsNumber($_POST['id']) || !IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	
	RowText("Processing Copy");
	
	// Same Name, Same Account
	if ($_POST['sn'] && $_POST['sa'])
		copy_character($_POST['origin'], $_POST['destination'], $admindb, $uid, true, true, $_POST['id']);
	// Same Name, Different Account
	elseif ($_POST['sn'] && !$_POST['sa'])
	{
		if (!IsTextAndNumbers($_POST['accountName']))
			data_error();
		
		copy_character($_POST['origin'], $_POST['destination'], $admindb, $uid, true, false, $_POST['id'], "", $_POST['accountName']);
	}
		
		
}
// Check account
elseif ($_GET['a'] == "ca")
{
	RowText("Checking Account");
	
	if (!IsNumber($_POST['sn']) || !IsNumber($_POST['sa']) || !IsNumber($_POST['id']) || !IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	
	if (!IsTextAndNumbers($_POST['accountName']))
		data_error();
	
	$account_name = $_POST['accountName'];
	
	$destinationdb = DatabaseConnection($admindb, $_POST['destination'], $uid);
	if (!$destinationdb)
		data_error();
	
	// Check for account on destination server, and get ID if exists
	$query = "SELECT id FROM account WHERE name = '{$account_name}'";
	$result = $destinationdb->query($query);
	if ($result->num_rows == 0)
	{
		RowText("Destination account does not exist. Please create it or try another.");
		Row();
			Col();
			DivC();
			Col(true, '', 4);
				// Check Account context
?>
				<form action="copychar.php?a=ca" method="post">
					<div class="form-group">
						<!--<label for="accountName">Account Name</label>!-->
						<input type="text" class="form-control" id="accountName" placeholder="Enter Account Name" name="accountName">
					</div>
					<input type="hidden" name="origin" value="<?php print $_POST['origin']; ?>">
					<input type="hidden" name="destination" value="<?php print $_POST['destination']; ?>">
					<input type="hidden" name="id" value="<?php print $_POST['id']; ?>">
					<input type="hidden" name="sa" value="0">
					<input type="hidden" name="sn" value="1">
					<button type="submit" class="btn btn-primary">Copy to Different Account</button>
				</form>
<?php
			DivC();
			Col();
			DivC();
		DivC();
		
		// terminate script
		include_once("footer.php");
		die;
	}
	elseif ($result->num_rows > 1)
		data_error();
		
	$row = $result->fetch_assoc();
	$account_id = $row['id'];
	
	// Check for open slot
	$query = "SELECT count(*) AS count FROM character_data WHERE account_id = {$account_id}";
	$result = $destinationdb->query($query);
	if ($result->num_rows == 0)
		data_error();
	$row = $result->fetch_assoc();
	
	if ($row['count'] < 8)
	{
		// There's space - confirm before processing
		RowText("Destination account has space.");
		
		$origindb = DatabaseConnection($admindb, $_POST['origin'], $uid);
		if (!$origindb)
			data_error();
		
		// Get Player Name
		$query = "SELECT name FROM character_data WHERE id = {$_POST['id']}";
		$result = $origindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$player_name = $row['name'];
		
		// Get Origin Server Name
		$query = "SELECT name, user FROM connections WHERE id = {$_POST['origin']}";
		$result = $admindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$origin_name = $row['name'];
		
		// Get Destination Server Name
		$query = "SELECT name, user FROM connections WHERE id = {$_POST['destination']}";
		$result = $admindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$destination_name = $row['name'];
		
		RowText("Copy character {$player_name} from {$origin_name} to {$destination_name} keeping the same name and new account {$account_name}?");
		
		RowText("");
		Row();
			Col();
			DivC();
			Col(true, '', 4);
?>
				<form action="copychar.php?a=p" method="post">
					<input type="hidden" name="origin" value="<?php print $_POST['origin']; ?>">
					<input type="hidden" name="destination" value="<?php print $_POST['destination']; ?>">
					<input type="hidden" name="id" value="<?php print $_POST['id']; ?>">
					<input type="hidden" name="accountName" value="<?php print $account_name; ?>">
					<input type="hidden" name="sa" value="0">
					<input type="hidden" name="sn" value="1">
					<button type="submit" class="btn btn-primary">PROCESS COPY</button>
				</form>
<?php
			DivC();
			Col();
			DivC();
		DivC();
	}
	else
	{
		RowText("Account is full. Please free a character slot or try another account.");
		Row();
			Col();
			DivC();
			Col(true, '', 4);
				// Check Account context
?>
				<form action="copychar.php?a=ca" method="post">
					<div class="form-group">
						<!--<label for="accountName">Account Name</label>!-->
						<input type="text" class="form-control" id="accountName" placeholder="Enter Account Name" name="accountName">
					</div>
					<input type="hidden" name="origin" value="<?php print $_POST['origin']; ?>">
					<input type="hidden" name="destination" value="<?php print $_POST['destination']; ?>">
					<input type="hidden" name="id" value="<?php print $_POST['id']; ?>">
					<input type="hidden" name="sa" value="0">
					<input type="hidden" name="sn" value="1">
					<button type="submit" class="btn btn-primary">Copy to Different Account</button>
				</form>
<?php
			DivC();
			Col();
			DivC();
		DivC();
	}
	
}
// Confirm
elseif ($_GET['a'] == "c")
{
	if (!IsNumber($_POST['sn']) || !IsNumber($_POST['sa']) || !IsNumber($_POST['id']) || !IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	
	$origindb = DatabaseConnection($admindb, $_POST['origin'], $uid);
	
	// Same Name, Same Account
	if ($_POST['sn'] && $_POST['sa'])
	{
		// Get Player Name
		$query = "SELECT name FROM character_data WHERE id = {$_POST['id']}";
		$result = $origindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$playername = $row['name'];
		
		// Get Origin Server Name
		$query = "SELECT name, user FROM connections WHERE id = {$_POST['origin']}";
		$result = $admindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$originname = $row['name'];
		
		// Get Destination Server Name
		$query = "SELECT name, user FROM connections WHERE id = {$_POST['destination']}";
		$result = $admindb->query($query);
		if ($result->num_rows != 1)
			data_error();
		$row = $result->fetch_assoc();
		$destinationname = $row['name'];
		
		RowText("Copy character {$playername} from {$originname} to {$destinationname} keeping the same name and account?");
		
		RowText("");
		Row();
			Col();
			DivC();
			Col(true, '', 4);
?>
				<form action="copychar.php?a=p" method="post">
					<input type="hidden" name="origin" value="<?php print $_POST['origin']; ?>">
					<input type="hidden" name="destination" value="<?php print $_POST['destination']; ?>">
					<input type="hidden" name="id" value="<?php print $_POST['id']; ?>">
					<input type="hidden" name="sa" value="1">
					<input type="hidden" name="sn" value="1">
					<button type="submit" class="btn btn-primary">PROCESS COPY</button>
				</form>
<?php
			DivC();
			Col();
			DivC();
		DivC();
	}
}
// Check Name
elseif ($_GET['a'] == "cn")
{
	if (!IsNumber($_GET['id']) || !IsNumber($_GET['o']) || !IsNumber($_GET['d']))
		data_error();
	
	$origindb = DatabaseConnection($admindb, $_GET['o'], $uid);
	if (!$origindb)
		data_error();
	
	$destinationdb = DatabaseConnection($admindb, $_GET['d'], $uid);
	if (!$destinationdb)
		data_error();
	
	$query = "SELECT character_data.name AS char_name, character_data.account_id AS account_id, account.name AS account_name FROM character_data LEFT JOIN account ON character_data.account_id = account.id WHERE character_data.id = {$_GET['id']}";
	$result = $origindb->query($query);
	if ($result->num_rows != 1)
		data_error();
	$row = $result->fetch_assoc();
	$playername = $row['char_name'];
	$oldaccountid = $row['account_id'];
	$newaccountid = 0;
	$account_name = $row['account_name'];
	
	$query = "SELECT id, account_id FROM character_data WHERE name = '{$playername}'";
	$result = $destinationdb->query($query);
	if ($result->num_rows == 0)
	{
		// No results - name available
		RowText("Name <b>{$playername}</b> available on destination server. Checking for space on the account.");
		
		// Get account id based on account name on destination server
		$query = "SELECT id FROM account WHERE name = '{$account_name}'";
		$resultaccount = $destinationdb->query($query);
		if ($resultaccount->num_rows != 1)
		{
			RowText("Account <b>{$account_name}</b> does not exist on destination server.");
		}
		else
		{
			$row = $resultaccount->fetch_assoc();
			$newaccountid = $row['id'];
			$query = "SELECT count(*) AS numchars FROM character_data WHERE account_id = {$newaccountid}";
			$resultaccount = $destinationdb->query($query);
			if ($resultaccount->num_rows == 0)
				data_error();
			$row = $resultaccount->fetch_assoc();
			if ($row['numchars'] < 8)
			{
				RowText("Space available on account on destination server.");
				RowText("");
				Row();
					Col();
					DivC();
					Col(true, '', 4);
?>
						<form action="copychar.php?a=c" method="post">
							<input type="hidden" name="origin" value="<?php print $_GET['o']; ?>">
							<input type="hidden" name="destination" value="<?php print $_GET['d']; ?>">
							<input type="hidden" name="id" value="<?php print $_GET['id']; ?>">
							<input type="hidden" name="sa" value="1">
							<input type="hidden" name="sn" value="1">
							<button type="submit" class="btn btn-primary">Copy to Same Account</button>
						</form>
<?php
					DivC();
					Col();
					DivC();
				DivC();
			}
			RowText("or");
		}
		
		Row();
			Col();
			DivC();
			Col(true, '', 4);
				// Check Account context
?>
				<form action="copychar.php?a=ca" method="post">
					<div class="form-group">
						<!--<label for="accountName">Account Name</label>!-->
						<input type="text" class="form-control" id="accountName" placeholder="Enter Account Name" name="accountName">
					</div>
					<input type="hidden" name="origin" value="<?php print $_GET['o']; ?>">
					<input type="hidden" name="destination" value="<?php print $_GET['d']; ?>">
					<input type="hidden" name="id" value="<?php print $_GET['id']; ?>">
					<input type="hidden" name="sa" value="0">
					<input type="hidden" name="sn" value="1">
					<button type="submit" class="btn btn-primary">Copy to Different Account</button>
				</form>
<?php
			DivC();
			Col();
			DivC();
		DivC();
	}

	elseif ($result->num_rows == 1)
	{
		// Name is taken - prompt for new
		RowText("Name <b>{$playername}</b> is taken on destination server.");
		RowText("Choose a new name, or rename/delete existing character on destination server and try again.");
		display_newname_form($_GET['o'], $_GET['d']);
	}
	else	// Multiple characters of same name? Error
		data_error();
}
// Search characters
elseif ($_GET['a'] == "s")
{
	if (!IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	
	display_char_search($origin, $destination);
}
// Select Destination Server
elseif ($_GET['a'] == "sd")
{
	if (!IsNumber($_POST['origin']))
		data_error();
	
	$origin = $_POST['origin'];
	display_select_destination_connection($admindb, $uid, $origin);
}
// Player search results
elseif ($_GET['a'] == "sp")
{
	if (!IsText($_POST['playerName']))
		data_error();
	
	if (!IsNumber($_POST['origin']) || !IsNumber($_POST['destination']))
		data_error();
	$origin = $_POST['origin'];
	$destination = $_POST['destination'];
	
	$playername = $eqdb->real_escape_string($_POST['playerName']);
	
	$origindb = DatabaseConnection($admindb, $origin, $uid);
	if (!$origindb)
		data_error();
	
	display_char_search_results($origindb, $playername, $origin, $destination);
}
// Select Origin Connection - Step 1
else
{
	display_select_origin_connection($admindb, $uid);
}

include_once("footer.php");

function copy_character($odb, $ddb, $adb, $uid, $same_name, $same_account, $character_id, $new_character_name = "", $new_account_name = "")
{
	$origindb = DatabaseConnection($adb, $odb, $uid);
	$destinationdb = DatabaseConnection($adb, $ddb, $uid);
	
	// character_data table first
	$query = "SELECT * FROM character_data WHERE id = {$character_id}";
	$result = $origindb->query($query);
	$row = $result->fetch_assoc();
	
	$query = "INSERT INTO character_data VALUES (";
	foreach ($row as $key => $value)
	{
		if ($key != "id")
		{
			if ($value == "")
				$query = $query . "NULL, ";
			else
				$query =  $query . $value . ', ';
		}
	}
	
	if (!isset($row['is_online']))
		$query = $query . "0, ";

	$query = rtrim($query, " ");
	
	$query = rtrim($query, ",");
	
	$query = $query . ")";
	
	RowText($query);
	
	$insert_id = 0;
	
	//$result = $destinationdb->query($query);
	//$insert_id = $destinationdb->insert_id;
	
	//RowText("Insert ID: {$insert_id}");
	
	// character_alternate_abilities
	
	$query = "SELECT * FROM character_alternate_abilities WHERE id = {$character_id}";
	$result = $origindb->query($query);
	
	if ($result->num_rows < 1)
		RowText("No Alternate Abilities");
	else
	{
		$query = "INSERT INTO character_alternate_abilities VALUES ";
		while ($row = $result->fetch_assoc())
		{
			$query = $query . "(";
			foreach ($row as $key => $value)
			{
				if ($key == "id")
					$query = $query . $insert_id . ",";
				else
				{
					if ($value == "")
						$query = $query . "NULL, ";
					else
						$query =  $query . $value . ',';
				}
			}
			$query = rtrim($query, ',');
			$query = $query . "),";
		}
		
		$query = rtrim($query, ",");
		//$result = $destinationdb->query($query);
	}
	
	RowText($query);
	
	// character_bind
	$query = "SELECT * FROM character_bind WHERE id = {$character_id}";
	$result = $origindb->query($query);
	
	if ($result->num_rows < 1)
		RowText("No Binds?");
	else
	{
		$query = "INSERT INTO character_bind VALUES ";
		while ($row = $result->fetch_assoc())
		{
			$query = $query . "(";
			foreach ($row as $key => $value)
			{
				if ($key == "id")
					$query = $query . $insert_id . ",";
				else
				{
					if ($value == "")
						$query = $query . "NULL, ";
					else
						$query =  $query . $value . ',';
				}
			}
			$query = rtrim($query, ',');
			$query = $query . "),";
		}
		
		$query = rtrim($query, ",");
		//$result = $destinationdb->query($query);
	}
	
	RowText($query);
}

function display_newname_form($origin, $destination)
{
?>
	<form action="copychar.php?a=nn" method="post">
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
}

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