<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission['zonemerger'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

// old dump but has some stuff wfh doesn't - zone
$p2002db = new mysqli($dbhost, "neq", "mariaslargesekrets", "p2002");

if ($p2002db->connect_errno)
{
	print "Failed to connect to p2002 database.";
	include_once("footer.php");
	die;
}

$wfhdb = new mysqli($dbhost, "neq", "mariaslargesekrets", "dev");
if ($wfhdb->connect_errno)
{
	print "Failed to connect to WFH database.";
	include_once("footer.php");
	die;
}


RowText("<h4>Zone Merger</h4>");

if (!isset($_GET['a']))
	display_zoneselect_form($eqdb);

// Show Zone
elseif ($_GET['a'] == "sz")
{
	if (!IsNumber($_POST['inputZone']))
		data_error();
	$zone_id = $_POST['inputZone'];
	
	show_zone_tasks($eqdb, $zone_id);
}

// Transfer full zone content
elseif ($_GET['a'] == "tfzc")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	
	swap_zone_data($eqdb, $p2002db, $zone_id);
	delete_existing_npcs($eqdb, $zone_id);
	copy_wfh_npcs($eqdb, $wfhdb, $zone_id);
	delete_existing_spawn_data($eqdb, $zone_id);
	copy_spawn_data($eqdb, $wfhdb, $zone_id);
	copy_graveyard_data($eqdb, $p2002db, $zone_id);
	copy_grid_data($eqdb, $wfhdb, $zone_id);
	copy_loot_data($eqdb, $wfhdb, $zone_id);
}

// Swap Zone Data
elseif ($_GET['a'] == "szd")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];

	show_zone_tasks($eqdb, $zone_id);	
	swap_zone_data($eqdb, $p2002db, $zone_id);
}

// Delete Existing NPCs
elseif ($_GET['a'] == "den")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	delete_existing_npcs($eqdb, $zone_id);
}

// Copy Over NPCs
elseif ($_GET['a'] == "con")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_wfh_npcs($eqdb, $wfhdb, $zone_id);
}

// Delete Existing Spawn Data
elseif ($_GET['a'] == "desd")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	delete_existing_spawn_data($eqdb, $zone_id);
}

// Copy Spawn Data
elseif ($_GET['a'] == "csd")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_spawn_data($eqdb, $wfhdb, $zone_id);
}

// Copy Graveyard Data
elseif ($_GET['a'] == "cgy")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_graveyard_data($eqdb, $p2002db, $zone_id);
}

// Copy Grid Data
elseif ($_GET['a'] == "cg")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_grid_data($eqdb, $wfhdb, $zone_id);
}

// Copy Loot Data
elseif ($_GET['a'] == "cld")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_loot_data($eqdb, $wfhdb, $zone_id);
}

elseif ($_GET['a'] == "cns")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	show_zone_tasks($eqdb, $zone_id);
	copy_npc_spell_data($eqdb, $wfhdb, $zone_id);
}

else
	display_zoneselect_form($eqdb);

include_once("footer.php");

