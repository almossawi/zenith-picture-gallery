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
 
 File: f_search.php
 Description: Contains search-related functions.
 You may wish to edit lines 121, 145 or 149.  Also, see f_global 
 (showEntry()) if necessary.
 Last updated: July 8, 2007
 Random quote: "Command is lonely."
*******************************************************************/

require_once("f_misc.php");

//--------------------------------------------
// outputSearchPageBar()
// @param add_to_right added in v0.9.4 to allow users to get the full url of a search page
// since search.php has now been ajaxificationified (TM)
// @param ajax_on added in v0.9.4
//--------------------------------------------
function outputSearchPageBar($config, $lang, $is_what, $ajax_on="0") {
	//output pagination bar
	if($_GET['nr'] > $config['perpage']) {
	  $pages = ceil($_GET['nr'] / $config['perpage']);
	
	  if(!isset($_GET['p'])) 	    //by default, currentpage is the first page
		 $currentpage = 1;
	  else
		 $currentpage = $_GET['p']; //otherwise, whatever value is in the URI

	  $upto = $config['perpage'];
	
	  echo "<table class='search_page_cell' cellspacing='4' cellpadding='3' style='width:{$config['gallery_width']};margin-top:1em'>
	  <tr>
	  <td align='center' width='100%'"; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">{$lang[goto_page]} ($pages) ";

	  //build arrays of all the values for manip. later on
	  $start_arr[] = 0;
	  $upto_arr[] = $config['perpage'];
	  
	  $please_wait_msg = "'<div align=\'center\' class=\'loading_msg_box\'><img src=\'" . getSkinElement($config['stylesheet'], "images/loading_small.gif") . "\' title=\'\' alt=\'\' /> {$lang['loading_please_wait']}</div>'";

	  //first & prev
	  if($ajax_on == "1") {
		  echo "<a href='#' onClick=\"JavaScript:return(xmlhttpPost('" . 
			  $is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[0] . "&amp;upto=" . $upto_arr[0] . "&amp;p=1&amp;by={$_GET['by']}&amp;order={$_GET['order']}','search_results_div', 'msg_div', $please_wait_msg));\">" . 
			  $lang['first'] . "</a> &nbsp;"; 
	  }
	  else {
	    echo "<a href='" . 
			$is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[0] . "&amp;upto=" . $upto_arr[0] . "&amp;p=1&amp;by={$_GET['by']}&amp;order={$_GET['order']}'>" . 
			$lang['first'] . "</a> &nbsp;"; 
	  }
	  
	  $start = 0;
	  for($i=1;$i<=$pages;$i++) {
		 $start += $config['perpage'];
		 $upto = $start + $config['perpage'];

		 //build arrays of all the values for manip. later on
		 $start_arr[] = $start;
		 $upto_arr[] = $upto;
	  }
	  
	  //display pages
	  for($i=$currentpage - 5;$i<=$currentpage + 5;$i++) {
	    if($i < 1 || $i > $pages)   continue;
		 
		if($currentpage == $i)   echo "<b>[". $i . "]</b> ";
		 else {
		 	if($ajax_on == "1") {
				echo "<a href='#' onClick=\"JavaScript:return(xmlhttpPost('" . 
					$is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[$i-1] . "&amp;upto=" . $upto_arr[$i-1] . "&amp;p=$i&amp;by={$_GET['by']}&amp;order={$_GET['order']}','search_results_div', 'msg_div', $please_wait_msg));\">" .
					$i . "</a> ";
			}
			else {
				echo "<a href='" .  
					$is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[$i-1] . "&amp;upto=" . $upto_arr[$i-1] . "&amp;p=$i&amp;by={$_GET['by']}&amp;order={$_GET['order']}'>" .
					$i . "</a> ";
			}
		 }
		  
		 $start += $config['perpage'];
		 $upto = $start + $config['perpage'];
	  }
	  
	  //next & last
	  if($ajax_on == "1") {
		  echo "&nbsp;<a href='#' onClick=\"JavaScript:return(xmlhttpPost('" .
			$is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[count($start_arr)-2] . "&amp;upto=" . $upto_arr[count($upto_arr)-2] . "&amp;p=$pages&amp;by={$_GET['by']}&amp;order={$_GET['order']}','search_results_div', 'msg_div', $please_wait_msg));\">" . 
			$lang['last'] . "</a>";
		}
		else {
		  echo "&nbsp;<a href='" .
			$is_what . ".php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=" . $start_arr[count($start_arr)-2] . "&amp;upto=" . $upto_arr[count($upto_arr)-2] . "&amp;p=$pages&amp;by={$_GET['by']}&amp;order={$_GET['order']}'>" . 
			$lang['last'] . "</a>";
		}
			  
		 echo "</td>
			  </tr>
			  </table>
			  ";
	}
}

