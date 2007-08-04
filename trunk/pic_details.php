<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://cyberiapc.com

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: pic_details.php
 Description: -
 Last updated: July 8, 2007
 Random quote: "Integrity is all you've got."  
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_db.php');
require_once('functions/f_lib.php');

//what's the state of the gallery?
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

$pid = $_GET['pid'];
if(!is_numeric($pid)) $pid = -1;
$ip = getenv("REMOTE_ADDR");

//CHECK FOR FINAL VERSION BEFORE RELEASE OF v0.8.6
//June 16 2005
//added to fix ricsbride's problem.  Check to see if better solution exists.
/*if(!isset($_SESSION['pic_size'])) {
	$_SESSION['pic_size'] = $config['default_preview_pic_size'];
	$redirect = "Location: pic_details.php?pid=$pid&size={$config['default_preview_pic_size']}";
	header("$redirect");
	exit();
}*/

$row = mysql_fetch_array(mysql_query("SELECT cshow FROM {$config['table_prefix']}categories, {$config['table_prefix']}pictures WHERE pid=$pid AND cid=category"));
if($row['cshow'] == 0)   doBox("error", $lang['cat_is_hidden_msg'], $config, $lang);

//set preview picture's size
if(isset($_GET['size']) && is_numeric($_GET['size'])) {
	$_SESSION['pic_size'] = $_GET['size'];
}

//new in ZVG
if(isVideo($config, $pid,"")) {
	$_SESSION['pic_size'] = 0; //always set thumb size to small for videos
}

//process rating if rating data is in URI
if(isset($_GET['rateit'])) {
   //has user already voted?
   $row = mysql_fetch_array(mysql_query("SELECT voter_date FROM {$config['table_prefix']}ratings WHERE pid=$pid AND voter_ip='$ip'"));
   if($row) {
      doBox("error", $lang['already_voted_msg'] . " <a href='pic_details.php?pid={$_GET['pid']}'>{$lang['redirect_proceed']}</a>", $config, $lang);
   }
   else {
	if($_GET['rateit'] > 5) $_GET['rateit'] = 5;
	elseif($_GET['rateit'] < 0) $_GET['rateit'] = 0;

      $row = mysql_fetch_array(mysql_query("SELECT rating, rating_count FROM {$config['table_prefix']}pictures WHERE pid=$pid"));
      $new_rating_count = $row['rating_count']+1;
      $new_rating = $row['rating']+$_GET['rateit'];

      //update the current picture
      $result = mysql_query("UPDATE {$config['table_prefix']}pictures SET rating=$new_rating, rating_count=$new_rating_count WHERE pid=$pid");

      //record user's ip, picture and current time so that they can't rate again
      $result = mysql_query("INSERT INTO {$config['table_prefix']}ratings (pid, voter_ip, voter_date) VALUES ($pid, '$ip',NOW())");
   }
}

//Oct 21, 2005 ecarey's problem
$statement = "SELECT pid, title, keywords FROM {$config['table_prefix']}pictures WHERE pid=$pid";
$row = mysql_fetch_array(mysql_query($statement));
$_SESSION['pic_title'] = " - " . $row['title'];
$_SESSION['pic_description'] = "<meta name=\"description\" content=\"" . $row['keywords'] . "\" />";

require('head.php');

$statement = "SELECT pid, rating, rating_count, description, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter FROM {$config['table_prefix']}pictures WHERE pid=$pid";
$row = mysql_fetch_array(mysql_query($statement));
if(!$row)   doBox("error", $lang['pic_no_exist_msg'], $config, $lang);

// CHECK THIS FOR FINAL VERSION OF v0.8.6
if($_SESSION['pic_title'] == "") {
	$_SESSION['pic_title'] = $row['title'];
	$redirect = "Location: pic_details.php?pid=$pid";
	header("$redirect");
	exit();
}

$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
$epoch_time = $row_t['t'];
$me = $epoch_time + $config['timezone_offset'];
$row['d'] = strftime($config['long_date_format'],$me);

$title = $row['title']; 

//get dimensions
$pic_info['dimensions'] = $row['pic_width'] . " x " . $row['pic_height'] . " px";
$pic_info['votes'] = $row['rating_count'];

