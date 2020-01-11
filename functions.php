<?php

include_once("displayfunctions.php");

/*******************************************************************************
Function:	Container
Purpose:	Create a container <div>
In:			None
*******************************************************************************/
function Container()
{
	print '<div class="container">';
}

function DivC()
{
	print '</div>';
}

/*******************************************************************************
Function:	RowText
Purpose:	Create a row of text
In:			$text - text to display
			$center - whether or not the row is horizontally centered
			$formatting - formatting class tags
*******************************************************************************/
function RowText($text, $center = true, $formatting = "pt-4 pb-2")
{
	Row();
		Col($center, $formatting);
			print $text;
		DivC();
	DivC();
}

/*******************************************************************************
Function:	Row
Purpose:	Create a row <div>
In:			$center - whether or not the row is vertically centered
*******************************************************************************/
function Row($center = false)
{
	print '<div class="row' . ($center ? ' align-items-center' : '') . '">';
}

/*******************************************************************************
Function:	Col
Purpose:	Create a col <div>
In:			$center - whether or not text in the column is center-aligned
*******************************************************************************/
function Col($center = false, $formatting = '')
{
	print '<div class="col' . ($center ? ' text-center' : '') . ($formatting != '' ? ' ' . $formatting : '') . '">';
}

/*******************************************************************************
Function:	IsNumber
Purpose:	Check if value is number
In:			$num - the number to checkdate
Out:		Whether or not $num is a number
*******************************************************************************/
function IsNumber($num)
{
	if (!preg_match("/^\d{1,7}$/", $num, $matches))
		return false;
	return true;
}

/*******************************************************************************
Function:	data_error
Purpose:	General error that results in page termination
*******************************************************************************/
function data_error()
{
	Row();
		Col(true, 'pt-4 pb-2');
			print "<h6>Bad Data - Script Terminated</h6>";
		DivC();
	DivC();
	
	include_once("footer.php");
	die;
}

/*******************************************************************************
Function:	get_client_ip
Purpose:	Get user's IP
Out:		User's IP as string
*******************************************************************************/
function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/*******************************************************************************
Function:	display_pagination
Purpose:	Show pagination links
In:			$start - the page we are currently on
			$pages - the number of total pages
			$link - the base link before pagination context in $_GET
*******************************************************************************/
function display_pagination($start, $pages, $link)
{
	$containsget = (strpos($link, '?') !== false ? true : false);
	
	print "<ul class='pagination'>";
	print "<li class='page-item" . ($pages == 1 || $start == 1 ? " disabled" : "") . "'>";
	print "<a class='page-link' href='{$link}" . ($containsget ? "&" : "?") . "s=" . ($start - 1) . "'>Previous</a></li>";
	
	for($i = 1; $i <= $pages; $i++)
	{
		if(($i - $start >= -4 && $i - $start <= 4) || ($i % 10 == 0) || ($i == 1) || ($i == $pages)) {
			print "<li class='page-item" . ($i == $start ? " active" : "") . "'>";
			print "<a class='page-link' href='{$link}" . ($containsget ? "&" : "?") . "s={$i}'>{$i}</a></li>";
		}
	}
	
	print "<li class='page-item" . ($pages == 1 || $start == $pages ? " disabled" : "") . "'>";
	print "<a class='page-link' href='{$link}" . ($containsget ? "&" : "?") . "s=" . ($start + 1) . "'>Next</a></li>";
	print "</ul>";
}

?>