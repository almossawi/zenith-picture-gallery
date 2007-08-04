<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com

 Modifications:
 * Marcin Krol <hawk@limanowa.net>, CAPTCHA support, v0.9.4 DEV

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: register.php
 Description: -
 Random quote: "The only thing to do with good advice is pass it on.
 It is never any use to oneself." -Oscar Wilde
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_db.php');

$cookie_name = $config['cookie_prefix'] . 'username';
if(isset($_COOKIE[$cookie_name]))   setcookie($cookie_name, '', time()-86400,"/"); //unset cookie

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
//elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

if($config['allow_registrations'] == 0)   doBox("error", $lang['not_authorized_msg'], $config, $lang);

if ($_POST['submit']) {
	require_once('functions/f_db.php');
	
	$username = addslashes(dealWithSingleQuotes(charsEscaper($_POST['username'], $config['escape_chars'])));
	$password = addslashes(dealWithSingleQuotes(charsEscaper($_POST['password'], $config['escape_chars'])));
	$password_repeat = addslashes(dealWithSingleQuotes(charsEscaper($_POST['password_repeat'], $config['escape_chars'])));
	$email = dealWithSingleQuotes(charsEscaper($_POST['email'],$config['escape_chars']));
	$email_repeat = dealWithSingleQuotes($_POST['email_repeat']);
	$day = $_POST['day'];
	$month = $_POST['month'];
	$year = addslashes(dealWithSingleQuotes(charsEscaper($_POST['year'], $config['escape_chars'])));
	
	//new in v0.9.4 DEV (author credited at the top)
	$agree = $_POST['agree'];
	if($config['registration_captcha'] == "1") {
	    $vcode = $_POST['vcode'];
	    $authcode = $_SESSION['authcode'];
	    unset($_SESSION['authcode']);
	} 
	
	//remember fields
	$_SESSION["{$config['cookie_prefix']}frmRegisterUsername"] = $username;
	$_SESSION["{$config['cookie_prefix']}frmRegisterEmail"] = $email;
	$_SESSION["{$config['cookie_prefix']}frmRegisterEmailRepeat"] = $email_repeat;
	$_SESSION["{$config['cookie_prefix']}frmRegisterDay"] = $day;
	$_SESSION["{$config['cookie_prefix']}frmRegisterMonth"] = $month;
	$_SESSION["{$config['cookie_prefix']}frmRegisterYear"] = $year;
   
	if(!validateAlphaNumericFields(array($username,$password,$password_repeat))
	|| strlen($username) == 0 || strlen($password) == 0 || strlen($password_repeat) == 0
	|| strlen($password) < 6 || strlen($username) < 3 || strlen($username) > 20) {
		 $redirect = "Location: register.php?status=-1";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['new_user_error_msg'];
		 exit();
	}
	elseif(!validateEmailFields(array($email,$email_repeat)) || strlen($email) == 0 || strlen($email_repeat) == 0) {
		 $redirect = "Location: register.php?status=-2";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['invalid_email_msg'];
		 exit();
	}
	elseif(strcmp($password,$password_repeat) != 0 || strcmp($email,$email_repeat) != 0) {
		 $redirect = "Location: register.php?status=-3";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['not_the_same_msg'];
		 exit();
	}
	elseif(!is_numeric($day) || !is_numeric($month) || !is_numeric($year) || $year < 1906 || $year > 2005) {
		 $redirect = "Location: register.php?status=-4";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['dob_error_msg'];
		 exit();
	}
	elseif(!$agree) {
		 $redirect = "Location: register.php?status=-5";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['need_to_agree'];
		 exit();
	}
	elseif($config['registration_captcha'] == "1" && strtolower($vcode) != strtolower($authcode)) { //new in v0.9.4 DEV (author credited at the top)
		 $redirect = "Location: register.php?status=-6";
		 header("$redirect");
		 $_SESSION['msg'] = $lang['bad_verification_code'];
		 exit();
	}
   
	$statement = "SELECT uid FROM {$config['table_prefix']}users WHERE username='$username'";
	$result = mysql_query($statement);
 
	if(mysql_num_rows($result) > 0) {
		$redirect = "Location: register.php?status=-7";
		header("$redirect");
		$_SESSION['msg'] = $lang['username_in_use_msg'];
		exit();
	}
	else { //if the username is not used and everything validated fine, proceed
		$crypted_password = crypt($password);
		$activation_key = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
		$cookie = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
		
		if($config['user_account_validation_method'] == 0) { //email
			$statement = "INSERT INTO `{$config['table_prefix']}users` ( `uid` , `username` , `password` , `admin_status` , `country` , `bday_day` , `bday_month` , `bday_year` , `language` , `approved` , `activated` , `activation_key` , `submissions` , `join_date` , `hide_email` , `website` , `avatar_location` , `register_ip` , `email`, `cookie` ) 
			VALUES ('', '$username', '$crypted_password', '0', NULL , '$day', '$month', '$year', 'en', '1', '0', '$activation_key', '0', NOW( ) , '1', NULL , NULL , '$users_ip', '$email', '$cookie')";
			$result = mysql_query($statement) or die(mysql_error());
			if($result) {
				$url = getCurrentInternetPath2($config,"register.php")."?username=$username&key=$activation_key";
				$body = "Hi $username,\n\nThank you for registering with us.  In order to start using your account, please activate it by clicking on the link below\n\n
				$url
				\n\nIf you feel that this email was sent in error, please forward it back as a reply.  This email was automatically generated by the Zenith Picture Gallery script.";
				$headers = "From: {$config['title']} <{$config['admin_email']}>\r\n";
				mail($email,"{$config['title']} Activate your account",$body,$headers);
				//mailReloaded($email,"",$config['admin_email'],$config['title'],"{$config['title']} Activate your account",$body);
			} 
		}
		elseif($config['user_account_validation_method'] == 1) { //admin
			//$body = "Hello,\n\nThis is to inform you that a new user has just submitted their registration for approval.\n\n
			//\n\nThis email was automatically generated by the Zenith Picture Gallery script.";
			//$headers = "From: {$config['title']} <{$config['admin_email']}>\r\n";
			//mail($config['admin_email'],"{$config['title']} New user registration awaiting approval","",$headers);
					
			$statement = "INSERT INTO `{$config['table_prefix']}users` ( `uid` , `username` , `password` , `admin_status` , `country` , `bday_day` , `bday_month` , `bday_year` , `language` , `approved` , `activated` , `activation_key` , `submissions` , `join_date` , `hide_email` , `website` , `avatar_location` , `register_ip` , `email`, `cookie` ) 
			VALUES ('', '$username', '$crypted_password', '0', NULL , '$day', '$month', '$year', 'en', '0', '1', '$activation_key', '0', NOW( ) , '1', NULL , NULL , '$users_ip', '$email', '$cookie')";
			$result = mysql_query($statement) or die(mysql_error());
		}
		elseif($config['user_account_validation_method'] == 2) { //none
			$statement = "INSERT INTO `{$config['table_prefix']}users` ( `uid` , `username` , `password` , `admin_status` , `country` , `bday_day` , `bday_month` , `bday_year` , `language` , `approved` , `activated` , `activation_key` , `submissions` , `join_date` , `hide_email` , `website` , `avatar_location` , `register_ip` , `email`, `cookie` ) 
			VALUES ('', '$username', '$crypted_password', '0', NULL , '$day', '$month', '$year', 'en', '1', '1', '$activation_key', '0', NOW( ) , '1', NULL , NULL , '$users_ip', '$email', '$cookie')";
			$result = mysql_query($statement) or die(mysql_error());
		}		
		
		//forget form fields on successful submit
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterUsername"]);
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterEmail"]);
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterEmailRepeat"]);
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterDay"]);
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterMonth"]);
		unset($_SESSION["{$config['cookie_prefix']}frmRegisterYear"]);
		
        $redirect = "Location: redirecting.php?register=1";
		header("$redirect");
		exit(); 
	}
	mysql_close($connection);
}

