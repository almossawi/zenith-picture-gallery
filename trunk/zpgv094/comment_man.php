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
 
 File: comment_man.php
 Description: -
 Random quote: "You live and learn. At any rate, you live."
 -Douglas Adams
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_db.php');

if($config['debug_mode'] == 1)   print_r($_POST);

switch($_POST['is_what']) {
   //if adding a new comment
   case 'comment-add':
	//check if user is blacklisted
      isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);

      if($config['comments_captcha'] == 2 || ($config['comments_captcha'] == 1 && !loggedIn($config))) {
          $vcode = $_POST['vcode'];
          $authcode = $_SESSION['authcode'];
          unset($_SESSION['authcode']);
          if(strtolower($vcode) != strtolower($authcode)) {
             doBox("error", $lang['bad_verification_code'] . " <a href='{$_POST['uri']}'>{$lang['redirect_proceed']}</a>", $config, $lang);     
	      }
      }

      $author = htmlentities(charsEscaper(quickEscaper($_POST['comment_author']), $config['escape_chars']), ENT_QUOTES, "UTF-8");
      $body = htmlentities(charsEscaper(quickEscaper(nl2br($_POST['comment_body'])), $config['escape_chars']), ENT_QUOTES, "UTF-8"); //nl2br new in v0.9.4 DEV

	  if(strlen($body) > 0) {
		  if($config['mod_queue_guests'])   $approved = $_POST['approved'];
		  else   $approved = 1;
		  
		  //add it to the db
		  $statement = "INSERT INTO {$config['table_prefix']}comments (pid, comment_author, comment_date, comment_ip, comment_body, approved) 
		  VALUES({$_POST['pid']}, '$author', NOW(), '{$_POST['comment_ip']}', '$body', $approved)";
		  $result = mysql_query($statement);
		  if($result)   $append = "s";
		  else   $append = mysql_error();
	  }
	  
      doBox("msg", $lang['comment_submitted_msg'] . " <a href='{$_POST['uri']}&c=$append'>{$lang['redirect_proceed']}</a>", $config, $lang);     
	  
      break;
		
   //if editing a comment
   case 'comment-edit':
      if(loggedIn($config) && adminStatus($config)) {
		$comment_id = $_POST['id'];
		$comment_author = quickEscaper($_POST['author']);
		$comment_body = quickEscaper($_POST['body']);
		
		//001 -- validation check
		if(strlen($comment_author) <= 0 || 
		strlen($comment_body) <= 0) {
		  $_SESSION['edit_comment_msg'] = $lang['required_error'];
		  $redirect = "Location: comment_man.php?id=$comment_id&is_what=comment-edit-form";
		  header("$redirect");
		  exit();
		}
		
		//only proceed if form validated
		$statement = "UPDATE {$config['table_prefix']}comments SET comment_author='$comment_author', comment_body='$comment_body' WHERE comment_id='$comment_id'";
		$result = mysql_query($statement);
			
		if($result) {
		  $redirect = "Location: comment_man.php?id=$comment_id&is_what=comment-edit-form";
		  header("$redirect");
		  $_SESSION['edit_comment_msg'] = $lang['record_updated'];
		  exit();
		}
	  }//end if admin
	  
      break;
   
	default:
}

//if deleting a comment
if($_GET['is_what'] == "comment-delete" && loggedIn($config) && adminStatus($config)) {
   $statement = "DELETE FROM {$config['table_prefix']}comments WHERE comment_id = {$_GET['id']}";
   $result = mysql_query($statement);
		
   if($result) {
	   $redirect = "Location: pic_details.php?pid={$_GET['pid']}"; //redirect back to image
	   header("$redirect");
	   exit();
   }
}

header("Content-type: text/html; charset=UTF-8");
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
   \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<link rel=\"stylesheet\" type=\"text/css\" href=\"" . getSkinElement($config['stylesheet'], "stylesheet.css") . "\" />
<body>";

//show comment edit form
if($_GET['is_what'] == 'comment-edit-form' && loggedIn($config) && adminStatus($config)) {

   //get comments data (again)
   $statement = "SELECT comment_author, comment_body FROM {$config['table_prefix']}comments WHERE comment_id='{$_GET['id']}' LIMIT 1";
   $result = @mysql_query($statement);
   $row = @mysql_fetch_array($result);

   if(isset($_SESSION['edit_comment_msg'])) {
      echo "<div align='center'>{$_SESSION['edit_comment_msg']}</div>";
      unset($_SESSION['edit_comment_msg']);
   }
   if(@mysql_num_rows($result) == 0) {
      echo "<div align='center'>{$lang['edit_comment_error']}</div>";
   }	
   else {
      echo "<div align='center'><br />
	  <form name='frmEditComment' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post'>
	  <input type='hidden' name='is_what' value='comment-edit' />";
	  if(is_numeric($_GET['id']))   echo "<input type='hidden' name='id' value='{$_GET['id']}' />";
      echo "<table class='table_layout_sections' cellspacing='0' cellpadding='2' width='580' style='padding:0;margin:0'>
      <tr><td class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' colspan='2'>{$lang['head_edit_record']}</td></tr>
      <tr><td width='30%'><b>{$lang['author']}</b></td><td width='70%'><input type='text' name='author' value ='{$row['comment_author']}' maxlength='32' class='spoiltTextBox' /></td></tr>
      <tr><td width='30%'><b>{$lang['comment']}</b></td><td width='70%'><textarea cols='40' rows='5' name='body' maxlength='255' class='spoiltTextBox'>{$row['comment_body']}</textarea></td></tr>
      <tr><td colspan='2' class='cell_foot'>
	  <div align='center'><input type='submit' name='submit' value='{$lang['button_update']}' class='submitButton2' /></div>
	  </td></tr>
      </form>
	  </div>";
   }
}

mysql_close($connection);

?>