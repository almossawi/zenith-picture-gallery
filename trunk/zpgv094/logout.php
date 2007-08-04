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

//unset cookie
$cookie_name_username = $config['cookie_prefix'] . 'username';
$cookie_name_hash = $config['cookie_prefix'] . 'hash';
if(isset($_COOKIE[$cookie_name_username]))   setcookie($cookie_name_username, '', time()-86400,"/");
if(isset($_COOKIE[$cookie_name_hash]))   setcookie($cookie_name_hash, '', time()-86400,"/");

//unset session
unset($GLOBALS[_SESSION]["{$config['cookie_prefix']}_$_COOKIE[$cookie_username]"]);

require_once('head.php');
$msg = "{$lang['logged_out_msg']}&nbsp;&nbsp;<a href='index.php' target=\"_parent\">{$lang['redirect_proceed']}</a><meta http-equiv='Refresh' content='2; url=index.php'>";
doBox("msg", $msg, $config, $lang);

?>