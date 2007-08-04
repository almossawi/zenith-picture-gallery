<?php
require_once('../config.php');

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());
  

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.8.5 -> v0.8.6",
'installing' 					=> "Installing...",
'ok'							=> "OK",
'create_table_ud_fields' 			=> "Creating table ud_fields... ",
'update_table_ud_fields' 			=> "Updating table ud_fields... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished'							=> "<b>Finished!</b><br><br>To updated your gallery's version number to v0.8.6, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";	
		
		# --------------------------------------------------------------------------
		# create table ud_fields
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$config['table_prefix']}ud_fields` (
		`fid` INT(6) NOT NULL AUTO_INCREMENT,
		`ud_field_name` VARCHAR(255) NULL,
		PRIMARY KEY (`fid`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_ud_fields']}";
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
