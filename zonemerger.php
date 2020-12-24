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
	display_zonemerger_form();
else
	display_zonemerger_form();

include_once("footer.php");

function display_zone_merger_form($mysqli = NULL)
{
	return;
}