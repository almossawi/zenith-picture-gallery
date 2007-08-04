<?php
require_once('../config.php');

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.7/v0.7.2 -> v0.8",
'installing' 					=> "Installing...",
'ok' 							=> "OK",
'alter_table_categories' 			=> "Altering table categories... ",
'alter_table_users' 				=> "Altering table users... ",
'update_table_users' 				=> "Updating table users... ",
'update_table_categories' 			=> "Updating table categories... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished' 						=> "<b>Finished!</b><br><br>In order to enable the new features in v0.8, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";	
		
		# --------------------------------------------------------------------------
		# Alter table categories
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}categories` ADD `parent` INT( 6 ) DEFAULT '-1' NOT NULL ,
					ADD `path` VARCHAR( 128 ) NOT NULL ,
					ADD `cshow` INT( 1 ) DEFAULT '1' NOT NULL ;");

		$msg .= "<br>{$lang_install['alter_table_categories']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Alter table users
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}users` ADD `country` VARCHAR( 20 ),
					ADD `bday_day` INT( 2 ) NOT NULL,
					ADD `bday_month` INT( 2 ) NOT NULL,
					ADD `bday_year` INT( 4 ) NOT NULL,
					ADD `language` VARCHAR( 2 ) DEFAULT 'en' NOT NULL,
					ADD `approved` INT( 1 ) DEFAULT '0' NOT NULL,
					ADD `activated` INT( 1 ) DEFAULT '0' NOT NULL,
					ADD `activation_key` VARCHAR( 42 ),
					ADD `submissions` INT( 6 ) DEFAULT '0' NOT NULL,
					ADD `join_date` DATETIME NOT NULL,
					ADD `hide_email` INT( 1 ) DEFAULT '0' NOT NULL,
					ADD `website` VARCHAR( 255 ) AFTER `hide_email` ,
					ADD `avatar_location` VARCHAR( 255 ),
					ADD `email` VARCHAR( 64 ) NOT NULL,
					ADD `register_ip` VARCHAR( 15 ) NOT NULL,
					ADD `personal_quote` VARCHAR( 255 ),
					ADD `interests` VARCHAR( 255 );");

		$msg .= "<br>{$lang_install['alter_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Update table users
		# --------------------------------------------------------------------------

		$result = mysql_query("UPDATE `{$config['table_prefix']}users` SET `approved` = '1', `activated` = '1' WHERE `uid` = '1';");

		$msg .= "<br>{$lang_install['update_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Update table categories
		# --------------------------------------------------------------------------

		$result = mysql_query("UPDATE `{$config['table_prefix']}users` SET `approved` = '1', `activated` = '1' WHERE `uid` = '1';");

		$msg .= "<br>{$lang_install['update_table_categories']}";

		//update categories
		$statement = "SELECT cid, cname FROM {$config['table_prefix']}categories";
		$result = mysql_query($statement);

		while($row = mysql_fetch_array($result)) {
			$result_pic = mysql_query("UPDATE {$config['table_prefix']}pictures SET category = '{$row['cid']}' WHERE category = '{$row['cname']}'");
	
			if($result_pic) $msg .= "<br>----{$row['cname']} [<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";
			else $msg .= "<br>----<span style='color:#ff0000'>{$row['cname']} ERROR!</span>";
		}

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
