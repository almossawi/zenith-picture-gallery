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
 
 File: thumbify.php
 Description: -
 Random quote: "Simplicity does not preceed complexity, but follows it."
 -Alan J. Perlis
*******************************************************************/

require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

//--------------------------------------------
// process()
//--------------------------------------------
function process($config, $pic, $w, $h, $folder, $smart_resize, $crop) {
	if($folder == "incoming")   $path = getCurrentPath2("$folder/$pic");
	else $path = getPicturesPath($config, $pic, 1);
	include("image_man.php");
	$imn = new ImageManipulator($config, $lang);
	$imn -> doItToBrowser($pic, $path, "$w", "$h", $config['jpeg_quality'],$smart_resize, $crop, $config['pending_pics_suffix']);
}

//set attribs
$pic = charsEscaper($_GET['pic'], $config['escape_chars']);
$w = $_GET['w'];
$h = $_GET['h'];
$crop = $_GET['crop'];
$smart_resize = $_GET['smart_resize'];
$folder = charsEscaper($_GET['folder'], $config['escape_chars']);
if(!is_numeric($w))   $w = 50;
if(!is_numeric($h))   $h = 33;
if(!is_numeric($smart_resize))   $smart_resize = 1;

//create thumbnail
process($config, $pic, $w, $h, $folder, $smart_resize, $crop);

?>