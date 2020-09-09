<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_inventory)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Inventory</h4>");

include_once("footer.php");

?>