<?php
/***************************************************************************************************
File:			logs.php
Description:	Logging of system usage - just shows all entries
***************************************************************************************************/

include_once("functions.php");

include_once("header.php");

// Check for permissions
if (!$permission['logging'])
{
	RowText("<h5>You are not authorized!</h5>");
	include_once("footer.php");
	die;
}

	RowText("<h4>Logs</h4>");
	
	$days = 1000;
	$query = "SELECT count(*) AS count FROM logs WHERE time > (NOW() - INTERVAL {$days} DAY)";
	$result = $admindb->query($query);
	$row = $result->fetch_assoc();
	
	$logcount = $row['count'];
	
	if($logcount < 1)
	{
		RowText("No logs found in last {$days} days.");
		include_once("footer.php");
		die;
	}
	
	// Pagination Data
	$start = 1;
	if(isset($_GET['s']))
		$start = $_GET['s'];
	
	$pagesize = 20;
	
	$pages = ceil($logcount / $pagesize);
	
	$begin = ($start - 1) * $pagesize;	
	
	display_pagination($start, $pages, "logs.php");

	$query = "SELECT time AS timenum, DATE_FORMAT(time, '%a %b %d, %Y %T') AS time, uid, type, message, users.username AS username FROM logs LEFT JOIN users ON users.id = logs.uid ORDER BY timenum DESC LIMIT {$begin}, {$pagesize}";
	$result = $admindb->query($query);

?>
	<table class="table">
		<thead>
			<tr>
				<th scope="col">Time</th>
				<th scope="col">User</th>
				<th scope="col">Type</th>
				<th scope="col">Message</th>
			</tr>
		</thead>
		<tbody>
<?php
			while ($row = $result->fetch_assoc())
			{
				print "<tr><td>{$row['time']}</td><td>{$row['username']}</td><td>";
				if ($row['type'] == Logs::Session)
					print "Session";
				elseif ($row['type'] == Logs::User)
					print "User";
				elseif ($row['type'] == Logs::Rollback)
					print "Rollback";
				elseif ($row['type'] == Logs::Transfer)
					print "Transfer";
				else
					print "Error";
				print "</td><td>{$row['message']}</td></tr>";
			}
		print "</tbody>";
	print "</table>";
	
	display_pagination($start, $pages, "logs.php");

include_once("footer.php");

?>