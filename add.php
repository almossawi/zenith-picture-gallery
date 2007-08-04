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
 Last updated: July 8, 2007
 Random quote: "Forgiveness is the fragrance that the violet sheds on 
 the heal that has crushed it. " -Mark Twain
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_db.php');
require_once('functions/f_lib.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

//is user allowed to add?
if(!$config['allow_user_uploads'] && !loggedIn($config))   doBox("error", $lang['add_disabled_msg'], $config, $lang);

if ($_POST['submit']) {
	//get and sanitize all fields
	$allowed_file_types = array(1 => 'jpg','jpeg','png','gif');
	$allowed_file_types = array_merge($allowed_file_types, $config['allowed_media_filetypes']); //add the media extensions specified in the admin cp to the array of allowed types

	$bits = splitFilenameAndExtension($_FILES['upload']['name']);
	$extension = strtolower($bits[1]);
	$filename_escape_chars = $config['escape_chars'];  $filename_escape_chars[] = '+';
	$filename = charsEscaper($_FILES['upload']['name'], $filename_escape_chars);
	$is_at = $final_upload_dir_server.$_FILES['upload']['tmp_name'];
	$ip = charsEscaper($_POST['ip'], $config['escape_chars']);
	$submitter = htmlentities(charsEscaper($_POST['submitter'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$uploaded_title = htmlentities(charsEscaper($_POST['title'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	$uploaded_description = htmlentities(charsEscaper($_POST['image_description'], $config['escape_chars']), ENT_QUOTES, "UTF-8"); 
	$uploaded_keywords 	= htmlentities(charsEscaper($_POST['keywords'], $config['escape_chars']), ENT_QUOTES, "UTF-8");
	if($config['mod_queue_guests'])   $uploaded_approved = $_POST['approved'];
	else   $uploaded_approved = 1;
	
	if(isVideo($config,"",$filename))   $is_video = 1; //new in ZVG

	if($_POST['category'] != "")   $uploaded_category = charsEscaper($_POST['category'], $config['escape_chars']); //get cat
	//new in v0.8.4
	if($uploaded_category != '') {
		$final_upload_location = getPicturesPathFromCategory($config, $uploaded_category, 0,"");
		$final_upload_location_server = getPicturesPathFromCategory($config, $uploaded_category, 1,"");
	}
	//any user-defined fields?
	if(isset($_POST['udf'])) {
		$udf = array(); //added on january 9, 2006
		foreach($_POST['udf'] as $key => $value) {
			$udf["udf_".$key] = htmlentities(charsEscaper($value, $config['escape_chars']), ENT_QUOTES, "UTF-8");
			//print_r($udf);
		}
	}
	if($config['debug_mode'] == 1)   print_r($udf); //added on january 9, 2006
	
	//remember fields
	$_SESSION["{$config['cookie_prefix']}frmAddTitle"] = stripslashes($uploaded_title);
	$_SESSION["{$config['cookie_prefix']}frmAddDescription"] = stripslashes($uploaded_description);
	$_SESSION["{$config['cookie_prefix']}frmAddKeywords"] = stripslashes($uploaded_keywords);
	$_SESSION["{$config['cookie_prefix']}frmAddCategory"] = stripslashes($uploaded_category);
	//any user-defined fields?
	if(isset($udf)) {
		foreach($udf as $key => $value) {
			$stripped_udf[$key] = stripslashes($value);
		}
		$_SESSION["{$config['cookie_prefix']}frmAddUdf"] = $stripped_udf;
	}
	//print_r($_SESSION);
	
	//001 -- validation check
	if(strlen($uploaded_category) <= 0 || 
	strlen($uploaded_title) <= 0 || 
	strlen($uploaded_keywords) <= 0) {
		$_SESSION['add_msg'] .= "<br />".$lang['add_check1'];
		$any_errors = 1;
		$validation_error = 1;
	}
	//has admin prevented submissions to parent category?
	if($config['prevent_uploads_to_parents'] && catHasChildren($uploaded_category, $config) > 0) {
		$_SESSION['add_msg'] .= "<br />".$lang['cant_upload_to_parent_msg'];
		$any_errors = 1;
		$validation_error = 1;
	}
	
	if(!function_exists("gd_info") && $config['create_thumbs'] == "1") {
		$_SESSION['add_msg'] .= "<br />".$lang['no_gd_msg'];
		$any_errors = 1;
		$validation_error = 1;
	}
	
	//if form did not validate, skip remaining checks
	if(!$validation_error) {
	  //002 -- extension check
	  if (!in_array($extension, $allowed_file_types)) {
		$_SESSION['add_msg'] .= "<br />".$lang['add_check2'];
		$any_errors = 1;
	  }
	
	//003 -- valid image check
	//new in ZVG, skip this code block for videos
	if (!$is_video) {		
		$usize = @getimagesize($is_at); //get dimensions of image
		//$handle = @fopen($is_at, "r");
      	//$contents = @fread($handle, filesize($is_at));
      	//@fclose($handle);
	  	//if($usize && !strpos(strtolower($contents),"php")) { //is valid image check
		if($usize) { //is valid image check
			$uploaded_pwidth = $usize[0];
			$uploaded_pheight = $usize[1];
	  	}
	  	else {
			//unlink($_FILES['upload']['tmp_name']);
			$_SESSION['add_msg'] .= "<br />".$lang['add_check3'];
			$any_errors = 1;
	  	}
	  
	  //004 -- width/height check
	  $usize = @getimagesize($is_at); //get dimensions of image
	  if($uploaded_pwidth > $config['max_pic_width'] || $uploaded_pheight > $config['max_pic_width']) { 
		$_SESSION['add_msg'] .= sprintf("<br />".$lang['add_check4'], $config['max_pic_width'], $config['max_pic_width']);
		$any_errors = 1;
	  }
	}//end new in ZVG
	else { //set dimensions to thumb dimensions since it's a video
		$uploaded_pwidth = $config['thumb_width'];
		$uploaded_pheight = $config['thumb_height'];
	}
			  
	  //005 -- file already exists check (on filename only)
	  //doesFileExist does the check and suggests new filename in case of one already existing - new in v0.9.2 DEV
	  //$fully_qualified_name = getPicturesPath($config, $_FILES['upload']['name'], 1);
	  $dfe_bits = doesFileExist($config, $filename);
	  $filename = $dfe_bits[0];
	  $name_change_counter = $dfe_bits[1];
	  $is_at = $final_upload_location_server.$filename;
	  if($name_change_counter > 0)   $_SESSION['add_msg'] .= "{$lang['add_check_file_exists']}<br />"; //display a message informing the user of the name change
			  
	  //006 -- file size check
	  if($_FILES['upload']['size'] > ($config['max_filesize']*1024)) {
		$_SESSION['add_msg'] .= sprintf("<br />".$lang['add_check5'], $config['max_filesize']);
		$any_errors = 1;
	  }
	
	}//end if !$validation_error
			
	//If all went well...
	if(!$any_errors) {  
		//there's no restriction on the description field, but just for the sake of safety, limit it to 512 chars
		if(strlen($uploaded_description)>512)   $uploaded_description = substr($uploaded_description,0,512);
		
		//Add to table (include all user-defined fields too)
		//new in ZVG (modified sql statement)
		$statement = "INSERT INTO {$config['table_prefix']}pictures (file_name,file_size,file_type,title,description,date,pic_width,pic_height,keywords,approved,category,ip,is_video,submitter" . array2set($udf,"0","key",",","") . ") 
		VALUES ('$filename','{$_FILES['upload']['size']}','{$_FILES['upload']['type']}','$uploaded_title','$uploaded_description',NOW(),'$uploaded_pwidth','$uploaded_pheight','$uploaded_keywords','$uploaded_approved','$uploaded_category','$ip','$is_video','$submitter'" . arrayHacker($udf,"value","0","'","'",",","",",") . ")";
		if($config['debug_mode'] == 1)   echo "<br /><br />".$statement;; //added on january 9, 2006
		$result = mysql_query($statement);
				
		if($result) {
			if(@move_uploaded_file($_FILES['upload']['tmp_name'], $final_upload_location_server.$filename)) {
				//chmod original file in case the server's default isn't world readable
				if(!@chmod($final_upload_location_server.$filename, 0644))
        			if($config['debug_mode'] == 1)   echo "Cannot change the mode of file ($path_server)";
					
				//now that the image is in the db and the uploads dir, create its thumbnail
				if($config['create_thumbs'] == 1 && !$is_video) { //new in ZVG, modified guard, no need to create a thumb if the file is a video
					include("image_man.php");
					$imn = new ImageManipulator($config, $lang);
					$imn -> decider($extension,$filename,$config['thumb_width'],$config['thumb_height'],$config['jpeg_quality'],$final_upload_location, $final_upload_location_server, $config['thumb_suffix'], $config['chmod_pics'], $config, $lang); //filename,width,height,quality,path
				}
				
				//rename picture and thumbnail if necessary (added in v0.9.4)
				if($uploaded_approved != 1 && $config['pending_pics_suffix'] != "") { //if picture will be mod-queued and suffixing is enabled
					$bits_i = splitFilenameAndExtension($filename);
					$filename_thumb = $bits_i[0].$config['thumb_suffix'] . "." . $bits_i[1];
					
					//suffix pending pictures with this until they're approved by admin
					rename($final_upload_location_server.$filename,$final_upload_location_server.$filename.".{$config['pending_pics_suffix']}"); //rename pic
					rename($final_upload_location_server.$filename_thumb,$final_upload_location_server.$filename_thumb.".{$config['pending_pics_suffix']}"); //rename its thumb
				}
				
				//email admin
				if($config['mail_on_add']) {
					$final_upload_location_mail = str_replace(" ","%20",$final_upload_location.$filename);
					$body = "Hello,\n\nThis is to inform you that a new picture has just been submitted to your Zenith gallery.\n\n
					$final_upload_location_mail\n\nThis email was automatically generated by the Zenith Picture Gallery script.";
					$headers = "From: {$config['title']} <{$config['admin_email']}>\r\n";
					if(!mail($config['admin_email'],"{$config['title']} New Picture submitted",$body,$headers))
					//if(!mailReloaded($config['admin_email'],$config['title'],$config['admin_email'],$config['title'],"{$config['title']} New Picture submitted",$body))
						$_SESSION['add_msg'] .= "<br />".$lang['couldnt_find_mail_server_msg'];
				}
				
				//up user's submissions counter
				$row = mysql_fetch_array(mysql_query("SELECT uid, submissions FROM {$config['table_prefix']}users WHERE username='$submitter'"));
				$counter = $row['submissions']+1;
				$result = mysql_query("UPDATE {$config['table_prefix']}users SET submissions='$counter' WHERE username='$submitter'");
			
				//forget form fields on successful submit
				unset($_SESSION["{$config['cookie_prefix']}frmAddTitle"]);
				unset($_SESSION["{$config['cookie_prefix']}frmAddDescription"]);
				unset($_SESSION["{$config['cookie_prefix']}frmAddKeywords"]);
				unset($_SESSION["{$config['cookie_prefix']}frmAddCategory"]);
				//any user-defined fields?
				if(isset($_SESSION["{$config['cookie_prefix']}frmAddUdf"])) {
					foreach($_SESSION["{$config['cookie_prefix']}frmAddUdf"] as $key => $value) {
						//I have to do it this way to be able to delete the array in $_SESSION
						$_SESSION["{$config['cookie_prefix']}frmAddUdf"] = null;
						unset($_SESSION["{$config['cookie_prefix']}frmAddUdf"]);
					}
				}
			
				//print "<meta http-equiv='Refresh' content='0; url=index.php'>";
				$redirect = "Location: add.php?pic=" . $uploaded_title;
				header("$redirect");
				$_SESSION['success_msg'] = $lang['file_uploaded_msg'] . " ($filename)";
				exit();
			}
			else {
			  $_SESSION['add_msg'] .= "<br />".$lang['add_check6'];
			  if($config['debug_mode'] == 1) {
				 $_SESSION['add_msg'] .= "<br />{$lang['debug_error_returned']} " . mysql_errno() . ' ' . mysql_error();
				 $_SESSION['add_msg'] .= "<br />{$lang['debug']} {$lang['debug_related1']} {$_FILES['upload']['tmp_name']} ";
				 $_SESSION['add_msg'] .= "{$lang['debug_related2']} $final_upload_location_server";
			  }
			
			  $pid = mysql_insert_id();
			  $statement = "DELETE FROM {$config['table_prefix']}pictures WHERE pid = $pid";
			  $result = mysql_query($statement);
			}
		}
		else {
			$_SESSION['add_msg'] .= "<br />".$lang['add_check7'];
			if(isset($config['debug_mode']))   $_SESSION['add_msg'] .= "<br />{$lang['debug_error_returned']} " . mysql_errno() . ' ' . mysql_error();
		}
	}
}//end if submit

require_once('head.php');

//print any error messages that might have occurred
if(isset($_SESSION['add_msg'])) {
  echo "<div class='msg' style='color:red;font-size:10px'>{$_SESSION['add_msg']}</div>";
  unset($_SESSION['add_msg']);
}

if(isset($_SESSION['success_msg'])) {
  echo "<div class='msg'>{$_SESSION['success_msg']}</div>";
  unset($_SESSION['success_msg']);
}

$cat = getCats(2,$config,$lang);

$frmAddTitle = $_SESSION["{$config['cookie_prefix']}frmAddTitle"];
$frmAddDescription = $_SESSION["{$config['cookie_prefix']}frmAddDescription"];
$frmAddKeywords = $_SESSION["{$config['cookie_prefix']}frmAddKeywords"];
$frmAddCategory = $_SESSION["{$config['cookie_prefix']}frmAddCategory"];
//any user-defined fields?
if(isset($_SESSION["{$config['cookie_prefix']}frmAddUdf"])) {
	foreach($_SESSION["{$config['cookie_prefix']}frmAddUdf"] as $key => $value) {
		//echo "$key $value <br />";
		$frmAddUdf[$key] = $value;
		//echo "<b>frmAddUdf: {$frmAddUdf[$key]}</b><br />";
	}
}

echo "<form name='frmAdd' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='post' style='margin:0;padding:0'>";

//approved = 1 if user is logged in or approved = 0 if admin has chosen to moderate registered users too
if(adminStatus($config))   echo "<input type='hidden' name='approved' value='1' />";
elseif(loggedIn($config) && $config['mod_queue_registered_users'] == "0")   echo "<input type='hidden' name='approved' value='1' />";
else   echo "<input type='hidden' name='approved' value='0' />";
  
$ip = getenv("REMOTE_ADDR"); // get the user's ip
echo "<input type='hidden' name='ip' value='$ip' />";

if(loggedIn($config)) {
  $cookie_name_username = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
  echo "<input type='hidden' name='submitter' value='{$_COOKIE[$cookie_name_username]}' />";
}

echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='1' cellspacing='0' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' valign='middle' "; if($config['page_direction'] == "1")   echo "align='right' "; echo "colspan='2' width='100%'>
<img src='" . getSkinElement($config['stylesheet'], "images/arrow.gif") . "' border='0' alt='' /> {$lang['head_add']}</td></tr>
<tr><td class='search_page_cell' colspan='2' width='100%'><span class='tiny_text_dark'>"; printf($lang['max_display'], $config['max_filesize'], $config['max_pic_width'], $config['max_pic_height']); echo "</span></td></tr>
";

//any media types?  if so, show them
$allowed_media_filetypes = printArrayAsCSV($config['allowed_media_filetypes']);

//show fields
echo "
<tr><td colspan='2' width='100%'><table "; if($config['page_direction'] == "1")   echo "style='text-align:right' align='right' "; echo "width='100%'>
<tr><td width='40%' style='padding-left:10px'>{$lang['file']} </td><td width='60%'><input type='file' name='upload' class='spoiltTextBox' /><br /><span style='font-size:9px'>{$lang['add_txt_sub1']}$allowed_media_filetypes</span></td></tr>
<tr><td valign='top' width='40%' style='padding-left:10px'>{$lang['image_description']} </td><td width='60%'><textarea name='image_description' rows='3' cols='40' class='spoiltTextBox'>$frmAddDescription</textarea></td></tr>
<tr><td width='40%' style='padding-left:10px'>{$lang['title']} </td><td width='60%'><input type='text' name='title' value='$frmAddTitle' class='spoiltTextBox'  maxlength='64' /></td></tr>
<tr><td width='40%' style='padding-left:10px'>{$lang['keywords']} </td><td width='60%'><input type='text' name='keywords' value='$frmAddKeywords' class='spoiltTextBox' maxlength='128' onfocus=\"this.value=filler(this.value)\" /><br /><span style='font-size:9px'>{$lang['add_txt_sub3']}</span></td></tr>
<tr><td width='40%' style='padding-left:10px'>{$lang['category']} </td><td width='60%'>
";
$cats = getCatsTreeView(0,0,$config, $lang);
echo "<select name='category' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">";
foreach($cats as $key => $value) {
	if($key == 0) unset($key);
	echo "<option value='$key'";
	if($key == $frmAddCategory)   echo " selected='selected'";
	echo ">$value</option>";
}
echo "</select>
<br />
</td></tr>
";
//any user-defined fields?
echo displayCurrentUserDefinedFields("<tr><td width='40%' style='padding-left:10px'>### </td><td width='60%'>", "</td></tr>", $frmAddUdf, "textbox", "1", $config, $lang);

echo "
<tr><td width='40%'> </td>
<td width='60%'><br /><input type='submit' name='submit' value='{$lang['button_upload_pic']}' id='frm_button' class='submitButton3' /><br />
<br />
</td></tr>
</table></td></tr>
</table>
</form>
";

//<td width='60%'><br /><input type='submit' name='submit' value='{$lang['button_upload_pic']}' id='frm_button' class='submitButton3' onclick=\"this.enabled='false';hidify_showify_skinny('loading')\" /><br />

//forget form fields on form load
unset($_SESSION["{$config['cookie_prefix']}frmAddTitle"]);
unset($_SESSION["{$config['cookie_prefix']}frmAddDescription"]);
unset($_SESSION["{$config['cookie_prefix']}frmAddKeywords"]);
unset($_SESSION["{$config['cookie_prefix']}frmAddCategory"]);
//any user-defined fields?
if(isset($_SESSION["{$config['cookie_prefix']}frmAddUdf"])) {
	foreach($_SESSION["{$config['cookie_prefix']}frmAddUdf"] as $key => $value) {
		//I have to do it this way to be able to delete the array in $_SESSION
		$_SESSION["{$config['cookie_prefix']}frmAddUdf"] = null;
		unset($_SESSION["{$config['cookie_prefix']}frmAddUdf"]);
	}
}

showJumpToCatForm($config, $lang);

include('foot.php');

?>