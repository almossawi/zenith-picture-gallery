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
 
 File: lost_password.php
 Description: -
 Random quote: "Hearts of people are like wild birds, they attach
 themselves to those who love and train them."
*******************************************************************/

session_start();
session_cache_expire(360);
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_db.php');

//is user blacklisted?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);

if (isset($_POST['submit'])) {
	require_once('functions/f_db.php');
	$password = addslashes(dealWithSingleQuotes(charsEscaper($_POST['password_new'], $config['escape_chars'])));
	$password_repeat = addslashes(dealWithSingleQuotes(charsEscaper($_POST['password_repeat'], $config['escape_chars'])));
	//make sure passwords are valid before continuing
	if(!validateAlphaNumericFields(array($password,$password_repeat,$_POST['hash']))
	|| strlen($password) == 0 || strlen($password_repeat) == 0
	|| strlen($password) < 6 || strlen($password) > 12
	|| strcmp($password,$password_repeat) != 0) {
		 $redirect = "Location: lost_password.php?hash={$_POST['hash']}&hid={$_POST['hid']}&status=0";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['passwords_not_the_same_msg'];
		 exit();
	}
	
	//if the hash cannot be validated
	if(!is_numeric($_POST['hid']) || !validateAlphaNumericFields(array($_POST['hash']))) {
		$redirect = "Location: lost_password.php?hash={$_POST['hash']}&hid={$_POST['hid']}&status=-1";
		header("$redirect");
		$_SESSION['msg'] = $lang['admin_error_msg'];
		exit();
	}

	//reset the user's password
	$statement = "SELECT username, hash FROM {$config['table_prefix']}password_hashes WHERE hid={$_POST['hid']}";
	$result = mysql_query($statement) or die(mysql_error());
	$row = mysql_fetch_array($result);

	$crypted_password = crypt($password);
	$hash_to_compare = crypt($_POST['hash'], $row['hash']);
	
	//if the hash is invalid
	if(mysql_num_rows($result) == 0	|| !validateAlphaNumericFields(array($password,$password_repeat))) {
		$redirect = "Location: lost_password.php?hash={$_POST['hash']}&hid={$_POST['hid']}&status=-1";
		header("$redirect");
		$_SESSION['msg'] = $lang['admin_error_msg'];
		exit();
	}
	
	$statement = "UPDATE {$config['table_prefix']}users SET password='".$crypted_password."' WHERE username='".$row['username']."'";
	$result = mysql_query($statement) or die(mysql_error());
	
	//remove the hash record from the table
	$statement = "DELETE FROM {$config['table_prefix']}password_hashes WHERE hid={$_POST['hid']} AND hash='".$hash_to_compare."'";
	$result = mysql_query($statement) or die(mysql_error());
	
	$redirect = "Location: login.php?status=1";
	header("$redirect");
	$_SESSION['sent_msg'] = $lang['password_reset_msg'];
	exit();
}

if(isset($_GET['hash'])) {
	require_once('head.php');
	
	if(isset($_SESSION['msg'])) {
		if($_GET['status'] <= "0")   echo "<div class='msg' style='color:red'>{$_SESSION['msg']}</div>";
		elseif($_GET['status'] == "1")   echo "<div class='msg'>{$_SESSION['msg']}</div>";
		unset($_SESSION['msg']);
	}
	
	echo "<div align='center'><br /><table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
	<tr><td colspan='2' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['forgot_pass']}</td></tr>
	<form name='frmSendPasswordReset' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='POST'>
	<tr><td width='40%'>{$lang['password_new']}</td><td width='60%'><input type='password' name='password_new' class='textBox' style='width:50%' maxlength='12'></td></tr>
	<tr><td width='40%'>{$lang['password_repeat']}</td><td width='60%'><input type='password' name='password_repeat' class='textBox' style='width:50%' maxlength='12'></td></tr>
	<tr><td width='100%' colspan='2' align='center' class='cell_foot'><input type='submit' name='submit' value='{$lang['button_go']}' class='submitButton2'></td></tr>
	<input type='hidden' name='hash' value='{$_GET['hash']}'>
	<input type='hidden' name='hid' value='{$_GET['hid']}'>
	</form>
	</table>
	</div><br />
	";
}

require_once('head.php');
include('foot.php');

?>