//--------------------------------------------
// outputSearchResults()
//--------------------------------------------
function outputSearchResults($lang, $result, $config, $start, $upto, $head_box, $show_get_url_link="0") {
    if(!isset($start)) $start = 0;
    if(!isset($upto)) $upto = $config['perpage'];
	
  	$td_width = 100 / $config['thumbs_perrow']; //determine td widths based on thumbs_perrow
	$numrows = mysql_num_rows($result);
	
	$url = getCurrentInternetPath2($config, "") . "search.php?t={$_GET['t']}&amp;q={$_GET['q']}&amp;a={$_GET['a']}&amp;strength={$_GET['strength']}&amp;cat={$_GET['cat']}&amp;nr={$_GET['nr']}&amp;st=$start&amp;upto=$upto&amp;p={$_GET['p']}&amp;by={$_GET['by']}&amp;order={$_GET['order']}";
	  
	echo "<table id='search_results' class='table_layout_main' style='width:{$config['gallery_width']}' cellpadding='0' cellspacing='0'>
		<tr>
		<td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='$td_width%' "; if($config['page_direction'] == "1")   echo "dir='rtl' style='text-align:right' align='right'"; echo ">
		<div style='float:left'>$head_box</div>
		";
		if($show_get_url_link == "1")   echo "<div style='float:right'>(<a href='#' onClick='displayPrompt(\"$url\")'>{$lang['get_url']}</a>)</div>";
		echo "
		</td>
		</tr>
		";

	//show results according to perpage setting
	$j=0;

	while($j < $config['perpage'] && $j < $numrows) {
		//show first column
		echo "<tr>";
		outputSearchResultsCell($td_width, $result, $config, $lang);
		$j++;

		//show rest of columns
		for($i=1;$i<$config['thumbs_perrow'];$i++) {	 
		  if($j < $numrows) {
				outputSearchResultsCell($td_width, $result, $config, $lang);
				$j++;
			}
		}//end for
		echo "</tr>";
	}//end while $row
	echo "</table>";
}

//--------------------------------------------
// outputSearchResultsCell()
// A recursive algorithm would look much more elegant than this.
// A future version will hopefully update this (January 3, 2005)
//--------------------------------------------
function outputSearchResultsCell($td_width, $result, $config, $lang) {
	require_once("f_global.php");

	//loop until we get to a public row if logged in user isn't admin (new in v0.9.4)
	//do {
		$row = mysql_fetch_array($result);
	//}while($row['who_can_see_this'] == 1 && (!loggedIn($config) || adminStatus($config)));
		
	//if picture hasn't yet been approved or is private, don't show it
	if($row['approved'] != 1) return $result;
	
	//apologies for this extremely crude and dirty workaround
	$row_t = mysql_fetch_array(mysql_query("SELECT UNIX_TIMESTAMP('{$row['d']}') as t;"));
	if($row['d'] != null)   $row['d'] = strftime($config['short_date_format'],$row_t['t'] + $config['timezone_offset']);
	
	echo "<td width='$td_width%' class='pic_cell' valign='bottom'>
	<table width='100%' cellspacing='0' cellpadding='3'>
	<tr><td colspan='{$config['thumbs_perrow']}' align='center'>";
	showPic($config, $lang, $row, '0', '0');
	echo "</td></tr>";
	
	if($config['thumb_show_details'] == 1 || loggedIn($config)) {
		if($config['thumb_details_align'] == 0)   $thumb_details_align = "left";
		elseif($config['thumb_details_align'] == 1)   $thumb_details_align = "center";
		elseif($config['thumb_details_align'] == 2)   $thumb_details_align = "right";
		echo "<tr><td class='cell_highlight' align='$thumb_details_align' width='100%' "; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">";
		$must_close = 1;
	}
	
	//conditional output of data in showEntry()
	if(mysql_field_name($result,3) == "r")   showEntry($lang, $row, $config, array("title","comments","rating"));
	elseif(mysql_field_name($result,1) == "comment_body")   showEntry($lang, $row, $config, array("comment_body","for_gods_sake_no_views"));
	//otherwise, show only the bits that the user wants shown
	//edit this to suit. array() <= {title, date, size, category, rating, comment_body, description}
	else   showEntry($lang, $row, $config, array("title","comments","date"));
	
	//showEntry($lang, $row, $config, array("title","comments","rating"));
	showAdminTools($config, $lang, $row);
	if($must_close)   echo "</td></tr>";
	echo "</table></td>";
}