function show_zone_tasks($eqdb, $zone_id)
{
	// Show zone number and name
	$query = "SELECT short_name FROM zone WHERE zoneidnumber = {$zone_id}";
	$result = $eqdb->query($query);
	if ($result->num_rows != 1)
		data_error();
	$row = $result->fetch_assoc();
	RowText("{$zone_id} - {$row['short_name']}");
	
	Row();
		Col();
		DivC();
		Col(true, '', 6);
?>
			<table class="table">
				<thead>
					<tr>
						<th scope="col">Task</th>
						<th scope="col">Do It</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Transfer Full Zone Content</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=tfzc&zid=<?php print $zone_id; ?>" role="button">GO</a></td>
					</tr>
					<tr>
						<td>Swap Over Zone Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=szd&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Delete Existing NPCs</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=den&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Copy Over WFH NPCs</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=con&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Delete Existing Spawn Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=desd&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Copy Spawn Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=csd&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Copy Graveyard Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=cgy&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Copy Grid Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=cg&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>Copy Loot Data</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=cld&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
					<tr>
						<td>NPC Spells</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=cns&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
				</tbody>
			</table>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

function swap_zone_data($eqdb, $p2002db, $zone_id)
{
	$query = "DELETE FROM zone WHERE zoneidnumber = {$zone_id}";
	$result = $eqdb->query($query);
	$affected_rows = $eqdb->affected_rows;
	RowText("{$affected_rows} zone header(s) were deleted for Zone {$zone_id}");
	
	$query = "SELECT short_name, id, file_name, long_name, map_file_name, safe_x, safe_y, safe_z, graveyard_id, min_level, min_status, zoneidnumber, version, timezone, maxclients, ruleset, note, underworld, 
				minclip, maxclip, fog_minclip, fog_maxclip, fog_blue, fog_red, fog_green, sky, ztype, zone_exp_multiplier, walkspeed, time_type, fog_red1, fog_green1, fog_blue1, fog_minclip1, fog_maxclip1, 
				fog_red2, fog_green2, fog_blue2, fog_minclip2, fog_maxclip2, fog_red3, fog_green3, fog_blue3, fog_minclip3, fog_maxclip3, fog_red4, fog_green4, fog_blue4, fog_minclip4, fog_maxclip4, 
				fog_density, flag_needed, canbind, cancombat, canlevitate, castoutdoor, hotzone, insttype, shutdowndelay, peqzone, expansion, suspendbuffs, 
				rain_chance1, rain_chance2, rain_chance3, rain_chance4, rain_duration1, rain_duration2, rain_duration3, rain_duration4, 
				snow_chance1, snow_chance2, snow_chance3, snow_chance4, snow_duration1, snow_duration2, snow_duration3, snow_duration4, 
				gravity, type, skylock, skip_los, music FROM zone WHERE zoneidnumber = {$zone_id}";
	
	$result = $p2002db->query($query);
	if ($result->num_rows != 1)
		data_error();
	$r = $result->fetch_assoc();

	$query = "INSERT INTO zone (short_name, id, file_name, long_name, map_file_name, safe_x, safe_y, safe_z, graveyard_id, min_level, min_status, zoneidnumber, version, timezone, maxclients, ruleset, note, underworld, 
				minclip, maxclip, fog_minclip, fog_maxclip, fog_blue, fog_red, fog_green, sky, ztype, zone_exp_multiplier, walkspeed, time_type, fog_red1, fog_green1, fog_blue1, fog_minclip1, fog_maxclip1, 
				fog_red2, fog_green2, fog_blue2, fog_minclip2, fog_maxclip2, fog_red3, fog_green3, fog_blue3, fog_minclip3, fog_maxclip3, fog_red4, fog_green4, fog_blue4, fog_minclip4, fog_maxclip4, 
				fog_density, flag_needed, canbind, cancombat, canlevitate, castoutdoor, hotzone, insttype, shutdowndelay, peqzone, expansion, suspendbuffs, 
				rain_chance1, rain_chance2, rain_chance3, rain_chance4, rain_duration1, rain_duration2, rain_duration3, rain_duration4, 
				snow_chance1, snow_chance2, snow_chance3, snow_chance4, snow_duration1, snow_duration2, snow_duration3, snow_duration4, 
				gravity, type, skylock, fast_regen_hp, fast_regen_mana, fast_regen_endurance, npc_max_aggro_dist, max_movement_update_range,
				ffa, lockout, logout_kick_timer, min_expansion, max_expansion, content_flags, content_flags_disabled) VALUES
				('{$r['short_name']}', {$r['id']}, '{$r['file_name']}', '{$r['long_name']}', NULL, '{$r['safe_x']}', '{$r['safe_y']}', '{$r['safe_z']}', 
				'{$r['graveyard_id']}', {$r['min_level']}, {$r['min_status']}, {$r['zoneidnumber']}, {$r['version']}, {$r['timezone']}, {$r['maxclients']}, {$r['ruleset']}, '{$r['note']}', '{$r['underworld']}', 
				'{$r['minclip']}', '{$r['maxclip']}', '{$r['fog_minclip']}', '{$r['fog_maxclip']}', {$r['fog_blue']}, {$r['fog_red']}, {$r['fog_green']}, {$r['sky']}, {$r['ztype']}, '{$r['zone_exp_multiplier']}', '{$r['walkspeed']}', {$r['time_type']}, 
				{$r['fog_red1']}, {$r['fog_green1']}, {$r['fog_blue1']}, '{$r['fog_minclip1']}', '{$r['fog_maxclip1']}', 
				{$r['fog_red2']}, {$r['fog_green2']}, {$r['fog_blue2']}, '{$r['fog_minclip2']}', '{$r['fog_maxclip2']}', 
				{$r['fog_red3']}, {$r['fog_green3']}, {$r['fog_blue3']}, '{$r['fog_minclip3']}', '{$r['fog_maxclip3']}', 
				{$r['fog_red4']}, {$r['fog_green4']}, {$r['fog_blue4']}, '{$r['fog_minclip4']}', '{$r['fog_maxclip4']}', 
				'{$r['fog_density']}', '{$r['flag_needed']}', {$r['canbind']}, {$r['cancombat']}, {$r['canlevitate']}, {$r['castoutdoor']}, {$r['hotzone']}, {$r['insttype']}, {$r['shutdowndelay']}, {$r['peqzone']}, {$r['expansion']}, {$r['suspendbuffs']}, 
				{$r['rain_chance1']}, {$r['rain_chance2']}, {$r['rain_chance3']}, {$r['rain_chance4']}, {$r['rain_duration1']}, {$r['rain_duration2']}, {$r['rain_duration3']}, {$r['rain_duration4']}, 
				{$r['snow_chance1']}, {$r['snow_chance2']}, {$r['snow_chance3']}, {$r['snow_chance4']}, {$r['snow_duration1']}, {$r['snow_duration2']}, {$r['snow_duration3']}, {$r['snow_duration4']}, 
				{$r['gravity']}, {$r['type']}, {$r['skylock']}, 180, 180, 180, 600, 600, 
				0, 0, 0, 0, 0, NULL, NULL)";
	
	$result = $eqdb->query($query);
	if ($result)
		RowText("Zone Header Data for Zone {$zone_id} successfully inserted!");
	else
		RowText("Zone Header Data for Zone {$zone_id} NOT successfully inserted!");
}

function copy_graveyard_data($eqdb, $p2002db, $zone_id)
{
	$query = "DELETE FROM graveyard WHERE zone_id = {$zone_id}";
	$result = $eqdb->query($query);
	if (!$result)
		RowText("Existing Graveyard Delete query failed.");
	else
		RowText("{$eqdb->affected_rows} rows of graveyard deleted for zone {$zone_id}");
	
	$query = "SELECT x, y, z, heading FROM graveyard WHERE zone_id = {$zone_id}";
	$result = $p2002db->query($query);
	if ($result->num_rows != 1)
	{
		RowText("No Graveyard data for zone {$zone_id}");
		return;
	}
	
	$r = $result->fetch_assoc();
	
	$query = "INSERT INTO graveyard (zone_id, x, y, z, heading) VALUES 
				({$zone_id}, '{$r['x']}', '{$r['y']}', '{$r['z']}', '{$r['heading']}')";
	$result_insert = $eqdb->query($query);
	if (!$result_insert)
		RowText("Graveyard data insert failed.");
	else
		RowText("Graveyard data insert successful.");
	
	$insert_id = $eqdb->insert_id;
	
	$query = "UPDATE zone SET graveyard_id = {$insert_id} WHERE zoneidnumber = {$zone_id}";
	$result_update = $eqdb->query($query);
	if (!$result_update)
		RowText("Zone update for gravyard_id failed.");
	else
		RowText("Zone update for graveyard_id successful.");
}

