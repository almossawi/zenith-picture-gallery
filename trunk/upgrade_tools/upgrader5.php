<?php
require_once('../config.php');
require_once('../functions/f_global.php');
#connect to mysql
$connection = @mysql_connect($sql_host,$sql_user,$sql_pass)   or die("Sorry, unable to connect to MySQL. " . mysql_error());

#select db
$db_selected = @mysql_select_db($db_name,$connection)   or die("Sorry, unable to connect to database." . mysql_error());

$lang_install = array (
'title'								=> "Zenith Picture Gallery Upgrader v0.8 -> v0.8.4",
'installing' 							=> "Installing...",
'ok' 									=> "OK",
'create_tables' 							=> "Creating new tables... ",
'create_subdirectories' 					=> "Creating subdirectories based on your map of categories... ",
'move_pics' 							=> "Moving your pictures to their correct subdirectories... ",
'moved' 								=> "Successfuly moved:",
'didnt_move' 							=> "Could not move/already moved:",
'proceed_to_admincp'						=> "Click here to proceed to the Admin CP",
'finished' 									=> "<b>Finished!</b><br><br>In order to enable the new features in v0.8.4, you will need to update your configuration file.  In Admin CP --> Setup and Config., scroll down and click on 'Update Settings'",
);

echo "<html><head>
<title>{$lang_install['title']}</title>
<LINK rel='Stylesheet' href='../skins/default/stylesheet.css' type='text/css'>
</head>
<body>";	

function recursiveMkdir($path) {
	if (!file_exists($path)) {
		recursiveMkdir(dirname($path));
		mkdir("$path", 0777);
		chmod("$path",0777);
	}
}
   
		# --------------------------------------------------------------------------
		# Create subdirectories
		# --------------------------------------------------------------------------

		$result = mysql_query("SELECT cid, cname, parent FROM `{$config['table_prefix']}categories");
		while($row = mysql_fetch_array($result)) {
			$path = getPicturesPathFromCategory($config, $row['cid'], 1, "");
			
			if(!file_exists($path)) { //make it if directory doesn't already exist
				recursiveMkdir("$path");
				//chmod("$path",0777);
			}
		}

		$msg .= "<br>{$lang_install['create_subdirectories']}";
		$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";
		
		# --------------------------------------------------------------------------
		# Move pictures
		# --------------------------------------------------------------------------
		$success_counter = 0;
		$fail_counter = 0;
		
		$result = mysql_query("SELECT cid, cname FROM `{$config['table_prefix']}categories`");
		while($row = mysql_fetch_array($result)) {			
			$current_cat_id = $row['cid'];
			
			//loop through all pics for this category
			$statement2 = "SELECT file_name FROM `{$config['table_prefix']}pictures` WHERE category=$current_cat_id";
			$result2 = mysql_query($statement2);
			while($row2 = mysql_fetch_array($result2)) {
				$current_pic_name = $row2['file_name'];
				
				$path_from = $config['upload_dir_server'] . $current_pic_name;
				$path_to = getPicturesPathFromCategory($config, $current_cat_id, 1, "") . $current_pic_name;
				if(@rename($path_from, $path_to))   $success_counter++;
				else   $fail_counter++;
				
				$bits = splitFilenameAndExtension($path_from);
  				$path_from_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
				$bits = splitFilenameAndExtension($path_to);
  				$path_to_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
				if(@rename($path_from_thumb, $path_to_thumb))   $success_counter++;
				else   $fail_counter++;
			}
		}

		$msg .= "<br>{$lang_install['move_pics']}";
		$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>] <b>( {$lang_install['moved']} <span style='color:#666666'>$success_counter</span> {$lang_install['didnt_move']} <span style='color:red'>$fail_counter</span> )</b>";
		
		# --------------------------------------------------------------------------
		# Create tables
		# --------------------------------------------------------------------------

		$result = mysql_query("CREATE TABLE `{$config['table_prefix']}password_hashes` (
		`hid` INT(6) NOT NULL AUTO_INCREMENT,
		`username` VARCHAR(32) NOT NULL,
		`hash` VARCHAR(42) NOT NULL,
		`hash_date` DATETIME NOT NULL,
		PRIMARY KEY (`hid`) 
		)");
		
		$msg .= "<br>{$lang_install['create_tables']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>";}
		
		# --------------------------------------------------------------------------
		# Finish and tidy up
		# --------------------------------------------------------------------------
		
		echo "$msg";
		echo "<br><br>{$lang_install['finished']}  <a href='../login.php'>{$lang_install['proceed_to_admincp']}</a>.";
		
		mysql_close($connection);

echo "
</body>
</html>";


?>
