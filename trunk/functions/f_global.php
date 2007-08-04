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
 
 File: f_global.php
 Description: Contains functions used throughout the gallery.
 Last updated: July 8, 2007
 Random quote: "Once you label me, you negate me."   -Soren Kierkegaard
*******************************************************************/

require_once("f_misc.php");

//--------------------------------------------
// mailReloaded(): new in v0.9.4 DEV, If you're having problems with emails not being sent, try using 
// this function (created to fix user danicasati's problem)
// 1. set an smtp server address below (host), then
// 2. uncomment the line that calls this function in add.php and comment the line before it, which calls mail(...), then
// 3. do the same in login.php (twice), my.php and register.php
//--------------------------------------------
function mailReloaded($to_address, $to_name, $from_address, $from_name, $subject, $body) {
   require_once(bugFixRequirePath("../lib/phpmailer/class.phpmailer.php"));

   $mail = new PHPMailer();
   $mail->IsSMTP(); // telling the class to use SMTP
   $mail->Host = "smtp.yourserver.com"; // SMTP server
   $mail->From = $from_address; //email address must be accepted by specified smtp server
   $mail->FromName = $from_name;
   $mail->AddAddress($to_address, $to_name);

   $mail->Subject = $subject;
   $mail->Body = $body;
   $mail->WordWrap = 50;

   if(!$mail->Send())   return 0;
   else   return 1;
}

//--------------------------------------------
// doBox()
//--------------------------------------------
function doBox($type, $msg, $config, $lang) {
   require_once(bugFixRequirePath("../head.php"));
   echo "<table class='table_layout_sections' cellspacing='0' cellpadding='2' style='height:180px;width:{$config['gallery_width']}'>
		<tr> 
			<td width='100%' class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")"; if($config['page_direction'] == "1")   echo ";text-align:right' align='right"; echo "'>";
			if($type=="error")   echo "{$lang['error_box_title']}";
			elseif($type=="msg")   echo "{$lang['msg_box_title']}";
			echo "</td>
		</tr>
		<tr> 
			<td width='100%' height='100%' align='center'>$msg</td>
		</tr>
   </table>";
   include(bugFixRequirePath("../foot.php"));
   exit();
}

//--------------------------------------------
// showPic()
//--------------------------------------------
function showPic($config, $lang, $row, $link_to, $alt) {
	//get current picture's full path, but display its thumbnail and link it to its original
	//new in v0.8.4
	$at_full = getPicturesPath($config, $row['file_name'], 0);
	$at_full_server = getPicturesPath($config, $row['file_name'], 1);
	
	//new in ZVG, is this a video?  If so, use default video pic as thumbnail
	if(!isVideo($config, "",$row['file_name'])) {
		$at_thumb = getThumbPath($at_full, $at_full_server, $config['thumb_suffix']);
		//$size = @getimagesize($at_full_server);
		//$size_thumb = @getimagesize($at_thumb[1]);
	}
	else {
		$at_thumb[0] = getSkinElement($config['stylesheet'], "images/video.png");
		$is_video = 1;
		//$size = array($config['thumb_width'],$config['thumb_height']);
		//$size_thumb = array($config['thumb_width'],$config['thumb_height']);
	}

	$size = @getimagesize($at_full_server);

	//if 0, set to default values
	if($link_to == '0')   $link_to = "pic_details.php?pid={$row['pid']}";
	if($alt == '0')   $alt = $row['file_name'];
	
	if($config['thumb_align'] == 0)   $thumb_align = "left";
	elseif($config['thumb_align'] == 1)   $thumb_align = "center";
	elseif($config['thumb_align'] == 2)   $thumb_align = "right";
	echo "<div align='$thumb_align'>
	<table border='0' cellpadding='0' cellspacing='0'>
	<tr>
	<td nowrap='nowrap' style='border: 0px solid #000'>
	<a href='$link_to' class='thumb_link'>
	";
	
	//new in ZVG (added "!$is_video" to guards below)
	//if thumbnail does not exist, but the size of the full image is less than the allowed thumb limit
	if(!$is_video && !file_exists($at_thumb[1]) && $size[0] <= $config['thumb_width'] && $size[1] <= $config['thumb_height']) {
	   echo "<img src='$at_full' alt='$alt {$lang['thumbnail_unavailable']}' title='$alt {$lang['thumbnail_unavailable']}' class='thumb' style='float:none' width='{$size[0]}' height='{$size[1]}' />";
	}
	//if thumbnail does not exist, and the full size image is too big
	elseif(!$is_video && !file_exists($at_thumb[1])) {
	   echo "<img src='$at_full' alt='$alt {$lang['thumbnail_oversize']}' title='$alt {$lang['thumbnail_oversize']}' class='thumb' style='float:none' width='{$config[thumb_width]}' height='{$config[thumb_height]}' />";
	}
	else { //if thumbnail DOES exist
		if($is_video)   $size_thumb =  @getimagesize($at_thumb[0]); //new in ZVG
		else   $size_thumb = @getimagesize($at_thumb[1]);
	   echo "<img src='{$at_thumb[0]}' alt='$alt' title='$alt' class='thumb' style='float:none' width='{$size_thumb[0]}' height='{$size_thumb[1]}' />";
	}
	
	echo "</a>
	</td>
	<td nowrap='nowrap' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/shadow_tile_right.gif") . ")' valign='top'>
	<img border='0' src='" . getSkinElement($config['stylesheet'], "images/shadow_up.gif") . "' width='7' height='7' alt='' /></td>
	</tr>
	<tr>    
	<td nowrap='nowrap' align='left' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/shadow_tile_down.gif") . ")'>
	<img border='0' src='" . getSkinElement($config['stylesheet'], "images/shadow_down.gif") . "' width='6' height='7' alt='' /></td>
	<td nowrap='nowrap'><img border='0' src='" . getSkinElement($config['stylesheet'], "images/shadow_corner.gif") . "' width='7' height='7' alt='' /></td>
	</tr>
	</table></div>";
}

