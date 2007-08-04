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
 
 File: search_ajax.php
 Description: Called by xmlhttprequest in serch.php to process a 
 search query
 Created: July 5, 2007
 Last modified: July 8, 2007 
 Random quote: "The shell must break before the bird can fly"   -Alfred Tennyson
*******************************************************************/

session_start();
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_search.php');
echo "<script type=\"text/javascript\" src=\"javascripts/j_global.js\"></script>";
echo "<script type=\"text/javascript\" src=\"javascripts/j_ajax.js\"></script>";

//check skin stuff
$cookie_name_skin = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'skin'));
if(isset($_COOKIE[$cookie_name_skin])) {
	$config['stylesheet'] = "skins/".$_COOKIE[$cookie_name_skin];
}

//check lang stuff
$cookie_name_lang = trim(str_replace(array(".","/","\\","|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'lang'));
if(isset($_COOKIE[$cookie_name_lang])) {
	if(file_exists("lang/".$_COOKIE[$cookie_name_lang].".php")) {
		require_once("lang/".$_COOKIE[$cookie_name_lang].".php");
	}
}

if($_GET['st'] == "")   $_GET['st'] = 0;
if($_GET['p'] == "")   $_GET['p'] = 1;
//echo "p in url is {$_GET['p']}";

$sort_results_by = $_GET['by'];
$sort_results_order = $_GET['order'];
$cat = $_GET['cat'];
if(!is_numeric($_GET[strength]))   $strength = 0; //ensure it's numeric, otherwise set to default ("allow partial")

$searchquery = charsEscaper(stripslashes($_GET['q']), $config['escape_chars']);
$searchqueryToShow = $searchquery;

//added on Nov 9, 2005 to pass to the SQL statement values that have not gone through strToHex yet.
$searchqueryToProcess = htmlentities($searchquery,ENT_QUOTES, "UTF-8");
$searchauthorToProcess = htmlentities(charsEscaper(stripslashes($_GET['a']), $config['escape_chars']),ENT_QUOTES, "UTF-8");

$searchquery = htmlentities(strToHex($searchquery),ENT_QUOTES, "UTF-8");
$searchauthor = htmlentities(strToHex(charsEscaper(stripslashes($_GET['a']), $config['escape_chars'])),ENT_QUOTES, "UTF-8");

//get those matching search term (if submitter = null, ignore it)
$statement = "SELECT pid, file_name, who_can_see_this, description, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories";

//build the WHERE clause part of the statement
$arr = array(0 => $searchauthor, 1 => $cat, 2 => $searchqueryToProcess, 3 => $strength, 4 => $sort_results_by, 5 => $sort_results_order);
//new in v0.9.4
if(loggedIn($config) && adminStatus($config))
	$statement .= " WHERE {$config['table_prefix']}categories.cid={$config['table_prefix']}pictures.category AND {$config['table_prefix']}categories.cshow=1 AND {$config['table_prefix']}pictures.approved=1 " . queryBuilder($arr, $config);
else
	$statement .= " WHERE {$config['table_prefix']}categories.cid={$config['table_prefix']}pictures.category AND {$config['table_prefix']}categories.cshow=1 AND {$config['table_prefix']}pictures.approved=1 AND {$config['table_prefix']}pictures.who_can_see_this=0 " . queryBuilder($arr, $config);

$result = mysql_query($statement); //get result set from user entered criteria
$numrows = @mysql_num_rows($result); //get number of rows in result set

//modded in v0.9.4
//if($searchquery!=null)   $_SESSION['success_msg'] = sprintf($lang['search_complete1'],$numrows,$searchqueryToShow);
//else   $_SESSION['success_msg'] = sprintf($lang['search_complete2'],$numrows);

if($searchquery!=null)   $_SESSION['success_msg'] = sprintf($lang['search_complete1'],$numrows,$searchqueryToShow);
else   $_SESSION['success_msg'] = sprintf($lang['search_complete2'],$numrows);

echo "<div id='msg_div' class='msg' style='height:15px'>";
if(isset($_SESSION['success_msg'])) {
   echo "{$_SESSION['success_msg']}";
   unset($_SESSION['success_msg']);
}
echo "</div>";
  
//$redirect = "Location: search.php?t=$searchtype&q=$searchquery&a=$searchauthor&strength=$strength&cat=$cat&nr=$numrows&st=0&upto=".$config['perpage']."&p=1&by=$sort_results_by&order=$sort_results_order";
//header("$redirect");
//exit();

$_GET['nr'] = $numrows;
$_GET['upto'] = $_GET['st'] + $config['perpage'];

outputSearchPageBar($config, $lang, "search_ajax", 1);

//if query data is in the URI, requery table using that data instead of showing a blank page
if(isset($_GET['q'])) {
	$q = $_GET['q'];
	$a = $_GET['a'];
	$sort_results_by = $_GET['by'];
	$sort_results_order = $_GET['order'];
	$searchquery = htmlentities(hexToStr(charsEscaper(stripslashes($q), $config['escape_chars'])),ENT_QUOTES, "UTF-8");
	$searchauthor = htmlentities(hexToStr(charsEscaper(stripslashes($a), $config['escape_chars'])),ENT_QUOTES, "UTF-8");
	$strength = $_GET['strength'];
	if(!is_numeric($strength))   $strength = 0; //ensure it's numeric, otherwise set to default ("allow partial")
	$cat = $_GET['cat'];
	
	//get those matching search term (if submitter = null, ignore it)
	$statement = "SELECT pid, file_name, who_can_see_this, description, ROUND(file_size/1024) AS fsize, file_type, title, keywords, date AS d, pic_width, pic_height, keywords, approved, category, counter, submitter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories";
	
	//build the WHERE clause part of the statement
	$arr = array(0 => $searchauthor, 1 => $cat, 2 => $searchquery, 3 => $strength, 4 => $sort_results_by, 5 => $sort_results_order);
	
	//new in v0.9.4
	if(loggedIn($config) && adminStatus($config))
		$statement .= " WHERE {$config['table_prefix']}categories.cid={$config['table_prefix']}pictures.category AND {$config['table_prefix']}pictures.approved=1 AND {$config['table_prefix']}categories.cshow=1 " . queryBuilder($arr, $config);
	else
		$statement .= " WHERE {$config['table_prefix']}categories.cid={$config['table_prefix']}pictures.category AND {$config['table_prefix']}pictures.approved=1 AND {$config['table_prefix']}pictures.who_can_see_this=0 AND {$config['table_prefix']}categories.cshow=1 " . queryBuilder($arr, $config);
}

$statement .= " LIMIT " . $_GET['st'] . "," . $_GET['upto'];
$result = mysql_query($statement);   //get result set from user entered criteria from session statement
//echo $statement;
//output search results only if there exists a valid result set and its size is > 0
if($result && mysql_num_rows($result)>0)
	outputSearchResults($lang, $result, $config, $_GET['st'], $_GET['upto'], $lang['head_search_results'], 1);
  
outputSearchPageBar($config, $lang, "search_ajax", 1);
?>