//get rating
if($row['rating_count'] == 0) {
      $pic_info['rating_txt'] = $lang['no_votes'];
	$pic_info['rating'] = $lang['no_votes'];
}
else {
   $current_rating = round($row['rating'] / $row['rating_count'],2);
   $pic_info['rating_txt'] = $current_rating .  " / 5 {$lang['with']} {$pic_info['votes']} {$lang['votes']}"; 

   //determine rating picture
   $rounded_cr = round($current_rating);
   if($rounded_cr == 0)       $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate0.gif");
   elseif($rounded_cr == 1)   $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate1.gif");
   elseif($rounded_cr == 2)   $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate2.gif");
   elseif($rounded_cr == 3)   $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate3.gif");
   elseif($rounded_cr == 4)   $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate4.gif");
   elseif($rounded_cr == 5)   $pic_info['rating_pic'] = getSkinElement($config['stylesheet'], "images/rate5.gif");

   $pic_info['rating'] = "<img src='{$pic_info['rating_pic']}' alt='{$pic_info['rating_txt']}' title='{$pic_info['rating_txt']}'>";
}

//determine previous and next records
//second sort order added (v0.9.4 DEV)
$statement = "SELECT pid,date FROM {$config['table_prefix']}pictures WHERE category='{$row['category']}' AND approved=1 ORDER BY date DESC, pid DESC";
$result = mysql_query($statement);
while($row_record = mysql_fetch_array($result))   $strip[] = $row_record['pid'];
if($config['debug_mode'] == 1)   print_r($strip);
//get current picture's pos
$pic_position = 0;
reset($strip);
while ($name = current($strip)) {
	if ($name == $row['pid']) {
		 key($strip);
		 break;
	}
	next($strip);
	$pic_position++;
}
if(!next($strip))   end($strip); //if false (beyond last element), set pointer at last element (keep record_next empty)
else {
	$record_next = current($strip);
	prev($strip); //return to original position
}
if(!prev($strip))   reset($strip); //if false (before first element), set pointer at first element (keep record_prev empty)
else   $record_prev = current($strip);

//new in v0.9.4 DEV, author credited above
//do some calculation required for 'back to index' link
$pictures_num = count($strip);
$link_st = floor($pic_position / $config['perpage']) * $config['perpage'];
$link_upto = $link_st + $config['perpage'];
$link_p= ceil(($pic_position + 1) / $config['perpage']);

//output navigation bar
echo "<table class='search_page_cell' cellspacing='4' cellpadding='3' style='width:{$config['gallery_width']};margin-top:1em'"; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">
<tr>
<td align='center'>
<span class='tiny_text'>";
if($record_prev != '') echo "<a href='pic_details.php?pid=$record_prev' style='font-weight:bold;'>{$lang['previous']}</a>";
else echo "<span style='color:#cccccc'>{$lang['previous']}</span>";

echo " &nbsp; <a href='display.php?t=bycat&amp;q={$row['category']}&amp;nr=$pictures_num&amp;st=$link_st&amp;upto=$link_upto&amp;p=$link_p'><img src='" . getSkinElement($config['stylesheet'], "images/index.gif") . "' alt='{$lang['back_to_index']}' title='{$lang['back_to_index']}' border='0' /></a> &nbsp; ";

if($record_next != '') echo "<a href='pic_details.php?pid=$record_next' style='font-weight:bold;'>{$lang['next']}</a>";
else echo "<span style='color:#cccccc'>{$lang['next']}</span>";
echo "</span>
</td>
</tr>
</table>
";

//output picture table
echo "<table class='table_layout_main' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'"; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">
<tr>
	<td class='cell_header' style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ");text-align:center' width='60%'>{$row['title']}</td>
</tr>
<tr>
	<td colspan='5' width='100%' valign='bottom'>
		<table width='100%' cellpadding='2' cellspacing='0'"; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">
			<tr>
				<td colspan='5' align='center' class='pic_detail_cell' width='100%'>
				";

if(!isset($_SESSION['pic_size'])) $pic_size = $config['default_preview_pic_size'];
else   $pic_size = $_SESSION['pic_size'];
showDetailedPic($config, $lang, $row, $pic_size);

