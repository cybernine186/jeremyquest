<?php

include_once("dbcredential.php");

$admindb = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

if ($admindb->connect_errno)
{
	print "Failed to connect to database.";
	include_once("footer.php");
	die;
}
	
?>