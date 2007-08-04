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
 Random quote: "If man makes himself a worm he must not complain 
 when he is trodden on."  -Immanuel Kant
*******************************************************************/

session_start();
require_once('config.php');

//did user choose to change the skin?
if($_POST['submit_skin'] && $_POST['skinSelect'] != -1 && $_POST['skinSelect'] != -2) {
	$skin = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $_POST['skinSelect']));
	$cookie_time = 86400 * 14; //expire after 2 weeks
	setcookie($config['cookie_prefix']."skin", $skin, time() + $cookie_time,"/");
	$redirect = "Location: index.php?skin=~" . rand(0,127);
	header("$redirect");
	exit();
}

//did user choose to change the language?
if($_POST['submit_lang'] && $_POST['langSelect'] != -1 && $_POST['langSelect'] != -2) {
	$lang = trim(str_replace(array(".","/","\\","|","'","$",":","*","`","?",";"),"", $_POST['langSelect']));
	$cookie_time = 86400 * 14; //expire after 2 weeks
	setcookie($config['cookie_prefix']."lang", $lang, time() + $cookie_time,"/");
	$redirect = "Location: index.php?lang=~" . rand(0,127);
	header("$redirect");
	exit();
}

require('head.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_search.php');
require_once('functions/f_db.php');
  
//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

//output welcome box
showWelcomeBox($config, $lang);

//are we showing the news bar?
//if(strlen($config['news_bar']) > 0)   echo "<div class='news_bar'>{$config['news_bar']}</div>";
if(strlen($config['news_bar']) > 0) {
	//output navigation bar
	//align='center' removed in v0.9.4 DEV
    echo "<table class='table_layout_sections' cellspacing='4' cellpadding='3' style='width:{$config['gallery_width']};margin-top:1em'>
    <tr><td>
    <span class='news_bar'>{$config['news_bar']}</span>
    </td></tr>
    </table>
    ";
}

//output main content
if($config['main_page_show'] == "0")   showPicturesAsAlbum($config, $lang); //output album-type view
else   showLastAddedPictures($config, $lang); //output most recently added
  
//are we showing random pictures?
if($config['random_show'])   showRandomPictures($config, $lang);

showSkinsLangRssForm($config, $lang); //show skin and language selectors and a link to the rss feed
include('foot.php');
mysql_close($connection); //tidy up and close connection
  
?>