//--------------------------------------------
// showDetailedPic()
//--------------------------------------------
function showDetailedPic($config, $lang, $row, $pic_size) {
	//get current picture's full path, but display its thumbnail and link it to its original
	//new in v0.8.4
	$at_full = getPicturesPath($config, $row['file_name'], 0);
	$at_full_server = getPicturesPath($config, $row['file_name'], 1);
	
	//new in ZVG, is this a video?  If so, use default video pic as thumbnail
	if(!isVideo($config, "",$row['file_name'])) {
		$at_thumb = getThumbPath($at_full, $at_full_server, $config['thumb_suffix']);
		$size = @getimagesize($at_full_server);
		$size_thumb = @getimagesize($at_thumb[1]);
	}
	else {
		$at_thumb[0] = getSkinElement($config['stylesheet'], "images/video.png");
		$size = array($config['thumb_width'],$config['thumb_height']);
		$size_thumb = array($config['thumb_width'],$config['thumb_height']);
		$pic_size = 0; //only use small size if it's a video
	}

	$w_of_window = $size[0]+18;
	$h_of_window = $size[1]+2;
	$window_pos = "top='+((screen.height - $h_of_window) / 2)+',left='+((screen.width - $w_of_window) / 2)+'";
	
	echo "<table border='0' cellpadding='0' cellspacing='0' dir='ltr'>
	<tr>
	<td nowrap='nowrap' style='border: 0px solid #000'>
	<a href='show.php?pid={$row['pid']}&amp;file={$row['file_name']}' class='thumb_link' target='_blank' onclick=\"window.open(this.href,'{$row['pid']}','width=$w_of_window,height=$h_of_window,resizable=yes,status=no,scrollbars=1$window_pos');return false\">";

	//display picture
	if($pic_size == 0) { //small
		echo "<img src='{$at_thumb[0]}' alt='{$lang['full_view']}' title='{$lang['full_view']}' class='thumb' width='{$size_thumb[0]}' height='{$size_thumb[1]}' />
			";
	}
	elseif($pic_size == 1) { //medium
        //pass the default dimensions for the medium size pic and then correct it as necessary
        //to just use default dimensions, pass an empty string as the third arg
        $arr_correct_dimensions = correctDimensions(ceil(($size[0]+$size_thumb[0])/2), ceil(($size[1]+$size_thumb[1])/2), 1024); //new in v0.9.4 DEV
        $med_width = $arr_correct_dimensions[0];
        $med_height = $arr_correct_dimensions[1];
		
		if($med_width > $size[0])   $med_width = $size[0];
		if($med_height > $size[1])   $med_height = $size[1];
		
		//CHANGE 'uploads' WHEN CATEGORIES ARE MAPPED TO FILESTORE!!!!!!!
		$img = "thumbify.php?pic={$row['file_name']}&amp;w=$med_width&amp;h=$med_height&amp;folder=uploads&amp;smart_resize=0";
		echo "<div style='background-color:#fff;background-image:url(" . getSkinElement($config['stylesheet'], "images/loading.gif") . "); background-position:center; background-repeat:no-repeat;width:".$med_width."px;height:".$med_height."px'>
			<img id='thephoto' src='$img' alt='{$lang['full_view']}' title='{$lang['full_view']}' class='thumb' width='$med_width' height='$med_height' />
			</div>
			";
	}
	elseif($pic_size == 2) { //original
		echo "<div style='background-color:#fff;background-image:url(" . getSkinElement($config['stylesheet'], "images/loading.gif") . "); background-position:center; background-repeat:no-repeat;width:{$size[0]}px;height:{$size[1]}px'>
			<img id='thephoto' src='$at_full' alt='{$lang['full_view']}' title='{$lang['full_view']}' class='thumb' width='{$size[0]}' height='{$size[1]}' />
			</div>
			";
		}

	echo "</a>
	</td>
	<td nowrap='nowrap' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/shadow_tile_right.gif") . ")' valign='top'>
	<img border='0' src='" . getSkinElement($config['stylesheet'], "images/shadow_up.gif") . "' alt='' /></td>
	</tr>
	<tr>    
	<td nowrap='nowrap' align='left' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/shadow_tile_down.gif") . ")'>
	<img border='0' src='{$config['stylesheet']}/images/shadow_down.gif' alt='' /></td>
	<td nowrap='nowrap'><img border='0' src='" . getSkinElement($config['stylesheet'], "images/shadow_corner.gif") . "' alt='' /></td>
	</tr>
	</table>";
}

//--------------------------------------------
// showEntry()
//--------------------------------------------
function showEntry($lang, $row, $config, $show_what) {
	$status = false;
	if($config['thumb_show_details']) {
		//output what?	
		echo "<span class='tiny_text'>";
		if(in_array("title",$show_what))   echo "<strong>{$row['title']}</strong><br />";
		if(in_array("description",$show_what))   echo "{$row['description']}<br />";
		if(in_array("date",$show_what))   echo "{$lang['last_updated']} {$row['d']}<br />";
		if(in_array("size",$show_what))   echo "{$lang['file_size']} {$row['fsize']}Kb<br />";
		if(in_array("category",$show_what))   echo "{$lang['category']} {$row['category']}<br />";
		if(in_array("rating",$show_what))   echo "{$lang['rating']} {$row['r']}<br />";
		if(in_array("comment_body",$show_what)) {
			$row['comment_body'] = str_replace("&lt;br &gt;","<br />", $row['comment_body']); //support line breaks (new in v0.9.4 DEV)
			echo "<strong><span class='tiny_text'>{$row['comment_body']}</span></strong><br />";
		}
		
		//show comments counter
		if(in_array("comments",$show_what)) {
			$c_row = query_numrows("comment_id","{$config['table_prefix']}comments","pid={$row['pid']} AND approved=1");
			echo "<span class='tiny_text'>{$lang['comments_counter']} "; if($c_row == "0") echo $lang['none']; else echo $c_row; echo "</span><br />";
		}
		
		if($config['views_show'] == 1 && !in_array("for_gods_sake_no_views",$show_what))   	echo "<span class='tiny_text'>{$lang['counter']} {$row['counter']} {$lang['times']}</span><br />";
		$status = true; //this is slightly (actually, completely) useless!
		echo "</span>";
	}
	
	return $status;
}

//--------------------------------------------
// showDetailedEntry()
//--------------------------------------------
function showDetailedEntry($lang, $row, $config, $pic_info) {
	$searchtype = "bycat";
	$searchquery = $row['category'];
	
	//get those matching selected category
	$statement = "SELECT pid, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter FROM {$config['table_prefix']}pictures WHERE category='$searchquery' AND approved='1' ORDER BY date DESC";
	$result = mysql_query($statement);
	$numrows = mysql_num_rows($result);
	
	//get number of pictures by this submitter
	$numrows_submitter = query_numrows("pid","{$config['table_prefix']}pictures","submitter='{$row['submitter']}' AND approved='1'");
	
	//get other stuff
	$statement_convert = "SELECT cname FROM {$config['table_prefix']}categories WHERE cid='$searchquery'";
	$result_convert = @mysql_query($statement_convert); 
	$row_convert = @mysql_fetch_array($result_convert);
	$direct_path = getPicturesPath($config, $row['file_name'], 0);
	$link_category = "display.php?t=$searchtype&amp;q=$searchquery&amp;nr=$numrows&amp;st=0&amp;upto=" . $config['perpage'] . "&amp;p=1";
	$link_submitter = "search.php?t=&amp;q=&amp;a={$row['submitter']}&amp;strength=0&amp;cat={0}&amp;nr=$numrows_submitter&amp;st=0&amp;upto=" . $config['perpage'] . "&amp;p=1&amp;by=0&amp;order=0";

	//show everything based on user's preferences
	echo "<table"; if($config['page_direction'] == "1")   echo " style='text-align:right'"; echo " width='100%'>";
	if($config['picture_details_show'] == "1") { //are we showing picture details?
		echo "<tr><td width='30%'><span class='header'>{$lang['title']}</span></td><td width='70%'>{$row['title']}</td></tr>
		<tr><td width='30%'><span class='header'>{$lang['category']}</span></td><td width='70%'><a href='$link_category'>{$row_convert['cname']}</a> ($numrows {$lang['pics_in_cat']})</td></tr>
		<tr><td width='30%'><span class='header'>{$lang['dimensions']}</span></td><td width='70%'>{$pic_info['dimensions']}</td></tr>
		";
	}
	if($config['submission_details_show'] == "1") { //are we showing submission details?
		if($row['submitter'] != '-' && $row['submitter'] != '')   echo "<tr><td width='30%'><span class='header'>{$lang['submitted_by']}:</span></td><td width='70%'><a href='$link_submitter'>{$row['submitter']}</a></td></tr>";
		echo "<tr><td width='30%'><span class='header'>{$lang['last_updated']}</span></td><td width='70%'>{$row['d']}</td></tr>
		";
	}
	if($config['file_details_show'] == "1") { //are we showing file details?
		echo "<tr><td width='30%'><span class='header'>{$lang['file_name']}</span></td><td width='70%'>{$row['file_name']}</td></tr>
		<tr><td width='30%'><span class='header'>{$lang['file_size']}</span></td><td width='70%'>{$row['fsize']}Kb</td></tr>
		";
	}
	//are showing other stuff?
	if($config['voting_show'] == 1)   echo "<tr><td width='30%'><span class='header'>{$lang['rating']}</span></td><td width='70%'>{$pic_info['rating']}</td></tr>";
	if($config['views_show'] == 1)   echo "<tr><td width='30%'><span class='header'>{$lang['counter']}</span></td><td width='70%'>{$row['counter']} {$lang['times']}</td></tr>";
	if($config['direct_path_show'] == 1)   echo "<tr><td width='30%'><span class='header'>{$lang['path_direct']}</span></td><td width='70%'><a href='".$direct_path."' target='_blank'>".$direct_path."</a></td></tr>";
    if($config['keywords_show'] == 1)   echo "<tr><td width='30%'><span class='header'>{$lang['keywords']}</span></td><td width='70%'>{$row['keywords']}</td></tr>";
	
	//any user-defined fields?
	$fields = getCurrentUserDefinedFields("csv","udf_","",$config,$lang);
	if($fields != "") {
		$statement = "SELECT " . $fields ." FROM {$config['table_prefix']}pictures WHERE pid='{$row['pid']}'";
		$result_udf = mysql_query($statement);
		$row_udf = mysql_fetch_array($result_udf);
		echo displayCurrentUserDefinedFields("<tr><td width='30%'><span class='header'>### </span></td><td width='70%'>", "</td></tr>", $row_udf, "raw", "0", $config, $lang);
	}
	
	echo "</table>";
	
	return 1;
}

