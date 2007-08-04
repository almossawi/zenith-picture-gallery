<?php
require_once('../config.php');

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.6.1 -> v0.7",
'installing' 					=> "Installing...",
'ok' 						=> "OK",
'alter_table_pictures' 				=> "Altering table pictures... ",
'alter_table_users' 			=> "Altering table users... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished' 					=> "<b>Finished!</b><br><br>In order to enable the new features in v0.7, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";
		
		
		# --------------------------------------------------------------------------
		# Alter table pictures
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}pictures` ADD `submitter` VARCHAR( 32 ) DEFAULT '-' NOT NULL");

		$msg .= "<br>{$lang_install['alter_table_pictures']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Alter table users
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}users` CHANGE `password` `password` VARCHAR( 41 ) NOT NULL");

		$msg .= "<br>{$lang_install['alter_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
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
