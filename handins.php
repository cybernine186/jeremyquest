<?php


include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_handins)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Handins</h4>");

if ($_GET['a'] == "huh")
{
	
}
else
{
	display_handin_search($mysqli);
}

include_once("footer.php");

function display_handin_search($mysqli)
{
	Row();
		Col();
		DivC();
		Col(false, '', '6');
			print "jerk";
		DivC();
		Col();
		DivC();
	DivC();
		
}
?>