//--------------------------------------------
// isVideo(): Checks whether or not a picture is a video file
//checks the db on pid or the filename depending on whether the 2nd or 3rd param is null
//--------------------------------------------
function isVideo($config, $pid, $filename) {
	if($pid != "") { //if 2nd param is not null, check the db
		$statement = "SELECT is_video FROM {$config['table_prefix']}pictures WHERE pid=$pid";
		$result = mysql_query($statement);
		$row = mysql_fetch_array($result);
		return $row['is_video'];
	}
	elseif($filename != "") { //if 3rd param is not null, check the filename
		$bits = splitFilenameAndExtension($filename);
		$bits[1] = strtolower($bits[1]);
		if(is_array($config['allowed_media_filetypes'])) { //make sure that it's first defined as an array
			if(in_array($bits[1],$config['allowed_media_filetypes']))   return true;
			else   return false;
		}
	}
	
	return false;
}

//--------------------------------------------
// forceDownloadFile(): New in ZVG, force downloads a file
// based on function written by aarondunlap.com at http://www.php.net/header
//--------------------------------------------
function forceDownloadFile($file) {
   //First, see if the file exists
   if (!is_file($file)) { die("<b>404 File not found!</b>"); }

   //Gather relevent info about file
   $len = filesize($file);
   $filename = basename($file);
   $file_extension = strtolower(substr(strrchr($filename,"."),1));

   //This will set the Content-Type to the appropriate setting for the file
   switch($file_extension) {
     case "mpeg":
     case "mpg": $ctype="video/mpeg"; break;
     case "mov": $ctype="video/quicktime"; break;
	 case "avi": $ctype="video/x-msvideo"; break;
     case "wmv": $ctype="video/x-msvideo"; break;

     //The following are for extensions that shouldn't be downloaded
     case "php":
     case "htm":
     case "html":
     case "exe":
     case "asp":
     case "txt": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;

     default: $ctype="application/force-download";
   }

   //Begin writing headers
   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: public"); 
   header("Content-Description: File Transfer");
   
   //Use the switch-generated Content-Type
   header("Content-Type: $ctype");

   //Force the download
   $header="Content-Disposition: attachment; filename=".$filename.";";
   header($header );
   header("Content-Transfer-Encoding: binary");
   header("Content-Length: ".$len);
   @readfile($file);
   exit;
}

//--------------------------------------------
// loggedIn()
//--------------------------------------------
function loggedIn($config) {
	//get cookies
	$cookie_name_username = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
	$cookie_name_hash = $config['cookie_prefix'] . 'hash';
	
	$numrows = @query_numrows("uid","{$config['table_prefix']}users","username=\"{$_COOKIE[$cookie_name_username]}\" AND cookie=\"{$_COOKIE[$cookie_name_hash]}\"");
	if($numrows == 1)   $is = 1;
	else   $is = 0;

	return $is;
}

//--------------------------------------------
// adminStatus()
//--------------------------------------------
function adminStatus($config) {
	$cookie_name = trim(str_replace(array("|","/","$",":","*","`","?",";"),"", $_COOKIE["{$config['cookie_prefix']}username"]));
	$row = mysql_fetch_array(mysql_query("SELECT admin_status FROM {$config['table_prefix']}users WHERE username='$cookie_name'"));
	$admin_status = $row['admin_status'];
	if($admin_status)   $is = 1;
	else   $is = 0;
	
	return $is;
}

//--------------------------------------------
// sessionRegistered()
//--------------------------------------------
function sessionRegistered($config) {
	$cookie_value = $_COOKIE["{$config['cookie_prefix']}username"];
	if(isset($_SESSION["{$config['cookie_prefix']}_$cookie_value"]) && $_SESSION["{$config['cookie_prefix']}_$cookie_value"] == "1")   $is = 1;
	else   $is = 0;
	
	return $is;
}

//--------------------------------------------
// showAdminTools(): display admin options for particular picture
//--------------------------------------------
function showAdminTools($config, $lang, $row) {
	$orange_box = "border:1px solid;border-color:#FC9 #630 #330 #F96;padding:0 3px;font:bold 10px verdana,sans-serif;color:#fff;background:#F60;text-decoration:none;margin:0";
	
	//calculate the height of the edit box based on the number of custom fields
	$row_ud = mysql_fetch_array(mysql_query("SELECT COUNT(fid) as n FROM {$config['table_prefix']}ud_fields"));
	$h = $row_ud['n'];
	
	$h *= 30; //so if we have 4 user-defined fields, the edit window's height will grow by 120 px
	$h += 300;
	
	if(loggedIn($config) && adminStatus($config) && sessionRegistered($config)) {
	   echo "<a href='edit.php?id={$row['pid']}' onclick=\"window.open('edit.php?id={$row['pid']}','picman','width=600,height=$h,resizable=yes');return false\" style='$orange_box'>&nbsp;&nbsp;{$lang['edit']}&nbsp;&nbsp;</a> 
	   <a href='delete.php?id={$row['pid']}' onclick=\"window.open('delete.php?id={$row['pid']}','picman','width=600,height=200,resizable=yes');return false\" style='$orange_box'>{$lang['delete']}</a>
	   <br />";
	}
}

