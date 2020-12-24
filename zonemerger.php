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
	display_zonemerger_form($eqdb);
else
	display_zonemerger_form($eqdb);

include_once("footer.php");

function display_zonemerger_form($eqdb = NULL)
{
	RowText("");
	
	if ($mysqli == NULL)
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
						$query = "SELECT short_name, zoneidnumber FROM zones WHERE zoneidnumber >= 200 AND zoneidnumber <= 223";
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