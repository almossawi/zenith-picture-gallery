<?php
/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com]http://www.cyberiapc.com

 This program is free software; you can redistribute it and/or modify
 it under the terms of the ZPG General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: zenith_includer.php (version 0.3, updated July 13, 2007)
 Description: Allows last added or random pictures to be shown on other 
 pages of a website.
 Random quote: "No problem is too small or too trivial if we can 
 really do something about it." -Richard P. Feynman
*******************************************************************/

//set to 0 to return last added pictures, 1 to return random pictures
$_GET['do_what'] = 0;

//the number of pictures to return
$_GET['n'] = 10;

//show each picture's titles?
$_GET['include_title'] = 0;

//show each picture's post date?
$_GET['include_date'] = 0;

//show each picture's views counter?
$_GET['include_views'] = 0;


function _display_images($result, $config) {
	while($row = mysql_fetch_array($result)) {
			$title=$row['title'];
			$date=$row['date'];
			$counter=$row['counter'];
			$category=$row['category'];
			$filename=$row['file_name'];
			$thmpath=getPicturesPath($config, $filename, 0);
			$at_full = $thmpath;
			$at_full_server = $config['upload_dir_server'].$row['file_name'];
			$at_thumb = getThumbPath($at_full, $at_full_server, $config['thumb_suffix']);
			
			echo "<a href='" . getCurrentInternetPath2($config,"") . "pic_details.php?pid={$row['pid']}'>
				<img src='{$at_thumb[0]}' alt='{$row['file_name']}' title='{$row['file_name']}' style='border:1px solid black' /></a>
				<br />
				";
				
				if($_GET['include_title'] == 1)   echo "$title<br />";
				if($_GET['include_date'] == 1)   echo "$date<br />";
				if($_GET['include_views'] == 1)   echo "$counter<br />";
	}
}

function getRandom($n) {
	$arr = _connect_db();
	
	$config = $arr[0];
	$connection = $arr[1];
	
	require_once('functions/f_global.php');
	$statement = "SELECT pid, 
		file_name, 
		category, 
		DATE_FORMAT(date, '{$config['short_date_format']}') AS d 
		FROM {$config['table_prefix']}pictures 
		WHERE approved=1 
		ORDER BY RAND() 
		DESC 
		LIMIT $n";
	$result = mysql_query($statement);
	
	_display_images($result, $config);
	_cleanup_db($connection);
}

function getLastAdded($n) {
	$arr = _connect_db();
	require_once('functions/f_global.php');

	$config = $arr[0];
	$connection = $arr[1];

	$statement = "SELECT pid, 
		file_name,
		title,
		date,
		counter,
		DATE_FORMAT(date, '{$config['short_date_format']}') AS d 
		FROM {$config['table_prefix']}pictures 
		WHERE approved=1 
		ORDER BY date DESC 
		LIMIT $n";
	$result = mysql_query($statement);
	
	_display_images($result, $config);
	_cleanup_db($connection);
}

function _connect_db() {
	require_once('config.php');
	$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL.");
	$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database.");
	
	//echo "upload dir from config is " . $config['upload_dir'];
	
	return array($config, $connection);
}

function _cleanup_db($connection) {
	mysql_close($connection);
}

if(!is_numeric($_GET['n']))   exit();
if($_GET['do_what'] == 0)   getLastAdded($_GET['n']);
elseif($_GET['do_what'] == 1)   getRandom($_GET['n']);

?>