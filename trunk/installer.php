<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com

 Modifications:
 * Marcin Krol <hawk@limanowa.net>, CAPTCHA support, v0.9.4 DEV

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.

 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
*******************************************************************/

session_start();


$lang_install = array (

'title'						=> "Zenith Picture Gallery Installer",
'mysql_unable_to_connect' 			=> "Sorry, unable to connect to MySQL.",
'db_unable_to_connect' 				=> "Sorry, unable to connect to database.",
'back_to_installation' 				=> "Back to installation",
'installing' 					=> "Installing...",
'ok' 							=> "OK",
'error' 							=> "Error or directories already exist.  Make sure that uploads is chmoded to 777 and that your server path is correct on the previous page.",
'button_install'					=> "Install",
'button_agree'					=> "Agree",
'create_table_users' 				=> "Creating table users... ",
'create_table_pictures' 			=> "Creating table pictures... ",
'create_table_banned' 				=> "Creating table banned... ",
'create_table_password_hashes' 		=> "Creating table password hashes... ",
'create_table_categories' 			=> "Creating table categories... ",
'create_table_comments' 			=> "Creating table comments... ",
'create_table_ratings' 				=> "Creating table ratings... ",
'create_table_ud_fields' 			=> "Creating table ud_fields... ",
'populate_table_users' 				=> "Populating table users... ",
'populate_table_categories' 			=> "Populating table categories... ",
'create_subdirectories' 			=> "Creating subdirectories based on your map of categories... ",
'finished' 						=> "<b>Finished!</b><br><br>What you should do now is setup your gallery.",
'proceed_to_admincp'				=> "Click here to proceed to the Admin CP",
'installed_already_msg'					=> "The gallery has already been installed.<br>To reinstall, and reset your config file, please FTP into your server and remove the installer.lock file.",
'error_box_title'					=> "An error occurred!",
'or'							=> "Or, ",
'admin_account_error'					=> "Admin username must be between 6 and 12 charactes and password must be between 6 and 20 characters long.",
'clear_session'						=> "Clear session data set by the installer script",
'head_db'							=> "Database settings",
'head_paths'						=> "Directory paths",
'head_admin'						=> "Admin account",
'sql_user'							=> "SQL username",
'sql_pass'							=> "SQL password",
'sql_host'							=> "SQL host",
'db_name'							=> "Database name",
'table_prefix'						=> "Table prefix",
'cookie_prefix'						=> "Cookie prefix",
'upload'							=> "Upload directory path",
'upload_server'						=> "Upload directory server path",
'upload_description'				=> "Remember to include the trailing slash.",
'upload_server_description'			=> "Again, remember to include the trailing slash.",
'admin_user'						=> "Admin username",
'admin_pass'						=> "Admin password",
'email'								=> "Admin email address",
'intro'								=> "Thank you for downloading this version of Zenith Picture Gallery: an online gallery, which by version 1.0 will
										be a feature-rich picture management system.  Check back at <a href=\"http://www.cyberiapc.com\" target=\"_blank\">CyberiaPC.com</a> for constant updates.
										<br><br><span style='color:red'>Note:</span> Make sure you have chmoded config.php to 666 since the installer script will need to write to it.
										<br><span style='color:red'>Note 2:</span> Make sure you have also chmoded the 'uploads', 'incoming', 'outgoing' and 'lang' directories to 777 to allow files to be written to them.
										<br><b><span style='color:red'>Note 3:</span> Make sure you remove this file (installer.php) after setup completes.</b>",
'tos_head'							=> "Terms of use",
'tos_body'							=> "Please note that removing or obscuring the copyright notice, which appears at the bottom of every page, in any way is prohibited!  Refer to section 2c of the GNU GPL for more information.  If you do not agree with this term please remove all Zenith Picture Gallery files from your computer and abort this installation.",
);

require_once('lang/english.php');
require_once('functions/f_global.php');

$config['gallery_width'] = "75%";
$config['stylesheet'] = "skins/default";
$lang['installed_already_msg'] = $lang_install['installed_already_msg'];
if(file_exists("installer.lock")) {
   doBox("error", $lang_install['installed_already_msg'], $config, $lang);
}


?>

<html>
<head>
<title><?php echo "{$lang_install['title']}" ?></title>
<LINK rel="stylesheet" href="skins/default/stylesheet.css" type="text/css">
</head>
<body>

