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
	RowText($_POST['charID']);
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