//--------------------------------------------
// showAdminCommentTools(): display admin options for particular comment
//--------------------------------------------
function showAdminCommentTools($config, $lang, $row) {
   $orange_box = "border:1px solid;border-color:#FC9 #630 #330 #F96;padding:0 3px;font:bold 10px verdana,sans-serif;color:#fff;background:#F60;text-decoration:none;margin:0";

   //first, add slashes to comments before doing anything with them.
   $row['comment_body'] = addslashes($row['comment_body']);
			
   if(loggedIn($config) && adminStatus($config) && sessionRegistered($config)) {
		echo "<a href='comment_man.php?id={$row['comment_id']}&amp;is_what=comment-edit-form' onclick=\"window.open('comment_man.php?id={$row['comment_id']}&is_what=comment-edit-form','picman','width=600,height=300,resizable=yes');return false\" style='$orange_box'>&nbsp;&nbsp;{$lang['edit']}&nbsp;&nbsp;</a> 
		<a href='comment_man.php?pid={$row['pid']}&amp;id={$row['comment_id']}&amp;is_what=comment-delete' onclick=\"return confirm('{$lang['delete_comment_confirm']}');\" style='$orange_box'>{$lang['delete']}</a>
	<br />
	";
   }
}

//--------------------------------------------
// getPicturesPath()
// @type_of_path: 0 is internet 1 is server
//--------------------------------------------
function getPicturesPath($config, $filename, $type_of_path, $replace_last_nibble_with_this="") {
	require_once("f_cats.php");

	$statement = "SELECT category FROM {$config['table_prefix']}pictures WHERE file_name='".$filename."'"; //echo $statement;
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	$cid = $row['category'];
	
	if($cid != '') {
		$statement = "SELECT cname, cname_for_url FROM {$config['table_prefix']}categories WHERE cid=$cid";  //modified in v0.9.4
		$result = mysql_query($statement);
		$row = mysql_fetch_array($result);
		$cname = ($config['v094paths'] == 0) ? $row['cname'] : $row['cname_for_url']; //v0.9.4
		$arr = getParents($config, $cid);
		
		//now buld the picture's path
		if($type_of_path == 0)   $path = $config['upload_dir'];
		elseif($type_of_path == 1)   $path = $config['upload_dir_server'];
		foreach ($arr as $key => $value) {
			if($key == 0)   continue;
			$path .= $value[1] . "/";
		}
		
		//$path .= $cname."/".$filename;
		
		if($replace_last_nibble_with_this != "")   $path .= $cname."/".$replace_last_nibble_with_this;
		else   $path .= $cname."/".$filename;
	}
	//else {
	//	echo "Cannot get picture's path ($filename).  Make sure you've removed orphaned pictures in directory 'uploads/'";
	//}
	
	return $path;
}

//--------------------------------------------
// getPicturesPathFromCategory()
// @type_of_path: 0 is internet 1 is server
//--------------------------------------------
function getPicturesPathFromCategory($config, $cid, $type_of_path, $replace_last_nibble_with_this, $is_what = "is_not_rebuilding_directories") {
	require_once("f_cats.php");

	$statement = "SELECT cname, cname_for_url FROM {$config['table_prefix']}categories WHERE cid='".$cid."'"; //modified in v0.9.4
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	//$cname = ($config['v094paths'] == 0) ? $row['cname'] : $row['cname_for_url']; //v0.9.4
	if($config['v094paths'] == 0)   $cname = $row['cname'];
	else   $cname = $row['cname_for_url']; //v0.9.4
	
	$arr = getParents($config, $cid, $is_what);
	//echo "<br />parents are "; print_r($arr);
	//now buld the picture's path
	if($type_of_path == 0)   $path = $config['upload_dir'];
	elseif($type_of_path == 1)   $path = $config['upload_dir_server'];
	foreach ($arr as $key => $value) {
		if($key == 0)   continue;
		$path .= $value[1] . "/";
	}
	
	if($replace_last_nibble_with_this != "")   $path .= $replace_last_nibble_with_this;
	else   $path .= $cname."/";
	
	return $path;
}

//--------------------------------------------
// getBanned()
//--------------------------------------------
function getBanned($config) {
	$statement = "SELECT bid, ip_address FROM {$config['table_prefix']}banned";
	$result = mysql_query($statement);
	while($row = mysql_fetch_array($result)) {
	   $bid = $row['bid'];
	   $banned[$bid] = $row['ip_address'];
	}
	return $banned;
}

//--------------------------------------------
// getCurrentPath(): returns current script's full server path with an optional append var
//--------------------------------------------
function getCurrentPath($append) {
	$bits = explode("/",$_SERVER['PATH_TRANSLATED']);
	unset($bits[sizeof($bits)-1]);
	$path = implode("/",$bits); $path .= "/$append";
	
	return $path;
}

//--------------------------------------------
// getCurrentPath2(): returns current script's full server path with an optional append var (this version does not use $_SERVER)
//--------------------------------------------
function getCurrentPath2($append) {
	require(bugFixRequirePath("../config.php"));
	$path = str_replace("uploads/",$append,$config['upload_dir_server']);
	//echo "upload dir server is {$config['upload_dir_server']} path is $path append is $append";
	return $path;
}

//--------------------------------------------
// getCurrentInternetPath2()
//--------------------------------------------
function getCurrentInternetPath2($config, $append) {
	$path = str_replace("uploads/",$append,$config['upload_dir']);
	return $path;
}

//THE COPYRIGHT NOTICE SHOWN THE LINE BELOW MAY NOT BE REMOVED, EDITED OR CHANGED IN ANY WAY.
//DOING SO IS AGAINST THE GNU GPL.  OFFENDERS WILL RECEIVE WEDGIES.  
function returner($lang,$config) {
   echo "<div class='copyright_block'><a class='copyright' href='http://www.cyberiapc.com' target='_blank'>{$lang['powered_by']} Zenith Picture Gallery v{$config['version']} &copy; CyberiaPC.com</a></div>";
}

//--------------------------------------------
// getUsers()
//--------------------------------------------
function getUsers($config) {
	$statement = "SELECT uid, username FROM {$config['table_prefix']}users";
	$result = mysql_query($statement);
	while($row = mysql_fetch_array($result)) {
	   $uid = $row['uid'];
	   $users[$uid] = $row['username'];
	}
	return $users;
}

//--------------------------------------------
// getLangs()
//--------------------------------------------
function getLangs($config) {
	$dir = "lang";
	if($dh = opendir($dir)) {
		// iterate over file list
		while (false !== ($filename = readdir($dh))) {
			if (($filename != ".") && ($filename != "..")) {
				$key = substr($filename,0,2);
				$filelist[$key] = $filename;
			}
		}
		
		// close directory
		closedir($dh);
	}
	
	return $filelist;
}

//--------------------------------------------
// getSkins()
//--------------------------------------------
function getSkins($config) {
	$dir = "skins";
	if($dh = opendir($dir)) {
		// iterate over file list
		while (false !== ($dir = readdir($dh))) {
			if ($dir != "." && $dir != "..") {
				$dirlist[] = $dir;
			}
		}
		
		closedir($dh); //close directory
	}
	
	return $dirlist;
}

//--------------------------------------------
// getIncomingPics()
//--------------------------------------------
function getIncomingPics($config) {
	$dir = "incoming";
	if($dh = opendir($dir)) {
		// iterate over file list
		while (false !== ($filename = readdir($dh))) {  
			if ($filename != "." && $filename != ".." && $filename != "thumbs" && $filename != ".htaccess" && $filename != "index.htm") {
				$filelist[] = $filename;
			}
		}

		// close directory
		closedir($dh);
	}
	
	return $filelist;
}

