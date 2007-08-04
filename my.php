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
 
 File: add.php
 Description: -
 Random quote: "If you want to change the world, create like a God,
 command like a king and work like a slave." -Guy Kawasaki  
*******************************************************************/

session_start();
session_cache_expire(360);

require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_db.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

if ($_POST['submit']) {
	require_once('functions/f_db.php');
	
	switch($_POST['is_what']) {
		case 'edit-profile':
			//since these variables will need to be edited in text fields, sanitize them as follows, which is different to the usual method
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
				 $redirect = html_entity_decode("Location: my.php?m={$_POST['uid']}&amp;do=edit-profile&amp;status=0");
				 header("$redirect");
				 $_SESSION['msg'] = $lang['dob_error_msg'];
				 exit();
			}
		
			$statement = "UPDATE `{$config['table_prefix']}users` SET bday_day='$day', bday_month='$month', bday_year='$year', hide_email='$hide_email', website='$website', language='$language', avatar_location='$avatar_location', personal_quote='$personal_quote', interests='$interests', country='$location' WHERE uid='{$_POST['uid']}'";
			$result = mysql_query($statement) or die(mysql_error());
			$redirect = "Location: my.php?m={$_POST['uid']}&do=edit-profile&status=1";
			header("$redirect");
			exit();
			break;
	
		case 'change-email':
			//since these variables will need to be edited in text fields, sanitize them as follows, which is different to the usual method
			$email_new = dealWithSingleQuotes(charsEscaper($_POST['email_new'],$config['escape_chars']));
			$email_new_confirm = dealWithSingleQuotes(charsEscaper($_POST['email_new_confirm'],$config['escape_chars']));
			$password = dealWithSingleQuotes(charsEscaper($_POST['password_current'],$config['escape_chars']));
			$uid = dealWithSingleQuotes(charsEscaper($_POST['uid'],$config['escape_chars']));

			//validate new email
			if(!validateEmailFields(array($email_new,$email_new_confirm)) || $email_new != $email_new_confirm
			|| !is_numeric($uid)) {
				$redirect = "Location: my.php?m={$_POST['uid']}&do=change-email&status=-1";
				header("$redirect");
				exit();
			}
			else {
				$statement = "SELECT password FROM {$config['table_prefix']}users WHERE uid='$uid'";
				$result = mysql_query($statement) or die(mysql_error()); 
				$row = mysql_fetch_array($result);
				if(crypt($password, $row['password']) == $row['password']) {
					//should we ask user to re-activate?
					if($config['user_account_validation_method'] == 0) {
						$statement = "UPDATE {$config['table_prefix']}users SET email='$email_new', activated='0' WHERE uid='$uid'";
						$result = mysql_query($statement) or die(mysql_error()); 
						
						$statement = "SELECT username, activation_key FROM {$config['table_prefix']}users WHERE uid='$uid'";
						$result = mysql_query($statement) or die(mysql_error());
						$row = mysql_fetch_array($result);
						
						//resend email
						$url = getCurrentInternetPath2($config,"register.php")."?username={$row['username']}&key={$row['activation_key']}";
						$body = "Hi $username,\n\nThank you for registering with us.  In order to start using your account, please activate it by clicking on the link below\n\n
						$url
						\n\nIf you feel that this email was sent in error, please forward it back as a reply.  This email was automatically generated by the Zenith Picture Gallery script.";
						$headers = "From: {$config['title']} <{$config['admin_email']}>\r\n";
						mail($email_new,"{$config['title']} Activate your account",$body,$headers);
						//mailReloaded($email_new,"",$config['admin_email'],$config['title'],"{$config['title']} Activate your account",$body);
						
						$cookie_name = $config['cookie_prefix'] . 'username';
						if(isset($_COOKIE[$cookie_name]))   setcookie($cookie_name, '', time()-86400,"/"); //unset cookie
						
						//$redirect = "Location: my.php?m={$_POST['uid']}&do=change-email&status=2";			
						$redirect = "Location: logout.php";			
					}
					else { //just update email address
						$statement = "UPDATE {$config['table_prefix']}users SET email='$email_new' WHERE uid='$uid'";
						$result = mysql_query($statement) or die(mysql_error()); 
						$redirect = html_entity_decode("Location: my.php?m={$_POST['uid']}&amp;do=change-email&amp;status=1");						
					}

					header("$redirect");
					exit();
				}
				else {
					$redirect = html_entity_decode("Location: my.php?m={$_POST['uid']}&amp;do=change-email&amp;status=-2");
					header("$redirect");
					exit();
				}
			}
			
			break;
			
		case 'change-pass':
			//since these variables will need to be edited in text fields, sanitize them as follows, which is different to the usual method
			$password_old = dealWithSingleQuotes(charsEscaper($_POST['password_old'],$config['escape_chars']));
			$password_new = dealWithSingleQuotes(charsEscaper($_POST['password_new'],$config['escape_chars']));
			$password_repeat = dealWithSingleQuotes(charsEscaper($_POST['password_repeat'],$config['escape_chars']));
			$uid = dealWithSingleQuotes(charsEscaper($_POST['uid'],$config['escape_chars']));

			//validate password
			if(!validateAlphaNumericFields(array($password_new,$password_repeat)) || $password_new != $password_repeat
				|| !is_numeric($uid) || strlen($password_new) == 0 || strlen($password_repeat) == 0
				|| strlen($password_new) < 6 || strlen($password_new) > 12 || strlen($password_repeat) < 6 || strlen($password_repeat) > 12) {
				$redirect = html_entity_decode("Location: my.php?m={$_POST['uid']}&amp;do=change-pass&amp;status=-1");
				header("$redirect");
				exit();
			}
			else {
				$statement = "SELECT password FROM {$config['table_prefix']}users WHERE uid='$uid'";
				$result = mysql_query($statement) or die(mysql_error()); 
				$row = mysql_fetch_array($result);
				if(crypt($password_old, $row['password']) == $row['password']) {
					$password_c = crypt($password_new);
					$statement = "UPDATE {$config['table_prefix']}users SET password='$password_c' WHERE uid='$uid'";
					$result = mysql_query($statement) or die(mysql_error()); 
					$redirect = html_entity_decode("Location: my.php?m=".$uid."&amp;do=change-pass&amp;status=1");
					header("$redirect");
					exit();
				}
				else {
					$redirect = html_entity_decode("Location: my.php?m=".$uid."&amp;do=change-pass&amp;status=-2");
					header("$redirect");
					exit();
				}
			}
			
			break;
	}
}
elseif($_POST['submit_pass']) {
	$password = dealWithSingleQuotes(charsEscaper($_POST['password_confirm'],$config['escape_chars']));
	$uid = dealWithSingleQuotes(charsEscaper($_POST['uid'],$config['escape_chars']));
	$statement = "SELECT password FROM {$config['table_prefix']}users WHERE uid='$uid'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	
	if(crypt($password, $row['password']) == $row['password']) {
		//create session then redirect
		$_SESSION["{$config['cookie_prefix']}_$uid"] = "1";
		
		$redirect = html_entity_decode("Location: my.php?m=".$uid."&amp;do=edit-profile");
		header("$redirect");
		exit();
	}
	else {
		$redirect = html_entity_decode("Location: my.php?m=".$uid."&amp;do=edit-profile&amp;status=0");
		header("$redirect");
		exit();
	}
}