function copy_wfh_npcs($eqdb, $p2002db, $zone_id)
{
	$npc_id_min = $zone_id * 1000;
	$npc_id_max = ($zone_id + 1) * 1000;
	
	$query = "SELECT id, name, lastname, level, race, class, bodytype, hp, mana, gender, texture, helmtexture, herosforgemodel, size, hp_regen_rate, mana_regen_rate, 
				loottable_id, merchant_id, alt_currency_id, npc_spells_id, npc_spells_effects_id, npc_faction_id, adventure_template_id, trap_template, 
				mindmg, maxdmg, attack_count, npcspecialattks, special_abilities, aggroradius, assistradius, 
				face, luclin_hairstyle, luclin_haircolor, luclin_eyecolor, luclin_eyecolor2, luclin_beardcolor, luclin_beard, drakkin_heritage, drakkin_tattoo, drakkin_details, 
				armortint_id, armortint_red, armortint_green, armortint_blue, d_melee_texture1, d_melee_texture2, ammo_idfile, prim_melee_type, sec_melee_type, ranged_type, runspeed, 
				MR, CR, DR, FR, PR, Corrup, PhR, see_invis, see_invis_undead, qglobal, AC, npc_aggro, spawn_limit, attack_speed, attack_delay, findable, 
				STR, STA, DEX, AGI, _INT, WIS, CHA, see_hide, see_improved_hide, trackable, isbot, exclude, ATK, Accuracy, Avoidance, slow_mitigation, 
				version, maxlevel, scalerate, private_corpse, unique_spawn_by_name, underwater, isquest, emoteid, spellscale, healscale, no_target_hotkey, raid_target, 
				chesttexture, armtexture, bracertexture, handtexture, legtexture, feettexture, light, walkspeed, peqid, unique_, fixed, combat_hp_regen, combat_mana_regen, aggro_pc, 
				ignore_distance, ignore_despawn, show_name, untargetable, disable_instance FROM npc_types WHERE id >= {$npc_id_min} AND id < {$npc_id_max}";
	
	$result = $p2002db->query($query);
	RowText("Found {$result->num_rows} NPCs in p2002 database for zone {$zone_id}.<br />");
	$insert_count = 0;
	while ($r = $result->fetch_assoc())
	{
		//																																					hp_regen_rate, mana_regen_rate
		$query = "INSERT INTO npc_types (id, name, lastname, level, race, class, bodytype, hp, mana, gender, texture, helmtexture, herosforgemodel, size, 
					loottable_id, merchant_id, alt_currency_id, npc_spells_id, npc_spells_effects_id, npc_faction_id, adventure_template_id, trap_template, 
					mindmg, maxdmg, attack_count, npcspecialattks, special_abilities, aggroradius, assistradius, 
					face, luclin_hairstyle, luclin_haircolor, luclin_eyecolor, luclin_eyecolor2, luclin_beardcolor, luclin_beard, drakkin_heritage, drakkin_tattoo, drakkin_details, 
					armortint_id, armortint_red, armortint_green, armortint_blue, d_melee_texture1, d_melee_texture2, ammo_idfile, prim_melee_type, sec_melee_type, ranged_type, runspeed, 
					MR, CR, DR, FR, PR, Corrup, PhR, see_invis, see_invis_undead, qglobal, AC, npc_aggro, spawn_limit, attack_speed, attack_delay, findable, 
					STR, STA, DEX, AGI, _INT, WIS, CHA, see_hide, see_improved_hide, trackable, isbot, exclude, ATK, Accuracy, Avoidance, slow_mitigation, 
					version, maxlevel, scalerate, private_corpse, unique_spawn_by_name, underwater, isquest, emoteid, spellscale, healscale, no_target_hotkey, raid_target, " .
					// chesttexture																												combat_hp_regen, combat_mana_regen, aggro_pc,
									"armtexture, bracertexture, handtexture, legtexture, feettexture, light, walkspeed, peqid, unique_, fixed, " . 
					// ignore_distance											  disable_instance, 
										"ignore_despawn, show_name, untargetable, 					charm_ac, charm_min_dmg, charm_max_dmg, charm_attack_delay, charm_accuracy_rating, charm_avoidance_rating, charm_atk, 
					skip_global_loot, rare_spawn, stuck_behavior, model, combat_hp_regen, hp_regen_rate, combat_mana_regen, mana_regen_rate, always_aggro, flymode) VALUES 
					
					({$r['id']}, '{$r['name']}', '{$r['lastname']}', {$r['level']}, {$r['race']}, {$r['class']}, {$r['bodytype']}, {$r['hp']}, {$r['mana']}, {$r['gender']}, {$r['texture']}, {$r['helmtexture']}, {$r['herosforgemodel']}, '{$r['size']}', 
					{$r['loottable_id']}, {$r['merchant_id']}, {$r['alt_currency_id']}, {$r['npc_spells_id']}, {$r['npc_spells_effects_id']}, {$r['npc_faction_id']}, {$r['adventure_template_id']}, {$r['adventure_template_id']}, 
					{$r['mindmg']}, {$r['maxdmg']}, {$r['attack_count']}, '{$r['npcspecialattks']}', '{$r['special_abilities']}', {$r['aggroradius']}, {$r['assistradius']}, 
					{$r['face']}, {$r['luclin_hairstyle']}, {$r['luclin_haircolor']}, {$r['luclin_eyecolor']}, {$r['luclin_eyecolor2']}, {$r['luclin_beardcolor']}, {$r['luclin_beard']}, {$r['drakkin_heritage']}, {$r['drakkin_tattoo']}, {$r['drakkin_details']}, 
					{$r['armortint_id']}, {$r['armortint_red']}, {$r['armortint_green']}, {$r['armortint_blue']}, {$r['d_melee_texture1']}, {$r['d_melee_texture2']}, '{$r['ammo_idfile']}', {$r['prim_melee_type']}, {$r['sec_melee_type']}, {$r['ranged_type']}, '{$r['runspeed']}', 
					{$r['MR']}, {$r['CR']}, {$r['DR']}, {$r['FR']}, {$r['PR']}, {$r['Corrup']}, {$r['PhR']}, {$r['see_invis']}, {$r['see_invis_undead']}, {$r['qglobal']}, {$r['AC']}, {$r['npc_aggro']}, {$r['spawn_limit']}, '{$r['attack_speed']}', {$r['attack_delay']}, {$r['findable']}, 
					{$r['STR']}, {$r['STA']}, {$r['DEX']}, {$r['AGI']}, {$r['_INT']}, {$r['WIS']}, {$r['CHA']}, {$r['see_hide']}, {$r['see_improved_hide']}, {$r['trackable']}, {$r['isbot']}, {$r['exclude']}, {$r['ATK']}, {$r['Accuracy']}, {$r['Avoidance']}, {$r['slow_mitigation']}, 
					{$r['version']}, {$r['maxlevel']}, {$r['scalerate']}, {$r['private_corpse']}, {$r['unique_spawn_by_name']}, {$r['underwater']}, {$r['isquest']}, {$r['emoteid']}, '{$r['spellscale']}', '{$r['healscale']}', {$r['no_target_hotkey']}, {$r['raid_target']}, 
					{$r['armtexture']}, {$r['bracertexture']}, {$r['handtexture']}, {$r['legtexture']}, {$r['feettexture']}, {$r['light']}, {$r['walkspeed']}, {$r['peqid']}, {$r['unique_']}, {$r['fixed']}, 
					{$r['ignore_despawn']}, {$r['show_name']}, {$r['untargetable']}, 0, 0, 0, 0, 0, 0, 0, 
					0, 0, 0, 0, {$r['combat_hp_regen']}, {$r['hp_regen_rate']}, {$r['combat_mana_regen']}, {$r['mana_regen_rate']}, {$r['aggro_pc']}, '-1')";
				
		$result_insert = $eqdb->query($query);
		if (!$result_insert)
			RowText("NPC of ID {$r['id']} NOT inserted properly.");
		else
			$insert_count++;
	}
	
	RowText("{$insert_count} NPCs were inserted for zone {$zone_id}");
}