<?php

function installerEscaper($data) {
   return trim(str_replace(array("$","|"),"",$data));
}

function recursiveMkdir($path) {
	if (!file_exists($path)) {
		recursiveMkdir(dirname($path));
		mkdir("$path", 0777);
		chmod("$path",0777);
	}
}

if($_POST['submit'] && !isset($_POST['page'])) {
	//$admin_user	= $_POST['admin_user'];
	//$admin_pass	= $_POST['admin_pass'];
	$admin_user = addslashes(dealWithSingleQuotes(installerEscaper($_POST['admin_user'], $config['escape_chars'])));
	$admin_pass = addslashes(dealWithSingleQuotes(installerEscaper($_POST['admin_pass'], $config['escape_chars'])));
	$email = dealWithSingleQuotes($_POST['email']);
	
	//$email = $_POST['email'];
	$users_ip = getenv("REMOTE_ADDR");
	
	if(strlen($admin_pass) >= 6 && strlen($admin_user) >= 3 && strlen($admin_user) <= 20 && 
		validateAlphaNumericFields(array($admin_pass,$admin_user)) && validateEmailFields(array($email))) {
	   //get new settings
	   $set_config['sql_host'] = installerEscaper($_POST['sql_host']);
	   $set_config['sql_user'] = installerEscaper($_POST['sql_user']);
	   $set_config['sql_pass'] = installerEscaper($_POST['sql_pass']);
	   $set_config['db_name'] = installerEscaper($_POST['db_name']);
	   $set_config['table_prefix'] = installerEscaper($_POST['table_prefix']);
	   $set_config['upload_dir'] = installerEscaper($_POST['upload_dir']);
	   $set_config['upload_dir_server'] = installerEscaper($_POST['upload_dir_server']);
	   $set_config['cookie_prefix'] = installerEscaper($_POST['cookie_prefix']);
	   $set_config['admin_email'] = installerEscaper($email);
	   
	   //add to session, case user needs to refresh for some reason
	   $_SESSION['installer_sql_host'] = $set_config['sql_host'];
	   $_SESSION['installer_sql_user'] = $set_config['sql_user'];
	   $_SESSION['installer_sql_pass'] = $set_config['sql_pass'];
	   $_SESSION['installer_db_name'] = $set_config['db_name'];
	   $_SESSION['installer_table_prefix'] = $set_config['table_prefix'];
	   $_SESSION['installer_cookie_prefix'] = $set_config['cookie_prefix'];
	   $_SESSION['installer_upload_dir'] = $set_config['upload_dir'];
	   $_SESSION['installer_upload_dir_server'] = $set_config['upload_dir_server'];
	   
	   //write MySQL details to config file (unix-style line breaks) change to \r\n for Windows, /r for Macintosh
	   $filename = 'config.php';
	   $content =
		
	"<?php\n" .
	"require_once('lang/english.php');\n\n" . 
	'$sql_host' . " = \"{$set_config['sql_host']}\";\n" .
	'$sql_user' . " = \"{$set_config['sql_user']}\";\n" .
	'$sql_pass' . " = \"{$set_config['sql_pass']}\";\n" .
	'$db_name' . " = \"{$set_config['db_name']}\";\n" .
	'$config[\'table_prefix\']' . " = \"{$set_config['table_prefix']}\";\n" .
	'$config[\'admin_email\']' . " = \"{$set_config['admin_email']}\";\n" .
	'$config[\'upload_dir\']' . " = \"{$set_config['upload_dir']}\";\n" .
	'$config[\'upload_dir_server\']' . " = \"{$set_config['upload_dir_server']}\";\n" .
	'$config[\'language\']' . " = \"lang/english.php\";\n" .
	'$config[\'stylesheet\']' . " = \"skins/default\";\n" .
	'$config[\'title\']' . " = \"CyberiaPC.com Gallery\";\n" .
	'$config[\'description\']' . " = \"Enter description here from Admin CP\";\n" .
	'$config[\'lastadded_perpage\']' . " = \"6\";\n" .
	'$config[\'perpage\']' . " = \"12\";\n" .
	'$config[\'guest_voting\']' . " = \"1\";\n" .
	'$config[\'guest_comments\']' . " = \"1\";\n" .
	'$config[\'comments_show\']' . " = \"1\";\n" .
	'$config[\'voting_show\']' . " = \"1\";\n" .	
	'$config[\'views_show\']' . " = \"1\";\n" .	
	'$config[\'direct_path_show\']' . " = \"1\";\n" .	
	'$config[\'exif_show\']' . " = \"0\";\n" .	
	'$config[\'random_show\']' . " = \"1\";\n" .
	'$config[\'random_perpage\']' . " = \"3\";\n" .
	'$config[\'timezone_offset\']' . " = \"GMT/UTC\";\n" .
	'$config[\'max_filesize\']' . " = \"512\";\n" .
	'$config[\'max_pic_width\']' . " = \"1024\";\n" .
	'$config[\'max_pic_height\']' . " = \"1024\";\n" .
	'$config[\'jpeg_quality\']' . " = \"90\";\n" .
	'$config[\'thumb_width\']' . " = \"150\";\n" .
	'$config[\'thumb_height\']' . " = \"100\";\n" .
	'$config[\'thumb_show_details\']' . " = \"100\";\n" .
	'$config[\'thumbs_perrow\']' . " = \"3\";\n" .
	'$config[\'thumb_suffix\']' . " = \"_small\";\n" .
	'$config[\'thumb_align\']' . " = \"left\";\n" .
	'$config[\'thumb_details_align\']' . " = \"left\";\n" .
	'$config[\'create_thumbs\']' . " = \"1\";\n" .
	'$config[\'short_date_format\']' . " = \"%B %d, %Y\";\n" .
	'$config[\'long_date_format\']' . " = \"%B %d, %Y %r\";\n" .
	'$config[\'cookie_prefix\']' . " = \"{$set_config['cookie_prefix']}\";\n" .
	'$config[\'script_timeout\']' . " = \"30\";\n" .
	'$config[\'chmod_pics\']' . " = \"0644\";\n" .
	'$config[\'escape_chars\'] = array("|","/","$",":","*","`","?",);' . "\n" .
	'$config[\'registration_captcha\']' . " = \"1\";\n" .
	'$config[\'comments_captcha\']' . " = \"1\";\n" .
	'$config[\'debug_mode\']' . " = \"0\";\n" .
	'$config[\'gallery_off\']' . " = \"0\";\n" .
	'$config[\'gallery_width\']' . " = \"750px\";\n" .
	'$config[\'version\']' . " = \"0.9.4 DEV\";\n" .
	'$config[\'default_preview_pic_size\']' . " = \"1\";\n" .
	'$config[\'default_search_box_style\']' . " = \"0\";\n" .
	'$config[\'file_details_show\']' . " = \"1\";\n" .
	'$config[\'submission_details_show\']' . " = \"1\";\n" .
	'$config[\'picture_details_show\']' . " = \"1\";\n" .
	'$config[\'mail_on_add\']' . " = \"0\";\n" .
	'$config[\'keywords_show\']' . " = \"1\";\n" .
	'$config[\'prevent_uploads_to_parents\']' . " = \"0\";\n" .
	'$config[\'page_direction\']' . " = \"0\";\n" .
	'$config[\'mod_queue_guests\']' . " = \"0\";\n" .
	'$config[\'locale\']' . " = \"\";\n" .
	'$config[\'marking_allowed\']' . " = \"1\";\n" .
	'$config[\'page_direction\']' . " = \"0\";\n" .
	'$config[\'marking_allowed\']' . " = \"1\";\n" .
	'$config[\'private\']' . " = \"0\";\n" .
	'$config[\'allowed_media_filetypes\']' . " = array(\"\",);\n" .
	"?>";
	
	$warning_msg = "Cannot open file ($filename).  Make sure it exists and is chmoded to 666 or 777.";
	
		if (is_writable($filename)) {
			if (!$handle = fopen($filename, 'w')) {
				echo "$warning_msg";
				exit;
			}
			if (fwrite($handle, $content) === FALSE) {
				echo "$warning_msg";
				exit;
			}
			fclose($handle);   
		}
		else {
		   echo "<br><br><span style='color:#ff0000'>$warning_msg</span> [<a href='installer.php?s=" . rand() . "'>{$lang_install['back_to_installation']}</a>]";
		   exit();
		}
	
		#connect to mysql
		$connection = mysql_connect($_POST['sql_host'],$_POST['sql_user'],$_POST['sql_pass'])
		  	or die("<br><br><span style='color:#ff0000'>{$lang_install['mysql_unable_to_connect']}</span> [<a href='installer.php?s=" . rand() . "'>{$lang_install['back_to_installation']}</a>]");
		
		#select db
		$db_selected = mysql_select_db($_POST['db_name'],$connection)
		  	or die("<br><br><span style='color:#ff0000'>{$lang_install['db_unable_to_connect']}</span> [<a href='installer.php?s=" . rand() . "'>{$lang_install['back_to_installation']}</a>]");
	  
		$msg .= "<span style=\"color:#666666;font-size:18px;font-weight:bold\">$title</span>
		<br><br><i>{$lang_install['installing']}</i><br><br>";
	
		# --------------------------------------------------------------------------
		# create table users
		# --------------------------------------------------------------------------
	
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}users` (
		`uid` INT(6) NOT NULL AUTO_INCREMENT,
		`username` VARCHAR(32) NOT NULL,
		`password` VARCHAR(42) NOT NULL,
		`admin_status` INT(1) NOT NULL,
		`country` VARCHAR( 20 ),
		`bday_day` INT( 2 ) NOT NULL,
		`bday_month` INT( 2 ) NOT NULL,
		`bday_year` INT( 4 ) NOT NULL,
		`language` VARCHAR( 2 ) DEFAULT 'en' NOT NULL,
		`approved` INT( 1 ) DEFAULT '0' NOT NULL,
		`activated` INT( 1 ) DEFAULT '0' NOT NULL,
		`activation_key` VARCHAR( 42 ),
		`submissions` INT( 6 ) DEFAULT '0' NOT NULL,
		`join_date` DATETIME NOT NULL,
		`hide_email` INT( 1 ) DEFAULT '0' NOT NULL,
		`website` VARCHAR( 255 ),
		`avatar_location` VARCHAR( 255 ),
		`email` VARCHAR( 64 ) NOT NULL,
		`register_ip` VARCHAR( 15 ) NOT NULL,
		`personal_quote` VARCHAR( 255 ),
		`interests` VARCHAR( 255 ),
		`cookie` VARCHAR( 42 ) NOT NULL,
		PRIMARY KEY(`uid`)
		)");
		
		$msg .= "<br>{$lang_install['create_table_users']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['users'] = 1;}
	
		# --------------------------------------------------------------------------
		# create table pictures
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}pictures` (
		`pid` INT(6) NOT NULL AUTO_INCREMENT,
		`file_name` VARCHAR(255) NOT NULL,
		`file_size` VARCHAR(255) NOT NULL,
		`file_type` VARCHAR(255) NOT NULL,
		`title` VARCHAR(255) NOT NULL,
		`date` datetime NOT NULL,
		`pic_width` INT(6) NOT NULL,
		`pic_height` INT(6) NOT NULL,
		`keywords` VARCHAR(255) NOT NULL,
		`approved` INT(6) NOT NULL,
		`category` VARCHAR(32) NOT NULL,
		`ip` VARCHAR(15) NULL,
		`submitter` VARCHAR(32) NULL,
		`counter` INT(12) DEFAULT '0' NOT NULL,
		`rating` INT(12) DEFAULT '0' NOT NULL,
		`rating_count` INT(12) DEFAULT '0' NOT NULL,
		`description` TEXT NULL,
		`is_video` INT(1) DEFAULT '0' NOT NULL,
		`who_can_see_this` INT(1) DEFAULT '0' NOT NULL,
		INDEX(`category`),
		PRIMARY KEY(`pid`)
		)");
		
		$msg .= "<br>{$lang_install['create_table_pictures']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
			else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['pictures'] = 1;}
		
		# --------------------------------------------------------------------------
		# create table banned
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}banned` (
		`bid` INT(6) NOT NULL AUTO_INCREMENT,
		`ip_address` VARCHAR(15) NULL,
		PRIMARY KEY(`bid`)
		)");
		
		$msg .= "<br>{$lang_install['create_table_banned']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		      else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['banned'] = 1;}
	
		# --------------------------------------------------------------------------
		# create table categories
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}categories` (
		`cid` INT(6) NOT NULL AUTO_INCREMENT,
		`cname` VARCHAR(255) NOT NULL,
		`downloadable` INT(6) DEFAULT '1' NOT NULL,
		`parent` INT( 6 ) DEFAULT '-1' NOT NULL ,
		`path` VARCHAR( 128 ) NOT NULL ,
		`cshow` INT( 1 ) DEFAULT '1' NOT NULL,
		`cname_for_url` VARCHAR( 255 ) NOT NULL,
		`position` INT( 3 ) DEFAULT '1' NOT NULL,
		PRIMARY KEY(`cid`)
		)");
		
		$msg .= "<br>{$lang_install['create_table_categories']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
			else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['categories'] = 1;}
		

		# --------------------------------------------------------------------------
		# create table comments
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}comments` (
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
			else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['comments'] = 1;}
			
		# --------------------------------------------------------------------------
		# create table ratings
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}ratings` (
		`pid` INT(6) NOT NULL,
		`voter_ip` VARCHAR(15) NOT NULL,
		`voter_date` DATETIME NOT NULL,
		PRIMARY KEY (`pid`, `voter_ip`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_ratings']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['ratings'] = 1;}
	
		# --------------------------------------------------------------------------
		# create table password hashes
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}password_hashes` (
		`hid` INT(6) NOT NULL AUTO_INCREMENT,
		`username` VARCHAR(32) NOT NULL,
		`hash` VARCHAR(42) NOT NULL,
		`hash_date` DATETIME NOT NULL,
		PRIMARY KEY (`hid`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_password_hashes']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['password_hashes'] = 1;}

		# --------------------------------------------------------------------------
		# create table ud_fields (for user-defined fields - new in v0.8.6 DEV)
		# --------------------------------------------------------------------------
		
		$result = mysql_query("CREATE TABLE `{$set_config['table_prefix']}ud_fields` (
		`fid` INT(6) NOT NULL AUTO_INCREMENT,
		`ud_field_name` VARCHAR(255) NULL,
		PRIMARY KEY (`fid`) 
		)");
		
		$msg .= "<br>{$lang_install['create_table_ud_fields']}";
		if($result) {$msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";}
		else {$msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span>"; $table_exists['ud_fields'] = 1;}

		# --------------------------------------------------------------------------
		# populate tables
		# --------------------------------------------------------------------------
		
		$msg .= "<br>";
		
		//Populate table users
		if($table_exists['users'] != 1) {
		   	if(strlen($admin_pass) >= 6 && strlen($admin_pass) <= 12 && strlen($admin_user) >= 6 && strlen($admin_user) <= 20 && 
				validateAlphaNumericFields(array($admin_pass,$admin_user)) && validateEmailFields(array($email))) {

			  $cookie = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
			  $admin_pass_c = crypt($admin_pass);

			  $result = mysql_query("INSERT INTO `{$set_config['table_prefix']}users` (`username`, `password`, `admin_status`, `approved`, `activated`, `email`, `join_date`, `register_ip`, `cookie`) VALUES ('$admin_user', '$admin_pass_c', '1', '1', '1', '$email', NOW(), '$users_ip', '$cookie');");
			  $msg .= "<br>{$lang_install['populate_table_users']}";
			  if   (!$result) $msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span><br>";
			  else   $msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";
		   }
		   else   $msg .= "<span style='color:#ff0000'>" . $lang_install['login_error_msg'] . "</span><br>";
			  
		}
		
		//Populate table categories
		if($table_exists['categories'] != 1) {
		   unset($INSERT);
		   
		   mt_srand(crc32(microtime())); $cname_for_url = "cat-".mt_rand(12121212,898989898989);
		   $INSERT[] = "INSERT INTO `{$set_config['table_prefix']}categories` (cname, cname_for_url) VALUES ('Technology', '$cname_for_url')";
		   
		   mt_srand(crc32(microtime())); $cname_for_url = "cat-".mt_rand(12121212,898989898989);
		   $INSERT[] = "INSERT INTO `{$set_config['table_prefix']}categories` (cname, cname_for_url) VALUES ('Games', '$cname_for_url')";
		   
		   mt_srand(crc32(microtime())); $cname_for_url = "cat-".mt_rand(12121212,898989898989);
		   $INSERT[] = "INSERT INTO `{$set_config['table_prefix']}categories` (cname, cname_for_url) VALUES ('Movies and Music', '$cname_for_url')";
		   
		   $INSERT[] = "INSERT INTO `{$set_config['table_prefix']}categories` (cname, cname_for_url) VALUES ('Other', 'Other')";
		
		   $msg .= "<br>{$lang_install['populate_table_categories']}";
		
		   foreach($INSERT as $statement) {
			  $result = mysql_query($statement);
		   if(!$result) $msg .= "<span style='color:#ff0000'>" . mysql_error() . "</span><br>";
		   }
		   if($result) $msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";
		}
	
		# --------------------------------------------------------------------------
		# Create subdirectories
		# --------------------------------------------------------------------------

		$dirs_created = 0;
		$dirs_counter = 0;

		$result = mysql_query("SELECT cid, cname, parent FROM `{$set_config['table_prefix']}categories");
		while($row = mysql_fetch_array($result)) {
			$path = $set_config['upload_dir_server'] . $row['cname'];
			
			if(!file_exists($path)) { //make it if directory doesn't already exist
				if(@mkdir("$path", 0777))   $dirs_created ++;
				@chmod("$path",0777);
			}
		
			$dirs_counter ++;
		}

		$msg .= "<br>{$lang_install['create_subdirectories']}";
		if($dirs_counter == $dirs_created)   $msg .= "[<span style='color:#00cc00;font-weight:bold'>{$lang_install['ok']}</span>]";
		else   $msg .= "<span style='color:#ff0000'>" . $lang_install['error'] . "</span><br>";

		# --------------------------------------------------------------------------
		# Finish and tidy up
		# --------------------------------------------------------------------------
		
		echo "$msg";
			echo "<br><br>{$lang_install['finished']}  <a href='login.php'>{$lang_install['proceed_to_admincp']}</a>.
			<br>{$lang_install['or']}[<a href='installer.php?s=" . rand() . "'>{$lang_install['back_to_installation']}</a>]";
		
		mysql_close($connection);

		//lock installer
		srand(time());
		$random = (rand()%262144);

    		if ($handler = @fopen(getCurrentPath("installer.lock"), 'w')) {
        		fwrite($handler, 'locked');
        		fclose($handler);
		}
		else {
			echo "<br><br><strong><span style='color:#ff0000'>Warning!  Couldn't lock the installer file.  Please manually remove installer.php</span></strong>";
		}
	}
	else {
		echo "<br><br><span style='color:#ff0000'>{$lang_install['admin_account_error']}</span> [<a href='installer.php?s=" . rand() . "'>{$lang_install['back_to_installation']}</a>]";
		exit();
	}

	//kill form details stored in session
	unset($_SESSION['installer_sql_user']);
	unset($_SESSION['installer_sql_pass']);
	unset($_SESSION['installer_db_name']);
	unset($_SESSION['installer_table_prefix']);
	unset($_SESSION['installer_upload_dir']);
	unset($_SESSION['installer_upload_dir_server']);
}
else {
	if(strcasecmp($_GET['do'],"clear") == 0)   session_destroy();

	if($_POST['page'] == 2 || isset($_GET['s'])) {
		echo "<table align='center' class='table_layout_sections' cellpadding='2' cellspacing='1' width='75%'>
		<tr><td><span style=\"color:#666666;font-size:18px;font-weight:bold\">{$lang_install['title']}</span><br><br>
		{$lang_install['intro']}</td></tr></table>

		<form name='frmInstaller' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='POST'>
		<table align='center' class='table_layout_sections' cellpadding='2' cellspacing='0' width='75%'>
		<tr><td colspan='2' width='100%' class='cell_header' style='background-image:url(skins/default/images/td_back_mid.gif)'>{$lang_install['head_db']}</td></tr>
		<tr><td align='left' width='50%'>{$lang_install['sql_host']}</td><td align='right' width='50%'><input type='text' name='sql_host' value="; if(isset($_SESSION['installer_sql_host'])) $value=$_SESSION['installer_sql_host']; else $value='localhost'; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['sql_user']}</td><td align='right' width='50%'><input type='text' name='sql_user' value="; if(isset($_SESSION['installer_sql_user'])) $value=$_SESSION['installer_sql_user']; else $value=''; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['sql_pass']}</td><td align='right' width='50%'><input type='password' name='sql_pass' value="; if(isset($_SESSION['installer_sql_pass'])) $value=$_SESSION['installer_sql_pass']; else $value=''; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['db_name']}</td><td align='right' width='50%'><input type='text' name='db_name' value="; if(isset($_SESSION['installer_db_name'])) $value=$_SESSION['installer_db_name']; else $value=''; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>
   		<tr><td align='left' width='50%'>{$lang_install['table_prefix']}</td><td align='right' width='50%'><input type='text' name='table_prefix' value="; if(isset($_SESSION['installer_table_prefix'])) $value=$_SESSION['installer_table_prefix']; else $value='zenith_'; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>
   		<tr><td align='left' width='50%'>{$lang_install['cookie_prefix']}</td><td align='right' width='50%'><input type='text' name='cookie_prefix' value="; if(isset($_SESSION['installer_cookie_prefix'])) $value=$_SESSION['installer_cookie_prefix']; else $value='zenith'.rand(0,262144).'_'; echo"'$value' maxlength='50' class='textBox' style='width:100%'></td></tr>

		<tr><td colspan='2' width='100%' class='cell_header' style='background-image:url(skins/default/images/td_back_mid.gif)'>{$lang_install['head_paths']}</td></tr>
		<tr><td align='left' width='50%'>{$lang_install['upload']}<br><span style='font-size:9px'>{$lang_install['upload_description']}</span></td><td align='right' width='50%'><input type='text' name='upload_dir' value="; if(isset($_SESSION['installer_upload_dir'])) $value = $_SESSION['installer_upload_dir']; else $value="http://www.yoursite.com/gallery/uploads/"; echo "'$value' maxlength='200' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['upload_server']}<br><span style='font-size:9px'>{$lang_install['upload_server_description']}</span></td><td align='right' width='50%'><input type='text' name='upload_dir_server' value="; if(isset($_SESSION['installer_upload_dir_server'])) $value = $_SESSION['installer_upload_dir_server']; else $value=getCurrentPath("uploads/"); echo "'$value' maxlength='255' class='textBox' style='width:100%'></td></tr>

		<tr><td colspan='2' width='100%' class='cell_header' style='background-image:url(skins/default/images/td_back_mid.gif)'>{$lang_install['head_admin']}</td></tr>
		<tr><td align='left' width='50%'>{$lang_install['admin_user']}<br></td><td align='right' width='50%'><input type='text' name='admin_user' maxlength='50' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['admin_pass']}<br></td><td align='right' width='50%'><input type='password' name='admin_pass' maxlength='50' class='textBox' style='width:100%'></td></tr>
		<tr><td align='left' width='50%'>{$lang_install['email']}<br></td><td align='right' width='50%'><input type='text' name='email' maxlength='50' class='textBox' style='width:100%'></td></tr>


		<tr><td colspan='2' align='center'><input type='submit' name='submit' value='{$lang_install['button_install']}' class='submitButton2'></td></tr>
		</table>
		</form>
		<div align='center'><a href='{$_SERVER['PHP_SELF']}?do=clear'><span style='font-size:9px'>{$lang_install['clear_session']}</span></a></div>
		";
	}
	else {
		//display tos
		echo "<table align='center' class='table_layout_sections' cellpadding='2' cellspacing='1' width='75%'>
		<tr><td><span style=\"color:#666666;font-size:18px;font-weight:bold\">{$lang_install['title']}</span><br><br>
		{$lang_install['intro']}</td></tr></table>
	
		<form name='frmTos' enctype='multipart/form-data' action='{$_SERVER['PHP_SELF']}' method='POST'>
		<table align='center' class='table_layout_sections' cellpadding='2' cellspacing='0' width='75%'>
		<tr><td align='center' colspan='2' width='100%' class='cell_header' style='background-image:url(skins/default/images/td_back_mid.gif)'>{$lang_install['tos_head']}</td></tr>
		<tr><td colspan='2' width='100%'>{$lang_install['tos_body']}</td></tr>
		<input type='hidden' name='page' value='2'>
		<tr><td colspan='2' align='center'><input type='submit' name='submit' value='{$lang_install['button_agree']}' class='submitButton2'></td></tr>
		</table>
		</form>
		";
	}
}

?>
