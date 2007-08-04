<?php
require_once('../config.php');
require_once('../functions/f_global.php');
#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.4.3 -> v0.5",
'installing' 					=> "Installing...",
'ok' 						=> "OK",
'create_table_comments' 			=> "Creating table comments... ",
'create_table_ratings' 				=> "Creating table ratings... ",
'alter_table_pictures' 				=> "Altering table pictures... ",
'drop_table_languages' 				=> "Dropping table languages... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished' 					=> "<b>Finished!</b><br><br>In order to enable the new features in v0.5, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";

		# --------------------------------------------------------------------------
		# create table comments
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$config['table_prefix']}comments` (
		`comment_id` INT(6) NOT NULL AUTO_INCREMENT,
		`pid` INT(6) NOT NULL,
		`comment_author` VARCHAR(32) NOT NULL,
		`comment_date` DATETIME NOT NULL,
		`comment_ip` VARCHAR(15) NULL,
		`comment_body` TEXT NOT NULL,
		`approved` INT(6) DEFAULT '0' NOT NULL,
		PRIMARY KEY (`comment_id`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_comments']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}
			
		# --------------------------------------------------------------------------
		# create table ratings
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$config['table_prefix']}ratings` (
		`pid` INT(6) NOT NULL,
		`voter_ip` VARCHAR(15) NOT NULL,
		`voter_date` DATETIME NOT NULL,
		PRIMARY KEY (`pid`, `voter_ip`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_ratings']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Alter table pictures
		# --------------------------------------------------------------------------

		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}pictures` ADD `rating` INT(12) DEFAULT '0' NOT NULL ,
		ADD `rating_count` INT(12) DEFAULT '0' NOT NULL");

		$msg .= "<br>{$lang_install['alter_table_pictures']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}

		# --------------------------------------------------------------------------
		# Drop table languages
		# --------------------------------------------------------------------------

		$result = mysql_query("DROP TABLE `{$config['table_prefix']}languages`");

		$msg .= "<br>{$lang_install['drop_table_languages']}";
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