$uid = $_GET['m'];
if(loggedIn($config) && is_numeric($uid)) {
	//check cookie
	$row = mysql_fetch_array(mysql_query("SELECT username FROM {$config['table_prefix']}users WHERE uid='$uid'"));
	$cookie_name = trim(str_replace(array("|","/","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
	$row_is_admin = mysql_fetch_array(mysql_query("SELECT admin_status FROM {$config['table_prefix']}users WHERE username='{$_COOKIE[$cookie_name]}'"));

	if($_COOKIE[$cookie_name] != $row['username'] || strlen($row['username']) == 0)   doBox("error", $lang['not_authorized_msg'], $config, $lang);
}
else {
	doBox("error", $lang['admin_error_msg'], $config, $lang);
}

if(loggedIn($config) && $_SESSION["{$config['cookie_prefix']}_$uid"] != "1") { //if logged in but session isn't set
	$head_aware['onload'] = "document.frmConfirmLogin.password_confirm.focus();";
	require_once('head.php');
	
	//check pass
	echo "<form name='frmConfirmLogin' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post' style='margin-top:0;padding-top:0'>
	<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
	<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['head_confirm_pass']}</td></tr>
	<input type='hidden' name='uid' value='{$_GET['m']}' />
	<tr><td width='100%' align='center'><input type='password' name='password_confirm' class='textBox' style='width:60%' maxlength='12' />
	<input type='text' class='textBox' name='crypto' value='' style='width:.1em;visibility:hidden' disabled='disabled' />
	<tr><td width='100%' align='center' class='cell_foot'><input type='submit' name='submit_pass' value='{$lang['button_login']}' class='submitButton2' /></td></tr>
	</table>
	</form>
	";
	exit();
}
else {
	require_once('head.php');
	require_once('functions/f_db.php');
	
	if(isset($_SESSION['msg'])) {
	  echo "<div class='msg' style='color:red'>{$_SESSION['msg']}</div>";
	  unset($_SESSION['msg']);
	}
	
	if(isset($_GET['m']) && is_numeric($_GET['m'])) {
		echo "<table class='table_layout_admin' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
		<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['head_my_controls']}</td></tr>
		<tr>
		<td width='195' valign='top' style='border:1px solid;border-color:#666666 #cccccc #666666 #666666;padding:0 3px"; if($config['page_direction'] == "1")   echo ";text-align:right' align='right"; echo "'>
		   <table width='195' cellspacing='0' cellpadding='0'>
			  <tr>
			  <td width='100%'>";
				 include("my_navbar.php");
				 echo "</td>
			  </tr>
		   </table>
		</td>
		<td width='100%' valign='top' style='border:1px solid;border-color:#666666 #666666 #666666 #cccccc;padding:0 3px"; if($config['page_direction'] == "1")   echo ";text-align:right' align='right"; echo "'>
		";
		
		$do = $_GET['do'];
		switch($do) {
			case 'edit-profile':
				$statement = "SELECT username, bday_day, bday_month, bday_year, personal_quote, interests, hide_email, country, avatar_location, website, language, email FROM {$config['table_prefix']}users WHERE uid={$_GET['m']}";
				$result = mysql_query($statement);
				$row = mysql_fetch_array($result);
			
				if(mysql_num_rows($result) > 0) {
					echo "<fieldset class='default'><legend>{$lang['my_nav_edit-profile']}&nbsp;</legend>
					<form name='frmEditProfile' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
						<table width='100%' cellpadding='2' cellspacing='2'>
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
						<tr><td width='50%'>{$lang['website']}: </td><td width='50%'><input type='text' name='website' value=\"{$row['website']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
						<td width='50%'>{$lang['location']}: </td><td width='50%'><input type='text' name='location' value=\"{$row['country']}\" class='textBox' style='width:200px' maxlength='128' /></td></tr>
						<tr><td width='50%'>{$lang['avatar_location']}: </td><td width='50%'><input type='text' name='avatar_location' value=\"{$row['avatar_location']}\" class='textBox' style='width:200px' maxlength='255' /></td></tr>
					";
					/*
					echo "<tr><td width='40%'>{$lang['language']}: </td><td width='60%'><select name='language' class='dropdownBox'>";
					foreach(getLangs($config) as $key => $value) {
						echo "<option value='$key'";
						if($row['language'] == $value)
							echo " selected";
						echo ">$value";
					}
					echo "</select>
					</td></tr>";
					*/
					echo "<tr><td width='50%'>{$lang['hide_email']}: </td><td width='50%'>";		
					if($row['hide_email'] == '0')   echo "<input type='radio' name='hide_email' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='hide_email' value='1' />{$lang['yes']}";
					elseif($row['hide_email'] == '1')   echo "<input type='radio' name='hide_email' value='0' />{$lang['no']}&nbsp;<input type='radio' name='hide_email' value='1' checked />{$lang['yes']}";
						
					echo "<tr><td width='50%'>{$lang['interests']}: </td><td width='50%'><input type='text' name='interests' value=\"{$row['interests']}\" class='textBox' style='width:200px' maxlength='255'></td></tr>
					<tr><td width='50%'>{$lang['personal_quote']}: </td><td width='50%'><input type='text' name='personal_quote' value=\"{$row['personal_quote']}\" class='textBox' style='width:200px' maxlength='255'></td></tr>
					
					</table>
						
					<input type='hidden' name='uid' value='{$_GET['m']}'>
					<input type='hidden' name='is_what' value='edit-profile'>
					<div align='center'>
					<br /><br />
					<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' />
					<br />
					</div>
					</form>
					</fieldset>
					";
				}
				else {
					doBox("error", $lang['admin_error_msg'], $config, $lang);
				}
		
				break;
				
			case 'change-email':
				$statement = "SELECT username, email FROM {$config['table_prefix']}users WHERE uid={$_GET['m']}";
				$result = mysql_query($statement);
				$row = mysql_fetch_array($result);
			
				if(mysql_num_rows($result) > 0) {
					$status = $_GET['status'];
					if(isset($status) && $status == '-1')
						echo "<br /><br /><div align='center'><span style='color:red'>{$lang['emails_not_the_same_msg']}</span></div>";
					elseif(isset($status) && $status == '-2')
						echo "<br /><br /><div align='center'><span style='color:red'>{$lang['incorrect_pass_msg']}</span></div>";
					elseif(isset($status) && $status == '1')
						echo "<br /><br /><div align='center'><i>{$lang['admin_success_msg']}</i></div>";
			
					echo "<fieldset class='default'><legend>{$lang['my_nav_change-email']}&nbsp;</legend>
					<form name='frmChangeEmail' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
						<table width='100%' cellpadding='2' cellspacing='2'>
						<tr><td width='100%' colspan='2'>{$lang['email_current']} <b>{$row['email']}</b><br /><br /></td></tr>
						<tr><td width='50%'>{$lang['email_new']} </td><td width='50%'><input type='text' name='email_new' value='' class='textBox' style='width:96%' maxlength='64' /></td></tr>
						<tr><td width='50%'>{$lang['email_new_confirm']} </td><td width='50%'><input type='text' name='email_new_confirm' value='' class='textBox' style='width:96%' maxlength='64' /></td></tr>
						<tr><td width='50%'>{$lang['password']} </td><td width='50%'><input type='password' name='password_current' value='' class='textBox' style='width:96%' maxlength='12' /></td></tr>
	
						</table>
						<div align='center'>
						<input type='hidden' name='uid' value='{$_GET['m']}' />
						<input type='hidden' name='is_what' value='change-email' />
						<br /><br /><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' /><br /></div>
					</form>
					</fieldset>
					";
				}
				else {
					doBox("error", $lang['admin_error_msg'], $config, $lang);
				}
				
				break;
			case 'change-pass':
				$status = $_GET['status'];
				if(isset($status) && $status == '-1')
					echo "<br /><br /><div align='center'><span style='color:red'>{$lang['passwords_not_the_same_msg']}</span></div>";
					if(isset($status) && $status == '-2')
					echo "<br /><br /><div align='center'><span style='color:red'>{$lang['incorrect_pass_msg']}</span></div>";
				elseif(isset($status) && $status == '1')
					echo "<br /><br /><div align='center'><i>{$lang['admin_success_msg']}</i></div>";
			
				echo "<fieldset class='default'><legend>{$lang['my_nav_change-pass']}&nbsp;</legend>
				<form name='frmChangePass' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
					<table width='100%' cellpadding='2' cellspacing='2'>
					<tr><td width='50%'>{$lang['password_old']} </td><td width='50%'><input type='password' name='password_old' value='' class='textBox' style='width:96%' maxlength='12' /></td></tr>
					<tr><td width='50%'>{$lang['password_new']} </td><td width='50%'><input type='password' name='password_new' value='' class='textBox' style='width:96%' maxlength='12' /></td></tr>
					<tr><td width='50%'>{$lang['password_repeat']} </td><td width='50%'><input type='password' name='password_repeat' value='' class='textBox' style='width:96%' maxlength='12' /></td></tr>
	
					</table>
					<input type='hidden' name='uid' value='{$_GET['m']}' />
					<input type='hidden' name='is_what' value='change-pass' />
					<br /><br />
					<div align='center'>
					<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' />
					<br />
					</div>
				</form>
				</fieldset>
				";
	
				break;
			default:
		}
	
		echo "</td></tr>
		</table>
		";
	}
	else {
			doBox("error", $lang['admin_error_msg'], $config, $lang);
	}
}

showJumpToCatForm($config, $lang);

include('foot.php');

?>