//--------------------------------------------
// getFullSizePics()
//--------------------------------------------
function getFullSizePics($config, $dir, $filelist) {
	//$dir = "uploads";
	if($dir != 'uploads') {
		//modified in v0.9.4
		if($config["v094paths"] == 0)   $statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname='".$dir."'";
		else   $statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname_for_url='".$dir."'";
		
		$result = mysql_query($statement);
		$row = mysql_fetch_array($result);
		$path = getPicturesPathFromCategory($config, $row['cid'], 1, "");
		//echo "path is $path<br />";
	}
	else {
		$path = $config['upload_dir_server'];
	}
	
	if($dh = opendir($path)) {
		//echo "opening path $path";
		
		//iterate over file list
		while (false !== ($filename = readdir($dh))) { 
			//echo "filename is $filename\n";
			//modified in v0.9.4 (added extra guard for checking pending pics' suffix)
			if ($filename != "." && $filename != ".." && $filename != ".htaccess" && $filename != "index.htm" && 
				($config['pending_pics_suffix'] == "" || !strstr($filename,".{$config['pending_pics_suffix']}")) && 
				strpos($filename, $config['thumb_suffix']) === false && !isVideo($config,"","$path$filename")) { 
				//new in ZVG, modified guard, don't rebuild thumbs for videos
				
				if(is_dir("$path$filename")) {
					//echo "<br />recursing...";
					$filelist = getFullSizePics($config, $filename, $filelist);	
				}
				else {
					$filelist[] = array("$path", "$filename");
					//echo "<br />adding pic to filelist...$filename";
				}
			}
		}
		
		closedir($dh); //close directory
	}
	
	//echo "is filelist an array? " . is_array($filelist);
	//new in v0.9.2 DEV (fixed the problem of gallery showing 1 full size pic in fresh installs)
	if(is_array($filelist))   return $filelist;
	else   return NULL;
}

//--------------------------------------------
// quick_escaper(): escape data with check for magic_quotes
//--------------------------------------------
function quickEscaper($d) {
   global $connection;
   if(ini_get('magic_quotes_gpc')) {
      $d = stripslashes($d);
   }
   return mysql_real_escape_string($d,$connection);
}

//--------------------------------------------
// charsEscaper()
//--------------------------------------------
function charsEscaper($data, $escape_chars_array) {
	$escape_chars_array[] = "select ";
	$escape_chars_array[] = "insert ";
	$escape_chars_array[] = "union";
	$escape_chars_array[] = "drop ";
	$escape_chars_array[] = "--";
	$escape_chars_array[] = "delete ";
	$escape_chars_array[] = "#";

	return trim(str_Ireplace_08($escape_chars_array,"", $data));
}

//--------------------------------------------
// strIreplace_08()
//--------------------------------------------
function str_Ireplace_08($search, $replace, $subject) {
	$regex_special_chars = "$|*+.?#^@";
	if (is_array($search)) {
		foreach ($search as $word) {
			if(strpos($regex_special_chars,$word) !== false) {
				$temp = $word;
				$word = "\\" . $temp;
			}
			$words[] = "@".$word."@i";
		}
  	}
	else {
		if(strpos($regex_special_chars,$search) !== false) $search = "\\" . $search;
		$words = "@".$search."@i";
	}

	return preg_replace($words, $replace, $subject);
}

//--------------------------------------------
// dealWithSingleQuotes()
//--------------------------------------------
function dealWithSingleQuotes($str) {
	return str_replace("'","''",$str); //replace ' with ''
}

//--------------------------------------------
// makeStringFileStructureFriendly(), recall that regex meta-chars are  ()+*?$.^\[|
//--------------------------------------------
function makeStringFileStructureFriendly($str) {
	$search = array ('@\@@',
					  '@\\\@',
					  '@/@',
					  '@\|@',
					  '@<@',
					  '@>@',
					  '@\*@',
					  '@\?@',
					  '@:@',
					  '@"@',
					  );
	$replace = array ('','','','','','','','','','');
					  
	return preg_replace($search, $replace, $str);
}

//--------------------------------------------
// strToHex(): Convert chars to their hex representations
//--------------------------------------------
function strToHex($str) {
	$pattern = '/^[a-zA-z0-9]/'; //don't convert english alphanumerics
	for($i=0;$i<strlen($str);$i++) {
		if(!preg_match($pattern,$str[$i]))   $str_new .= "%".bin2hex($str[$i]); //convert chars to hex
		else   $str_new .= $str[$i];
	}

	return $str_new;
}

//--------------------------------------------
// hexToStr(): Convert hex to binary
//--------------------------------------------
function hexToStr($str) {
	/*$pattern = '/^[a-zA-z0-9]/'; //don't convert english alphanumerics
	$bits = explode("%",$str); //tokenize $str on the %
	foreach($bits as $key => $value) {
		//if(!preg_match($pattern,$value))   $str_new .= "%".bin2hex($str[$i]); //convert chars to hex
		//else   $str_new .= $str[$i];
		echo $value . "<br />";
	}*/
	//chr(hexdec(substr($temp,$i,2)));

	return $str;
}

//--------------------------------------------
// array2set()
// @include_curly_braces E {0=no,1=yes}
// @touch_what E {key=array's keys, value=array's value}
// e.g. {1,2,3,4}
//--------------------------------------------
function array2set($arr, $include_curly_braces, $touch_what, $global_prepend, $global_append) {
	//make sure array isn't empty
	if(sizeof($arr) == 0)   return "";
	
	//should we include curly braces?
	if($include_curly_braces == 1) {
		$curly = "{";
		$wurly = "}";
	}
	else {
		$curly = "";
		$wurly = "";
	}

	//if we've only got 1 element, don't return a set (actually that's not quite true)
	if(sizeof($arr) == 1) {
		//the reason for this is because the array need not have a value that has a key of 0
		//e.g. in add.php whe the SQL query is being built for user-defined-fields
		//print_r($arr);
		$keys = array_keys($arr);
		//print_r($keys);
		$first_arr_key = $keys[0];
		$first_arr_value = $arr[$first_arr_key];
	
		if($touch_what == "key")   $str = $global_prepend . $curly . $first_arr_key  . $wurly . $global_append;
		elseif($touch_what == "value")   $str = $global_prepend . $curly . $first_arr_value . $wurly . $global_append;
		
		return $str;
	}
	else {
	    $arr_expanded=$global_prepend . $curly;
		foreach($arr as $key => $value) {
			if($touch_what == "key")   $arr_expanded .= $key . ",";
			elseif($touch_what == "value")   $arr_expanded .= $value . ",";
		}
		return substr($arr_expanded,0,strlen($arr_expanded)-1). $wurly . $global_append;
	}
}

//--------------------------------------------
// set2array()
//--------------------------------------------
function set2array($set) {
	if(!strstr($set, ",")) {
		//get rid of curly braces only if they exist
		if($set[0] == "{" && $set[strlen($set)-1] == "}") {
			$cat = substr($set,1,strlen($set)-2);
		}
		return array($cat); //if it's a single value, just return the value
	}
	else {
		$cat = substr($set,1,strlen($set)-2); //get rid of curly braces
		$cat = explode(",",$cat); //convert set-form of cats into array
		return $cat;
	}
}

