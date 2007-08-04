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
 
 File: edit.php
 Description: -
 Random quote: "I am not young enough to know everything." -Oscar Wilde
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
	assert(is_numeric($_POST['id']));

	$new_category = charsEscaper($_POST['category'], $config['escape_chars']);
	$new_title = htmlentities(charsEscaper($_POST['title'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$new_keywords = htmlentities(charsEscaper($_POST['keywords'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$new_who_can_see_this = htmlentities(charsEscaper($_POST['who_can_see_this'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$id = $_POST['id'];
	$new_description = htmlentities(charsEscaper($_POST['description'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	if(strlen($new_description)>512)   $new_description = substr($new_description,0,512);
	//any user-defined fields?
	if(isset($_POST['udf'])) {
		$udf = array(); //added on january 9, 2006 to fix mikko's problem (see forums)
		foreach($_POST['udf'] as $key => $value) {
			$udf["udf_".$key] = htmlentities(charsEscaper($value, $config['escape_chars']), ENT_QUOTES, "UTF-8");
		}
	}
	  
	//001 -- validation check
	if(strlen($new_category) <= 0 || 
	strlen($new_title) <= 0 || 
	strlen($new_keywords) <= 0) {
		$redirect = "Location: edit.php?id=" . $id . "&error=1";
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['required_error'];
		exit();
	}

	//get picture's old cid
	$statement = "SELECT file_name FROM {$config['table_prefix']}pictures WHERE pid=$id";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	$old_path = getPicturesPath($config, $row['file_name'], 1);
	$bits = splitFilenameAndExtension($old_path);
	$old_path_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
	
	//print_r($udf);
	//only proceed if form validated
	$statement = "UPDATE {$config['table_prefix']}pictures SET title='$new_title', description='$new_description', keywords='$new_keywords', category='$new_category', who_can_see_this='$new_who_can_see_this' " .
					arrayHacker($udf,"value","0","###='","'",",","",",") . " WHERE pid=$id";
	//die($statement);
	$result = mysql_query($statement);
	
	//move the picture and its thumb to a different directory if its category was changed
	$new_path = getPicturesPath($config, $row['file_name'], 1); //echo $new_path . "<br />";
	$bits = splitFilenameAndExtension($new_path);
	$new_path_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
	rename($old_path, $new_path);
	@rename($old_path_thumb, $new_path_thumb); //ZVG: since videos don't have thumbs, suppress warnings
	
	if($result) {
		$redirect = "Location: edit.php?id=" . $id;
		header("$redirect");
		$_SESSION['edit_msg'] = $lang['record_updated'];
		exit();
	}
    
}

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

if(isset($_SESSION['edit_msg'])) {
	echo "<div align='center'>{$_SESSION['edit_msg']}</div>";
	unset($_SESSION['edit_msg']);
}

//does this image exist?
$statement = "SELECT COUNT(pid) as n FROM {$config['table_prefix']}pictures WHERE pid={$_GET['id']}";
$result = mysql_query($statement);
$numrows = mysql_fetch_array($result);
if($numrows['n'] == "0")   die("<div align='center'>{$lang['edit_error']}</div>");

//if so, output the table
$statement = "SELECT pid, title, description, keywords, category, who_can_see_this FROM {$config['table_prefix']}pictures WHERE pid={$_GET['id']}";
$result = mysql_query($statement);
$row = mysql_fetch_array($result); 

echo "<div align='center'><br />
<form name='frmUpdate' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post' style='padding:0;margin:0'>
<input type='hidden' name = 'id' value='{$row['pid']}' />
<table class='table_layout_sections' id='the_table' cellspacing='0' cellpadding='2' width='580'>
<tr><td class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' colspan='2'>
{$lang['head_edit_record']}
</td></tr>

<tr><td width='30%'>{$lang['title']} </td>
<td width='70%'><input type='text' name='title' value ='{$row['title']}' class='spoiltTextBox' maxlength='64' />
</td></tr>

<tr><td valign='top' width='30%'>
{$lang['description']} </td>
<td width='70%'><textarea name='description' rows='3' cols='40' class='spoiltTextBox' maxlength='255'>{$row['description']}</textarea>
</td></tr>

<tr><td width='30%'>
{$lang['keywords']} </td>
<td width='70%'><input type='text' name='keywords' value ='{$row['keywords']}' class='spoiltTextBox' maxlength='128' />
<br /><span style='font-size:9px'>{$lang['add_txt_sub3']}</span>
</td></tr>

<tr><td width='30%'>
{$lang['category']} </td>
<td width='70%'><select name='category' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">";
$cats = getCatsTreeView(0,0,$config, $lang);
foreach($cats as $key => $value) {
if($key == 0) unset($key);
	echo "<option value='$key'";
	if($key == $row['category'])   echo " selected='selected'";
	echo ">$value</option>";
}
echo "</select>
</td></tr>
";
	
//any user-defined fields?
$fields = getCurrentUserDefinedFields("csv","udf_","",$config,$lang);
if($fields != "") {
	$statement = "SELECT " . $fields ." FROM {$config['table_prefix']}pictures WHERE pid='{$_GET['id']}'";
	$result_udf = mysql_query($statement);
	$row_udf = mysql_fetch_array($result_udf);	
	echo displayCurrentUserDefinedFields("<tr><td width='30%'>### </td><td width='70%'>", "</td></tr>", $row_udf, "textbox", "1", $config, $lang);
}

echo "<tr><td valign='top' width='30%'>
{$lang['who_can_see_this']} </td>
<td width='70%'>
";
if($row["who_can_see_this"] == '0')   echo "<input type='radio' name='who_can_see_this' value='0' checked='checked' />{$lang['public']}&nbsp;<input type='radio' name='who_can_see_this' value='1' />{$lang['private']}";
elseif($row["who_can_see_this"] == '1')   echo "<input type='radio' name='who_can_see_this' value='0' />{$lang['public']}&nbsp;<input type='radio' name='who_can_see_this' value='1' checked='checked' />{$lang['private']}";
else   echo "<input type='radio' name='who_can_see_this' value='0' checked='checked' />{$lang['public']}&nbsp;<input type='radio' name='who_can_see_this' value='1' />{$lang['private']}";	
echo "</td></tr>";
	
echo "<tr><td colspan='2' class='cell_foot'>
<div align='center'><input type='submit' name='submit' value='{$lang['button_update_pic']}' class='submitButton3' /></div>
</td></tr>
</form>

</table>
</div>
</body>
</html>
";

mysql_close($connection); 

?>