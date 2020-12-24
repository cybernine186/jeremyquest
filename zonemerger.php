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

RowText("<h4>Zone Merger</h4>");

if (!isset($_GET['a']))
	display_zoneselect_form($eqdb);
elseif ($_GET['a'] == "sz")
{
	if (!IsNumber($_POST['inputZone']))
		data_error();
	$zone_id = $_POST['inputZone'];
	
	show_zone_tasks($eqdb, $zone_id);
}
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
						<td>Delete Existing NPCs</td>
						<td><a class="btn btn-primary" href="zonemerger.php?a=den&zid=<?php print $zone_id; ?>" role="button">Go</a></td>
					</tr>
				</tbody>
			</table>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

function delete_existing_npcs($eqdb, $zone_id)
{
	$npc_id_min = $zone_id * 1000;
	$npc_id_max = ($zone_id + 1) * 1000;
	$query = "DELETE FROM npc_types WHERE id >= {$npc_id_min} AND id < {$npc_id_max}";
	$result = $eqbd->query($query);
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