//--------------------------------------------
// arrayHacker(): does some crazy stuff with arrays
// @touch_what E {key=array's key,value=array's value}
// @include_curly_braces E {0=no,1=yes}
// @prepend and append are added to each element in the array
// @global_prepend and global_append are added to the output string
// function returns a string
//--------------------------------------------
function arrayHacker($arr,$touch_what,$include_curly_braces,$prepend,$append,$global_prepend,$global_append,$seperator) {
	//make sure array isn't empty
	if(sizeof($arr) == 0)   return "";
	
	//should we include curly braces?
	if($include_curly_braces == 1) {
		$curly = "{";
		$wurly = "}";
	}
	else {
		$curly = "";
		$wurly = "";
	}
	
	//build the output string
	$output = $global_prepend . $curly; 
	foreach($arr as $key => $value) {
		$output .= $prepend;
		//either add the array's keys or values to the string
		if($touch_what == "key")   $output .= $key;
		elseif($touch_what == "value")   $output .= $value;
		$output .= $append . $seperator;
		
		//replace any occurences of ### with the key
		$output = str_replace("###",$key,$output);
	}
	//remove seperator from the end of the string
	if($output[strlen($output)-1] == $seperator)   $output = substr($output,0,strlen($output)-1);
	$output .= $wurly . $global_append;

	return $output; //returns a string
}

//--------------------------------------------
// assertActive()
//--------------------------------------------
function assertActivate() {
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_WARNING, 0);
	assert_options(ASSERT_QUIET_EVAL, 1);
	assert_options(ASSERT_CALLBACK, 'assertHandler');
}

//--------------------------------------------
// assertHandler()
// this safety function handles cases where invariants are violated.  General errors
// are handled differently and do not cause the script to end as is the case here.
//--------------------------------------------
function assertHandler($file, $line, $code) {
   echo "<hr /><b>Invariant violated: line '$line', file '$file'<br />
   Please report this error and the full URL to the <a href='http://www.cyberiapc.com/forums' target='_blank'>author</a> along with a description of what you were doing at the time.<br /></b><hr />";
   exit();
}

//--------------------------------------------
// getThumbPath(): Returns a string array containing the Internet and server paths of the thumb
//--------------------------------------------
function getThumbPath($at_full, $at_full_server, $suffix) {
		//create internet path string
		$bits = splitFilenameAndExtension($at_full);
		$bits[count($bits)-2] .= $suffix;
		$at_thumb[0] = implode('.', $bits);
		
		//create server path string
		$bits = splitFilenameAndExtension($at_full_server);
		$bits[count($bits)-2] .= $suffix;
		$at_thumb[1] = implode('.', $bits);
		
		return $at_thumb;
}

//--------------------------------------------
// splitFilenameAndExtension()
//--------------------------------------------
function splitFilenameAndExtension($filename) {
		$str = strrev($filename);
		$bits = explode(".",$str);
		$extension = strrev($bits[0]);
		$name = substr($filename,0,strlen($filename)-strlen($extension)-1);
		$arrBits[0] = $name; $arrBits[1] = $extension;

		return $arrBits;
}

//--------------------------------------------
// validateNumericFields(): validate that all keys in the passed-in array are numeric
//--------------------------------------------
function validateNumericFields($fields) {
	$is_what = "no_errors";
	foreach($fields as $key => $value) {
		if(!is_numeric($value)) {
			$is_what = "error";
			break;
		}
	}
	return $is_what;
}

//--------------------------------------------
// validateAlphaNumericFields(): validate that all keys in the passed-in array are alphanumeric
//--------------------------------------------
function validateAlphaNumericFields($fields) {
	$is_what = 1;
	foreach($fields as $key => $value) {
		if(preg_match("/[^A-Z0-9_]/i", $value)) {
			$is_what = 0; //echo "hala walla!!!!";
			break;
		}
	}
	return $is_what;
}

//--------------------------------------------
// validateEmailFields(): validate that all keys in the passed-in array are valid email addresses
// pattern credit: sam at macrepublic dot com
//--------------------------------------------
function validateEmailFields($fields) {
	$is_what = 1;
	foreach($fields as $key => $value) {
		if(!preg_match("/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,4})+$/", $value)) {
			$is_what = 0;
			break;
		}
	}
	return $is_what;
}

//--------------------------------------------
// showSkinsLangRssForm(): allows the user to choose a skin, a lang or get to the rss feed
//--------------------------------------------
function showSkinsLangRssForm($config, $lang) {
	echo "<form name='frmSkin' enctype='multipart/form-data' action='index.php' method='post' style='margin:0;padding:0'>
	<table class='table_layout_main' style='width:{$config['gallery_width']}' cellpadding='3' cellspacing='2'>
	<tr><td width='33%' valign='middle' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	";
	
	//allow the user to choose a skin
	$skins = getSkins($config);
	echo "<select name='skinSelect' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	<option value='-1'>{$lang['skin_select']}</option>
	<option value='-2'></option>";
	foreach($skins as $key => $value) {
		echo "<option value='$value'";	
		echo ">$value</option>";
	}
	echo "</select>&nbsp;<input type='submit' name='submit_skin' value='{$lang['button_go']}' class='submitButtonTiny' />
	</td>
	";
	
	//allow the user to choose a lang
	$langs = getLangs($config);
	echo "<td width='33%' align='center' valign='middle' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	<select name='langSelect' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	<option value='-1'>{$lang['lang_select']}</option>
	<option value='-2'></option>";
	foreach($langs as $key => $value) {
		//strip the ".php" after making sure it exists in the filename
		if(strtolower(substr($value,strlen($value)-3)) != "php")   continue;
		$value = substr($value,0,strlen($value)-4);		
		echo "<option value='$value'";	
		echo ">$value</option>";
	}
	echo "</select>&nbsp;<input type='submit' name='submit_lang' value='{$lang['button_go']}' class='submitButtonTiny' />
	</td>
	";
	
	//show link to rss feed
	echo "<td width='33%' align='right' valign='bottom'>
	<a href='rss.php?n=10'><img src='" . getSkinElement($config['stylesheet'], "images/xml.gif") . "' alt='{$lang['rss']}' title='{$lang['rss']}' border='0' /></a>
	</td></tr>
	</table>
	</form>
	";
}

