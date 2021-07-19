<?php
/***************************************************************************************************
File:			looted.php
Description:	Interface to show items looted by players with some details - currently incomplete
***************************************************************************************************/

include_once("functions.php");
include_once("header.php");

// Check for permissions
if (!$permission['looted'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Looted Items</h4>");
RowText("<h5>This tool is not currently available.</h5>");

include_once("footer.php");

?>