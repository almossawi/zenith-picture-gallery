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
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('head.php');

if($config['show_members_list'] == '0' && (!loggedIn($config) || !adminStatus($config)))
   doBox("error", $lang['members_list_blocked_msg'], $config, $lang);

$statement = "SELECT uid, username, submissions FROM {$config['table_prefix']}users ORDER BY submissions DESC";
$result = mysql_query($statement);

if(mysql_num_rows($result) > 0) {
	echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='4' cellspacing='0'>
  	<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' valign='middle' colspan='2'>
	<img src='" . getSkinElement($config['stylesheet'], "images/arrow.gif") . "' border='0' alt='' /> {$lang['head_members']}</td></tr>
	<tr><td class='search_page_cell' width='80%'><span class='tiny_text_dark'> </span></td>
	<td class='search_page_cell' width='20%' align='center'><span class='tiny_text_dark'>{$lang['submissions']}</span></td></tr>
	";

	$i=0; $j=1;
	while($row = mysql_fetch_array($result)) {
		$at = "profile.php?m=".$row['uid'];
		
		if($i == 0) {
		   echo "<tr><td width='80%' class='rowcolor1'>".$j.". <a href='$at'>{$row['username']}</a></td>
		   <td width='20%' class='rowcolor1' align='center'><b>{$row['submissions']}</b></td></tr>";
		}
		else {
		   echo "<tr><td width='80%' class='rowcolor2'>".$j.". <a href='$at'>{$row['username']}</a></td>
		   <td width='20%' class='rowcolor2' align='center'><b>{$row['submissions']}</b></td></tr>";
		}
		
		if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
		$j++;
	}
	echo "</table>";
}

showJumpToCatForm($config, $lang);

include('foot.php');

?>