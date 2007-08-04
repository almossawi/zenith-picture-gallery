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

require_once('config.php');
require_once('functions/f_global.php');
require_once('functions/f_db.php');

if($_GET['register'] == 1) {
	if($config['user_account_validation_method'] == 0)
		$msg = "{$lang['registered_0_msg']}&nbsp;&nbsp;<a href='login.php' target=\"_parent\">{$lang['redirect_proceed']}</a>";
	elseif($config['user_account_validation_method'] == 1)
		$msg = "{$lang['registered_1_msg']}&nbsp;&nbsp;<a href='login.php' target=\"_parent\">{$lang['redirect_proceed']}</a>";
	elseif($config['user_account_validation_method'] == 2)
		$msg = "{$lang['registered_2_msg']}&nbsp;&nbsp;<a href='login.php' target=\"_parent\">{$lang['redirect_proceed']}</a>";
	else
		$msg = "{$lang['registered_2_msg']}&nbsp;&nbsp;<a href='login.php' target=\"_parent\">{$lang['redirect_proceed']}</a>";
	
	doBox("msg", $msg, $config, $lang);
}
else {
	//what type of user logged in?
	$cookie_name = $config['cookie_prefix'] . 'username';
	$row = mysql_fetch_array(mysql_query("SELECT admin_status FROM {$config['table_prefix']}users WHERE username='{$_COOKIE[$cookie_name]}'"));  
	if($config['debug_mode'] == 1)   print_r($_COOKIE);
	
	//redirect to admin cp if admin
	if($row['admin_status'] == 1) { //if admin
		$msg = "{$lang['logged_in_msg']}&nbsp;&nbsp;<a href='admincp.php?do=config' target=\"_parent\">{$lang['redirect_proceed']}</a>
		<meta http-equiv='refresh' content='1;url=admincp.php?do=config'>";
	}
	else { //otherwise, to main page
		$msg = "{$lang['logged_in_msg']}&nbsp;&nbsp;<a href='index.php' target=\"_parent\">{$lang['redirect_proceed']}</a>
		<meta http-equiv='refresh' content='1;url=index.php'>";
	}
	
	doBox("msg", $msg, $config, $lang);
}

?>