function delete_existing_npcs($eqdb, $zone_id)
{
	$npc_id_min = $zone_id * 1000;
	$npc_id_max = ($zone_id + 1) * 1000;
	$query = "DELETE FROM npc_types WHERE id >= {$npc_id_min} AND id < {$npc_id_max}";
	$result = $eqdb->query($query);
	$affected_rows = $eqdb->affected_rows;
	RowText("{$affected_rows} NPCs were deleted from Zone {$zone_id}");
}

function delete_existing_spawn_data($eqdb, $zone_id)
{
	$query = "SELECT short_name FROM zone WHERE zoneidnumber = {$zone_id}";
	$result = $eqdb->query($query);
	if ($result->num_rows != 1)
		data_error();
	$row = $result->fetch_assoc();
	$zone_name = $row['short_name'];
	
	// Delete spawn_events
	$query = "DELETE FROM spawn_events WHERE zone = '{$zone_name}'";
	$result = $eqdb->query($query);
	if ($result)
	{
		$affected_rows = $eqdb->affected_rows;
		RowText("{$affected_rows} spawn_events were deleted.");
	}
	else
		RowText("Delete from spawn_events unsuccessful.");
	
	// Delete spawn_condition_values
	$query = "DELETE FROM spawn_condition_values WHERE zone = '{$zone_name}'";
	$result = $eqdb->query($query);
	if ($result)
	{
		$affected_rows = $eqdb->affected_rows;
		RowText("{$affected_rows} spawn_condition_values were deleted.");
	}
	else
		RowText("Delete from spawn_condition_values unsuccessful.");
	
	// Delete spawn_conditions
	$query = "DELETE FROM spawn_conditions WHERE zone = '{$zone_name}'";
	$result = $eqdb->query($query);
	if ($result)
	{
		$affected_rows = $eqdb->affected_rows;
		RowText("{$affected_rows} spawn_conditions were deleted.");
	}
	else
		RowText("Delete from spawn_conditions unsuccessful.");
	
	// Delete spawn2, spawngroup, and spawnentry
	$query = "DELETE spawn2, spawngroup, spawnentry FROM spawn2 INNER JOIN spawngroup ON spawn2.spawngroupID = spawngroup.id INNER JOIN spawnentry ON spawnentry.spawngroupID = spawngroup.id WHERE zone = '{$zone_name}'";
	$result = $eqdb->query($query);
	if ($result)
	{
		$affected_rows = $eqdb->affected_rows;
		RowText("{$affected_rows} bispawndelete rows were deleted.");
	}
	else
		RowText("Delete from bispawndelete unsuccessful.");
}

