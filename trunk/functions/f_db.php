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
 
 File: f_db.php
 Description: Contains functions that manipulate the database
 Random quote: "Don't tell me how difficult it is to give birth, just
 show me the baby." 
*******************************************************************/

require_once("f_misc.php");
require_once(bugFixRequirePath("../config.php"));

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL.");

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database.");
  
//--------------------------------------------
// query_count()
// returns number of rows based on specified criteria
//--------------------------------------------
function query_numrows($count_what, $table, $filter) {
	$q_statement = "SELECT COUNT($count_what) as n FROM $table WHERE $filter";
	$q_result = mysql_query($q_statement);
	$q_row = mysql_fetch_array($q_result);
	
	return $q_row['n'];
}

?>