<?php
include_once("functions.php");
include_once("header.php");

if ($_GET['a'] == "login")
{
	Row();
		Col(true, 'pt-4 pb-2');
		if ($uid >= 0)
		{
			print "Thanks for logging in, {$username}.";
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

/***************************************************************************************************
DISPLAY FUNCTIONS
***************************************************************************************************/

function display_login_page()
{
Row();
	Col(true, 'pt-4 pb-2');
		print "<h3>Please Log In</h3>";
	DivC();
DivC();
Row();
	Col();
?>
		<form action="login.php?a=login" method="post">
			<div class="form-group">
				<label for="uname">User Name</label>
				<input type="text" class="form-control" id="uname" placeholder="Enter User Name" name="uname">
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" class="form-control" id="password" placeholder="Password" name="password">
			</div>
			<button type="submit" class="btn btn-primary">Submit</button>
		</form>
<?php
	DivC();
DivC();
}

?>