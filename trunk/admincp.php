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
 
 File: admincp.php
 Description: -
 Random quote: "If you don't have something unique to offer, know
 that you're disposable."  
*******************************************************************/

session_start();
session_cache_expire(360);
require_once("config.php");
require_once("functions/f_admin.php");
require_once("functions/f_global.php");
require_once("functions/f_cats.php");
require_once("functions/f_db.php");

//only allow access to admins
if(!loggedIn($config) || !adminStatus($config))   doBox("error", $lang['admin_guest_msg'], $config, $lang);

if ($_POST['submit'] || $_POST['submitAdd'] || $_POST['submitRemove']) {  
	// -----------------------
	// Processings: on submit
	// -----------------------
  
	//validate inputs if 'config'
	if($_POST['is_what'] == 'config') {
		//numeric fields
		$fields = array("{$_POST['perpage']}","{$_POST['lastadded_perpage']}","{$_POST['random_perpage']}","{$_POST['max_filesize']}","{$_POST['max_pic_width']}",
		"{$_POST['max_pic_height']}","{$_POST['jpeg_quality']}","{$_POST['thumb_width']}","{$_POST['thumb_height']}","{$_POST['thumbs_perrow']}","{$_POST['script_timeout']}",
		);	  
		
		//are any non-numeric?  If so, returns "error", else take $is_what from $_POST
		$is_what = validateNumericFields($fields);
		if($is_what == "error")   $_POST['is_what'] = "error";
	}
  
	switch($_POST['is_what']) {
		case 'error':
			//print "<meta http-equiv='Refresh' content='0; url=admincp.php?do=config&status=error'>";
			header("Location: admincp.php?do=config&status=error");
			break;
	
		case 'new-user':
			$status = doAdminNewUser($config);
			header("Location: admincp.php?do=new-user&status=$status");
			break;
			
		case 'user-man':
			$status = doAdminUserMan($config);
			header("Location: admincp.php?do=user-man&status=$status");
			break;
	  
		case 'user-del':
			$status = doAdminUserDel($config);
			header("Location: admincp.php?do=user-del&status=$status");
			break;
	  
		case 'cat-edit':
			$status = doAdminCatEdit($config);
			header("Location: admincp.php?do=cat-edit&status=$status");
			break;
	  
		case 'cat-man':
			$status = doAdminCatMan($config);
			header("Location: admincp.php?do=cat-man&status=$status");
			break;
			
		case 'cat-permissions':
			$status = doAdminCatPermissions($config);
			header("Location: admincp.php?do=cat-permissions&status=$status");
			break;
			
		case 'cat-rebuild':
			$status = doAdminCatRebuild($config);
			header("Location: admincp.php?do=cat-rebuild&status=$status");
			break;
			
		case 'cat-order':
			$status = doAdminCatOrder($config);
			header("Location: admincp.php?do=cat-order&status=$status");
			break;
	  
		case 'approve':
			doAdminApprove($config);
			header("Location: admincp.php?do=approve");
			break;

		case 'approve-comments':
			doAdminApproveComment($config);
			header("Location: admincp.php?do=approve-comments");
			break;
			
		case 'approve-users':
			doAdminApproveUser($config);
			header("Location: admincp.php?do=approve-users");
			break;

		case 'batch-del':
			$append = doAdminBatchDel($config, $lang);
			header("Location: admincp.php?do=batch-del$append");
			break;
			
		case 'batch-add':
			doAdminBatchAdd($config, $lang);
			header("Location: admincp.php?do=batch-add&show=10");
         	break;
			
		case 'recalc-users':
			$append = doAdminRecalcUsers($config);
			header("Location: admincp.php?do=recalc-users$append");
			break;
			
		case 'rebuild-thumbs':
			$cycleLength = $_POST['cycleLength'];
			$next = $_POST['next'];
			$append = doAdminRebuildThumbs($config, $lang, $cycleLength, $next);
			header("Location: admincp.php?do=rebuild-thumbs$append");
			break;
			
		case 'define-new-fields':
			$status = doAdminDefineNewFields($config, $lang);
			header("Location: admincp.php?do=define-new-fields&status=$status");
         	break;
	  
		case 'edit-login':
			$status = doAdminEditLogin($config);
			header("Location: admincp.php?do=edit-login&status=$status");
			break;
	  
		case 'optimize':
			$status = doAdminOptimize($config);
			header("Location: admincp.php?do=optimize&status=$status");
			break;
	  
		case 'ban':
			$status = doAdminBan($config);
			header("Location: admincp.php?do=ban&status=$status");
			break;
	 
		case 'config':
			$status = doAdminConfig($config, $sql_pass); //changed in v0.8.5
			header("Location: admincp.php?do=config&status=$status");
			break;
	  
		case 'lang-man':
			$status = doAdminLang($config);
			header("Location: admincp.php?do=lang-man&status=$status");
			break;

      default:
   }
}
elseif($_POST['submit_pass']) {
	$password = dealWithSingleQuotes(charsEscaper($_POST['password_confirm'],$config['escape_chars']));
	$username = dealWithSingleQuotes(charsEscaper($_POST['username'],$config['escape_chars']));
	$statement = "SELECT password FROM {$config['table_prefix']}users WHERE username='$username'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	
	if(crypt($password, $row['password']) == $row['password']) {
		//create session then redirect
		$_SESSION["{$config['cookie_prefix']}_$username"] = "1";
		
		//$redirect = "Location: admincp.php?do=config";
		$redirect = "Location: {$_POST['url']}";
		header("$redirect");
		exit();
	}
	else {
		$redirect = "Location: admincp.php?do=config&status=0";
		header("$redirect");
		exit();
	}
}