function copy_spawn_data($eqdb, $p2002db, $zone_id)
{
	$query = "SELECT short_name FROM zone WHERE zoneidnumber = {$zone_id}";
	$result = $eqdb->query($query);
	if ($result->num_rows != 1)
		data_error();
	$row = $result->fetch_assoc();
	$zone_name = $row['short_name'];
	
	// spawn_events
	$query = "SELECT id, zone, cond_id, name, period, next_minute, next_hour, next_day, next_month, next_year, enabled, action, argument, strict FROM spawn_events WHERE zone = '{$zone_name}'";
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
		RowText("No spawn_events data for zone {$zone_name} ({$zone_id}).");
	
	$spawn_event_count = 0;
	while ($r = $result->fetch_assoc())
	{
		$query = "INSERT INTO spawn_events (zone, cond_id, name, period, next_minute, next_hour, next_day, next_month, next_year, enabled, action, argument, strict VALUES 
			('{$r['zone']}', {$r['cond_id']}, '{$r['name']}', {$r['period']}, {$r['next_minute']}, {$r['next_hour']}, {$r['next_day']}, {$r['next_month']}, {$r['next_year']}, {$r['enabled']}, {$r['action']}, {$r['argument']}, {$r['strict']})";
		$result_insert = $eqdb->query($query);
		if ($result_insert)
			$spawn_event_count++;
		else
			RowText("spawn_events insert failed.");
	}
	RowText("{$spawn_event_count} spawn_events copied over.");
	
	// spawn_conditions
	$query = "SELECT zone, id, value, onchange, name FROM spawn_conditions WHERE zone = '{$zone_name}'";
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
		RowText("No spawn_conditions data for zone {$zone_name} ({$zone_id}).");
	
	$spawn_condition_count = 0;
	while ($r = $result->fetch_assoc())
	{
		$query = "INSERT INTO spawn_conditions (zone, id, value, onchange, name) VALUES 
			('{$r['zone']}', {$r['id']}, {$r['value']}, {$r['onchange']}, '{$r['name']}')";
		RowText($query);
		$result_insert = $eqdb->query($query);
		if ($result_insert)
			$spawn_condition_count++;
		else
			RowText("spawn_conditions insert failed.");
	}
	RowText("{$spawn_condition_count} spawn_conditions copied over.");
	
	// spawn_condition_values
	$query = "SELECT id, value, zone, instance_id FROM spawn_condition_values WHERE zone = '{$zone_name}'";
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
		RowText("No spawn_condition_values data for zone {$zone_name} ({$zone_id}).");

	$spawn_condition_value_count = 0;
	while ($r = $result->fetch_assoc())
	{
		$query = "INSERT INTO spawn_condition_values (id, value, zone, instance_id) VALUES 
			({$r['id']}, {$r['value']}, '{$r['zone']}', {$r['instance_id']})";
		RowText($query);
		$result_insert = $eqdb->query($query);
		if ($result_insert)
			$spawn_condition_value_count++;
		else
			RowText("spawn_condition_values insert failed.");
	}
	RowText("{$spawn_condition_value_count} spawn_condition_values copied over.");
	
	// spawn2 links to spawngroup, to which spawnentry links
	$query = "SELECT id, spawngroupID, zone, version, x, y, z, heading, respawntime, variance, pathgrid, _condition, cond_value, enabled, animation, boot_respawntime, clear_timer_onboot FROM spawn2 WHERE zone = '{$zone_name}'";
	RowText($query);
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
	{
		RowText("No spawn2 data for zone {$zone_name} ({$zone_id}).");
		return;
	}
	
	$spawn2_count = 0;
	$spawngroup_count = 0;
	$spawnentry_count = 0;
	$sgid = array();
	$s2id = array();
	
	while ($r = $result->fetch_assoc())
	{
		/* FIX THIS - spawn2s referencing repeat spawngroupIDs result in the spawn2 and spawnentry not being copied
		if (isset($sgid[$r['spawngroupID']]))
			continue;
		*/
		
		// spawngroup
		$query = "SELECT id, name, spawn_limit, dist, max_x, min_x, max_y, min_y, delay, mindelay, despawn, despawn_timer FROM spawngroup WHERE id = {$r['spawngroupID']}";
		RowText($query);
		$result_spawngroup = $p2002db->query($query);
		if ($result_spawngroup->num_rows != 1)
			RowText("Hanging spawn2->spawngroup reference of ID {$r['spawngroupID']}");
		else
		{
			$rsg = $result_spawngroup->fetch_assoc();
			$do_dist = true;
			if ($rsg['max_x'] == $rsg['min_x'] || $rsg['min_y'] == $rsg['max_y'])
				$do_dist = false;
			$query = "INSERT INTO spawngroup (name, spawn_limit, dist, max_x, min_x, max_y, min_y, delay, mindelay, despawn, despawn_timer) VALUES 
				('{$rsg['name']}', {$rsg['spawn_limit']}, " . ($do_dist ? $rsg['dist'] : 0) . ", {$rsg['max_x']}, {$rsg['min_x']}, {$rsg['max_y']}, {$rsg['min_y']}, {$rsg['delay']}, {$rsg['mindelay']}, {$rsg['despawn']}, {$rsg['despawn_timer']})";
			RowText($query);
			$result_insert = $eqdb->query($query);
			if ($result_insert)
			{
				$spawngroup_count++;
				$sgid[$rsg['id']] = $eqdb->insert_id;
			}
			else
				RowText("spawngroup insert failed for spawngroup of ID {$rsg['id']}");
		}
		
		// spawnentry - multiple
		$query = "SELECT spawngroupID, npcID, chance, mintime, maxtime FROM spawnentry WHERE spawngroupID = {$r['spawngroupID']}";
		RowText($query);
		$result_spawnentry = $p2002db->query($query);
		if ($result_spawnentry->num_rows < 1)
			RowText("No spawnentry data for spawngroup ID {$r['spawngroupID']} and spawn2 ID {$r['id']}");
		while ($rse = $result_spawnentry->fetch_assoc())
		{
			$query = "INSERT INTO spawnentry (spawngroupID, npcID, chance) VALUES 
				({$sgid[$r['spawngroupID']]}, {$rse['npcID']}, {$rse['chance']})";
			RowText($query);
			$result_insert = $eqdb->query($query);
			if ($result_insert)
				$spawnentry_count++;
			else
				RowText("spawnentry insert failed of spawngroupID {{$sgid[$r['spawngroupID']]} and npcID {$rse['npcID']}");
		}
		
		// copy spawn2
		$query = "INSERT INTO spawn2 (spawngroupID, zone, version, x, y, z, heading, respawntime, variance, pathgrid, _condition, cond_value, enabled, animation) VALUES 
			({$sgid[$r['spawngroupID']]}, '{$r['zone']}', {$r['version']}, '{$r['x']}', '{$r['y']}', '{$r['z']}', '{$r['heading']}', {$r['respawntime']}, {$r['variance']}, {$r['pathgrid']}, {$r['_condition']}, {$r['cond_value']}, {$r['enabled']}, {$r['animation']})";
		RowText($query);
		$result_insert = $eqdb->query($query);
		if ($result_insert)
		{
			$spawn2_count++;
			$s2id[$r['id']] = $eqdb->insert_id;
		}
		else
			RowText("spawn2 insert failed for spawn2 ID {$r['id']}");
	}
	RowText("{$spawn2_count} spawn2 rows inserted");
	RowText("{$spawngroup_count} spawngroup rows inserted");
	RowText("{$spawnentry_count} spawnentry rows inserted<br />");
	var_dump($sgid);
	print "<br />";
	var_dump($s2id);
}

