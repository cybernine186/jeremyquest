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

if (!isset($_GET['a']))
{
	display_char_search();
}
else
{
	display_char_search();
}

include_once("footer.php");

function display_char_search()
{
	
}

?>