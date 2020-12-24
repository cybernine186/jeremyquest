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
	show_zone_tasks($eqdb);
elseif ($_GET['a'] == "den")
{
	delete_existing_npcs($eqdb);
	show_zone_tasks($eqdb);
}
else
	display_zoneselect_form($eqdb);

include_once("footer.php");

function show_zone_tasks($eqdb)
{
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
						<td><a class="btn btn-primary" href="zonemerger.php?a=den" role="button">Go</a></td>
					</tr>
				</tbody>
			</table>
<?php
		DivC();
		Col();
		DivC();
	DivC();
}

function delete_existing_npcs($eqdb)
{
	RowText("NPCs deleted...<br />");
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