function copy_grid_data($eqdb, $p2002db, $zone_id)
{
	// Delete from grid
	$query = "DELETE FROM grid WHERE zoneid = {$zone_id}";
	$result_delete = $eqdb->query($query);
	if (!$result_delete)
		RowText("DELETE query for grid failed.");
	else
		RowText("{$eqdb->affected_rows} grids deleted");

	// Delete from grid_entries
	$query = "DELETE FROM grid_entries WHERE zoneid = {$zone_id}";
	$result_delete = $eqdb->query($query);
	if (!$result_delete)
		RowText("DELETE query for grid_entries failed.");
	else
		RowText("{$eqdb->affected_rows} grid_entries deleted");
	
	// copy grids over
	$query = "SELECT id, type, type2 FROM grid WHERE zoneid = {$zone_id}";
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
		RowText("No grids for zone {$zone_id}");
	$grid_count = 0;
	while ($r = $result->fetch_assoc())
	{
		$query = "INSERT INTO grid (id, zoneid, type, type2) VALUES 
			({$r['id']}, {$zone_id}, {$r['type']}, {$r['type2']})";
		$result_insert = $eqdb->query($query);
		if (!$result_insert)
			RowText("INSERT for grid failed of id {$r['id']}");
		else
			$grid_count++;
	}
	RowText("{$grid_count} grids were inserted for zone {$zone_id}");
	
	// copy grid_entries over
	$query = "SELECT gridid, number, x, y, z, heading, pause FROM grid_entries WHERE zoneid = {$zone_id}";
	$result = $p2002db->query($query);
	if ($result->num_rows < 1)
		RowText("No grid_entries for zone {$zone_id}");
	$grid_entry_count = 0;
	while ($r = $result->fetch_assoc())
	{
		$query = "INSERT INTO grid_entries (gridid, zoneid, number, x, y, z, heading, pause) VALUES 
			({$r['gridid']}, {$zone_id}, {$r['number']}, '{$r['x']}', '{$r['y']}', '{$r['z']}', '{$r['heading']}', {$r['pause']})";
		$result_insert = $eqdb->query($query);
		if (!$result_insert)
			RowText("INSERT for grid_entries failed of id {$r['gridid']} and zone {$zone_id}");
		else
			$grid_entry_count++;
	}
	RowText("{$grid_entry_count} grid_entries were inserted for zone {$zone_id}");

}

