<?php
include_once("dbcredential.php");
	$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

	if ($mysqli->connect_errno)
	{
		print "Failed to connect to database.";
		include_once("footer.php");
		die;
	}
?>