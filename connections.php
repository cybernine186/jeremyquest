<?php
/***************************************************************************************************
File:			logs.php
Description:	Logging of system usage
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
if (!$permission_connections)
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

RowText("<h4>Connections</h4>");

include_once("footer.php");

?>