function copy_loot_data($eqdb, $p2002db, $zone_id)
{
	$npc_id_min = $zone_id * 1000;
	$npc_id_max = ($zone_id + 1) * 1000;
	$query = "SELECT id, loottable_id FROM npc_types WHERE id >= {$npc_id_min} AND id < {$npc_id_max}";
	$result = $p2002db->query($query);
	if (!$result)
	{
		RowText("SELECT FROM npc_types query failed.");
		return;
	}
	
	// for tracking ID changes - loottable and lootdrop
	$ltid = array();
	$ldid = array();
	
	$hanging_loottables = array();
	$hanging_lootdrops = array();
	
	// item transfer tracker
	$itt = array();
	
	// total numbers copied
	$loottables = 0;
	$loottable_entries = 0;
	$lootdrops = 0;
	$lootdrop_entries = 0;
	
	while ($r = $result->fetch_assoc())
	{
		if ($r['loottable_id'] == 0)
			continue;
		
		// Copy loottable rows
		$query = "SELECT id, name, mincash, maxcash, avgcoin, done FROM loottable WHERE id = {$r['loottable_id']}";
		$result_loottable = $p2002db->query($query);
		if (!$result_loottable)
		{
			RowText("SELECT FROM loottable query failed.");
			return;
		}
		if ($result_loottable->num_rows != 1)
		{
			RowText("Referenced loottable does not exist - skipping and setting to 0 - NPC ID {$r['id']}");
			array_push($hanging_loottables, $r['id']);
			$query = "UPDATE npc_types SET loottable_id = 0 WHERE id = {$r['id']}";
			$result_update = $eqdb->query($query);
			if (!$result_update)
				RowText("UPDATE npc_types for loottable_id = 0 query FAILED");
			continue;
		}
		
		$rlt = $result_loottable->fetch_assoc();
		$query = "INSERT INTO loottable (name, mincash, maxcash, avgcoin, done) VALUES 
			('{$rlt['name']}', {$rlt['mincash']}, {$rlt['maxcash']}, {$rlt['avgcoin']}, {$rlt['done']})";
		$result_insert = $eqdb->query($query);
		if (!$result_insert)
			RowText("INSERT loottable query failed - ID {$rlt['id']}");
		else
			$loottables++;
		$lt_insert_id = $eqdb->insert_id;
		$ltid[$rlt['id']] = $lt_insert_id;
		
		// Parse loottable_entries rows
		$query = "SELECT loottable_id, lootdrop_id, multiplier, probability, droplimit, mindrop, multiplier_min FROM loottable_entries WHERE loottable_id = {$r['loottable_id']}";
		$result_loottable_entries = $p2002db->query($query);
		if (!$result_loottable_entries)
		{
			RowText("SELECT FROM loottable_entries query failed.");
			return;
		}
		
		while ($rlte = $result_loottable_entries->fetch_assoc())
		{
			// copy the lootdrops - new IDs
			$query = "SELECT id, name FROM lootdrop WHERE id = {$rlte['lootdrop_id']}";
			$result_lootdrop = $p2002db->query($query);
			if (!$result_lootdrop)
				RowText("SELECT FROM lootdrop query failed");
			if ($result_lootdrop->num_rows != 1)
			{
				array_push($hanging_lootdrops, $r['loottable_id']);
				RowText("Referenced lootdrop does not exist - skipping - lootdrop_id of {$rlte['lootdrop_id']}");
				continue;
			}
					
			$rld = $result_lootdrop->fetch_assoc();
			$query = "INSERT INTO lootdrop (name) VALUES ('{$rld['name']}')";
			$result_insert = $eqdb->query($query);
			if (!$result_insert)
				RowText("INSERT INTO lootdrop query failed");
			else
				$lootdrops++;
			$ld_insert_id = $eqdb->insert_id;
			$ldid[$rlte['lootdrop_id']] = $ld_insert_id;
			
			// Copy loottable_entries over after new IDs for lootdrops established
			$query = "INSERT INTO loottable_entries (loottable_id, lootdrop_id, multiplier, droplimit, mindrop, probability, multiplier_min) VALUES 
				({$lt_insert_id}, {$ld_insert_id}, {$rlte['multiplier']}, {$rlte['droplimit']}, {$rlte['mindrop']}, {$rlte['probability']}, {$rlte['multiplier_min']})";
			$result_insert = $eqdb->query($query);
			if (!$result_insert)
				RowText("INSERT INTO loottable_entries query failed");
			else
				$loottable_entries++;
			
			// copy lootdrop_entries
			$query = "SELECT lootdrop_id, item_id, item_charges, equip_item, chance, disabled_chance, minlevel, maxlevel, multiplier FROM lootdrop_entries WHERE lootdrop_id = {$rlte['lootdrop_id']}";
			$result_lootdrop_entries = $p2002db->query($query);
			if (!$result_lootdrop_entries)
				RowText("SELECT FROM lootdrop_entries query failed");
			while ($rlde = $result_lootdrop_entries->fetch_assoc())
			{
				$query = "INSERT INTO lootdrop_entries (lootdrop_id, item_id, item_charges, equip_item, chance, disabled_chance, minlevel, maxlevel, multiplier) VALUES 
					({$ld_insert_id}, {$rlde['item_id']}, {$rlde['item_charges']}, {$rlde['equip_item']}, {$rlde['chance']}, {$rlde['disabled_chance']}, {$rlde['minlevel']}, {$rlde['maxlevel']}, {$rlde['multiplier']})";
				$result_insert = $eqdb->query($query);
				if (!$result_insert)
					RowText("INSERT INTO lootdrop_entries query failed");
				else
					$lootdrop_entries++;
				
				// item not transferred - transfer it
				if (!isset($itt[$rlde['item_id']]))
				{
					$query = "SELECT id_peq, id_wfh FROM items_map WHERE id_peq = {$rlde['item_id']}";
					$result_item_map = $p2002db->query($query);
					if ($result_item_map->num_rows != 1)
						RowText("Item map results != 1 for item {$rlde['item_id']}");
					else
					{
						$rim = $result_item_map->fetch_assoc();
						$item_id_swap = false;
						if ($rim['id_peq'] != $rim['id_wfh'])
						{
							RowText("Item map for item {$rlde['item_id']} applicable");
							$item_id_swap = true;
						}
						
						// delete old item
						$query = "DELETE FROM items WHERE id = {$rlde['item_id']}";
						$result_delete = $eqdb->query($query);
						if (!$result_delete)
							RowText("DELETE FROM items query failed");
						else
							RowText("Item {$rlde['item_id']} deleted");
						
						// copy the item
						$query = "SELECT * FROM items WHERE id = {$rlde['item_id']}";
						$result_items = $p2002db->query($query);
						if (!$result_items)
							RowText("SELECT FROM items query failed");
						if ($result_items->num_rows != 1)
							RowText("SELECT FROM items query returned {$result_items->num_rows} rows");
						$ri = $result_items->fetch_assoc();
						$query = "INSERT INTO items VALUES (";
						foreach ($ri as $key => $value)
						{
							if ($key == "gmflag" || $key == "soulbound")
								continue;
							elseif ($value === NULL)
								$query = $query . "NULL, ";
							elseif ($key == "Name" || $key == "charmfile" || $key == "charmfileid" || $key == "combateffects" || $key == "filename" || $key == "idfile" || $key == "lore" || $key == "sellrate" ||
								$key == "updated" || $key == "comment" || $key == "UNK134" || $key == "serialized" || $key == "verified" || $key == "serialization" || $key == "source" || $key == "lorefile" || 
								$key == "UNK132" || $key == "clickunk6" || $key == "procunk6" || $key == "wornunk6" || $key == "focusunk6" || $key == "scrollunk6" || $key == "clickname" || $key == "procname" || 
								$key == "wornname" || $key == "focusname" || $key == "scrollname" || $key == "created" || $key == "bardname")
								$query = $query . "'" . $eqdb->real_escape_string($value) . "', ";
							else
								$query = $query . $value . ", ";
						}
						$query = rtrim($query, ", ");
						$query = $query . ")";
						RowText($query);
						$result_insert = $eqdb->query($query);
						if (!$result_insert)
							RowText("INSERT INTO items query failed");
						else
							$itt[$rlde['item_id']] = 1;
					}
				}
			}
			
		}
		
		$query = "UPDATE npc_types SET loottable_id = {$ltid[$r['loottable_id']]} WHERE id = {$r['id']}";
		$result_update = $eqdb->query($query);
		if (!$result_update)
			RowText("UPDATE npc_types query failed");
	}
	
	RowText("{$loottables} loottables  inserted");
	RowText("{$loottable_entries} loottable_entries inserted");
	RowText("{$lootdrops} lootdrops inserted");
	RowText("{$lootdrop_entries} lootdrop_entries inserted<br />");
	
	var_dump($hanging_loottables);
	
	print "<br /><br />";
	
	var_dump($hanging_lootdrops);
	
	print "<br /><br />";
	
	var_dump($ltid);
	
	print "<br /><br />";
	
	var_dump($ldid);
}