echo "</td></tr>";

if(!isVideo($config, "",$row['file_name'])) {
	echo "<tr><td colspan='5' class='pic_detail_cell' align='center' width='100%'>{$lang['picture_size']} ";
	if(!isset($_SESSION['pic_size'])) {
		if($config['default_preview_pic_size'] == 0) echo "<b>{$lang['small']}</b> | ";   else   echo "<a href='pic_details.php?pid=$pid&amp;size=0'>{$lang['small']}</a> | ";
		if($config['default_preview_pic_size'] == 1) echo "<b>{$lang['medium']}</b> | ";   else   echo "<a href='pic_details.php?pid=$pid&amp;size=1'>{$lang['medium']}</a> | ";
		if($config['default_preview_pic_size'] == 2) {
			echo "<b>{$lang['original']}</b>";
			
			//increment views counter for full-size pics
			$pid = $_GET['pid'];
			if(is_numeric($_GET['pid'])) {
				$row_counter = mysql_fetch_array(mysql_query("SELECT counter, title, pic_width, pic_height FROM {$config['table_prefix']}pictures WHERE pid='$pid'"));
				$counter = $row_counter['counter']+1;
				$result_counter = mysql_query("UPDATE {$config['table_prefix']}pictures SET counter=$counter WHERE pid='$pid'");
			}
		}
		else   echo "<a href='pic_details.php?pid=$pid&amp;size=2'>{$lang['original']}</a>";
	}
	else {
		if($_SESSION['pic_size'] == 0) echo "<b>{$lang['small']}</b> | ";   else   echo "<a href='pic_details.php?pid=$pid&amp;size=0'>{$lang['small']}</a> | ";
		if($_SESSION['pic_size'] == 1) echo "<b>{$lang['medium']}</b> | ";   else   echo "<a href='pic_details.php?pid=$pid&amp;size=1'>{$lang['medium']}</a> | ";
		if($_SESSION['pic_size'] == 2) {
			echo "<b>{$lang['original']}</b>";
			
			//increment views counter for full-size pics
			$pid = $_GET['pid'];
			if(is_numeric($_GET['pid'])) {
				$row_counter = mysql_fetch_array(mysql_query("SELECT counter, title, pic_width, pic_height FROM {$config['table_prefix']}pictures WHERE pid='$pid'"));
			$counter = $row_counter['counter']+1;
				$result_counter = mysql_query("UPDATE {$config['table_prefix']}pictures SET counter=$counter WHERE pid='$pid'");
			}
		}
		else   echo "<a href='pic_details.php?pid=$pid&amp;size=2'>{$lang['original']}</a>
		";
	}

	echo "</td></tr>";
}

//colspans changed to 3 and 2 from 4 and 1 in v0.9.4 DEV
echo "<tr><td colspan='5' class='tiny_text_dark' align='center' width='100%'>{$row['description']}</td></tr>
";
echo "<tr><td colspan='3' class='light_cell' width='50%'"; if($config['page_direction'] == "1")   echo " align='right'"; echo ">
<b>{$lang['pic_info']}</b></td>
<td colspan='2' class='light_cell' width='50%'"; echo ($config['page_direction'] == "1") ? " align='left'>" : " align='right'>";


//check added in v0.9
if($config['marking_allowed']) {
	//mark for download (new in v0.8.8 DEV)
	if(isset($_SESSION["marked_".getenv("REMOTE_ADDR")]) && in_array($pid,$_SESSION["marked_".getenv("REMOTE_ADDR")]))
		echo "<a href='marked_pics.php?do=unmark&amp;pid=$pid'><span class='tiny_text'><b>{$lang['unmark']}</b></span></a>&nbsp;";
	else
		echo "<a href='marked_pics.php?do=mark&amp;pid=$pid'><span class='tiny_text'><b>{$lang['mark_for_download']}</b></span></a>&nbsp;";
}

showAdminTools($config, $lang, $row);
echo "</td></tr>
<tr><td colspan='5' class='cell_highlight' width='100%'>";
showDetailedEntry($lang, $row, $config, $pic_info);
echo "</td></tr>";

