<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com
 
  Modifications:
 * Marcin Krol <hawk@limanowa.net>, Added second sort order to SQL 
   query (search for v0.9.4 DEV below)

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: display.php
 Description: -
 Last updated: July 8, 2007
 Random quote: "If you want the sky, aim for the moon."  
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_search.php');
require_once('functions/f_db.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

//on form submit
if ($_POST['submit']) {
	$searchtype = "bycat";
	$searchquery = $_POST['byCat'];
	//echo "<br />The cat is: {$_POST['byCat']}<br />";
	//if user chose to search by category
    if(isset($searchquery) && is_numeric($searchquery) && $searchquery != -1) {
		$numrows = query_numrows("pid","{$config['table_prefix']}pictures","category='$searchquery'");
		$redirect = "Location: display.php?t=$searchtype&q=$searchquery&nr=$numrows&st=0&upto=".$config['perpage']."&p=1";
		header("$redirect");
		exit();
    } 	
}
  
require_once('head.php');
if($config['debug_mode'] == 1)   print_r($config);
  
//if query data is in the URI, requery table using that data instead of showing a blank page
if(isset($_GET['t'])) {
   //if user chose to search by category
   if($_GET['t'] == "bycat" && is_numeric($_GET['q'])) { //get those matching selected category
		//first, check the category's permissions
		$statement = "SELECT cshow FROM {$config['table_prefix']}categories WHERE cid={$_GET['q']}";
		$result = mysql_query($statement); 
		$row = mysql_fetch_array($result);
		
		//get those matching selected category (cid => cname)
		if($row['cshow'] == 1) {
			$searchquery = $_GET['q'];
			
			//second sort order added (v0.9.4 DEV)
			$statement = "SELECT pid, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter FROM {$config['table_prefix']}pictures WHERE category='$searchquery' AND approved='1' ";
			$statement .= (!loggedIn($config) || !adminStatus($config)) ? "AND who_can_see_this='0' " : "";
			$statement .= "ORDER BY date DESC, pid DESC";
		}
		else {
			doBox("error", $lang['cat_is_hidden_msg'], $config, $lang);
		}
   }  
   //(cid => cname)
   elseif($_GET['t'] == "r1") { //sort by top rated
		//modified in v0.9.4
		$statement = "SELECT pid, rating, rating_count, SUM(rating/rating_count) AS r, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE approved='1' AND ";
		$statement .= (!loggedIn($config) || !adminStatus($config)) ? "who_can_see_this='0' AND " : "";
		$statement .= "{$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}categories.cshow=1 GROUP BY pid ORDER BY r DESC";
		$rule = $lang['top_rated'];
   }
   elseif($_GET['t'] == "r2") { //sort by most viewed
	   //modified in v0.9.4
		$statement = "SELECT pid, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE approved='1' AND ";
		$statement .= (!loggedIn($config) || !adminStatus($config)) ? "who_can_see_this='0' AND " : "";
		$statement .= "{$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}categories.cshow=1 ORDER BY counter DESC";
		$rule = $lang['most_viewed'];
   }
   elseif($_GET['t'] == "r3") { //sort by last comments
		//modified in v0.9.4
		$statement = "SELECT {$config['table_prefix']}comments.pid, {$config['table_prefix']}comments.comment_body, file_name, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date, cshow AS d, pic_width, pic_height, keywords, {$config['table_prefix']}pictures.approved, category, counter, comment_date FROM {$config['table_prefix']}pictures, {$config['table_prefix']}comments, {$config['table_prefix']}categories WHERE {$config['table_prefix']}pictures.pid={$config['table_prefix']}comments.pid AND {$config['table_prefix']}comments.approved='1' AND ";
		$statement .= (!loggedIn($config) || !adminStatus($config)) ? "who_can_see_this='0' AND " : "";
		$statement .= "{$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND {$config['table_prefix']}categories.cshow=1 ORDER BY comment_date DESC";
		$rule = $lang['last_comments'];
   }
}
elseif(!isset($_GET['r'])) { //if no data is in the URI, and no rule is to be displayed, load categories map	
   $totalpics = query_numrows("pid","{$config['table_prefix']}pictures","approved=1");
   $totalcomms = query_numrows("comment_id","{$config['table_prefix']}comments","approved=1");
	
   echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='0' cellspacing='0'>
   <tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' valign='middle' "; if($config['page_direction'] == "1")   echo "align='right' "; echo "colspan='3' width='100%'><img src='" . getSkinElement($config['stylesheet'], "images/arrow.gif") . "' border='0' alt='' /> {$lang['sort_by']}</td></tr>
   <tr><td align='center' width='33%'><a href='display.php?t=r1&nr=$totalpics&st=0&upto={$config['perpage']}&p=1'>{$lang['top_rated']}</a></td>
   <td align='center' width='33%'><a href='display.php?t=r2&nr=$totalpics&st=0&upto={$config['perpage']}&p=1'>{$lang['most_viewed']}</a></td>
   <td align='center' width='33%'><a href='display.php?t=r3&nr=$totalcomms&st=0&upto={$config['perpage']}&p=1'>{$lang['last_comments']}</a></td>
   </tr>
   </table>
   
   <table class='table_layout_sections' style='width:{$config['gallery_width']};margin-top:8px' cellpadding='2' cellspacing='0'>
   <tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' valign='middle' "; if($config['page_direction'] == "1")   echo "align='right' "; echo "colspan='4' width='100%'>
   <img src='" . getSkinElement($config['stylesheet'], "images/arrow.gif") . "' border='0' alt='' /> {$lang['head_display']}</td></tr>";

   //$cat = justGetTheDamnCats(1,0,$config,$lang);
   $cat = getCatsTreeView(1,0,$config,$lang);
   unset($cat[0]); //kill first one (blank)
   
   //output header
   echo "<tr class='search_page_cell'><td class='tiny_text_dark' width='33%'>{$lang['browser_category']}</td>
   <td class='tiny_text_dark' width='33%' align='center'>{$lang['browser_last_update']}</td>
   <td class='tiny_text_dark' width='17%' align='center'>{$lang['browser_pictures']}</td>
   <td class='tiny_text_dark' width='17%' align='center'>{$lang['download']}</td></tr>";
		
	$i=0;
	foreach($cat as $key => $value) {
		$searchtype = "bycat";
		$searchquery = $key;

		//get number of pictures in category (modified in v0.9.4)
		$statement = "SELECT downloadable, date AS d FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE {$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND category='$searchquery' AND {$config['table_prefix']}pictures.approved='1' ";
		$statement .= (!loggedIn($config) || !adminStatus($config)) ? "AND {$config['table_prefix']}pictures.who_can_see_this=0 " : "";
		$statement .= "ORDER BY date DESC";
		
		$result = mysql_query($statement) or die(mysql_error()); 	 	  //get result set from user entered criteria
		$row = mysql_fetch_array($result);
		
		//apologies for this extremely crude and dirty workaround.
		$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
		if($row['d'] != null)   $row['d'] = strftime($config['short_date_format'],$row_t['t'] + $config['timezone_offset']);
		  
		$numrows = mysql_num_rows($result);	 	  //get number of rows in result set
		if(isset($row['d']))   $last_added = $row['d'];
		else   $last_added = "-";
		//$searchquery = strToHex($searchquery);
		$searchquery = $key;
		$redirect_to = "display.php?t=$searchtype&q=$searchquery&nr=$numrows&st=0&upto=".$config['perpage']."&p=1";
		
		if($i == 0) {
			echo "<tr><td width='33%'><a href='$redirect_to'>$value</a></td>";
			echo "<td width='33%' align='center'>$last_added</td>";
			echo "<td width='17%' align='center'>$numrows</td>";
			echo "<td width='17%' align='center'>";
		}
		else {
			echo "<tr><td width='33%' class='rowcolor2'><a href='$redirect_to'>$value</a></td>";
			echo "<td width='33%' align='center' class='rowcolor2'>$last_added</td>";
			echo "<td width='17%' align='center' class='rowcolor2'>$numrows</td>";
			echo "<td width='17%' align='center' class='rowcolor2'>";
		}
		
		if($row['downloadable'] == 1)   echo "<a href='zipfile.php?c=$searchquery'><img src='" . getSkinElement($config['stylesheet'], "images/download.gif") . "' alt='{$lang['download']}' title='{$lang['download']}' border='0' /></a>";
		else   echo "-";
		echo "</td></tr>";
		
		if($i == 1) $i = 0;	elseif($i == 0) $i = 1;
   }
	echo "</table>";
}

//get name
if(isset($_GET['q']) && $_GET['q'] != '' && is_numeric($_GET['q'])) {
	//rule is cat's name (wasteful query?  remove if required)
	$result_wasteful = mysql_query("SELECT cname FROM {$config['table_prefix']}categories WHERE cid='$searchquery'");
	$row_wasteful = mysql_fetch_array($result_wasteful);
	$rule = $row_wasteful['cname'];
	
	$gimme_the_path = getHierarchyOfCategories($config, $searchquery);
	$rule = $gimme_the_path;
	
	//out the current category's children
	$statement_children = "SELECT cid, cname FROM {$config['table_prefix']}categories WHERE parent='{$_GET['q']}' AND cshow=1 ORDER BY cname";
	$result_children = mysql_query($statement_children);
	if(mysql_num_rows($result_children) > 0) {
		echo "<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0'>
		<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' valign='middle' "; if($config['page_direction'] == "1")   echo "align='right' "; echo "colspan='4' width='100%'>{$lang['head_subcategories']}</td></tr>";
		while($row_children = mysql_fetch_array($result_children)) {
			//modified in v0.9.4
			$statement_rows = "SELECT pid, file_name, who_can_see_this, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter FROM {$config['table_prefix']}pictures WHERE category='{$row_children[cid]}' AND approved='1' ";
			$statement_rows .= (!loggedIn($config) || !adminStatus($config)) ? "AND who_can_see_this='0' " : "";
			$statement_rows .= "ORDER BY date DESC";
			
			$result_rows = mysql_query($statement_rows); 	 //get result set from user entered criteria
			$numrows = mysql_num_rows($result_rows);	 	 //get number of rows in result set
			$redirect_to = html_entity_decode("display.php?t=bycat&q={$row_children['cid']}&nr=$numrows&st=0&upto=".$config['perpage']."&p=1");
			echo "<tr><td><a href='$redirect_to'><img src='" . getSkinElement($config['stylesheet'], "images/folder.gif") . "' alt='' border='0' /> {$row_children['cname']}</a></td></tr>";
		}
		echo "</table>";
	}
}
  
$statement .= " LIMIT " . $_GET['st'] . "," . $_GET['upto'];
$result = mysql_query($statement);

outputSearchPageBar($config, $lang, "display");

//output search results only if there exists a valid result set and its size is > 0
if($result && mysql_num_rows($result)>0)
     outputSearchResults($lang, $result, $config, $_GET['st'], $_GET['upto'], $lang['head_display']." - ".$rule);
else {
     if($_GET['t'] == 'r3')   doBox("msg", $lang['no_comments_msg'], $config, $lang);
     if($_GET['t'] == 'bycat')   doBox("msg", $lang['no_pics_in_cat_msg'], $config, $lang);
}
  
outputSearchPageBar($config, $lang, "display");
showJumpToCatForm($config, $lang);

include('foot.php');
mysql_close($connection);

?>