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
 
 File: edit_field.php
 Description: Provides an interface to allow any field in the db to
 be updated.  Currently, it's not very flexible. -Dec 20, 2005
 Random quote: "The visionary lies to himself, the liar only to 
 others." -Friedrich Nietzsche 
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');

if(!loggedIn($config) || !adminStatus($config) || !sessionRegistered($config))   doBox("error", $lang['admin_guest_msg'], $config, $lang);

//on submit
if ($_POST['submit']) {
	//if id isn't numeric, all bets are off
	assertActivate();
	assert(is_numeric($_POST['fid']));

	$ud_field_name = htmlentities(charsEscaper($_POST['ud_field_name'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$fid = $_POST['fid'];
	  
	//001 -- validation check
	if(strlen($ud_field_name) == 0) {
		$redirect = "Location: edit_field.php?fid=" . $fid . "&error=1";
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['required_error'];
		exit();
	}
	
	//only proceed if form validated
	$statement = "UPDATE {$config['table_prefix']}ud_fields SET ud_field_name='$ud_field_name' WHERE fid=$fid";
	$result = mysql_query($statement);
	if($result) {
		$redirect = "Location: edit_field.php?fid=" . $fid;
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['record_updated'];
		exit();
	}
    
}

//if id isn't numeric, all bets are off
assertActivate();
assert(is_numeric($_GET['fid']));

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

if(isset($_SESSION['edit_msg'])) {
	echo "<div align='center'>{$_SESSION['edit_msg']}</div>";
	unset($_SESSION['edit_msg']);
}

//does this image exist?
$statement = "SELECT COUNT(fid) as n FROM {$config['table_prefix']}ud_fields WHERE fid={$_GET['fid']}";
$result = mysql_query($statement);
$numrows = mysql_fetch_array($result);
if($numrows['n'] == "0")   die("<div align='center'>{$lang['edit_error']}</div>");

//if so, output the table
$statement = "SELECT fid, ud_field_name FROM {$config['table_prefix']}ud_fields WHERE fid={$_GET['fid']}";
$result = mysql_query($statement);
$row = mysql_fetch_array($result); 

//the reason for the textfield crypto is because for some reason, the form's fields aren't appended
//to the url when the user hits return instead of clicking the button with the mouse when there's
//only one textfield.
echo "<form name='frmEdit' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post' style='padding:0;margin:0'>
<input type='hidden' name ='fid' value='{$row['fid']}'>
<div align='center'><table class='table_layout_sections' cellspacing='0' cellpadding='2' width='580'>
<tr><td class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' colspan='2'>
{$lang['head_edit_record']}
</td></tr>

<tr><td width='30%'>{$row['ud_field_name']} </td>
<td width='70%'>
<input type='text' class='textBox' name='crypto' value='' style='width:.1em;visibility:hidden' disabled='disabled' />
<input type='text' name='ud_field_name' value ='{$row['ud_field_name']}' class='spoiltTextBox' style='width:92%' maxlength='255' />
</td></tr>

</td></tr>
<tr><td align='center' colspan='2' class='cell_foot'>
<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton3'>
</td></tr>
</form>

</table>
</div>
</body>
</html>";

mysql_close($connection); 

?>