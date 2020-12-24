<?php

include_once("functions.php");
include_once("header.php");

$p2002db = new mysqli($dbhost, "neq", "mariaslargesekrets", "p2002");

if ($p2002db->connect_errno)
{
	print "Failed to connect to p2002 database.";
	include_once("footer.php");
	die;
}

// Check for permissions
if (!$permission['zonemerger'])
{
	RowText("<h5>You are not authorized!</h5>");
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

// Swap Zone Data
elseif ($_GET['a'] == "szd")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	swap_zone_data($eqdb, $p2002db, $zone_id);
	show_zone_tasks($eqdb, $zone_id);
}

// Copy Over NPCs
elseif ($_GET['a'] == "con")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	copy_wfh_npcs($eqdb, $p2002db, $zone_id);
	show_zone_tasks($eqdb, $zone_id);
}

// Delete Existing NPCs
elseif ($_GET['a'] == "den")
{
	if (!IsNumber($_GET['zid']))
		data_error();
	$zone_id = $_GET['zid'];
	
	delete_existing_npcs($eqdb, $zone_id);
	show_zone_tasks($eqdb, $zone_id);
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
				('{$r['short_name']}', {$r['id']}, '{$r['file_name']}', '{$r['long_name']}', '{$r['map_file_name']}', '{$r['safe_x']}', '{$r['safe_y']}', '{$r['safe_z']}', 
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
				0, 0, 0, 0, 0, 0, NULL, NULL)";
	
	$result = $eqdb->query($query);
	if ($result)
		RowText("Zone Header Data for Zone {$zone_id} successfully inserted!");
	else
		RowText("Zone Header Data for Zone {$zone_id} NOT successfully inserted!");
	
	
}

function copy_wfh_npcs($eqdb, $p2002db, $zone_id)
{
	
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