//--------------------------------------------
// showWelcomeBox(): Displays misc stats and cat jump form
//--------------------------------------------
function showWelcomeBox($config, $lang) {
	echo "<form name='frmCatOnlySearch' enctype='multipart/form-data' action='display.php' method='post' style='margin:0;padding:0'>
	<table class='table_layout_sections' style='width:{$config['gallery_width']}'>";
	$numpics = @mysql_num_rows(mysql_query("SELECT pid FROM {$config['table_prefix']}pictures WHERE approved=1"));
	$numcats = @mysql_num_rows(mysql_query("SELECT cid FROM {$config['table_prefix']}categories"));
	$numcomms = @mysql_num_rows(mysql_query("SELECT comment_id FROM {$config['table_prefix']}comments WHERE approved=1"));
	$statement = "SELECT date AS d FROM {$config['table_prefix']}pictures WHERE approved=1 ORDER BY date DESC LIMIT 1";
	$result = @mysql_query($statement);
	$row = @mysql_fetch_array($result);
	
	//apologies for this extremely crude and dirty workaround.
	$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
	if($row['d'] != null)   $row['d'] = strftime($config['short_date_format'],$row_t['t'] + $config['timezone_offset']);			
	$lastadded = $row['d'];
	
	$row = @mysql_fetch_array(@mysql_query("SELECT SUM(counter) AS v FROM {$config['table_prefix']}pictures WHERE approved=1"));
	$totalviews = $row['v'];
	
	if($config['allow_registrations'] == 1) { //new in v0.9.4 DEV, added guard
		$members = @query_numrows("uid","{$config['table_prefix']}users","1=1");
		$row = @mysql_fetch_array(@mysql_query("SELECT uid, username FROM {$config['table_prefix']}users ORDER BY join_date DESC LIMIT 1"));
		$lastmember = "<a href='profile.php?m={$row['uid']}'>{$row['username']}</a>";
	}
	
	echo "<tr><td valign='middle' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	<b>{$config['title']}</b><br /><span class='tiny_text_dark'>{$config['description']}</span><br />";
	
	//display cats filter form
	$cats = getCatsTreeView(0,0,$config,$lang);
	echo "<br /><select name='byCat' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">";
	foreach($cats as $key => $value) {
		if($key == 0) {
			$key  = -1;
			echo "<option value='$key'>{$lang['select_a_category']}";
		}
		else   echo "<option value='$key'>$value";
		
		if($value[0] == ".")   echo "</optgroup>"; //if it's a parent category
		else   echo "</option>";
	}
	
	echo "</select>&nbsp;<input type='submit' name='submit' value='{$lang['button_go']}' class='submitButtonTiny' />
	</td><td align='right' valign='top' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">";
	
	printf($lang['stats_line1'] . "<br />", $numpics, $numcats);
	printf($lang['stats_line2'] . "<br />", $lastadded);
	printf($lang['stats_line3'], $totalviews); 
	if($config['allow_registrations'] == 1) {
		printf("<br />" . $lang['stats_line5'], $lastmember); //new in v0.9.4 DEV, added guard (sp?)
		echo " <a href='#' onclick=\"return hidify_showify('member_stats','more_less_text','$path_skin', '{$lang['less']}', '{$lang['more']}', 'span');\"><span id='more_less_text'>{$lang['more']}</span></a>";
	}
	else   printf("<br />" . $lang['stats_line6'], $numcomms); //always show 4 lines for stats
	
	echo "<div id='member_stats' style='display:none'>";
		if($config['allow_registrations'] == 1)   printf($lang['stats_line4'] . "<br />", $members); //new in v0.9.4 DEV, added guard (sp?)
		printf($lang['stats_line6'] . "<br />", $numcomms);
	echo "</div>";
	
	echo "</td></tr>
	</table>
	</form>";
}

//--------------------------------------------
// getCurrentUserDefinedFields(): gets the user-defined fields
// format E {arr, csv}
// arr: e.g. {12 -> camera type, 15 -> camera model}
// csv: e.g. {12,13}
// prepend and append are not applied to arr's
//--------------------------------------------
function getCurrentUserDefinedFields($format, $prepend, $append, $config, $lang) {
	$statement = "SELECT fid, ud_field_name FROM {$config['table_prefix']}ud_fields";
	$result = mysql_query($statement);
	while($row = mysql_fetch_array($result)) {
		$fid = $row['fid'];
		//how are we formatting the data?
		if($format == "arr") {
			$ud_fields[$fid] = $row['ud_field_name'];
		}
		elseif($format == "csv") {
			$ud_fields .= $prepend.$fid.$append.",";
		}
	}
	
	//make sure there isn't a comma at end of a csv string
	if($format == "csv" && $ud_fields[strlen($ud_fields)-1] == ",")
		$ud_fields = substr($ud_fields,0,strlen($ud_fields)-1);
		
	return $ud_fields;
}

//--------------------------------------------
// displayCurrentUserDefinedFields(): displays the user-defined fields
// the returned data is prefixed with prepend and suffixed with append 
// @pid is for getting the fields for a particular picture
// @values_to_show are the values to be displayed for the keys.
//   they can either be from the db or user-specified
// @show_as_what E {textbox, raw}
//--------------------------------------------
function displayCurrentUserDefinedFields($prepend, $append, $values_to_show, $show_as_what, $show_null_ones, $config, $lang) {
	//what fields do we have?
	$ud_fields = getCurrentUserDefinedFields("arr","","",$config,$lang);
	
	if(sizeof($ud_fields) == 0)   return "";
	//build the output string
	$output = ""; //unnecessary, but to improve readability
	foreach($ud_fields as $key => $name) {	
		$key_for_value = "udf_".$key;

		//don't display rows that are empty
		if($show_null_ones == "0" && $values_to_show[$key_for_value] == "")   continue;
		
		//currently, we're assuming that all ud_fields are textboxes with maxlength=255
		$output .= $prepend;
		if($show_as_what == "textbox")
			$output .="<input type='text' name='udf[$key]' value='{$values_to_show[$key_for_value]}' class='spoiltTextBox' maxlength='255' />";
		elseif($show_as_what == "raw")
			$output .="{$values_to_show[$key_for_value]}";
		//replace any occurences of ### with the name
		$output = str_replace("###",$name,$output);
		$output .= $append."
		";
	}

	return $output;
}

//--------------------------------------------
// isIpBlacklisted(): checks if an IP address is blacklisted
//--------------------------------------------
function isIpBlacklisted($users_ip, $config, $lang) {
	$statement = "SELECT COUNT(ip_address) as n FROM {$config['table_prefix']}banned WHERE ip_address='$users_ip'";
	//echo $statement;
	$result_ip = mysql_query($statement);
	$row_ip = mysql_fetch_array($result_ip);
	if($row_ip['n'] > 0)   doBox("error", $lang['add_banned'], $config, $lang);
}

//--------------------------------------------
// showLastAddedPictures()
//--------------------------------------------
function showLastAddedPictures($config, $lang) {
	require_once("f_search.php");
	
	//new in v0.9.4
	$statement = "SELECT pid, file_name, who_can_see_this, description, ROUND(file_size/1024) AS fsize, file_type, title, date AS d, pic_width, pic_height, keywords, approved, category, counter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE approved=1 AND ";
	$statement .= (!loggedIn($config) || !adminStatus($config)) ? "who_can_see_this='0' AND " : "";
	$statement .= "cid=category  AND {$config['table_prefix']}categories.cshow=1 ORDER BY date DESC LIMIT {$config['lastadded_perpage']}";

	$result = mysql_query($statement);
	if($config['debug_mode'] == 1) {
		echo "{$lang['debug_related3']} $statement<br />{$lang['debug_related4']} " . mysql_num_rows($result) . " rows";
	}
	outputSearchResults($lang, $result, $config, 0, mysql_num_rows($result), $lang['head_last_added']); //output last added pictures
}

//--------------------------------------------
// showRandomPictures()
//--------------------------------------------
function showRandomPictures($config, $lang) {
	require_once("f_search.php");
	
	//new in v0.9.4
	$statement = "SELECT pid, file_name, description, who_can_see_this, ROUND(file_size/1024) AS fsize, file_type, title, date AS d, pic_width, pic_height, keywords, approved, category, counter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE approved=1 AND ";
	$statement .= (!loggedIn($config) || !adminStatus($config)) ? "who_can_see_this='0' AND " : "";
	$statement .= "{$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}categories.cshow=1 ORDER BY RAND() DESC LIMIT {$config['random_perpage']}";

	$result = mysql_query($statement);
	if($config['debug_mode']) {
		echo "{$lang['debug_related3']} $statement<br />{$lang['debug_related3']} " . mysql_num_rows($result) . " rows";
	}
	outputSearchResults($lang, $result, $config, 0, mysql_num_rows($result), $lang['head_random']); //output random pictures
}

