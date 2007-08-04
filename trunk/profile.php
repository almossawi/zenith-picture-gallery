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
  
 File: index.php
 Description: -
 Random quote:  "I have hardly ever known a mathematician who was 
 capable of reasoning." -Plato
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_db.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

require_once('head.php');

if(isset($_SESSION['msg'])) {
  echo "<div class='msg' style='color:red'>{$_SESSION['msg']}</div>";
  unset($_SESSION['msg']);
}

if(isset($_GET['m']) && is_numeric($_GET['m'])) {
	$statement = "SELECT username, bday_day, bday_month, bday_year, personal_quote, interests, submissions, join_date, hide_email, country, avatar_location, website, email FROM {$config['table_prefix']}users WHERE uid={$_GET['m']}";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	//apologies for this extremely crude and dirty workaround.
	$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['join_date']}') as t;"));
	if($row['join_date'] != null)   $row['join_date'] = strftime($config['short_date_format'],$row_t['t'] + $config['timezone_offset']);

	if($row['bday_day'] == '0' || $row['bday_month'] == '0' || $row['bday_year'] == '0') $age = $lang['no_info'];
	else {
		$y_dash = date('Y') - $row['bday_year'];
		$m_dash = ($row['bday_month'] - date('n')) * 31;
		$d_dash = $row['bday_day'] - date('j');
		
		$age = floor($y_dash - (($m_dash + $d_dash) / 365));
	}
	
	if($row['join_date'] == NULL) $join_date = $lang['no_info']; else $join_date = $row['join_date'];
	if($row['country'] == NULL) $country = $lang['no_info']; else $country = $row['country'];
	if($row['website'] == NULL) $website = $lang['no_info']; else $website = "<a href='{$row['website']}' target='_blank'>{$row['website']}</a>";
	if($row['personal_quote'] == NULL) $personal_quote = $lang['no_info']; else $personal_quote = $row['personal_quote'];
	if($row['interests'] == NULL) $interests = $lang['no_info']; else $interests = $row['interests'];
	if($row['submissions'] == NULL) $submissions = $lang['no_info']; else $submissions = $row['submissions'];
	//if($row['avatar_location'] === NULL) $avatar = "skins/default/images/no_avatar.gif"; else $avatar = $row['avatar_location'];	
	if($row['hide_email'] == 1) $email = ""; else $email = "<a href='mailto:{$row['email']}'>{$row['email']}</a>";	
	$link_submitter = "search.php?t=&amp;q=&amp;a={$row['username']}&amp;strength=0&amp;cat={0}&amp;nr=$submissions&amp;st=0&amp;upto=" . $config['perpage'] . "&amp;p=1&amp;by=0&amp;order=0";
	
	if(mysql_num_rows($result) > 0) {
		echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
		<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='2' align='right'>{$lang['head_profile']}</td></tr>
		<tr><td width='100%'>
			<table width='100%'>
				<tr><td width='50%' valign='top'"; if($config['page_direction'] == "1")   echo " align='right' dir='rtl'"; echo ">
				<table border='0'>
				<tr><td width='100%'><span class='large_text_dark'><b>{$row['username']}</b></span><br>$email</td></tr>
				<tr><td width='100%'><b>{$lang['join_date']}:</b> $join_date</td></tr>
				<tr><td width='100%'><b>{$lang['location']}:</b> $country</td></tr>
				<tr><td width='100%'><b>{$lang['website']}:</b> $website</td></tr>
				<tr><td width='100%'><b>{$lang['age']}:</b> $age</td></tr>
				<tr><td width='100%'><b>{$lang['interests']}:</b> $interests</td></tr>
				<tr><td width='100%'><b>{$lang['personal_quote']}:</b> $personal_quote</td></tr>
				<tr><td width='100%'><br>{$lang['submitted']}: <b><a href='$link_submitter'>$submissions</a></b></td></tr>
				</table>
				</td>
		";
				
		if($row['avatar_location'] !== NULL && $row['avatar_location'] !='') {
			echo "<td width='50%' align='right' valign='top'>
				<table class='table_layout_sections' cellpadding='2' cellspacing='0'>
				<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' align='center'>{$lang['head_user_id']}</td></tr>
				<tr><td width='100%' align='center'>
				<img src=\"{$row['avatar_location']}\" alt='{$row['username']}' title='{$row['username']}'>
				</td>
				</table>
				</td>
			";
		}
				
		echo "</table>
		</td></tr>
		</table>
		";
	}
	else {
		doBox("error", $lang['admin_error_msg'], $config, $lang);
	}
}
else {
		doBox("error", $lang['admin_error_msg'], $config, $lang);
}

showJumpToCatForm($config, $lang);

include('foot.php');

?>