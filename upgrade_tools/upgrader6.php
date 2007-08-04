<?php
require_once('../config.php');

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.8.4 -> v0.8.5",
'installing' 					=> "Installing...",
'ok' 							=> "OK",
'alter_table_users' 				=> "Altering table users... ",
'update_table_users' 				=> "Updating table users... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished' 						=> "<b>Finished!</b><br><br>To updated your gallery's version number to v0.8.5, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";	
		
		# --------------------------------------------------------------------------
		# Alter table users
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}users` ADD `cookie` VARCHAR( 42 )");

		$msg .= "<br>{$lang_install['alter_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Update table users
		# --------------------------------------------------------------------------

		//update activation_key
		$statement = "SELECT uid,cookie FROM {$config['table_prefix']}users WHERE cookie IS NULL";
		$result = mysql_query($statement);
		
		$i = 0;
		while($row = mysql_fetch_array($result)) {
			$cookie = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
			$uid = $row['uid'];
			$result_key = mysql_query("UPDATE {$config['table_prefix']}users SET cookie = '$cookie' WHERE uid='$uid'");
			if($result_key)   $i++;
		}

		$msg .= "<br>{$lang_install['update_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}: $i</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Finish and tidy up
		# --------------------------------------------------------------------------
		
		echo "$msg";
		echo "<br><br>{$lang_install['finished']}  <a href='../login.php'>{$lang_install['proceed_to_admincp']}</a>.";
		
		mysql_close($connection);

echo"
</body>
</html>";


?>
