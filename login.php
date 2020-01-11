<?php
include_once("functions.php");
include_once("header.php");

if ($_GET['a'] == "login")
{
	Row();
		Col(true, 'pt-4 pb-2');
		if ($uid >= 0)
		{
			print "Thanks for logging in, {$first_name}.";
			header("refresh:3;url=index.php");
		}
		else
			print "You failed to login. Please try again.";
		DivC();
	DivC();
	
	if ($uid < 0)
		display_login_page();
}
elseif ($_GET['a'] == "logout")
{
	Row();
		Col(true, 'pt-4 pb-2');
			print "You have logged out.";
		DivC();
	DivC();
	Row();
		Col(true, 'pt-4 pb-2');
			print "You will be redirected to the main page shortly.";
		DivC();
	DivC();
	
	header("refresh:3;url=index.php");
}
else
	display_login_page();

include_once("footer.php");

?>