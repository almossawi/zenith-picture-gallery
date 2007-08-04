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
 
 File: delete.php
 Description: -
 Random quote: "If the facts don't fit the theory, change the facts."
 -Albert Einstein
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');

if(loggedIn($config) == 0 || adminStatus($config) == 0 || sessionRegistered($config) == 0)   doBox("error", $lang['admin_guest_msg'], $config, $lang);

//on submit
if ($_POST['submit']) {
	//if id isn't numeric, all bets are off
	assertActivate();
	assert(is_numeric($_POST['id']));
	
	$id = $_POST['id'];
	$statement = "SELECT pid, file_name FROM {$config['table_prefix']}pictures WHERE pid=$id";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	
	//paths, paths and more paths
	if($row) {
		$to_delete = getPicturesPath($config, $row['file_name'], 1);
		$to_delete_show = getPicturesPath($config, $row['file_name'], 0);
		$bits = splitFilenameAndExtension($to_delete);
		$to_delete_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
		$bits = splitFilenameAndExtension($to_delete_show);
		$to_delete_thumb_show = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
		
		if(isVideo($config,"",$row['file_name']))   $is_video=1; //new in ZVG
	}
  
	//remove the pictures
	$statement = "DELETE FROM {$config['table_prefix']}pictures WHERE pid=$id";
	$result = mysql_query($statement);
	if(@unlink("$to_delete"))   $files_deleted[0] = 1;
	else   $files_deleted[0] = 0;
	if(@unlink("$to_delete_thumb"))   $files_deleted[1] = 1;
	else   $files_deleted[1] = 0;
  
	if($result) {
		$redirect = "Location: delete.php?id=" . $id;
		header("$redirect");
		$_SESSION['del_msg'] = "{$lang[record_deleted]}";
	}

	//if full-size image was deleted
	if($files_deleted[0] == 1) {
		$_SESSION['del_msg'] .= "<br />$to_delete_show [{$lang['ok']}]";
	}
	else {
		$_SESSION['del_msg'] .= "<br />$to_delete_show [{$lang['error']}]";
	}
  
	//if thumbnail was deleted
	if($files_deleted[1] == 1) {
		$_SESSION['del_msg'] .= "<br />$to_delete_thumb_show [{$lang['ok']}]";
	}
	else {
		//new in ZVG, added guard: don't show a message for the deleted thumbnail if the file was a video since there is none
		if(!$is_video)   $_SESSION['del_msg'] .= "<br />$to_delete_thumb_show [{$lang['error']}]";
	}
	
	//finally, remove all comments for this picture...
	$statement = "DELETE FROM {$config['table_prefix']}comments WHERE pid=$id";
	$result = mysql_query($statement);
	
	//...and votes
	$statement = "DELETE FROM {$config['table_prefix']}ratings WHERE pid=$id";
	$result = mysql_query($statement);
	
	exit();
  
}//end if submit

//if id isn't numeric, all bets are off
assertActivate();
assert(is_numeric($_GET['id']));

//set stylesheet
$cookie_name_skin = $config['cookie_prefix'] . 'skin';
$cookie_name_trimmed_skin = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $cookie_name_skin));
if(isset($_COOKIE[$cookie_name_trimmed_skin])) {
	$config['stylesheet'] = "skins/".$_COOKIE[$cookie_name_trimmed_skin];
}

header("Content-type: text/html; charset=UTF-8");
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
   \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<link rel=\"stylesheet\" type=\"text/css\" href=\"" . getSkinElement($config['stylesheet'], "stylesheet.css") . "\" />
<body>";

//check if id exists
$statement = "SELECT pid FROM {$config['table_prefix']}pictures WHERE pid={$_GET['id']}";
$result = mysql_query($statement);

if(isset($_SESSION['del_msg'])) {
	echo "<div align='center'>{$_SESSION['del_msg']}</div>";
	unset($_SESSION['del_msg']);
}
elseif(@mysql_num_rows($result) == 0) {
	echo "<div align='center'>{$lang['delete_error']}</div>";
}
else {
	echo "<div align='center'><br />
	<table class='table_layout_sections' cellspacing='0' cellpadding='2' width='580'>
	<tr><td class='cell_header' style='background-image:url({$config['stylesheet']}/images/td_back_mid.gif)' colspan='2'>{$lang['head_delete_record']}</td></tr>
	
	<tr><td>{$lang['delete_warning']}</td></tr>
	<form name='frmDelete' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
	<input type='hidden' name = 'id' value='{$_GET['id']}'>  
	<tr><td colspan='2' class='cell_foot'>
	<div align='center'><input type='submit' name='submit' value='{$lang['button_delete_pic']}' class='submitButton3'></div>
	</td></tr>
	</form>
	
	</table>
	</div>
	";
}

echo "</body>
</html>
";

mysql_close($connection);

?>