function copy_npc_spell_data($eqdb, $wfhdb, $zone_id)
{
	// look through npc_types for the zone
	$npc_id_min = $zone_id * 1000;
	$npc_id_max = ($zone_id + 1) * 1000;
	$query = "SELECT id, npc_spells_id FROM npc_types WHERE id >= {$npc_id_min} AND id < {$npc_id_max}";
	$result = $wfhdb->query($query);
	if (!$result)
		RowText("SELECT FROM npc_types query failed");
	if ($result->num_rows < 1)
		RowText("SELECT FROM npc_types query for npc spell data had 0 rows");
	$nsi = array();
	$ns_count = 0;
	$nse_count = 0;
	while ($r = $result->fetch_assoc())
	{
		// skip if already copied or set to 0
		if (isset($nsi[$r['npc_spells_id']]) || $r['npc_spells_id'] == 0 )
			continue;
		
		// get the npc_spells base info
		$query = "SELECT name, parent_list, attack_proc, proc_chance, range_proc, rproc_chance, defensive_proc, dproc_chance, fail_recast, engaged_no_sp_recast_min, engaged_no_sp_recast_max,
			engaged_b_self_chance, engaged_b_other_chance, engaged_d_chance, pursue_no_sp_recast_min, pursue_no_sp_recast_max, pursue_d_chance, idle_no_sp_recast_min, idle_no_sp_recast_max, idle_b_chance 
			FROM npc_spells WHERE id = {$r['npc_spells_id']}";
		RowText($query);
		$result_spells = $wfhdb->query($query);
		if (!$result_spells)
		{
			RowText("SELECT FROM npc_spells query failed");
			$nsi[$r['npc_spells_id']] = 0;
			continue;
		}
		if ($result_spells->num_rows < 1)
		{
			RowText("npc_spells row {$r['npc_spells_id']} not found - setting to 0 etc");
			$nsi[$r['npc_spells_id']] = 0;
			continue;
		}
		$rns = $result_spells->fetch_assoc();
		// copy the npc_spells info to target db
		$query = "INSERT INTO npc_spells (name, parent_list, attack_proc, proc_chance, range_proc, rproc_chance, defensive_proc, dproc_chance, fail_recast, engaged_no_sp_recast_min, engaged_no_sp_recast_max,
			engaged_b_self_chance, engaged_b_other_chance, engaged_d_chance, pursue_no_sp_recast_min, pursue_no_sp_recast_max, pursue_d_chance, idle_no_sp_recast_min, idle_no_sp_recast_max, idle_b_chance) 
			VALUES ('{$rns['name']}', {$rns['parent_list']}, {$rns['attack_proc']}, {$rns['proc_chance']}, {$rns['range_proc']}, {$rns['rproc_chance']}, {$rns['defensive_proc']}, {$rns['dproc_chance']}, 
			{$rns['fail_recast']}, {$rns['engaged_no_sp_recast_min']}, {$rns['engaged_no_sp_recast_max']}, {$rns['engaged_b_self_chance']}, {$rns['engaged_b_other_chance']}, {$rns['engaged_d_chance']}, 
			{$rns['pursue_no_sp_recast_min']}, {$rns['pursue_no_sp_recast_max']}, {$rns['pursue_d_chance']}, {$rns['idle_no_sp_recast_min']}, {$rns['idle_no_sp_recast_max']}, {$rns['idle_b_chance']})";
		RowText($query);
		$result_insert = $eqdb->query($query);
		if (!$result_insert)
		{
			RowText("INSERT INTO npc_spells query failed");
			continue;
		}
		else
			$ns_count++;
		$insert_id = $eqdb->insert_id;
		// keep track of which npc_spells have already been copied
		$nsi[$r['npc_spells_id']] = $insert_id;
		
		// copy the npc_spells_entries
		$query = "SELECT npc_spells_id, spellid, type, minlevel, maxlevel, manacost, recast_delay, priority, resist_adjust, min_hp, max_hp FROM npc_spells_entries WHERE npc_spells_id = {$r['npc_spells_id']}";
		$result_npc_spells_entries = $wfhdb->query($query);
		if (!$result_npc_spells_entries)
			RowText("SELECT FROM npc_spells_entries query failed");
		if ($result_npc_spells_entries->num_rows < 1)
			RowText("No entries for npc spells id  {$r['npc_spells_id']}");
		while ($rnse = $result_npc_spells_entries->fetch_assoc())
		{
			$query = "INSERT INTO npc_spells_entries (npc_spells_id, spellid, type, minlevel, maxlevel, manacost, recast_delay, priority, resist_adjust, min_hp, max_hp) VALUES 
				({$rnse['npc_spells_id']}, {$rnse['spellid']}, {$rnse['type']}, {$rnse['minlevel']}, {$rnse['maxlevel']}, {$rnse['manacost']}, {$rnse['recast_delay']}, {$rnse['priority']}, " .
				($rnse['resist_adjust'] === NULL ? "NULL, " : "{$rnse['resist_adjust']}, ") . "{$rnse['min_hp']}, {$rnse['max_hp']})";
				
			RowText($query);
			$result_insert = $eqdb->query($query);
			if (!$result_insert)
				RowText("INSERT INTO npc_spells_entries failed");
			else
				$nse_count++;
		}
		
		// change the npc_types data to reflect new ID for npc_spells
		$query = "UPDATE npc_types SET npc_spells_id = {$insert_id} WHERE id = {$r['id']}";
		$result_update = $eqdb->query($query);
		if (!$result_update)
			RowText("UPDATE npc_types query failed");
	}
	RowText("{$ns_count} npc_spells were copied over.<br />");
	RowText("{$nse_count} npc_spells_entries were copied over.<br />");
	var_dump($nsi);
}

function display_zoneselect_form($eqdb = NULL)
{
	RowText("");
	
	if ($eqdb == NULL)
	{
		RowText("No Database Connection");
		include_once("footer.php");
		die;
	}
	
	Row();
		Col();
		DivC();
		Col(true, '', 4);
?>
			<form action="zonemerger.php?a=sz" method="post">
				<div class="form-group">
					<label for="destination"><h6>Select POP Zone</h6></label>
					<select class="form-control" id="inputZone" name="inputZone">
<?php
						$query = "SELECT short_name, zoneidnumber FROM zone WHERE zoneidnumber >= 200 AND zoneidnumber <= 223";
						$result = $eqdb->query($query);
				
						while ($row = $result->fetch_assoc())
						{
							print "<option value='{$row['zoneidnumber']}'>{$row['zoneidnumber']} - {$row['short_name']}</option>";
						}
?>
					</select>
				</div>
				<!--<input type="hidden" name="origin" value="<?php print $origin; ?>">!-->
				<button type="submit" class="btn btn-primary">Next</button>
			</form>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}