//output exif data if exists
showExif($row['file_name'], $config, $lang);

//get comments data
$statement = "SELECT pid, comment_id, comment_author, comment_body, comment_date AS d, comment_ip, approved FROM {$config['table_prefix']}comments WHERE pid='$pid' ORDER BY comment_date DESC";
$result = mysql_query($statement);
$comments_numrows = mysql_num_rows($result);

echo "</td></tr>";

//output rate it box
if($config['guest_voting'] == 1 && $config['voting_show'] == 1) { //if voting is enabled
   echo "<tr><td colspan='5' class='light_cell' width='100%'"; if($config['page_direction'] == "1")   echo " align='right'"; echo ">
   <b>{$lang['rate_it']}</b><span class='tiny_text_dark'>&nbsp; ({$lang['currently']} {$pic_info['rating_txt']})</span></td></tr>
   <tr>
      <td class='cell_highlight' width='20%' align='center'><a href='{$_SERVER['PHP_SELF']}?pid=$pid&amp;rateit=1'><img src='" . getSkinElement($config['stylesheet'], "images/rate1.gif") . "' alt='{$lang['poor']}' title='{$lang['poor']}' border='0' /></a></td>		
      <td class='cell_highlight' width='20%' align='center'><a href='{$_SERVER['PHP_SELF']}?pid=$pid&amp;rateit=2'><img src='" . getSkinElement($config['stylesheet'], "images/rate2.gif") . "' alt='{$lang['fair']}' title='{$lang['fair']}' border='0' /></a></td>
      <td class='cell_highlight' width='20%' align='center'><a href='{$_SERVER['PHP_SELF']}?pid=$pid&amp;rateit=3'><img src='" . getSkinElement($config['stylesheet'], "images/rate3.gif") . "' alt='{$lang['good']}' title='{$lang['good']}' border='0' /></a></td>
      <td class='cell_highlight' width='20%' align='center'><a href='{$_SERVER['PHP_SELF']}?pid=$pid&amp;rateit=4'><img src='" . getSkinElement($config['stylesheet'], "images/rate4.gif") . "' alt='{$lang['very_good']}' title='{$lang['very_good']}' border='0' /></a></td>
      <td class='cell_highlight' width='20%' align='center'><a href='{$_SERVER['PHP_SELF']}?pid=$pid&amp;rateit=5'><img src='" . getSkinElement($config['stylesheet'], "images/rate5.gif") . "' alt='{$lang['excellent']}' title='{$lang['excellent']}' border='0' /></a></td>
   </tr>
   ";
}

