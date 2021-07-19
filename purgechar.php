<?php
/***************************************************************************************************
File:			purgechar.php
Description:	Interface to purge all character data from database for a character id
***************************************************************************************************/

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

// no action parameter - show purge form
if (!isset($_GET['a']))
	display_purge_form();
elseif ($_GET['a'] == "p")
{
	// purge the character data
	if (!IsNumber($_POST['charID']))
		data_error();
	
	$char_id = $_POST['charID'];
	
	RowText("Deleting all character data for ID: {$char_id}");
	
	$queries = array();
	// character_data
	array_push($queries, "DELETE FROM character_data WHERE id = {$char_id}");
	// character_alternate_abilities
	array_push($queries, "DELETE FROM character_alternate_abilities WHERE id = {$char_id}");
	// character_bind
	array_push($queries, "DELETE FROM character_bind WHERE id = {$char_id}");
	// character_buffs
	array_push($queries, "DELETE FROM character_buffs WHERE character_id = {$char_id}");
	
	// character_corpses
	array_push($queries, "DELETE FROM character_corpses WHERE charid = {$char_id}");
	// character_corpse_items
	$query = "SELECT id FROM character_corpses WHERE charid = {$char_id}";
	$result = $eqdb->query($query);
	while ($row = $result->fetch_assoc())
	{
		array_push($queries, "DELETE FROM character_corpse_items WHERE corpse_id = {$row['id']}");
	}
	
	// character_corpses_backup
	array_push($queries, "DELETE FROM character_corpses_backup WHERE charid = {$char_id}");
	// character_corpse_items_backup
	$query = "SELECT id FROM character_corpses_backup WHERE charid = {$char_id}";
	$result = $eqdb->query($query);
	while ($row = $result->fetch_assoc())
	{
		array_push($queries, "DELETE FROM character_corpse_items_backup WHERE corpse_id = {$row['id']}");
	}
	
	// character_disciplines
	array_push($queries, "DELETE FROM character_disciplines WHERE id = {$char_id}");
	
	// character_ffa
	array_push($queries, "DELETE FROM character_ffa WHERE char_id = {$char_id}");
	
	// character_inspect_messages
	array_push($queries, "DELETE FROM character_inspect_messages WHERE id = {$char_id}");
	
	// character_item_recast
	array_push($queries, "DELETE FROM character_item_recast WHERE id = {$char_id}");
	
	// character_languages
	array_push($queries, "DELETE FROM character_languages WHERE id = {$char_id}");
	
	// character_lockout
	array_push($queries, "DELETE FROM character_lockout WHERE char_id = {$char_id}");
	
	// character_material
	array_push($queries, "DELETE FROM character_material WHERE id = {$char_id}");
	
	// character_memmed_spells
	array_push($queries, "DELETE FROM character_memmed_spells WHERE id = {$char_id}");
	
	// character_pet_buffs
	array_push($queries, "DELETE FROM character_pet_buffs WHERE char_id = {$char_id}");
	
	// character_pet_info
	array_push($queries, "DELETE FROM character_pet_info WHERE char_id = {$char_id}");
	
	// character_pet_inventory
	array_push($queries, "DELETE FROM character_pet_inventory WHERE char_id = {$char_id}");
	
	// character_pvp
	array_push($queries, "DELETE FROM character_pvp WHERE char_id = {$char_id}");
	
	// character_skills
	array_push($queries, "DELETE FROM character_skills WHERE id = {$char_id}");
	
	// character_spells
	array_push($queries, "DELETE FROM character_spells WHERE id = {$char_id}");
	
	// faction_values
	array_push($queries, "DELETE FROM faction_values WHERE char_id = {$char_id}");
	
	// faction_values_prefix
	array_push($queries, "DELETE FROM faction_values_prefix WHERE char_id = {$char_id}");
	
	// guild_members
	array_push($queries, "DELETE FROM guild_members WHERE char_id = {$char_id}");
	
	// inventory
	array_push($queries, "DELETE FROM inventory WHERE charid = {$char_id}");
	
	// inventory_snapshots
	array_push($queries, "DELETE FROM inventory_snapshots WHERE charid = {$char_id}");
	
	// inventory_snapshots_v1_bak
	array_push($queries, "DELETE FROM inventory_snapshots_v1_bak WHERE charid = {$char_id}");
	
	foreach($queries as $key => $value)
		RowText($value);
		
	foreach($queries as $key => $value)
	{
		$result = $eqdb->query($value);
		if (!$result)
			RowText("Query Failed: {$value}");
		
	}
	
	RowText("Done");
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