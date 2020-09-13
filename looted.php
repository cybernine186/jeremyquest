<?php

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission_trades)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Looted Items</h4>");
RowText("<h5>Under Construction</h5>");

include_once("footer.php");

?>