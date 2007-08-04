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
 
 File: edit_user.php
 Description: -
 Random quote: "Why is it that that those who have something to say
 can't say it, while those who have nothing to say keep saying it?"
*******************************************************************/

session_start();
session_cache_expire(360);
require_once('config.php');
require_once('functions/f_admin.php');
require_once('functions/f_global.php');
require_once('functions/f_db.php');

if($config['debug_mode'] == 1)   print_r($_COOKIE);
if(!loggedIn($config) || !adminStatus($config) || !sessionRegistered($config))   doBox("error", $lang['admin_guest_msg'], $config, $lang);

if ($_POST['submit']) {
	//since these variables will need to be edited in text fields, sanitize them as follows, which is different to the usual method
	$email = dealWithSingleQuotes(charsEscaper($_POST['email'],$config['escape_chars']));
	$website = htmlentities(str_replace("\"", "'", stripslashes($_POST['website'])),ENT_QUOTES, "UTF-8");
	$location = htmlentities(str_replace("\"", "'", stripslashes($_POST['location'])),ENT_QUOTES, "UTF-8");
	$avatar_location = htmlentities(str_replace("\"", "'", stripslashes($_POST['avatar_location'])),ENT_QUOTES, "UTF-8");
	$personal_quote = htmlentities(str_replace("\"", "'", stripslashes($_POST['personal_quote'])),ENT_QUOTES, "UTF-8");
	$interests = htmlentities(str_replace("\"", "'", stripslashes($_POST['interests'])),ENT_QUOTES, "UTF-8");
	//$language = dealWithSingleQuotes($_POST['language']);
	$hide_email = dealWithSingleQuotes($_POST['hide_email'],ENT_QUOTES, "UTF-8");
	$day = dealWithSingleQuotes($_POST['day'],ENT_QUOTES, "UTF-8");
	$month = dealWithSingleQuotes($_POST['month'],ENT_QUOTES, "UTF-8");
	$year = dealWithSingleQuotes(charsEscaper($_POST['year'], $config['escape_chars']));
	
	if(!is_numeric($day) || !is_numeric($month) || !is_numeric($year) || $year < 1906 || $year > 2005) {
		 $redirect = "Location: edit_user.php?uid={$_POST['uid']}&status=-1";
		 header("$redirect");
		 $_SESSION['edit_msg'] = $lang['dob_error_msg'];
		 exit();
	}
	//validate new email
	elseif(!validateEmailFields(array($email))) {
		$redirect = "Location: edit_user.php?uid={$_POST['uid']}&status=-2";
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['invalid_email_msg'];
		exit();
	}
	
	$statement = "UPDATE `{$config['table_prefix']}users` SET email='$email', bday_day='$day', bday_month='$month', bday_year='$year', hide_email='$hide_email', website='$website', language='$language', avatar_location='$avatar_location', personal_quote='$personal_quote', interests='$interests', country='$location' WHERE uid='{$_POST['uid']}'";
	$result = mysql_query($statement) or die(mysql_error());
	
	if($result) {
		$redirect = "Location: edit_user.php?uid=" . $_POST['uid'];
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['record_updated'];
		exit();
    }
    
}//end if submit
else {
  if(is_numeric($_GET['uid'])) {
  	$uid = $_GET['uid'];
	$statement = "SELECT username, bday_day, bday_month, bday_year, personal_quote, interests, hide_email, country, avatar_location, website, language, email FROM {$config['table_prefix']}users WHERE uid=$uid";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result); 
  }
}

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

$statement = "SELECT uid FROM {$config['table_prefix']}users WHERE uid={$_GET['uid']}";
$result = mysql_query($statement);

if(isset($_SESSION['edit_msg'])) {
  echo "<div align='center'>{$_SESSION['edit_msg']}</div>";
  unset($_SESSION['edit_msg']);
}

if(@mysql_num_rows($result) == 0) {
   echo "<div align='center'>{$lang['edit_error']}</div>";
}
else {
   echo "<div align='center'><br />
   <table class='table_layout_sections' cellspacing='0' cellpadding='2' width='580'>
   <tr><td class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' colspan='2'>{$lang['head_edit_record']}</td></tr>
   <tr>
   
   <form name='frmEditProfile' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
		<td width='50%'>{$lang['dob']} </td><td width='50%'>
		<select name='day' class='dropdownBox'>
		<option value='-' selected='selected'>{$lang['day']}</option>";
		for($i=1;$i<=31;$i++) {
			echo "<option value='$i'";
			if($i == $row['bday_day'])   echo " selected='selected'";
			echo ">$i</option>";
		}
		echo "</select>
		&nbsp;/&nbsp;
		<select name='month' class='dropdownBox'>
		<option value='-'>{$lang['month']}</option>";
		for($i=1;$i<=12;$i++) {
			echo "<option value='$i'";
			if($i == $row['bday_month'])   echo " selected='selected'";
			echo ">$i</option>";
		}
		echo "</select>
		&nbsp;/&nbsp;";

		if($row['bday_year'] == '0')   echo " <input type='text' name='year' value='{$lang['year']}' class='textBox' size='4' maxlength='4' onClick=\"this.value=''\" />";
		else      echo " <input type='text' name='year' value='{$row['bday_year']}' class='textBox' size='4' maxlength='4' />";
		
		echo "</td></tr>
		<tr><td width='50%'>{$lang['email']} </td><td width='50%'><input type='text' name='email' value=\"{$row['email']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
		<tr><td width='50%'>{$lang['website']}: </td><td width='50%'><input type='text' name='website' value=\"{$row['website']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
		<td width='50%'>{$lang['location']}: </td><td width='50%'><input type='text' name='location' value=\"{$row['country']}\" class='textBox' style='width:200px' maxlength='128' /></td></tr>
		<tr><td width='50%'>{$lang['avatar_location']}: </td><td width='50%'><input type='text' name='avatar_location' value=\"{$row['avatar_location']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
	";

	echo "<tr><td width='50%'>{$lang['hide_email']}: </td><td width='50%'>";		
	if($row['hide_email'] == '0')   echo "<input type='radio' name='hide_email' value='0' checked>{$lang['no']}&nbsp;<input type='radio' name='hide_email' value='1' />{$lang['yes']}";
	elseif($row['hide_email'] == '1')   echo "<input type='radio' name='hide_email' value='0'>{$lang['no']}&nbsp;<input type='radio' name='hide_email' value='1' checked='checked' />{$lang['yes']}";
		
	echo "<tr><td width='50%'>{$lang['interests']}: </td><td width='50%'><input type='text' name='interests' value=\"{$row['interests']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
	<tr><td width='50%'>{$lang['personal_quote']}: </td><td width='50%'><input type='text' name='personal_quote' value=\"{$row['personal_quote']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
	
	<input type='hidden' name='uid' value='{$_GET['uid']}'>
	<input type='hidden' name='is_what' value='edit-profile'>
	<tr><td colspan='2' class='cell_foot'><div align='center'><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' /><br /></div></td></tr>
	</form>
	";
	
}//end else

echo "</table>
</div>
</body>
</html>";

mysql_close($connection); 

?>