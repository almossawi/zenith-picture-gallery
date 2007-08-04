<?php
/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 Modified by qwerti
 http://www.cyberiapc.com]http://www.cyberiapc.com

 This program is free software; you can redistribute it and/or modify
 it under the terms of the ZPG General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: zenith_includer.php (version 2, updated January 23, 2006)
 Description: Allows last added or random pictures to be shown on other 
 pages of a website.
 Random quote: "No problem is too small or too trivial if we can 
 really do something about it." -Richard P. Feynman
*******************************************************************/

require_once('config.php');
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL.");
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database.");

function getRandom($config, $n) {
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
	while($row = mysql_fetch_array($result)) {
		$category=$row['category'];
		$filename=$row['file_name'];
		$thmpath=getPicturesPath($config, $filename, 0);
		$at_full = $thmpath;
		$at_full_server = $config['upload_dir_server'].$row['file_name'];
		$at_thumb = getThumbPath($at_full, $at_full_server, $config['thumb_suffix']);	
		echo "<a href='" . getCurrentInternetPath2($config,"") . "pic_details.php?pid={$row['pid']}'><img src='".$at_thumb[0]."' alt='{$row['file_name']}' title='{$row['file_name']}' border='0' /></a><br />";
		
	}
}

function getLastAdded($config, $n) {
	require_once('functions/f_global.php');
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
			echo "<a href='" . getCurrentInternetPath2($config,"") . "pic_details.php?pid={$row['pid']}'><img src='{$at_thumb[0]}' alt='{$row['file_name']}' title='{$row['file_name']}' border='0' /></a><br />
				{$title}<br />
				{$date}<br />";
			if ($counter>1){
				$counter .=" views";
			} else {
				$counter .=" view";
			}
			$counter = $counter."<br />";
			echo $counter;
	}
}


?>