if(!sessionRegistered($config) && loggedIn($config)) {
	$cookie_name_username = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
	$head_aware['onload'] = "document.frmConfirmLogin.password_confirm.focus();";
	require_once('head.php');
	
	//store the current full URL
	if($_GET['do'] == '')   $goto = "admincp.php?do=config";
	else   $goto = "admincp.php?do={$_GET['do']}";
	
	//check pass
	echo "<form name='frmConfirmLogin' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post' style='margin-top:0;padding-top:0'>
	<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
	<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['head_confirm_pass']}</td></tr>
	<input type='hidden' name='username' value='{$_COOKIE[$cookie_name_username]}' />
	<tr><td width='100%' align='center'><input type='password' name='password_confirm' class='textBox' style='width:60%' maxlength='12' />
	<input type='text' class='textBox' name='crypto' value='' style='width:.1em;visibility:hidden' disabled='disabled' />
	</td></tr>
	<tr><td width='100%' align='center' class='cell_foot'><input type='submit' name='submit_pass' value='{$lang['button_login']}' class='submitButton2' /></td></tr>
	<input type='hidden' name='url' value='$goto' />
	</table>
	</form>
	";
	exit();
}
else {
	//output head
	require_once('head.php');
	require_once('functions/f_db.php');
	
	echo "<table class='table_layout_admin' cellpadding='2' cellspacing='0' >
	<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2'>{$lang['head_admincp']}</td></tr>
	<tr>
	<td width='195' valign='top' style='border:1px solid;border-color:#666666 #cccccc #666666 #666666;padding:0 3px;'>
	   <table width='195' cellspacing='0' cellpadding='0'>
		  <tr>
		  <td width='100%'>";
			 include("admin_navbar.php");
			 echo "</td>
		  </tr>
	   </table>
	</td>
	<td width='100%' valign='top' style='border:1px solid;border-color:#666666 #666666 #666666 #cccccc;padding:0 3px;'>
	";
	
	$do = $_GET['do'];
	
	//since batch-del contains GET data...
	if(strstr($do,"batch-del"))   $do = "batch-del";
	
	// -----------------------
	// Selections: Show on click
	// -----------------------
	switch($do) {
		case 'new-user':
			$status = $_GET['status'];
			if(isset($status) && $status != '0')
				printf("<br /><br /><div align='center'><i>{$lang['new_user_success_msg']}</i></div>",stripslashes(stripslashes($status)));
			elseif(isset($status) && $status == '0')
				echo "<br /><br /><div align='center'><span style='color:red'><i>{$lang['new_user_error_msg']}</i></span></div>";
			else {
				echo "<br /><br /><div align='left'>{$lang['intro_new-user']}</div>";
			}
			
			echo "<fieldset class='default'>
				<legend>{$lang['admincp_nav_new_user']}&nbsp;</legend>
				<table class='table_layout'>
				<form name='frmNew-user' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='new-user' />
				<tr><td><b>{$lang['username']}</b> </td><td><input type='text' name='username' class='spoiltTextBox' maxlength='32' /></td></tr>
				<tr><td><b>{$lang['password']}</b> </td><td><input type='password' name='password' class='spoiltTextBox' maxlength='12' /></td></tr>
				<tr><td><b>{$lang['email']}</b> </td><td><input type='text' name='email' class='spoiltTextBox' maxlength='64' /></td>
				<tr><td>&nbsp;</td><td><input type='checkbox' name='admin_status' /> {$lang['is_new_user_admin']}</td></tr>
				<tr><td colspan='2' align='center'><input type='submit' name='submit' value='{$lang['button_add']}' class='submitButton2' /></td></tr>
				</table></form>
				</fieldset>";
	
			break;
			
		case 'user-del':
			if(isset($_GET['u']) && is_numeric($_GET['u'])) {
					$status = doAdminUserDel($config, $_GET['u']);
			}
			
			echo "<br /><br /><div align='left'>{$lang['intro_user-del']}</div>
			<fieldset class='default'>
			<legend>{$lang['admincp_nav_userdel']}&nbsp;</legend>";
			
			$user = getUsers($config);
			if(sizeof($user)>0) {
				echo "<br /><table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['user_existing']}</td></tr>";
				
				$i=0;
				foreach($user as $key => $value) {
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					echo "<tr><td width='40%' class='$rowcolor'>$value</td>";
					//The root admin account cannot be deleted
					if($key != "0" && $key != "1")   echo "<td width='60%' align='right' class='$rowcolor'><a href='admincp.php?do=user-del&u=$key' onclick=\"return confirm('{$lang['delete_confirm']}');\">{$lang['delete']}</a></td></tr>";
					else   echo "<td width='60%' align='right' class='$rowcolor'><i>{$lang['delete_cannot']}</i></td></tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				echo "</table>";
			}
			else {	
				echo "<br /><br /><div align='center'><i>{$lang['user-del_no_msg']}</i></div>
				</fieldset>";
			}
			
			break;
			
		case 'user-man':
			$status = $_GET['status'];
			if(isset($status) && $status == '1')
				echo "<br /><br /><div align='center'><i>{$lang['user_man_success_msg']}</i></div>";
			elseif(isset($status) && $status == '0')
				echo "<br /><br /><div align='center'><span style='color:red'><i>{$lang['admin_error_msg']}</i></span></div>";
			elseif(isset($status)) {
				$msg = sprintf($lang['pass_too_short_msg'],$status);
				echo "<br /><br /><div align='center'><span style='color:red'><i>$msg</i></span></div>";
			}
			else {
				echo "<br /><br /><div align='left'>{$lang['intro_user-man']}</div><fieldset class='default'>
				<legend>{$lang['admincp_nav_userman']}&nbsp;</legend>";
			}
			
			$statement = "SELECT uid, username, admin_status, email FROM {$config['table_prefix']}users";
			$result = mysql_query($statement);
	
			if(mysql_num_rows($result) > 0) {
				echo "<br /><form name='frmUser-man' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='user-man' />
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td width='40%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				<td width='30%' align='center' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['is_admin']}</span></td>
				<td width='30%' align='center' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['reset_password']}</span></td>
				</tr>";
	
				$i=0;
				while($row = mysql_fetch_array($result)) {
					$at = "profile.php?m=".$row['uid'];
					$at_edit = "my.php?m=".$row['uid'];
	
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					echo "<tr><td width='40%' class='$rowcolor' style='height:40px'><a href='$at' target='_blank'><b>{$row['username']}</b></a> (<a href='admincp.php?do=edit-user&uid={$row['uid']}' onclick=\"window.open('edit_user.php?uid={$row['uid']}','picman','width=600,height=390,resizable=yes');return false\" target='_blank'><i>{$lang['edit']}</i></a>)<br /><span class='tiny_text_dark'>{$row['email']}</span></td>";
				
					//The root admin account cannot be deleted
					if($row['uid'] != "0" && $row['uid'] != "1") {
						if($i == 0)   $rowcolor = "rowcolor1";
						else   $rowcolor = "rowcolor2";
					
						if($row['admin_status'] == "0")   echo "<td width='30%' align='center' class='$rowcolor'><input type='radio' name='admin[{$row['uid']}]' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='admin[{$row['uid']}]' value='1' />{$lang['yes']}</td>";
						else   echo "<td width='30%' align='center' class='$rowcolor'><input type='radio' name='admin[{$row['uid']}]' value='0' />{$lang['no']}&nbsp;<input type='radio' name='admin[{$row['uid']}]' value='1' checked />{$lang['yes']}</td>";
						
						echo "<td width='30%' align='center' class='$rowcolor'><input type='text' class='spoiltTextBox' maxlength='12' name='password[{$row['uid']}]' ondblclick=\"this.value=genPass()\" /></td>";
						
						echo "</tr>";
					}
					else {
						echo "<td width='30%' align='center'><input type='hidden' name='admin[{$row['uid']}]' value='1' /><i>{$lang['root']}</i></td>";
						echo "<td width='30%' align='center'><input type='text' class='textBox' maxlength='12' style='width:95%' name='password[{$row['uid']}]' ondblclick=\"this.value=genPass()\" /></td>";
						echo "</tr>";
					}
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				echo "</table><br />
				<div align='center'><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' /></div>
				</form>";
			}
			else {	
				echo "<br /><br /><div align='center'><i>{$lang['user-del_no_msg']}</i></div>
				</fieldset>";
			}
			
			break;
			
		case 'cat-edit':
			$status = $_GET['status'];
			if(isset($status) && $status==1)
				$_SESSION['catedit_msg'] = "<br /><br /><div align='center'><i>{$lang['catedit_success_msg']}</i></div>";
			elseif(isset($status) && $status==0)
				$_SESSION['catedit_msg'] = "<br /><br /><div align='center'><i>{$lang['admin_error_msg']}</i></div>";
			
			if(isset($_SESSION['catedit_msg'])) {
				echo $_SESSION['catedit_msg'];
				unset($_SESSION['catedit_msg']);
			}
			else {
				echo "<br /><br /><div align=left>{$lang['intro_cat-edit']}</div>";
			}
			
			$statement = "SELECT cid, downloadable, cname, parent FROM {$config['table_prefix']}categories ORDER BY cname";
			$result = mysql_query($statement);
	
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_catedit']}&nbsp;</legend>";
			
			if(mysql_num_rows($result) > 0) {
				echo "<form name='frmCat-edit' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='cat-edit' />
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
			   <td width='30%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'> </span></td>
			   <td width='20%' class='dark_cell' align='center' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['browser_category']}</span></td>
			   <td width='20%' class='dark_cell' align='center' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['parent_category']}</span></td>
			   <td width='30%' class='dark_cell' align='center' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['allow_cat_download']}</span></td>";
	
				$cats = getCatsTreeView(0,1,$config,$lang);
				
				$i = 0;
				while($row = mysql_fetch_array($result)) {
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					//cat parent
					echo "<tr><td width='30%' class='$rowcolor'><span class='tiny_text_dark'> <b>{$row['cname']}:</b> </span></td>";
					
					//cat name
					if($row['cname'] != 'Other')   echo "<td width='20%' class='$rowcolor' align='center'><input type='text' name='{$row['cid']}' size='20' value=\"{$row['cname']}\" class='textBox' maxlength='32'>";
					else   echo "<td width='20%' class='$rowcolor' align='center'><input type='text' name='{$row['cid']}' size='20' value=\"{$row['cname']}\" class='textBox' readonly='readonly' maxlength='32'>";
				
					echo "<td width='20%' class='$rowcolor' align='center'><select name='parent[{$row['cid']}]' class='dropdownBox'>";
					foreach($cats as $key => $value) {
						if($value == 'Other') continue; //don't show Other
						if($key == 0) $key  = -1;
						if($row['parent'] == $key)   echo "<option value='$key' selected>";	else   echo "<option value='$key'>";
						echo "$value
						</option>";
					}
					echo "</select>";
				
					echo "</td><td width='30%' class='$rowcolor' align='center'>";
					if($row['downloadable'] == '0')   echo "<input type='radio' name='downloadable[{$row['cid']}]' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='downloadable[{$row['cid']}]' value='1' />{$lang['yes']}";
					elseif($row['downloadable'] == '1')   echo "<input type='radio' name='downloadable[{$row['cid']}]' value='0' />{$lang['no']}&nbsp;<input type='radio' name='downloadable[{$row['cid']}]' value='1' checked />{$lang['yes']}";
					else   echo "<input type='radio' name='downloadable[{$row['cid']}]' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='downloadable[{$row['cid']}]' value='1' />{$lang['yes']}";
					echo "</td></tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				
				echo "</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2'>
				</div>
				</form>
				</fieldset>";
			}
			else {
				echo "<br /><i>{$lang['cat-edit_no_msg']}</i></fieldset>";
			}
				
			break;
			
		case 'cat-man':
			//if user chose to delete category
			if(isset($_GET['c']) && is_numeric($_GET['c'])) {
				$status = doAdminCatDel($config);
			}
				
			$cat = getCats(1,$config,$lang);
			@natcasesort($cat);
			$cat[] = "Other";
			
			echo "<br /><br /><div align=left>{$lang['intro_cat-man']}</div><fieldset class='default'>
			<legend>{$lang['admincp_nav_catman']}&nbsp;</legend>
			<form name='frmLang-man' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='cat-man'>
			<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
			<tr>
			<td width='100%' colspan='3' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['cat_existing']}
			</td></tr>";
			
			$i=0;
			foreach($cat as $key => $value) {
				if($i == 0)   $rowcolor = "rowcolor1";
				else   $rowcolor = "rowcolor2";
				
				echo "<tr><td width='75%' colspan='2' class='$rowcolor'>$value</td>";
				if($value != "Other")   echo "<td width='25%' align='right' class='$rowcolor'><a href='admincp.php?do=cat-man&c=$key' onclick=\"return confirm('{$lang['delete_confirm']}');\">{$lang['delete']}</a></td></tr>";
				else   echo "<td width='25%' align='right' class='$rowcolor'><i>{$lang['delete_cannot']}</i></td></tr>";
				
				if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
			}
			
			echo "<tr>
				   <td width='40%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['cat_add']}</td>
					<td width='35%' class='dark_cell' align='center' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['parent_category']}</span></td>
				   <td width='25%' class='dark_cell' align='right' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['allow_cat_download']}</span></td></tr>";
	
			//add new cat rows
			for($i = 0;$i<4;$i++) {
				echo "<tr><td width='40%'>{$lang['name']} <input type='text' name='category[$i]' class='textBox' maxlength='32' style='width:70%'></td>
				<td width='35%' align='center'>";
				
				$cats = getCatsTreeView(0,1,$config,$lang);

				echo "<select name='parent[$i]' class='dropdownBox'>";
				foreach($cats as $key => $value) {
					if($value == 'Other') continue; //don't show Other
					if($key == 0) $key  = -1;
					echo "<option value='$key'>
					$value
					</option>";
				}
				echo "</select>";
					
				echo "</td>
			   <td width='25%' align='right'><input type='radio' name='downloadable[$i]' value='0' />{$lang['no']}&nbsp;<input type='radio' name='downloadable[$i]' value='1' checked />{$lang['yes']}</td></tr>";
			}
			
			echo "</table>
			<br />
			<div align='center'>
			<input type='submit' name='submit' value='{$lang['button_add']}' class='submitButton2'>
			</div>
			</form>
			</fieldset>";
			break;
			
		case 'cat-permissions':
			$status = $_GET['status'];
			
			$statement = "SELECT cid, cname, cshow FROM {$config['table_prefix']}categories ORDER BY cname";
			$result = mysql_query($statement);
	
			echo "<br /><br /><div align=left>{$lang['intro_cat-permissions']}</div><fieldset class='default'><legend>{$lang['admincp_nav_permissions']}&nbsp;</legend>";
			
			if(mysql_num_rows($result) > 0) {
				echo "<form name='frmCat-permissions' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='cat-permissions'>
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
			   <td width='70%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'> </span></td>
			   <td width='30%' class='dark_cell' align='center' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['show_to_all']}</span></td>";
	
				$i=0; //to calculate alternating row colors
				while($row = mysql_fetch_array($result)) {
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					echo "<tr><td width='70%' class='$rowcolor'><span class='tiny_text_dark' style='font-weight:bold'>{$row['cname']}:</span></td>";
					echo "<td width='30%' align='center' class='$rowcolor'>";
	
					if($row['cshow'] == '0')   echo "<input type='radio' name='cshow[{$row['cid']}]' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='cshow[{$row['cid']}]' value='1' />{$lang['yes']}";
					elseif($row['cshow'] == '1')   echo "<input type='radio' name='cshow[{$row['cid']}]' value='0' />{$lang['no']}&nbsp;<input type='radio' name='cshow[{$row['cid']}]' value='1' checked />{$lang['yes']}";
					else   echo "<input type='radio' name='cshow[{$row['cid']}]' value='0' />{$lang['no']}&nbsp;<input type='radio' name='cshow[{$row['cid']}]' value='1' checked />{$lang['yes']}";
					echo "</td></tr>
					";
					
					//$i = fmod($i,2);
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				echo "</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2'>
				</div>
				</form>
				</fieldset>";
			}
			else {
				echo "<br /><i>{$lang['cat-edit_no_msg']}</i></fieldset>";
			}
				
			break;
	
		case 'cat-rebuild':
			$status = $_GET['status'];
			if(isset($status) && $status==1)
				printf("<div align='center'><br />{$lang['cat_rebuild_report']}</div>",$_SESSION['counter']);
			elseif(isset($status) && $status==0)
				echo "<br /><br /><div align='center'><i>{$lang['admin_error_msg']}</i></div>";
			else
				echo "<br /><br /><div align='left'>{$lang['intro_cat_rebuild']}</div>";
				
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_cat_rebuild']}&nbsp;</legend>
			<form name='frmOptimize' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='cat-rebuild'>
			<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
				<td width='100%' align='center'>
					<input type='submit' name='submit' value='{$lang['button_cat_rebuild']}' class='submitButton' onclick=\"return confirm('{$lang['cat_rebuild_confirm']}');\">
				</td>
			</td></tr>
			</table>
			</fieldset>
			";

			unset($_SESSION['counter']);
			
			break;
			
		case 'cat-order':
			$status = $_GET['status'];
			
			if(isset($status) && $status==1)
				echo "<br /><br /><div align='center'><i>{$lang['admin_success_msg']}</i></div>";
			elseif(isset($status) && $status==0)
				echo "<br /><br /><div align='center'><i>{$lang['admin_error_msg']}</i></div>";
			else
				echo "<br /><br /><div align='left'>{$lang['intro_cat_order']}</div>";
				
			echo "<fieldset class='default' "; if($config['page_direction'] == "1")   echo "dir='rtl' style='text-align:right'"; echo ">
				<legend>{$lang['admincp_nav_cat_order']}&nbsp;</legend>
				<form name='frmCatOrder' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='cat-order'>
				";
			
			$cat_use_for_position_data = justGetTheDamnCats(0, 1, $config, $lang); //to get the position data for each cat
			//print_r($cat_use_for_position_data);
			
			$cat = getCatsTreeView(0,1,$config, $lang);
			foreach($cat as $key => $value) {
				if($key == 0)   continue;
				
				$pattern = '/(&nbsp;)*/';
				preg_match($pattern, $value, $matches);
				$value = preg_replace($pattern, "", $value);
				$position = $cat_use_for_position_data["$key"]["position"];

				echo "{$matches[0]}<input type='text' name='$key' value='$position' style='width:26px;margin:1px' maxlength='3'> $value<br />";
			}			
			
			
			echo "<div class='search_page_cell' style='width:100%;margin-top:10px' align='center'>
					<input type='submit' name='submit' value='{$lang['button_go']}' class='submitButton'>
				</div>
				</fieldset>
				";
			
			break;
			
		case 'lang-man':
			$status = $_GET['status'];
			if(isset($status) && $status==0)
				echo "<br /><br /><div align='center'><i>{$lang['admin_error_msg']}</i></div>";
			else {
				//if user chose to delete a language file, delete it from lang directory
				if(isset($_GET['l'])) {
					if(!@unlink("lang/".$_GET['l']))   echo "<i>{$lang['lang_remove_error_msg']}</i>";
				}
			
				echo "<fieldset class='default'>
				<legend>{$lang['admincp_nav_langman']}&nbsp;</legend>
				<form name='frmLang-man' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='lang-man'>
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['lang_existing']}</td></tr>";
				
				$lang_modules = getLangs($config);
				$i=0;
				foreach($lang_modules as $key => $value) {
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					echo "<tr><td width='40%' class='$rowcolor'>lang/$value</td>";
					if($value != "english.php")   echo "<td width='60%' align='right' class='$rowcolor'><a href='admincp.php?do=lang-man&l=$value' onclick=\"return confirm('{$lang['delete_confirm']}');\">{$lang['delete']}</a></td></tr>";
					else   echo "<td width='60%' align='right' class='$rowcolor'><i>{$lang['delete_cannot']}</i></td></tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				
				echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url({$config['stylesheet']}/images/td_back_mid.gif)'>{$lang['lang_add']}</td></tr>
				<tr><td width='40%'>{$lang['add_new_language']}</td>
				<td width='60%' align='right'>
				<input type='file' name='lang_upload' class='spoiltTextBox' maxlength='255'></td></tr>
				</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_add']}' class='submitButton2'>
				</div>
				</form>
				</fieldset>";
			}
			break;
			
		case 'approve':
			$statement = "SELECT pid, ip, file_name, ROUND(file_size/1024) AS fsize, file_type, title, date AS d, pic_width, pic_height, keywords, approved, category FROM {$config['table_prefix']}pictures WHERE approved=0 ORDER BY date";
			$result = mysql_query($statement);
			
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_pending_pics']}&nbsp;</legend>";
			
			if(mysql_num_rows($result) > 0) {
				echo "<form name='frmApprove' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='approve'>
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' border='0'>
				<tr><td width='100%' align='right'>
				<a href='#' onClick=\"selectDropdownValue(document.frmApprove, 0);\">{$lang['approve_all']}</a>,  
				<a href='#' onClick=\"selectDropdownValue(document.frmApprove, 2);\">{$lang['leave_all']}</a>, 
				<a href='#' onClick=\"selectDropdownValue(document.frmApprove, 1);\">{$lang['delete_all']}</a>
				</td></tr>
				</table>
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td width='10%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				<td width='20%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['title']}</span></td>
				<td width='15%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['category']}</span></td>
				<td width='45%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['keywords']}</span></td>
				<td width='10%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				</tr><tr>";
				
				$i = 0;
				while($row = mysql_fetch_array($result)) { 
					if($row['approved']==0) {
						//apologies for this extremely crude and dirty workaround.
						$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
						$row['d'] = strftime($config['long_date_format'],$row_t['t'] + $config['timezone_offset']);

						$row_cid_to_cname = mysql_fetch_array(mysql_query("SELECT cname FROM {$config['table_prefix']}categories WHERE cid='{$row['category']}'"));

						//doAdminShowEntry($lang, $row, $config['upload_dir']);
						//$at = $config['upload_dir'].$row['file_name'];//updated as of v0.8.4
						$at = getPicturesPath($config, $row['file_name'], "0");
						$at_server = getPicturesPath($config, $row['file_name'], "1");
						
						//added in v0.9.4
						if(file_exists($at_server.".{$config['pending_pics_suffix']}"))   $at .= ".{$config['pending_pics_suffix']}";
												
						//new in ZVG, is this file a video?  if so, don't show a thumbnail for it
						if(isVideo($config,"",$at))   $thumb="{$lang['media']}";
						else   $thumb="<img src='thumbify.php?pic={$row['file_name']}&w=50&h=33&folder=uploads&smart_resize=1' alt='{$row['file_name']}' title='{$row['file_name']}' class='thumb' border='0' />";
						
						if($i == 0)   $rowcolor = "rowcolor1";
						else   $rowcolor = "rowcolor2";
					
						echo "<td width='10%' class='$rowcolor' style='height:40px'><a href='$at' target='_blank'>$thumb</span></a></td>
						<td width='20%' class='$rowcolor'><span title='{$lang['submitted_by']} {$row['ip']} {$lang['posted_on']} {$row['d']}'>{$row['title']}</span></a></td<td width='10%' class='$rowcolor'><i>{$row_cid_to_cname['cname']}</i></td>
						<td width='50%' class='$rowcolor'>{$row['keywords']}</td>
						<td width='10%' align='right' class='$rowcolor'>";

						
						echo "<select name='{$row['pid']}' class='dropdownBox'>
						<option value='2'>{$lang['leave']}</option>
						<option value='0' />{$lang['approve']}</option>
						<option value='1' />{$lang['delete']}</option>
						</select>";
			
						echo "</td></tr>";
					}
					else
					  continue;
		
					echo "<tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				
				echo "</tr>
				</td></tr>
				</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2'>
				</div>
				</form></fieldset>";
			}
			else {
				echo "<br /><br /><div align='center'><i>{$lang['approval_no_msg']}</i></div></fieldset>";
			}
			break;
	
		case 'approve-comments':
			//$statement = "SELECT comment_id, pid, comment_author, comment_date AS d, comment_body, comment_ip, approved FROM {$config['table_prefix']}comments WHERE approved=0 ORDER BY comment_date";
			$statement = "SELECT c.comment_id, c.pid, c.comment_author, c.comment_date AS d, c.comment_body, c.comment_ip, c.approved, p.file_name FROM {$config['table_prefix']}comments AS c INNER JOIN {$config['table_prefix']}pictures AS p ON p.pid = c.pid WHERE c.approved=0 ORDER BY c.comment_date";
			$result = mysql_query($statement);
			
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_pending_comments']}&nbsp;</legend>";

			if(mysql_num_rows($result) > 0) {
				echo "<form name='frmApproveComments' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='approve-comments' />
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' border='0'>
				<tr><td width='100%' align='right'>
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveComments, 0);\">{$lang['approve_all']}</a>,  
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveComments, 2);\">{$lang['leave_all']}</a>, 
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveComments, 1);\">{$lang['delete_all']}</a>
				</td></tr>
				</table>
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td width='10%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				<td width='20%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['author']}</span></td>
				<td width='60%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['comment']}</span></td>
				<td width='10%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				</tr><tr>";
				
				$i=0;
				while($row = mysql_fetch_array($result)) { 
					if($row['approved']==0) {
						//apologies for this extremely crude and dirty workaround.
						$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
						$row['d'] = strftime($config['long_date_format'],$row_t['t'] + $config['timezone_offset']);
						
						$at = getPicturesPath($config, $row['file_name'], "0");
						//new in ZVG, is this file a video?  if so, don't show a thumbnail for it
						if(isVideo($config,"",$at))   $thumb="{$lang['media']}";
						else   $thumb="<img src='thumbify.php?pic={$row['file_name']}&w=50&h=33&folder=uploads&smart_resize=1' alt='{$row['file_name']}' title='{$row['file_name']}' class='thumb' border='0' />";

						$at = "pic_details.php?pid=".$row['pid'];
	
						if($i == 0)   $rowcolor = "rowcolor1";
						else   $rowcolor = "rowcolor2";
						
						echo "<td width='10%' class='$rowcolor' style='height:40px'><a href='$at' target='_blank'>$thumb</span></a></td>
						<td width='20%' class='$rowcolor'><i><span title='{$lang['submitted_by']} {$row['comment_ip']}'>{$row['comment_author']}</span></i></td>
						<td width='60%' class='$rowcolor'>{$row['comment_body']}</td><td width='10%' align='right' class='$rowcolor'>";
						
						echo "<select name='{$row['comment_id']}' class='dropdownBox'>
						<option value='2'>{$lang['leave']}</option>
						<option value='0' />{$lang['approve']}</option>
						<option value='1' />{$lang['delete']}</option>
						</select>";
			
						echo "</td></tr>";
					}
					else
					  continue;
		
					echo "<tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				
				echo "</tr>
				</td></tr>
				</table>
				<br />
				<div align='center'><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2'></div>
				</form>
				</fieldset>";
			}
			else {
				echo "<br /><br /><div align='center'><i>{$lang['approval_comment_no_msg']}</i></div></fieldset>";
			}
			break;
			
		case 'approve-users':
			$statement = "SELECT uid, username, email, register_ip, join_date AS d FROM {$config['table_prefix']}users WHERE approved=0 ORDER BY join_date";
			$result = mysql_query($statement);
			
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_pending_users']}&nbsp;</legend>";
			
			if(mysql_num_rows($result) > 0) {
				echo "<form name='frmApproveUsers' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='approve-users'>
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' border='0'>
				<tr><td width='100%' align='right'>
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveUsers, 0);\">{$lang['approve_all']}</a>,  
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveUsers, 2);\">{$lang['leave_all']}</a>, 
				<a href='#' onClick=\"selectDropdownValue(document.frmApproveUsers, 1);\">{$lang['delete_all']}</a>
				</td></tr>
				</table>
				
				<table width='95%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td width='30%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['username']}</span></td>
				<td width='50%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['email']}</span></td>
				<td width='10%' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'></td>
				</tr><tr>";
				
				$i=0;
				while($row = mysql_fetch_array($result)) { 
					if($row['approved']==0) {
						//apologies for this extremely crude and dirty workaround.
						$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
						$row['d'] = strftime($config['long_date_format'],$row_t['t'] + $config['timezone_offset']);
	
						$at = "profile.php?m=".$row['uid'];
	
						if($i == 0)   $rowcolor = "rowcolor1";
						else   $rowcolor = "rowcolor2";
					
						echo "<td width='30%' class='$rowcolor' style='height:40px'><i><span title='{$row['d']}'><a href='$at' target='_blank'>{$row['username']}</a></span> ({$row['register_ip']})</i></td>
						<td width='50%' class='$rowcolor'>{$row['email']}</td>
						<td width='10%' align='right' class='$rowcolor'>";
						
						echo "<select name='{$row['uid']}' class='dropdownBox'>
						<option value='2'>{$lang['leave']}</option>
						<option value='0' />{$lang['approve']}</option>
						<option value='1' />{$lang['delete']}</option>
						</select>";
			
						echo "</td></tr>";
					}
					else
					  continue;
		
					echo "<tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}
				
				echo "</tr>
				</td></tr>
				</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2'>
				</div>
				</form></fieldset>";
			}
			else {
				echo "<br /><br /><div align='center'><i>{$lang['approval_user_no_msg']}</i></div></fieldset>";
			}
			break;
			
		case 'batch-del':
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_batchdel']}&nbsp;</legend>";
			echo "<form name='frmBatchDelChooseCat' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='batch-del'>
	
			<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
			<tr><td>{$lang['category']} <select name='category' class='dropdownBox'>";
			//$cat = getCats(2,$config,$lang);
			$cat = getCatsTreeView(0,1,$config, $lang);
			foreach($cat as $key => $value) {
				if($key == 0)   $key = -1;
				echo "<option value='$key'";
				if($key == $_GET['c'])   echo " selected";
				echo ">$value</option>";
			}			
			echo "</select>&nbsp;&nbsp;&nbsp;{$lang['show_thumbs']} <input type='radio' name='show_thumbs' value='0' checked />{$lang['no']}&nbsp;<input type='radio' name='show_thumbs' value='1' />{$lang['yes']}</td>
			<td align='right'><input type='submit' name='submit' value='{$lang['button_go']}' class='submitButton'></td></tr></table></form>";
	
			//if user submitted pictures for deletion, print one-line report
			if(isset($_SESSION['batchdel_success_counter'])) {
			   printf("<br />{$lang['batchadd_report_delete']}",$_SESSION['batchdel_success_counter']);
			   unset($_SESSION['batchdel_success_counter']);
			}
	
			//show only if user chose a category
			if(isset($_GET['c']) && is_numeric($_GET['c'])) {
				echo "<form name='frmBatchDel' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='batch-del'>
				
				<table width='100%' align='center' cellpadding='2' cellspacing='0' border='0'>
				<tr><td width='100%' align='right'>
				<a href='#' onClick=\"selectOrClearAll(document.frmBatchDel);\">{$lang['select_clear_all']}</a></td></tr>
				</table>
				
				<table background='#cccccc' width='100%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['browser_title']}</span></td>";
				
				if($_GET['st'])   echo "<td width='60%' align='center' class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['browser_picture']}</span></td>";
				echo "<td class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ");text-align:center' width='20%'><span class='subhead'>{$lang['delete']}</span></td></tr>";
				$statement = "SELECT pid,file_name FROM {$config['table_prefix']}pictures WHERE category='{$_GET['c']}' ORDER BY file_name";
				$result = mysql_query($statement);
				
				$i=0;
				while($row = mysql_fetch_array($result)) {
				    $at_full = getPicturesPathFromCategory($config, $_GET['c'], 0,"") . $row['file_name'];
					$at_full_server = getPicturesPathFromCategory($config, $_GET['c'], 1,"") . $row['file_name'];
					$thumb = getThumbPath($at_full, $at_full_server, $config['thumb_suffix']);
					
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";
					
					echo "<tr>
					<td class='$rowcolor'><a href='{$thumb[0]}' target='_blank'>{$row['file_name']}</a></td>";

					if($_GET['st']) {
						//new in ZVG, is this file a video?  if so, don't show a thumbnail for it					
						if(isVideo($config,"",$at_full_server))   $the_thumb="{$lang['media']}";
						else   $the_thumb="<img src='{$thumb[0]}' alt='{$row['file_name']}' title'{$row['file_name']}' class='thumb' />";
						
						echo "<td width='60%' align='center' class='$rowcolor'><a href='$at_full' class='thumb_link' target='_blank'>$the_thumb</a></td>";
					}

					echo "<td width='20%' align='center' class='$rowcolor'><input type='checkbox' name='do_what[{$row['pid']}]'>
					<input type='hidden' name='file_name[{$row['pid']}]' value='{$row['file_name']}' /></td>
					</tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}//end while

