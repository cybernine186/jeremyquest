<?php
/***************************************************************************************************
File:			index.php
Description:	Default page - prompt to login if necessary
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

Row();
	Col(true, 'pt-4 pb-2');
		print "<h4>Welcome to JeremyQuest</h4>";
	DivC();
DivC();


Row();
	Col(true, 'pt-2 pb-2');
	if ($uid < 0)
		print "Please <a href='login.php'>login</a> to use the system.";
	else
		print "Good day, {$username}.";
	DivC();
DivC();

include_once("footer.php")

?>