//--------------------------------------------
// showPicturesAsAlbum()
//--------------------------------------------
function showPicturesAsAlbum($config, $lang) {
	$statement = "SELECT cid, cname FROM {$config['table_prefix']}categories WHERE cshow=1 AND parent=-1 ORDER BY cname ASC";
	$result = mysql_query($statement);
	$num_cats = mysql_num_rows($result);
	
	//display search results
	//determine td widths based on thumbs_perrow
	$td_width = 100 / $config['thumbs_perrow'];
	
	echo "<table class='table_layout_main' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
		<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='$td_width%' "; if($config['page_direction'] == "1")   echo "dir='rtl' style='text-align:right' align='right'"; echo ">{$lang['head_album_view']}</td></tr>";
		
	$j=0;
	while($j < $num_cats) {
		//output first column
		echo "<tr>";
		$result = showPicturesAsAlbumCell($td_width, $result, $config, $lang);
		$j++;

		//output rest of columns
		for($i=1;$i<$config['thumbs_perrow'];$i++) {	
			if($j < $num_cats) {
				$result = showPicturesAsAlbumCell($td_width, $result, $config, $lang);
				$j++;
			}
		}
		echo "</tr>";
	}
	echo "</table>";
}

//--------------------------------------------
// showPicturesAsAlbumCell()
// A recursive algorithm would look much more elegant than this.
// A future version will hopefully update this (January 3, 2005)
//--------------------------------------------
function showPicturesAsAlbumCell($td_width, $result, $config, $lang) {
	require_once("f_cats.php"); //since we're calling getChildren()
	$row_category = mysql_fetch_array($result);
	$cats = getChildren($row_category['cid'], $config, "sql_in", 1, 1);

	//give us a pic (not a media file - new in v0.9.2 DEV) for this category or from any of its children
	$statement_pic = "SELECT file_name FROM {$config['table_prefix']}pictures WHERE approved=1 AND is_video=0 AND category IN $cats ORDER BY RAND() DESC LIMIT 1";
	$result_pic = mysql_query($statement_pic);
	$row_pic = mysql_fetch_array($result_pic);

	//get number of pictures in category
	//I'm using date here, maybe I'll later add the option to display when the category was last updated
	$statement = "SELECT date AS d, parent FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE {$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}pictures.category='{$row_category['cid']}' AND {$config['table_prefix']}pictures.approved='1'";
	$result_data = mysql_query($statement);
	$row_data = mysql_fetch_array($result_data);
	$numrows = mysql_num_rows($result_data);
	
	//get number of pictures in category (deep)
	$statement = "SELECT COUNT(pid) as n FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE {$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}pictures.category IN $cats AND {$config['table_prefix']}pictures.approved='1'";
	$result_count = mysql_query($statement);
	$row_count = mysql_fetch_array($result_count);
	$numrows_deep = $row_count['n'];
	
	$redirect_to = html_entity_decode("display.php?t=bycat&amp;q={$row_category['cid']}&amp;nr=$numrows&amp;st=0&amp;upto=".$config['perpage']."&amp;p=1");
	
	//display image, name and number of pictures for current category
	$folder = "<a href='$redirect_to' class='thumb_link'>";
	if($row_pic['file_name'] == "") {
		$folder .= "<img src='" . getSkinElement($config['stylesheet'], "images/category_no_pics.png") . "' alt='{$row_category['cname']}' title='{$row_category['cname']}' class='thumb' style='float:none;border:0px' width='96' height='96' />";
	}
	else { //change crop=1 to crop=0 if you don't want to crop the image
		$folder .= "<img src='thumbify.php?pic={$row_pic['file_name']}&amp;w=96&amp;h=96&amp;folder=uploads&amp;smart_resize=0&amp;crop=1' alt='{$row_category['cname']}' title='{$row_category['cname']}' class='thumb' style='float:none' width='96' height='96' />";
	}
	$folder .= "</a>";
	
	if($config['thumb_details_align'] == 0)   $thumb_details_align = "left";
	elseif($config['thumb_details_align'] == 1)   $thumb_details_align = "center";
	elseif($config['thumb_details_align'] == 2)   $thumb_details_align = "right";
	
	echo "<td width='$td_width%' class='pic_cell' align='$thumb_details_align' "; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">";
	
	echo "<table width='100%' cellspacing='0' cellpadding='2'>
	<tr><td colspan='{$config['thumbs_perrow']}'>
	$folder
	<span class='category_name'><a href='$redirect_to'>{$row_category['cname']}</a></span><br />
	<span class='header'><b>$numrows_deep</b> {$lang['pics_in_cat']}</span><br />";
	echo "</td></tr>
	</table></td>
	";
	
	return $result;
}

//--------------------------------------------
// doesFileExist(): Checks if a filename already exists, new in v0.9.2 DEV
//--------------------------------------------
function doesFileExist($config, $filename, $name_change_counter="0") {
	$statement = "SELECT COUNT(file_name) as n FROM {$config['table_prefix']}pictures WHERE file_name='$filename'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	
	if($row['n'] > 0) { //if file with same name already exists
		$bits = splitFilenameAndExtension($filename);
		//suggest new name and try again
		$filename = $bits[0] . "_" . mt_rand(9999,999999) . "." . $bits[1]; //e.g. might rename test.jpg to test9999.jpg
		doesFileExist($config, $filename, $name_change_counter++);
		return array($filename, $name_change_counter);
	}
	else   return array($filename, $name_change_counter);
}

//--------------------------------------------
// getSkinElement(), new in v0.9.2 DEV
// Gets an image or file belonging to the currently active skin.  If it doesn't exist, it tries reading
// it from the default skin.
//--------------------------------------------
function getSkinElement($path_skins, $element) {
	if(file_exists($path_skins . "/" . $element))   return $path_skins . "/" . $element; //get it from the currently active skin
	else   return "skins/default/" . $element; //if it's not available, try getting it from the default skin
}

//--------------------------------------------
// showFeature(), new in v0.8.8 DEV
// Checks whether or not a feature is enabled in config.php.  If it is, it displays $show.
// This function is not yet used gallery-wide
// feature E {0, 1}
//--------------------------------------------
function showFeature($feature, $show) {
	if($feature)   echo $show;
}

//--------------------------------------------
// printArrayAsCSV(), new in v0.9.2 DEV
// returns an array as a string of comma-seperated values
//--------------------------------------------
function printArrayAsCSV($arr) {
	if(!is_array($arr))   return ""; //is the arg (most likely $config['allowed_media_filetypes']) non-existent or not defined?

	foreach($arr as $key => $value) {
		if($value != "")   $allowed_media_filetypes .= ", " . $value;
	}
	
	return $allowed_media_filetypes;
}

//--------------------------------------------
// correctDimensions(): maintains the ratio of the width and height args while making sure that the
// returned coords are less than the threshold (new in v0.9.4 DEV, user request: Bruwat)
// if desired, pass in height instead of width and vice versa to enforce a threshold on the height
// change / 2 to control granularity
//--------------------------------------------
function correctDimensions($width, $height, $width_threshold) {
    if($width <= $width_threshold || $width_threshold == "")   return array($width, $height);

    $width = ceil($width / 2);
    $height = ceil($height / 2);

    $arr = correctDimensions($width, $height, $width_threshold);
    
    return $arr;
}

?>