require_once('head.php');

//are we activating a user?
if(isset($_GET['key']) && isset($_GET['username']) && validateAlphaNumericFields(array($_GET['key'],$_GET['username']))) {
	$statement = "UPDATE {$config['table_prefix']}users SET activated='1' WHERE username='{$_GET['username']}' AND activation_key='{$_GET['key']}'";
	$result = mysql_query($statement);
	if(mysql_affected_rows() > 0)   doBox("msg", $lang['account_activated'], $config, $lang);
}

if(isset($_SESSION['msg'])) {
  echo "<div align='center'><br /><table style='width:{$config['gallery_width']}'><tr><td align='center'><span style='color:red'>{$_SESSION['msg']}</span></td></tr></table></div>";
  unset($_SESSION['msg']);
}

$frmRegisterUsername = $_SESSION["{$config['cookie_prefix']}frmRegisterUsername"];
$frmRegisterEmail = $_SESSION["{$config['cookie_prefix']}frmRegisterEmail"];
$frmRegisterEmailRepeat = $_SESSION["{$config['cookie_prefix']}frmRegisterEmailRepeat"];
$frmRegisterDay = $_SESSION["{$config['cookie_prefix']}frmRegisterDay"];
$frmRegisterMonth = $_SESSION["{$config['cookie_prefix']}frmRegisterMonth"];
$frmRegisterYear = $_SESSION["{$config['cookie_prefix']}frmRegisterYear"];

echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['head_register']}</td></tr>
<tr><td width='100%'>
<form name='frmRegister' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
<input type='hidden' name='is_what' value='new-user' />
	<table width='100%'>
		<tr><td width='20%'>{$lang['username']} </td>
		<td width='30%'>
		<input type='text' name='username' value='$frmRegisterUsername' class='textBox' style='width:200px' maxlength='32' />
		</td>
		<td width='20%'>{$lang['dob']} </td><td width='30%'>
		
		<select name='day' class='dropdownBox'>
		<option value='-' selected='selected'>{$lang['day']}</option>";
		for($i=1;$i<=31;$i++) {
			echo "<option value='$i'";
			if($i == $frmRegisterDay)   echo " selected='selected'";
			echo ">$i</option>";
		}
		echo "</select>
		&nbsp;/&nbsp;
		<select name='month' class='dropdownBox'>
		<option value='-'>{$lang['month']}</option>";
		for($i=1;$i<=12;$i++) {
			echo "<option value='$i'";
			if($i == $frmRegisterMonth)   echo " selected='selected'";
			echo ">$i</option>";
		}
		echo "</select>
		&nbsp;/&nbsp;";
		
		if(isset($frmRegisterYear))   $year_field = $frmRegisterYear;
		else   $year_field = $lang['year'];
		echo "<input type='text' name='year' value='$year_field' class='textBox' size='4' maxlength='4' onclick=\"this.value=''\" />";
		
		echo "</td></tr>
		<tr><td width='20%'>{$lang['password']} </td><td width='30%'><input type='password' name='password' class='textBox' style='width:200px' maxlength='12' /></td>
		<td width='20%'>{$lang['password_repeat']} </td><td width='30%'><input type='password' name='password_repeat' class='textBox' style='width:200px' maxlength='12' /></td></tr>
		<tr><td width='20%'>{$lang['email']} </td><td width='30%'><input type='text' name='email' value='$frmRegisterEmail' class='textBox' style='width:200px' maxlength='64' /></td>
		<td width='20%'>{$lang['email_repeat']} </td><td width='30%'><input type='text' name='email_repeat' value='$frmRegisterEmailRepeat' class='textBox' style='width:200px' maxlength='64' /></td></tr>		
		
		</table>";
		if($config['registration_captcha'] == "1") {
		    echo "<table align='center'><tr>
		    <td>{$lang['verification_code']}</td>
		    <td><img src='captcha.php' border='0' align='middle' alt='{$lang['generated_captcha']}' title='{$lang['generated_captcha']}'></td>
		    <td><input type='text' name='vcode' class='textBox' style='width:100px;height:12pt' /></td>
		    </tr></table>";
		}
		echo "<input type='hidden' name='ip' value='$users_ip' />

		<div align='center'><br />
		<textarea rows='4' name='agreement' cols='60' class='dropdownBox' readonly='readonly'>{$lang['agreement']}</textarea><br />
		<input type='checkbox' name='agree' onclick=\"javascript:toggleRegisterButton()\" /> <b>{$lang[agreement_accept]}</b>
		<br /><br /><input type='submit' name='submit' value='{$lang['button_submit']}' class='submitButton2' disabled='disabled' /></div>
</form>
</td></tr>
</table>
";

//forget form fields on form load
unset($_SESSION["{$config['cookie_prefix']}frmRegisterUsername"]);
unset($_SESSION["{$config['cookie_prefix']}frmRegisterEmail"]);
unset($_SESSION["{$config['cookie_prefix']}frmRegisterEmailRepeat"]);
unset($_SESSION["{$config['cookie_prefix']}frmRegisterDay"]);
unset($_SESSION["{$config['cookie_prefix']}frmRegisterMonth"]);
unset($_SESSION["{$config['cookie_prefix']}frmRegisterYear"]);

include('foot.php');

?>