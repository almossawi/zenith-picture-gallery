<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
*******************************************************************/

require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

$pid = $_GET['pid'];

if(is_numeric($pid)) {
	//changed on january 12, 2006
   	//$filename_escape_chars = $config['escape_chars'];  $filename_escape_chars[] = '+';
	$statement = "SELECT file_name FROM {$config['table_prefix']}pictures WHERE pid=$pid";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	$file_name = $row['file_name'];
	
	$path = getPicturesPath($config, charsEscaper($file_name, $filename_escape_chars), 0);
	$path_server = getPicturesPath($config, charsEscaper($file_name, $filename_escape_chars), 1); //get server path

	$row = mysql_fetch_array(mysql_query("SELECT counter, title, pic_width, pic_height FROM {$config['table_prefix']}pictures WHERE pid='$pid'"));
	$counter = $row['counter']+1;
	$title = $row['title']; 
	$dimensions = "(" . $row['pic_width'] . "x" . $row['pic_height'] . ")";
	$result = mysql_query("UPDATE {$config['table_prefix']}pictures SET counter=$counter WHERE pid='$pid'");
	
	//new in ZVG
	if(isVideo($config, "",$file_name)) {
		forceDownloadFile($path_server); //if we have a video on our hands, force download it
		exit;
	}
	
	header("Content-type: text/html; charset=UTF-8");
	echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
	
	<html><head>
	<title>$title $dimensions</title></head>
	<body style='margin:0;padding:0'>
	<img src='$path' alt='$title $dimensions' title='$title $dimensions' style='margin:0;padding:0;border=0' />
	</body><html>";
}

?>