if($config['comments_show'] == 1) {
	//if there are comments, display only approved ones
	echo "<tr><td colspan='5' class='light_cell' width='100%'"; if($config['page_direction'] == "1")   echo " align='right'"; echo ">
		<b>{$lang['visitor_comments']}</b></td></tr>
		<tr class='cell_highlight'>
			<td width='20%'>&nbsp;</td>
			<td width='20%'>&nbsp;</td>
			<td width='20%'>&nbsp;</td>
			<td width='20%'>&nbsp;</td>
			<td width='20%'>&nbsp;</td>
		</tr>
		";
	
	if($comments_numrows > 0) {
		$i = $comments_numrows;
		while($row = mysql_fetch_array($result)) {
			//new in v0.9.4 DEV
			$comment_body = $row['comment_body'];
			$comment_body = str_replace("&lt;br &gt;","<br />", $comment_body); //support line breaks
			
			//apologies for this extremely crude and dirty workaround.
			$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
			if($row['d'] != null)   $row['d'] = strftime($config['long_date_format'],$row_t['t'] + $config['timezone_offset']);
			
			if($row['approved'] == 1) {
				echo "<tr>
				<td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_small.gif") . ")' colspan='4' width='80%'"; if($config['page_direction'] == "1")   echo " dir='rtl' align='right'"; echo ">
				<span class='comment_text_head'>{$lang['posted_by']} {$row['comment_author']} {$lang['posted_on']} {$row['d']}
				";
				if(loggedIn($config) && adminStatus($config))   echo " ({$row['comment_ip']})";
				echo "</span></td>
				<td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_small.gif") . ")' dir='rtl' align='right' colspan='1' width='20%'>";
				showAdminCommentTools($config, $lang, $row);
				echo "</td></tr>
				<tr>
				<td colspan='5' class='cell_highlight' width='100%'"; if($config['page_direction'] == "1")   echo " dir='rtl' align='right'"; echo ">
				<span class='comment_text'><b>$i.</b> $comment_body</span>
				</td>
				</tr>
				";
			}
	
			$i--; //counter to show for comments
		}
	}

	if($config['guest_comments'] == 1 || loggedIn($config) == 1) { //if guest comments is enabled or admin is logged in
		echo "<tr><td colspan='5' valign='middle' class='cell_foot' width='100%'"; if($config['page_direction'] == "1")   echo " align='right'"; echo ">
		<form name='frmAddComment' action='comment_man.php' method='post'>
		";		
		
		//is user logged in, and has the admin chosen to mod-queue all registered members?
		if(adminStatus($config)) {
			echo "<input type='hidden' name='approved' value='1' />";
			$comment_author = $_COOKIE["{$config['cookie_prefix']}username"];
		}
		elseif(loggedIn($config) && $config['mod_queue_registered_users'] == "0") {
			echo "<input type='hidden' name='approved' value='1' />";
			$comment_author = $_COOKIE["{$config['cookie_prefix']}username"];
		}
		elseif(loggedIn($config)) {
			echo "<input type='hidden' name='approved' value='0' />";
			$comment_author = $_COOKIE["{$config['cookie_prefix']}username"];
		}
		else {
			echo "<input type='hidden' name='approved' value='0' />";
			$comment_author = $lang['guest'];
		}
	
		//if captchas are for all or (just for guests and user isn't logged in)
		if($config['comments_captcha'] == 2 || ($config['comments_captcha'] == 1 && !loggedIn($config))) {
			echo "
			<div style='height:80px;padding:3px;width:49%;float:left;border-right: 1px solid #fff'>{$lang['comment_new']}<br />
				<textarea rows='5' name='comment_body' cols='60' class='dropdownBox' maxlength='255' style='width:97%;height:60px;padding-top:3px'></textarea>
			</div>
			
			<div style='height:80px;padding:3px;text-align:center;width:29%;float:left;border-right: 1px solid #fff'>{$lang['type_letters_verification']}<br /><br />
				<img src='captcha.php' border='0' alt='{$lang['generated_captcha']}' title='{$lang['generated_captcha']}' class='thumb' /><br />
				<input type='text' name='vcode' class='textBox' style='margin-top:6px;width:58pt;height:12pt' />
			</div>
			
			<div style='height:80px;padding:3px;text-align:center;width:19%;float:left'>
				<input type='submit' name='submit' value='{$lang['button_add']}' class='submitButtonTiny' style='width:48px;height:48px;margin-top:18px;margin-left:3px' />
			</div>
			";
		}
		else { //if captchas are disabled
			echo "
			<div style='height:80px;padding:3px;width:79%;float:left;border-right: 1px solid #fff'>{$lang['comment_new']}<br />
				<textarea rows='5' name='comment_body' cols='60' class='dropdownBox' style='width:90%;height:60px;padding-top:3px'></textarea>
			</div>
			
			<div style='height:80px;padding:3px;text-align:center;width:19%;float:left'>
				<input type='submit' name='submit' value='{$lang['button_add']}' class='submitButtonTiny' style='width:48px;height:48px;margin-top:18px;margin-left:3px' />
			</div>
			";
		}
		
		echo "<input type='hidden' name='comment_ip' value='$ip' />
		<input type='hidden' name='comment_author' value='$comment_author' />
		<input type='hidden' name='is_what' value='comment-add' />
		<input type='hidden' name='pid' value='$pid' />
		<input type='hidden' name='uri' value='{$_SERVER['SCRIPT_NAME']}?pid=$pid' />
		";
		
		echo "</form>
		</td></tr>
		";
   }
}//end comment_show check

echo "</table>
</td></tr>
</table>
";

showJumpToCatForm($config, $lang);

require('foot.php');
unset($_SESSION['pic_title']);
unset($_SESSION['pic_description']);

?>