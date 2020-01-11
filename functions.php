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

function IsNumber($id)
{
	if (!preg_match("/^\d{1,7}$/", $id, $matches))
		return false;
	return true;
}

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

?>