/*
				echo "<tr>
				<td class='$rowcolor'><a href='{$thumb[0]}' target='_blank'>{$row['file_name']}</a></td>";
				if($_GET['st'])   echo "<td width='60%' align='center' class='$rowcolor'><img src='{$thumb[0]}' alt='{$row['file_name']}'></td>";
				echo "</tr>";
*/
	
				echo "</table>
				<br />
				<div align='center'>
				<input type='submit' name='submit' value='{$lang['button_delete_checked']}' class='submitButton3' onclick=\"return confirm('{$lang['delete_confirm']}');\">
				</div>
				</form></fieldset>";
			}
			break;
			
		case 'batch-add':
			$report_out = 0;
		   //print one-line report
			if(isset($_SESSION['success_counter']) && isset($_SESSION['counter'])) {
			   printf("<br />{$lang['batchadd_report_add']}",$_SESSION['success_counter'],$_SESSION['counter']);
			   unset($_SESSION['success_counter']); unset($_SESSION['counter']);
			   $report_out = 1;
			}
			if(isset($_SESSION['delete_counter'])) {
			   printf("<br />{$lang['batchadd_report_delete']}",$_SESSION['delete_counter']);
			   unset($_SESSION['delete_counter']);
			   $report_out = 1;
			}
			 
			//get Zenith server path (this one is only for debugging purposes)
			//$path = getCurrentPath("incoming/");
			$path = getCurrentPath2("incoming/");
	
			if($config['debug_mode'] == 1)   echo "{$lang['sever_path_is']} $path";
			 
			//print any error messages that might have occurred  
			echo "<div align='left'><span style='color:red;font-size:10px'>";
			if(isset($_SESSION['msg'])) {
				echo "<br />{$_SESSION['msg']}<br />";
				unset($_SESSION['msg']);
			}
			if(isset($_SESSION['msg_file_exists'])) {
				echo "<br />{$lang['file_exists_msg']} {$_SESSION['msg_file_exists']}<br />";
				unset($_SESSION['msg_file_exists']);
			}
			if(isset($_SESSION['msg_filesize'])) {
				echo "<br />"; printf($lang['batchadd_check5'] . " ", $config['max_filesize']); echo "{$_SESSION['msg_filesize']}<br />";
				unset($_SESSION['msg_filesize']);
			}
			if(isset($_SESSION['msg_dimensions'])) {
				echo "<br />"; printf($lang['batchadd_check4'] . " ", $config['max_pic_width'], $config['max_pic_width']); echo "{$_SESSION['msg_dimensions']}<br />";
				unset($_SESSION['msg_dimensions']);
			}
			if(isset($_SESSION['msg_valid'])) {
				echo "<br />{$lang['batchadd_check3']} {$_SESSION['msg_valid']}<br />";
				unset($_SESSION['msg_valid']);
			}
			if(isset($_SESSION['msg_filetype'])) {
				echo "<br />{$lang['batchadd_check2']} {$_SESSION['msg_filetype']}<br />";
				unset($_SESSION['msg_filetype']);
			}
			echo "</span></div>";
			if($report_out == 0)   echo "<br /><br /><div align='left'>{$lang['intro_batch-add']}</div>";
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_batchadd']}&nbsp;</legend>";
			
			$incoming_pics = getIncomingPics($config); //get incoming pics less thumbnails
			$ip = getenv("REMOTE_ADDR"); // get the ip number of the user
						
			if(sizeof($incoming_pics) > 0) {
				echo "<form name='frmBatchAdd' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='batch-add'>";
			
				//set our sentinel
				if(isset($_GET['show']))   $show = $_GET['show'];
				else   $show = sizeof($incoming_pics);
				
				if(sizeof($incoming_pics) < $show)   $show = sizeof($incoming_pics);
				
				//how many pictures are we showing
				echo "<table width='100%' cellspacing='3' cellpadding='2'><tr><td colspan='4' align='center'>";
				printf($lang['batchadd_showing'],$show,sizeof($incoming_pics));
				echo "<input type='hidden' name='temp'>
				( <a href='#' onClick=\"getShowValue();return false;\"'>{$lang['change']}</a> | <a href='{$_SERVER['PHP_SELF']}?do=batch-add'>{$lang['show_all']}</a> )</td></tr></table><br />";

				echo "<table width='100%' align='center' cellpadding='2' cellspacing='0' border='0'>
				<tr><td width='100%' align='right'>
				<a href='#' onClick=\"selectDropdownValue(document.frmBatchAdd, 0);\">{$lang['approve_all']}</a>,  
				<a href='#' onClick=\"selectDropdownValue(document.frmBatchAdd, 2);\">{$lang['leave_all']}</a>, 
				<a href='#' onClick=\"selectDropdownValue(document.frmBatchAdd, 1);\">{$lang['delete_all']}</a>
				</td></tr>
				</table>";
				
				echo "<table width='100%' align='center' cellpadding='2' cellspacing='0' style='border:1px solid #666666'>
				<tr>
				<td width='20%' class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'><span class='subhead'>{$lang['incoming_title']} (" . sizeof($incoming_pics) . ")</span></td>
				<td width='35%' class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ");align:center'><span class='subhead'>{$lang['browser_title']}</span></td>
				<td width='35%' class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ");align:center'><span class='subhead'>{$lang['browser_keywords']}</span></td>
				<td width='10%' class='light_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ");align:center'><span class='subhead'>{$lang['action']}</span></td>
				</tr>
				";
	
				$i=0;
				foreach($incoming_pics as $key => $value) {
					if($value == 'thumbs')   continue;
					if($show == 0)   break; //let's pretend I didn't use that break
					$show--;
					
				   //strip extension from value
					$bits = splitFilenameAndExtension($value);
					$value_wo = $bits[0];
	
					//4 arrays to store each part of each file
					if($i == 0)   $rowcolor = "rowcolor1";
					else   $rowcolor = "rowcolor2";

					//new in ZVG, is this file a video?  if so, don't show a thumbnail for it					
					if(isVideo($config,"",$value))   $thumb="{$lang['media']}";
					else   $thumb="<img src='thumbify.php?pic=$value&w=50&h=33&folder=incoming&smart_resize=1' alt='$value' title='$value' class='thumb' border='0' />";
					
					echo "<tr>
					  <td width='15%' class='$rowcolor'><a href='incoming/$value' target='_blank'>$thumb</a>
					  <td width='35%' align='center' class='$rowcolor'><input type='text' name='title[$value]' value='$value_wo' class='textBox' style='width:90%' maxlength='255'></td>
					  <td width='35%' align='center' class='$rowcolor'><input type='text' name='keyword[$value]' value='$value_wo' class='textBox' style='width:90%' maxlength='255'></td>
					  <td width='15%' align='center' class='$rowcolor'>";
					
					echo "<select name='do_what[$value]' class='dropdownBox'>
					<option value='0' />{$lang['approve']}</option>
					<option value='1' />{$lang['delete']}</option>
					<option value='2'>{$lang['leave']}</option>
					</select>
					<input type='hidden' name='filename[$value]' value='$value' />
					<input type='hidden' name='ip' value='$ip' /></td>
					</tr>";
					
					if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
				}//end for
				
				echo "</table>";
				echo "<br />
				<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
				<tr><td colspan='3'>{$lang['batchadd_cat']} <select name='category' class='dropdownBox'>";
				
				//$cat = getCats(2,$config,$lang);
				$cat = getCatsTreeView(0,1,$config,$lang);
				foreach($cat as $key => $value) {
					if($key == 0)   continue; //don't show a blank option at the top
					echo "<option value='$key'";
					if($value == 'Other')   echo "selected";
					echo ">$value</option>";
				}			

				echo "</select></td><td colspan='1' align='right'><input type='submit' name='submit' value='{$lang['button_go']}' class='submitButton2'></td></tr></table>";
				echo "</form></fieldset>";
			}
			else {
				echo "<br /><br /><div align='center'><i>{$lang['incoming_no_msg']}</i></div></fieldset>";
			}
			break;
			
		case 'recalc-users':
			$status = $_GET['status'];
			if(isset($status) && $status==1)   $_SESSION['recalc-users_msg'] = "<br /><br /><div align='center'><i>{$lang['user_man_success_msg']}</i></div>";
			
			if(isset($_SESSION['recalc-users_msg'])) {
				echo $_SESSION['recalc-users_msg'];
				unset($_SESSION['recalc-users_msg']);
			}
			else {
				echo "<br /><br /><div align='left'>{$lang['intro_recalc_users']}</div>";
			}
		
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_recalculate_user_stats']}&nbsp;</legend>
			<form name='frmRecalcUsers' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='recalc-users'>
			<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
			<td width='100%' align='center'><input type='submit' name='submit' value='{$lang['button_go']}' class='submitButton2'></td>
			</td></tr></table>
			</form></fieldset>";
	
			break;
			
		case 'rebuild-thumbs':
			$cycleLength = $_GET['cycleLength'];
			$next = $_GET['next'];
			$n = $_GET['n'];
			
			if($next < $n && isset($_GET['next'])) {
				echo "<fieldset class='default'><legend>{$lang['admincp_nav_rebuild_thumbs']}&nbsp;</legend><br /><br />";
				printf("<b>".$lang['rebuilt_so_far']."</b>", $_SESSION['counter']);
				echo "<br /><br />
					<form name='frmRebuildThumbs' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
					<input type='hidden' name='is_what' value='rebuild-thumbs'>
					<input type='hidden' name='cycleLength' value='$cycleLength'>
					<input type='hidden' name='next' value='$next'>
					<input type='submit' name='submit' value='{$lang['continue']}' class='submitButton'></form></fieldset>
				";
			}
			else {
				$report_out = 0;
				if(isset($_SESSION['counter'])) { //print one-line report
					printf("<div align='center'><br />{$lang['rebuild_thumbs_report']}</div>",$_SESSION['counter']);
					$report_out = 1;
					
					unset($_SESSION['counter']);
				}
				 
				//get Zenith server path (this one is only for debugging purposes)
				if($config['debug_mode'] == 1) {
					$path = getCurrentPath2("uploads/");
					echo "{$lang['sever_path_is']} $path";
				}
		
				if($report_out == 0)   echo "<br /><br /><div align='left'>{$lang['intro_rebuild_thumbs']}</div>";
				echo "<fieldset class='default'><legend>{$lang['admincp_nav_rebuild_thumbs']}&nbsp;</legend>";
				
				$pics = getFullSizePics($config, "uploads",""); //get full-size pics
	
				if(sizeof($pics) > 0) {
					echo "<form name='frmRebuildThumbs' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
					<input type='hidden' name='is_what' value='rebuild-thumbs'>
			
					<table width='100%' cellspacing='3' cellpadding='2'><tr><td width='100%' align='center'>
					{$lang['pics_detected_title']} <b>" . sizeof($pics) . "</b> &nbsp; {$lang['new_dimensions']}: <b>{$config['thumb_width']}</b> x <b>{$config['thumb_height']}</b> px &nbsp;<br />
					</td></tr></table>
					
					<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
					<tr><td width='80%'>
					{$lang['pics_per_cycle']}: <input type='text' class='textBox' maxlength='3' size='3' name='cycleLength' value='20'></td>
					<td width='20%' align='right'><input type='submit' name='submit' value='{$lang['button_rebuild']}' onClick=\"if(document.frmRebuildThumbs.cycleLength.value=='') document.frmRebuildThumbs.cycleLength.value=20;\" class='submitButton'></td>
					</td></tr></table>
					</form></fieldset>";
				}
				else {
					echo "<br /><br /><div align='center'><i>{$lang['thumbs_no_msg']}</i></div></fieldset>";
				}
			}
			break;
			
		case 'define-new-fields':
			$status = $_GET['status']; //why do I need this?
			echo "<br /><br /><div align='left'>{$lang['intro_define_new_fields']}</div>";
			
			//get all user-defined fields
			$ud_fields_arr = getCurrentUserDefinedFields("arr","","",$config,$lang);
			
			echo "<fieldset class='default'><legend>{$lang['admincp_nav_define_new_fields']}&nbsp;</legend>
			<form name='frmDefineNewFields' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='define-new-fields'>

			<div class='columnLeft' style='width:30%'>
			{$lang['new_field']}<br />
			<input type='text' name='txtUdField' class='spoiltTextBox' maxlength='255'>
			<br /><br />
			
			{$lang['current_ud_fields']}<br />
			<select name='ud_fields[]' size='8' class='spoiltDropdownBox' multiple='multiple'";
			//don't add the javascript function if there are no fields defined
			if(sizeof($ud_fields_arr) > 0)   echo " ondblclick=\"gotoPage('fid',this.options[this.selectedIndex].value,'edit_field.php')\"";
			echo ">";
			if(sizeof($ud_fields_arr) > 0) {
			   foreach($ud_fields_arr as $key => $value)
			      echo "
				  <option value='$key'>$value</option>";
			}
			echo "</select>
			</div>
			<div class='columnRight' style='width:70%;'>
			<table width='100%'>
			<tr><td align='right' width='100%' valign='bottom'>
			<input type='submit' name='submitAdd' value='{$lang['button_add']}' class='submitButton2' style='margin-top:142px' />
			<input type='submit' name='submitRemove' onclick=\"return confirm('{$lang['delete_confirm']}');\" value='{$lang['button_remove']}' class='submitButton2' style='margin-top:142px' /></div>
			</td></tr>
			</table>
			</div>
			</form>
			</fieldset>";
	
			break;
			
		case 'edit-login':
			$status = $_GET['status'];
			if(isset($status) && $status==1)
				echo "<br /><br /><div align='center'><i>{$lang['login_success_msg']}</i></div>";
			elseif(isset($status) && $status==0)
				echo "<br /><br /><div align='center'><i>{$lang['login_error_msg']}</i></div>";
			else {
				echo "<br /><br /><div align='left'>{$lang['intro_edit-login']}</div>";
			}
			
			$statement = "SELECT uid, username, password FROM {$config['table_prefix']}users LIMIT 1";
			$result = mysql_query($statement);
			$row = mysql_fetch_array($result);
			echo "<form name='frmEdit-login' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='edit-login'>
			<input type='hidden' name='uid' value='{$row['uid']}'>
			<input type='hidden' name='username_old' value='{$row['username']}'>
			<fieldset class='default'><legend>{$lang['admincp_nav_login']}&nbsp;</legend>
			<br /><table class='table_layout' cellspacing='1' cellpadding='2'>
			<tr><td width='40%'><b>{$lang['username']}</b> </td><td width='60%'><input type='text' name='username' value='{$row['username']}' class='textBox' style='width:95%' maxlength='32' /></td></tr>
			<tr><td width='40%'><b>{$lang['password_old']}</b> </td width='60%'><td><input type='password' name='password_old' class='textBox' style='width:95%' maxlength='12' /></td></tr>
			<tr><td width='40%'><b>{$lang['password_new']}</b> </td><td width='60%'><input type='password' name='password_new' class='textBox' style='width:95%' maxlength='12' /></td></tr>
			<tr><td width='40%'><b>{$lang['password_repeat']}</b> </td><td width='60%'><input type='password' name='password_new2' class='textBox' style='width:95%' maxlength='12' /></td></tr>
			<tr><td align='center' colspan='2'><br /><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' /></td></tr>
			</table>
			</fieldset>
			</form>";
	
			break;
			
			case 'optimize':
			$status = $_GET['status'];
			if(isset($status) && $status==1)
				echo "<br /><br /><div align='center'><i>{$lang['optimize_success_msg']}</i></div>";
			elseif(isset($status) && $status==0)
				echo "<br /><br /><div align='center'><i>{$lang['admin_error_msg']}</i></div>";
			else {
				echo "<br /><br /><div align='left'>{$lang['intro_optimize']}</div>
				<fieldset class='default'><legend>{$lang['admincp_nav_optimize']}&nbsp;</legend>
				<form name='frmOptimize' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
				<input type='hidden' name='is_what' value='optimize'>
				<table width='100%' class='search_page_cell' cellspacing='3' cellpadding='2'>
				<td width='100%' align='center'><input type='submit' name='submit' value='{$lang['button_optimize']}' class='submitButton2'></td>
				</td></tr></table>
				</fieldset>";
			}
			break;
			
		case 'ban':
			echo "<br /><br />
			<div align=left>{$lang['intro_ban']}</div>
			<fieldset class='default'>
			<legend>{$lang['admincp_nav_blacklist']}&nbsp;</legend>
			
			<div class='columnLeft' style='width:20%'>
			<form name='frmBan' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='ban'>
			<input type='text' name='txtIp' class='spoiltTextBox' maxlength='15'><br />
			<span class='tiny_text'>{$lang['ip_eg']}</span>
			<br /><br />
			<select name='ip[]' size='8' multiple class='spoiltDropdownBox'>";
			$banned = getBanned($config);
			if(sizeof($banned) > 0) {
			   foreach($banned as $key => $value)
			      echo "<option value='$key'>$value</option>";
			}
			echo "</select>			
			<br />
			</div>
			<div class='columnRight' style='width:80%;'>
			<table width='100%'>
			<tr><td align='right' width='100%' valign='bottom'>
			<input type='submit' name='submitAdd' value='{$lang['button_add']}' class='submitButton2' style='margin-top:128px' />
			<input type='submit' name='submitRemove' value='{$lang['button_remove']}' class='submitButton2' style='margin-top:128px' /></div>
			</td></tr>
			</table>
			</form>
			</div>
			</fieldset>";
			break;
			
		case 'config':
			$status = $_GET['status'];
			if(isset($status) && $status == "error")   echo "<br /><div align='center'><i><b>{$lang['isnumeric_fail_msg']}</b></i></div>";
			
			echo "<br /><form name='frmConfig' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
			<input type='hidden' name='is_what' value='config' />
			<table class='table_layout_sections' width='95%' cellpadding='2' cellspacing='0' style='border:1px solid #666666;'>
			";
			
			//basic settings
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url({$config['stylesheet']}/images/td_back_mid.gif)'>{$lang['head_basic_settings']}</td></tr>
			";
			showTextBoxAdminStyle("title_in_admincp", "title", "text", 50, "", $config, $lang);
			showTextBoxAdminStyle("description", "description", "text", 50, "", $config, $lang);
			showTextBoxAdminStyle("admin_email", "admin_email", "text", 50, "", $config, $lang);
			showDropdownBoxAdminStyle("main_page_show", "", array($lang['main_page_show_album_view'], $lang['main_page_show_last_added']), $config, $lang);
			showTextBoxAdminStyle("stylesheet", "stylesheet", "text", 50, "description_stylesheet", $config, $lang, "0", $_SESSION["{$config['cookie_prefix']}error_stylesheet_path"]);
			showTextBoxAdminStyle("language", "language", "text", 50, "description_language", $config, $lang, "0", $_SESSION["{$config['cookie_prefix']}error_lang_path"]);
			showTextBoxAdminStyle("lastadded_perpage", "lastadded_perpage", "text", 10, "description_perpage", $config, $lang);
			showTextBoxAdminStyle("perpage", "perpage", "text", 10, "description_perpage", $config, $lang);
			showTextBoxAdminStyle("random_perpage", "random_perpage", "text", 10, "", $config, $lang);
			showRadioButtonAdminStyle("random_show", "", $config, $lang);
			showTextBoxAdminStyle("gallery_width", "gallery_width", "text", 50, "description_gallery_width", $config, $lang);
			unset($_SESSION["{$config['cookie_prefix']}error_lang_path"]);
			unset($_SESSION["{$config['cookie_prefix']}error_stylesheet_path"]);
			echo "<tr><td align='left' class='white_cell' width='60%'>{$lang['timezone_offset']}</td><td align='left' class='white_cell' width='40%'>
			<select name='timezone_offset' class='dropdownBox'>";
			$pc_timezones = array(
			'(GMT-12:00)' => -12*3600,
			'(GMT-11:00)' => -11*3600,
			'(GMT-10:00)' => -10*3600,
			'(GMT-09:00)' => -9*3600,
			'(GMT-08:00)' => -8*3600,
			'(GMT-07:00)' => -7*3600,
			'(GMT-06:00)' => -6*3600,
			'(GMT-05:00)' => -5*3600,
			'(GMT-04:00)' => -4*3600,
			'(GMT-03:30)' => -3*3600-1800,
			'(GMT-02:00)' => -2*3600,
			'(GMT-01:00)' => -1*3600,
			'GMT/UTC' => 0,
			'(GMT+01:00)' => +1*3600,
			'(GMT+02:00)' => +2*3600,
			'(GMT+03:00)' => +3*3600,
			'(GMT+03:30)' => +3*3600+1800,
			'(GMT+04:00)' => +4*3600,
			'(GMT+04:30)' => +4*3600+1800,
			'(GMT+05:00)' => +5*3600,
			'(GMT+05:30)' => +5*3600+1800,
			'(GMT+06:00)' => +6*3600,
			'(GMT+06:30)' => +6*3600+1800,
			'(GMT+07:00)' => +7*3600,
			'(GMT+08:00)' => +8*3600,
			'(GMT+09:00)' => +9*3600,
			'(GMT+09:30)' => +9*3600+1800,
			'(GMT+10:00)' => +10*3600,
			'(GMT+11:00)' => +11*3600,
			'(GMT+12:00)' => +12*3600,
			'(GMT+13:00)' => +13*3600,
			) ;
			foreach($pc_timezones as $key => $value) {
				echo "<option value='$value'";
				if(strcasecmp($value, $config['timezone_offset'])==0)   echo " selected='selected'"; //if they're the same...
				echo ">$key</option>";
			}
			echo "</select>
			</td></tr>";
			showDropdownBoxAdminStyle("default_search_box_style", "", array($lang['collapsed'], $lang['expanded']), $config, $lang);
	
			//server config
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_server_config']}</td></tr>
			";
			showTextBoxAdminStyle("upload_dir", "upload_dir", "text", 200, "description_upload_dir", $config, $lang, "0", "style='width:300px'");
			showTextBoxAdminStyle("upload_dir_server", "upload_dir_server", "text", 200, "description_upload_dir_server", $config, $lang, "0", "style='width:300px'");
			//add the 4 server elements to $config[]
			$config['sql_user'] = $sql_user; $config['sql_pass'] = $sql_pass; $config['sql_host'] = $sql_host; $config['db_name'] = $db_name;
			showTextBoxAdminStyle("sql_host", "sql_host", "text", 50, "", $config, $lang);
			showTextBoxAdminStyle("sql_user", "sql_user", "text", 50, "", $config, $lang);
			showTextBoxAdminStyle("sql_pass", "sql_pass", "password", 50, "description_sql_pass", $config, $lang, -1);
			showTextBoxAdminStyle("db_name", "db_name", "text", 50, "", $config, $lang);
	
			//file_uploads
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_file_uploads']}</td></tr>
			";
			showTextBoxAdminStyle("max_filesize", "max_filesize", "text", 20, "", $config, $lang);
			showTextBoxAdminStyle("max_pic_width", "max_pic_width", "text", 20, "", $config, $lang);
			showTextBoxAdminStyle("max_pic_height", "max_pic_height", "text", 20, "", $config, $lang);
			//showTextBoxAdminStyle("allowed_media_filetypes", "allowed_media_filetypes", "text", 32, "", $config, $lang);
			echo "<tr><td align='left' class='white_cell' width='60%'>{$lang['allowed_media_filetypes']}<br /><span class='tiny_text'>{$lang['description_allowed_media_filetypes']}</span></td><td class='white_cell' width='40%'><input value=\"";
			//populate textbox with allowed media filetypes
			if($config['allowed_media_filetypes']) {
				foreach($config['allowed_media_filetypes'] as $value)
				   $str_to_flush .= stripslashes(trim($value)) . ","; //stripslashes() for chars that were escaped before being stored
			}
			$str_to_flush = substr($str_to_flush, 0, strlen($str_to_flush)-1);
			echo $str_to_flush;
			echo "\" type='text' name='allowed_media_filetypes' maxlength='32' class='spoiltTextBox'></td></tr>";
			showRadioButtonAdminStyle("allow_user_uploads", "", $config, $lang);
			showRadioButtonAdminStyle("prevent_uploads_to_parents", "", $config, $lang);
			showRadioButtonAdminStyle("mail_on_add", "", $config, $lang);
			showTextBoxAdminStyle("pending_pics_suffix", "pending_pics_suffix", "text", 3, "description_pending_pics_suffix", $config, $lang);
			
			//thumbnails		
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_thumbnails']}</td></tr>
			";
			showTextBoxAdminStyle("jpeg_quality", "jpeg_quality", "text", 3, "", $config, $lang);
			showTextBoxAdminStyle("thumb_width", "thumb_width", "text", 3, "", $config, $lang);
			showTextBoxAdminStyle("thumb_height", "thumb_height", "text", 3, "", $config, $lang);
			showTextBoxAdminStyle("thumb_suffix", "thumb_suffix", "text", 10, "", $config, $lang);
			showTextBoxAdminStyle("thumbs_perrow", "thumbs_perrow", "text", 2, "", $config, $lang);
			showDropdownBoxAdminStyle("thumb_align", "", array($lang['left'], $lang['center'], $lang['right']), $config, $lang);
			showDropdownBoxAdminStyle("thumb_details_align", "", array($lang['left'], $lang['center'], $lang['right']), $config, $lang);
			showRadioButtonAdminStyle("thumb_show_details", "", $config, $lang);
			showRadioButtonAdminStyle("create_thumbs", "", $config, $lang);
			showRadioButtonAdminStyle("fixed_sized_thumbs", "description_fixed_sized_thumbs", $config, $lang);
	
			//picture details
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_picture_details']}</td></tr>";
			showRadioButtonAdminStyle("picture_details_show", "description_picture_details", $config, $lang);
			showRadioButtonAdminStyle("submission_details_show", "description_submission_details", $config, $lang);
			showRadioButtonAdminStyle("file_details_show", "description_file_details", $config, $lang);
			showRadioButtonAdminStyle("keywords_show", "", $config, $lang);
			showRadioButtonAdminStyle("direct_path_show", "", $config, $lang);
			showRadioButtonAdminStyle("views_show", "", $config, $lang);
			showRadioButtonAdminStyle("voting_show", "", $config, $lang);
			showRadioButtonAdminStyle("comments_show", "", $config, $lang);	
			showRadioButtonAdminStyle("guest_voting", "", $config, $lang);
			showRadioButtonAdminStyle("guest_comments", "", $config, $lang);
			showRadioButtonAdminStyle("mod_queue_guests", "", $config, $lang);
			showDropdownBoxAdminStyle("default_preview_pic_size", "", array($lang['small'], $lang['medium'], $lang['original']), $config, $lang);
			showRadioButtonAdminStyle("exif_show", "", $config, $lang);
			showRadioButtonAdminStyle("marking_allowed", "", $config, $lang);

			//user accounts			
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_user_accounts']}</td></tr>
			";
			showDropdownBoxAdminStyle("user_account_validation_method", "", array($lang['user_account_validate_email'], $lang['user_account_validate_admin'], $lang['user_account_validate_none']), $config, $lang);
			showRadioButtonAdminStyle("allow_registrations", "", $config, $lang);
			showRadioButtonAdminStyle("show_members_list", "", $config, $lang);
			showRadioButtonAdminStyle("mod_queue_registered_users", "", $config, $lang);

			//other options		
			echo "<tr><td width='100%' colspan='2' class='dark_cell' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")'>{$lang['head_other_options']}</td></tr>
			";
			showRadioButtonAdminStyle("registration_captcha", "", $config, $lang);
			showDropdownBoxAdminStyle("comments_captcha", "", array($lang['disabled'], $lang['guests'], $lang['all']), $config, $lang);
			showRadioButtonAdminStyle("v094paths", "description_v094paths", $config, $lang, 1);
			
			//showTextBoxAdminStyle("news_bar", "news_bar", "text", 512, "description_news_bar", $config, $lang, "0", "style='width:300px'");
			showTextAreaAdminStyle("news_bar", "news_bar", 40, 3, "description_news_bar", $config, $lang, "0", "");
			
			showTextBoxAdminStyle("cookie_prefix", "cookie_prefix", "text", 12, "description_cookie_prefix", $config, $lang);
			echo "<tr><td align='left' class='white_cell' width='60%'>{$lang['short_date_format']}<br /><span class='tiny_text'>{$lang['description_short_date_format']}</span></td><td class='white_cell' width='40%'><input value='"; if(isset($config['short_date_format'])) echo "{$config['short_date_format']}"; else echo "%M %e, %Y";  echo "' type='text' name='short_date_format' maxlength='32' class='spoiltTextBox'></td></tr>
			<tr><td align='left' class='white_cell' width='60%'>{$lang['long_date_format']}<br /><span class='tiny_text'>{$lang['description_long_date_format']}</span></td><td class='white_cell' width='40%'><input value='"; if(isset($config['long_date_format'])) echo "{$config['long_date_format']}"; else echo "%M %e, %Y %h:%i%p";  echo "' type='text' name='long_date_format' maxlength='32' class='spoiltTextBox'></td></tr>
			";
			showTextBoxAdminStyle("locale", "locale", "text", 128, "description_locale", $config, $lang);
			showDropdownBoxAdminStyle("page_direction", "description_page_direction", array($lang['ltr'], $lang['rtl']), $config, $lang);
			echo "<tr><td align='left' class='white_cell' width='60%'>{$lang['chmod_pics']}</td><td class='white_cell' width='40%'><input value='{$config['chmod_pics']}' type='text' name='chmod_pics' maxlength='4' class='spoiltTextBox' readonly='readonly'></td></tr>
			<tr><td align='left' class='white_cell' width='60%'>{$lang['script_timeout']}<br /><span class='tiny_text'>{$lang['description_script_timeout']}</span></td><td class='white_cell' width='40%'><input value='"; if(isset($config['script_timeout'])) echo "{$config['script_timeout']}"; else echo "30";  echo "' type='text' name='script_timeout' maxlength='4' class='spoiltTextBox'></td></tr>
			<tr><td align='left' class='white_cell' width='60%'>{$lang['strip_chars']}<br /><span class='tiny_text'>{$lang['description_strip_chars']}</span></td><td class='white_cell' width='40%'><input value=\"";
			//populate textbox with escape_chars chars
			foreach($config['escape_chars'] as $value)
			   echo stripslashes(trim($value)); //stripslashes() for chars that were escaped before being stored
			echo "\" type='text' name='escape_chars' maxlength='20' class='spoiltTextBox'></td></tr>";
			showRadioButtonAdminStyle("debug_mode", "", $config, $lang);
			showRadioButtonAdminStyle("private", "", $config, $lang);
			showRadioButtonAdminStyle("gallery_off", "", $config, $lang);
			
			echo "</td></tr>
			</table>
			<input type='hidden' name='version' value='0.9.4 DEV'>
			<input type='hidden' name='table_prefix' value='{$config['table_prefix']}'>
			<br /><div align='center'><input type='submit' name='submit' value='{$lang['button_update_settings']}' class='submitButton3' /></div><br />
			</form>";
			break;
	
		default:
	}

	echo "</td></tr>
	</table>
	<br />";
}

include('foot.php');

?>