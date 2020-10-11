<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission['purgechar'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Purge Character Data</h4>");

if (!isset($_GET['a']))
	display_purge_form();
elseif ($_GET['a'] == "p")
{
	if (!IsNumber($_POST['charID']))
		data_error();
	
	$char_id = $_POST['charID'];
	
	RowText("Deleting all character data for ID: {$char_id}");
	
	$queries = array();
	array_push($queries, "DELETE FROM character_data WHERE id = {$char_id}");
	array_push($queries, "DELETE FROM character_alternate_abilities WHERE id = {$char_id}");
	array_push($queries, "DELETE FROM character_bind WHERE id = {$char_id}");
	array_push($queries, "DELETE FROM character_buffs WHERE character_id = {$char_id}");
	
	array_push($queries, "DELETE FROM character_corpses WHERE charid = {$char_id}");
	
	$query = "SELECT id FROM character_corpses WHERE charid = {$char_id}";
	$result = $eqdb->query($query);
	while ($row = $result->fetch_assoc())
	{
		array_push($queries, "DELETE FROM character_corpse_items WHERE corpse_id = {$row['id']}");
	}
	
	array_push($queries, "DELETE FROM character_corpses_backup WHERE charid = {$char_id}");
	
	$query = "SELECT id FROM character_corpses_backup WHERE charid = {$char_id}";
	$result = $eqdb->query($query);
	while ($row = $result->fetch_assoc())
	{
		array_push($queries, "DELETE FROM character_corpse_items_backup WHERE corpse_id = {$row['id']}");
	}
	
	foreach($queries as $key => $value)
		RowText($value);
}
else
	display_purge_form();

include_once("footer.php");

function display_purge_form()
{
	Row();
		Col();
		DivC();
		Col(false, '', 6);
?>
			<form action="purgechar.php?a=p" method="post">
				<div class="form-group">
					<label for="charID">Character ID</label>
					<input type="text" class="form-control" id="charID" placeholder="Enter Character ID" name="charID">
				</div>
				<button type="submit" class="btn btn-primary">Submit</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
		
}

?>