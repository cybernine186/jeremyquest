<?php

include_once("functions.php");
include_once("header.php");

include_once("footer.php");

if (!isset($_GET['a']))
{
	data_error();
}
elseif ($_GET['a'] == 'o')
{
	if (!IsNumber($_GET['id']))
		data_error();
	
	$charid = $_GET['id'];
	display_player_overview($eqdb, $charid);
}

function display_player_overview($eqdb, $charid)
{
	RowText("<h4>Player Overview - {$charid}</h4>");
}

?>