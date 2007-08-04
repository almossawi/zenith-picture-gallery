<?php
require_once('../config.php');

#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());
  

$lang_install = array (
'title'						=> "Zenith Picture Gallery Upgrader v0.9.2 DEV -> v0.9.4 DEV",
'installing' 					=> "Installing...",
'ok'							=> "OK",
'update_table_pictures' 			=> "Updating table pictures... ",
'update_table_categories' 			=> "Updating table categories... ",
'populate_table_categories' 		=> "Setting numeric ids for all categories... ",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'finished'							=> "<b>Finished!</b><br><br>To complete the upgrade, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";	
		
		# --------------------------------------------------------------------------
		# update table pictures
		# --------------------------------------------------------------------------
		$result = mysql_query("ALTER TABLE `{$config['table_prefix']}pictures` ADD `who_can_see_this` INT( 1 ) DEFAULT '0' NOT NULL ;");
		
		$msg .= "<br>{$lang_install['update_table_pictures']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}
		
		# --------------------------------------------------------------------------
		# update table categories
		# --------------------------------------------------------------------------
		$statement = "ALTER TABLE `{$config['table_prefix']}categories` ADD `cname_for_url` VARCHAR( 255 ) NOT NULL, ADD `position` INT( 3 ) DEFAULT '1' NOT NULL";
		$result = mysql_query($statement);

		$msg .= "<br>{$lang_install['update_table_categories']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}
		
		# --------------------------------------------------------------------------
		# update table categories
		# --------------------------------------------------------------------------
		$statement = "SELECT cid, cname, cname_for_url FROM {$config['table_prefix']}categories";
		$result = mysql_query($statement);
		
		$i = 0;
		while($row = mysql_fetch_array($result)) {
			$cid = $row['cid'];
			$cname = $row['cname'];

			if($cname != "Other") {
				mt_srand(crc32(microtime()));
				$cname_for_url = "cat-".mt_rand(12121212,898989898989);
			}
			else   $cname_for_url = "Other";
			
			if($row['cname_for_url'] == "") {
				$result_key = mysql_query("UPDATE {$config['table_prefix']}categories SET cname_for_url = '$cname_for_url' WHERE cid='$cid'");
				if($result_key)   $i++;
			}
		}
		
		$msg .= "<br>{$lang_install['populate_table_categories']}";
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
