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
 
 File: head.php
 Description: -
 Random quote: "Talent develops in quiet places." -Goethe  
*******************************************************************/

session_start();
require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
setlocale(LC_TIME, "{$config['locale']}"); //added in v0.8.8 DEV (suggested by joseramoncouso)

//is user logged in?
$cookie_name_username = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
$cookie_name_hash = $config['cookie_prefix'] . 'hash';

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

$n = query_numrows("uid","{$config['table_prefix']}users","username=\"{$_COOKIE[$cookie_name_username]}\" AND cookie=\"{$_COOKIE[$cookie_name_hash]}\"");
if($n == 1)   $logged_in = 1;
else   $logged_in = 0;

//is the gallery offline?  If so, let the world know
if($config['gallery_off'] == "1")   $gallery_status = $lang['offline'];

//output header
header("Content-type: text/html; charset=UTF-8");
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
   \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
<title>{$config['title']} {$_SESSION['pic_title']} $gallery_status</title>
<link rel=\"stylesheet\" type=\"text/css\" href=\"" . getSkinElement($config['stylesheet'], "stylesheet.css") . "\" />
<script type=\"text/javascript\" src=\"javascripts/j_global.js\"></script> 
<script type=\"text/javascript\" src=\"javascripts/j_ajax.js\"></script> 
{$_SESSION['pic_description']}
";
?>

<script type="text/javascript">
<!--
	document.write("<style type='text/css'>#thephoto {visibility:hidden;}</style>");
	window.onload = function() {initImage()}
// -->
</script>

<?php
echo "</head>
";

//are we adding anything to <body>
if(isset($head_aware['onload']))   echo "<body onload=\"" . $head_aware['onload'] . "\">";
else   echo "<body>";

//output top bar
echo "<table class='table_layout_main' style='width:{$config['gallery_width']};padding-top:0' cellpadding='0' cellspacing='0'>
<tr><td width='100%' colspan='3' style='background-image:url(" . getSkinElement($config['stylesheet'], "logo_back.gif") . ")'>
<a href='index.php'><img src='" . getSkinElement($config['stylesheet'], "logo.gif") . "' alt='{$lang['powered_by']} Zenith Picture Gallery' title='{$lang['powered_by']} Zenith Picture Gallery' border='0' id='img_tight' /></a>
</td></tr>";
if($config['gallery_off'] == "0" || adminStatus($config)) {
	echo "<tr><td class='navbar'"; if($config['page_direction'] == "1")   echo " dir='rtl'"; echo ">
	<div style='float:left'>";
	echo "<a href='index.php'>{$lang['nav_home']}</a>"; //home
	if($config['allow_user_uploads'] || $logged_in)   echo "<a href='add.php'>{$lang['nav_add']}</a>"; //add
	echo "<a href='display.php'>{$lang['nav_browse']}</a>"; //browse
	echo "<a href='search.php'>{$lang['nav_search']}</a>"; //search
	$row = @mysql_fetch_array(mysql_query("SELECT admin_status FROM {$config['table_prefix']}users WHERE username=\"{$_COOKIE[$cookie_name_username]}\""));
	$admin_status = $row['admin_status']; //admin
	echo "<a href=";
	if($logged_in && $admin_status) //if user is admin
	   echo "'admincp.php?do=config'>{$lang['nav_admincp']}</a><a href='logout.php'>{$lang['nav_logout']} ({$_COOKIE[$cookie_name_username]})</a>";
	elseif($logged_in == 1 && $admin_status == 0) //if user is not admin
	   echo "'logout.php'>{$lang['nav_logout']} ({$_COOKIE[$cookie_name_username]})</a>";
	else {
	   echo "'login.php'>{$lang['nav_login']}</a>";
	   if($config['allow_registrations'] == 1)   echo "<a href='register.php'>{$lang['nav_register']}</a>";
	}
	echo "</div>";
	
	//any pending counts?
	$row = @mysql_fetch_array(mysql_query("SELECT uid FROM {$config['table_prefix']}users WHERE username='{$_COOKIE[$cookie_name_username]}'"));
	$uid = $row['uid'];
	
	echo "<div style='float:right;padding-top:0;margin-top:0'>";
	//any pics marked for download?
	if(isset($_SESSION["marked_".getenv("REMOTE_ADDR")]) && sizeof($_SESSION["marked_".getenv("REMOTE_ADDR")]) > 0)
		echo "<a href='marked_pics.php'><img src='" . getSkinElement($config['stylesheet'], "images/save.gif") . "' alt='{$lang['marked_pics']}' title='{$lang['marked_pics']}' border='0' /></a>";
	if($logged_in && $admin_status) { //if user is admin
		$pending_p = @query_numrows("pid","{$config['table_prefix']}pictures","approved=0");
		$pending_c = @query_numrows("comment_id","{$config['table_prefix']}comments","approved=0");
		$pending_u = @query_numrows("uid","{$config['table_prefix']}users","approved=0");
		echo "<a href='my.php?m=$uid&do=edit-profile'>{$lang['head_my_controls']}</a>";
		if($pending_p > 0 || $pending_c > 0 || $pending_u > 0)   echo "<a href='admincp.php?do=approve'>{$lang['nav_pending']}</a>";
		if($pending_p > 0)   echo "<a href='admincp.php?do=approve' title='$pending_p {$lang['pics_in_cat']}'>" . $pending_p . "{$lang['p']}</a>";
		if($pending_c > 0)   echo "<a href='admincp.php?do=approve-comments' title='$pending_c {$lang['comments']}'>" . $pending_c . "{$lang['c']}</a>";
		if($pending_u > 0)   echo "<a href='admincp.php?do=approve-users' title='$pending_u {$lang['users']}'>" . $pending_u . "{$lang['u']}</a>";
		
	}
	elseif($logged_in) { //show profile link when there are no pending counts
		echo "<a href='my.php?m=$uid&do=edit-profile'>{$lang['head_my_controls']}</a>";
	}
	echo "</div>
	</td></tr>";
}
echo "</table>";

?>