//--------------------------------------------
// queryBuilder(): Builds an SQL query for the search tool
//--------------------------------------------
function queryBuilder($arr, $config) {
	//our bits
	$author = $arr[0];
	$cat = set2array($arr[1]);
	$queryString = $arr[2];
	$strength = $arr[3]; //E {0,1}
	$sort_results_by = $arr[4]; //E {0,1}
	$sort_results_order = $arr[5]; //E {0,1}

	//safety checks
	assertActivate();
	assert($strength == 0 || $strength == 1);
	assert($sort_results_by == 0 || $sort_results_by == 1);
	assert($sort_results_order == 0 || $sort_results_order == 1);

	//what are we including?
	
	//queryString
	if(strlen($queryString) > 0) {
		//any-words-earch new in v0.9.4
		if($strength == 0) { //if it's a partial search
            $bits = explode(" ", $queryString); //break up the words
            if(sizeof($bits)>0) { //if there's more than one word
                $queryString = "(";
                foreach($bits as $key => $value)
                    $queryString .= "keywords LIKE '%$value%' || "; //build part of our sql statament as such
                $queryString = substr($queryString,0,strlen($queryString)-4);
				$queryString .= ")";
            }
            else   $queryString = "keywords LIKE '%$queryString%'"; //otherwise, just do it as normal
        }
		elseif($strength == 1)   $queryString = "keywords = '$queryString'";
	}
	
	//author
	if(strlen($author) > 0) {
		if($strength == 0)   $author = "submitter LIKE '%$author%'";
		elseif($strength == 1)   $author = "submitter = '$author'";
	}
	
	//category
	$pass = 0;
	$cat_new .= "{$config['table_prefix']}categories.cid";
	
	if(!in_array(0,$cat)) { //if user didn't choose 'Search all categories'
		foreach($cat as $key => $value) {
			//echo "VALUE IS $value <br />";
			assertActivate();
			assert(is_numeric($value)); //safety check category ID
			
			//then, check the category's permissions
			$statement = "SELECT cshow FROM {$config['table_prefix']}categories WHERE cid=$value";
			$result = mysql_query($statement); 
			$row = mysql_fetch_array($result);
			if($row['cshow'] != 1) {
				if($pass == sizeof($cat)-1)   $cat_new .= ")"; //to accomodate case when there's only one cid
				$pass++;
				continue; //invalid cid, so skip it, but increment the counter
			}
			
			//things are ok, so proceed
			if($pass == 0)   $cat_new .= " IN(" . $value;
			elseif($pass > 0)   $cat_new .= "," . $value;
			
			if($pass == sizeof($cat)-1)   $cat_new .= ")"; //to accomodate the case when there's only one cid
			
			$pass++;
		}//end foreach
	}//end if
	
	//sort, order?
	$str_sort = "";
	if($sort_results_by == 0)  $str_sort .= " ORDER BY date";
	elseif($sort_results_by == 1)   $str_sort .= " ORDER BY title";
	if($sort_results_order == 0)  $str_sort .= " DESC";
	elseif($sort_results_order == 1)   $str_sort .= " ASC";
	
	//finally, build the query
	$str .= and_filler($queryString) . $queryString . and_filler($cat_new) . $cat_new . and_filler($author) . $author . $str_sort;
	//echo $str;
	
	return $str;
}

function and_filler($str) {
	if(strlen($str) > 0)   return " AND "; //if str isn't null, return an and
}

?>