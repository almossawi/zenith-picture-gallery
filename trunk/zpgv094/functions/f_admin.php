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
 
 File: f_admin.php
 Description: Contains functions used in the admin control panel
 Last updated: July 8, 2007
 Random quote: "Never say there's  no competition, there's always 
 competition." 
*******************************************************************/

session_start();

require_once("f_misc.php");
require_once("f_db.php");

//--------------------------------------------
// doAdminCatEdit()
//--------------------------------------------
function doAdminCatEdit($config) {
   $status = 1;
   $downloadable_array = $_POST['downloadable'];
   $parent_array = $_POST['parent'];
//print_r($_POST);
   foreach($_POST as $key => $value) {
      if(is_numeric($key)) { //if it's a cat
         
         $downloadable = $downloadable_array[$key];
		 $parent = $parent_array[$key];

         //get old cname from table categories
         $statement = "SELECT cname FROM {$config['table_prefix']}categories WHERE cid=$key";
         $result = mysql_query($statement); $row = mysql_fetch_array($result) or die(mysql_error());
         $oldcname = $row['cname'];
         if(!$result)   $status = 0;
	   
         //update cname, parent and downloadable in table categories
         if(strlen(trim($value)) > 0) {
            //ref. integrity.  Update all categories in table pictures for this updated category
			//v0.8 no need.  pictures.category now stores cid
            //$statement = "UPDATE {$config['table_prefix']}pictures SET category='$value' WHERE category='$oldcname'";
            //$result = mysql_query($statement) or die(mysql_error());
            //if(!$result)   $status = 0;
			
			//change the name of the directory to the new one			
			$old_path = getPicturesPathFromCategory($config, $key, 1, "");
			$old_path = substr($old_path,0,strlen($old_path)-1);
			$new_path = getPicturesPathFromCategory($config, $key, 1, $value);
			if(file_exists(getPicturesPathFromCategory($config, $key, 1,""))) {
				//echo "<b>$key $value</b> <br /> $old_path <br /> $new_path <br /><br />";
				rename("$old_path", "$new_path"); //change it to new cname
			}
		
			$value = charsEscaper(makeStringFileStructureFriendly($value), $config['escape_chars']);
            $statement = "UPDATE {$config['table_prefix']}categories SET cname='$value', ";
			if($parent != $key)   $statement .= "parent=$parent, "; //if parent is set to the category, ignore it or we'll go into a black hole
			$statement .= "downloadable=$downloadable WHERE cid=$key";
			$result = mysql_query($statement) or die(mysql_error());
            if(!$result)   $status = 0;
			
			//move the directory to a new location only if it's parent has been changed
			if($parent == '-1')   $new_path = $config['upload_dir_server'] . $value;
			else   $new_path = getPicturesPathFromCategory($config, $parent, 1, "") . $value; //echo "<b><br />Old Path is $old_path and new parent path is $new_parent_path";
			
			//has the category's parent been changed?
			if($parent != $key && $old_path != $new_path) { //echo "<br />detected a change in parent....";				
				//then move it to its new location and move over all files
				@rename($old_path,$new_path);
			}
         }
         else { $status = 0; }
      }
   }
   //print_r($_REQUEST);
	
   return $status;
}

//--------------------------------------------
// doAdminCatMan(): Adds new categories
//--------------------------------------------
function doAdminCatMan($config) {
   $status = 1;
   //print_r($_POST);
   $downloadable_array = $_POST['downloadable'];
   $parent_array = $_POST['parent'];
//print_r($parent_array);
   foreach($_POST['category'] as $value)
      $cat[] = charsEscaper($value, $config['escape_chars']); 

   $i = 0;
   foreach($cat as $value) {
      if($value != '') {
		  //v0.9.4  need to modify since this name will no longer be used as the directory name
	  	 $value = charsEscaper(makeStringFileStructureFriendly($value), $config['escape_chars']);

		 mt_srand(crc32(microtime()));
		 $value_for_url = "cat-".mt_rand(12121212,898989898989);
		 
		 //echo "parent at $i is ".$parent_array['r'];
         $statement = "INSERT INTO {$config['table_prefix']}categories (cname,cname_for_url, parent,downloadable) VALUES ('$value','$value_for_url','{$parent_array[$i]}','{$downloadable_array[$i]}')";
		 //echo $statement;
		 $result = mysql_query($statement)  or die(mysql_error());
         if(!$result)   $status = 0;
		 
		 //get the last category's ID (modified in v0.9.2 DEV)
		 //$statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname='".$value."'";
         $statement = "SELECT LAST_INSERT_ID() as cid";
		 $result = mysql_query($statement);
		 if(!$result) {
			//if for some reason, last statement didn't work, use old method.  Only problem is that if cname already exists, bad things will happen
		 	$statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname='".$value."'";
		 }
		 $row = mysql_fetch_array($result);

		 //create subdirectory
		 $path = getPicturesPathFromCategory($config, $row['cid'], 1, "");
		 if(!file_exists($path)) { //make it if directory doesn't already exist
			mkdir("$path");
			chmod("$path",0777);
		 }
      }

      $i++;
   }

   return $status;
}

//--------------------------------------------
// doAdminCatRebuild(): Rebuilds directory names
// numeric ids -> normal names or vice versa (settable in Setup & Config)
//--------------------------------------------
function doAdminCatRebuild($config) {
	$status = 1;
	$counter = 0;
	
	$statement = "SELECT cid, cname, cname_for_url FROM {$config['table_prefix']}categories";
	$result = mysql_query($statement)  or die(mysql_error()); if(!$result)   $status = 0; 

	$paths = array();
	while($row = mysql_fetch_array($result)) {
		$current_path = getPicturesPathFromCategory($config, $row['cid'], 1, "", "rebuilding_directories");
		
		if($config['v094paths'] == 1)   $what = $row['cname']."/"; //if v0.9.4 paths are off, return the category name
		else  $what = $row['cname_for_url']."/"; //otherwise, return its numerical id
		$new_path = getPicturesPathFromCategory($config, $row['cid'], 1, $what, "rebuilding_directories");
		$number_of_slashes = substr_count($current_path,"/"); //echo "number of slashes $number_of_slashes";
		$paths[] = array("item" => $current_path, "item_new" => $new_path, "cid" => $row['cid'], "id" => $number_of_slashes*-1); //why *-1 so we can reverse sort instead of having to modify msort()
	}
	
	//now sort then loop through and rename
	//print_r($paths);
	$paths = msort($paths);
	//echo "<br /><br />";print_r($paths);
	foreach($paths as $key => $value) {
		$to = $value['item']; $from = $value['item_new'];
		@rename($from, $to);
		@chmod($to,0777);
		$counter++;
	}
	
	$_SESSION['counter'] = $counter;
	
	if($result)   $status=1;
	else   $status=0;
	
	return $status;
}

//--------------------------------------------
// doAdminCatOrder(): Orders categories instead of defaulting to alphabetic sorting
//--------------------------------------------
function doAdminCatOrder($config) {
	$status = 1;
	
	foreach($_POST as $key => $value) {
		if(!is_numeric($key))   continue;
		$statement = "UPDATE {$config['table_prefix']}categories SET position=$value WHERE cid=$key";
		$result = mysql_query($statement);
		if(!$result)   $status = 0;
	}
	
	return $status;
}

//--------------------------------------------
// doAdminCatPermissions() (see v0.8 notes for comments and trace)
//--------------------------------------------
function doAdminCatPermissions($config) {
   $status = 1;
   //print_r($_POST);
   $cshow_array = $_POST['cshow'];

   //$i = 0;
   $updated_cshow_array = $cshow_array;
   
   foreach($cshow_array as $key => $value) {
      if(array_key_exists($key,$updated_cshow_array)) {
		  if($value != '') {
			 $statement = "UPDATE {$config['table_prefix']}categories SET cshow=$value WHERE cid=$key";
			 $result = mysql_query($statement)  or die(mysql_error()); if(!$result)   $status = 0;
			 //echo "cat: $statement <br />";
			 
			 if($value == 0) {
				 $statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE parent=$key";
				 $result = mysql_query($statement)  or die(mysql_error()); if(!$result)   $status = 0; 
	
				 while($row = mysql_fetch_array($result)) {
					$updated_cshow_array = setChildToHidden($row['cid'], $config['table_prefix'], $updated_cshow_array);
				 }
			 }
		  }
	  }

      //$i++;
   }

   return $status;
}

//--------------------------------------------
// setChildToHidden(): Helper method belonging to doAdminCatPermissions()
//--------------------------------------------
function setChildToHidden($cid, $table_prefix, $updated_cshow_array) {
	$statement = "UPDATE ".$table_prefix."categories SET cshow=0 WHERE cid=$cid";
	$result = mysql_query($statement)  or die(mysql_error());
	unset($updated_cshow_array[$cid]);

	$statement = "SELECT cid FROM ".$table_prefix."categories WHERE parent=$cid";
	$result = mysql_query($statement)  or die(mysql_error());
	if(mysql_num_rows($result) > 0) {
		while($row = mysql_fetch_array($result)) {
			$updated_cshow_array = setChildToHidden($row['cid'], $table_prefix, $updated_cshow_array);
		}
	}
	
	return $updated_cshow_array;
}

//--------------------------------------------
// doAdminCatDel()
//--------------------------------------------
function doAdminCatDel($config) { 
   $status = 1;	
   $value = $_GET['c'];  
   //require("functions/f_db.php.php");
   //get cname from table categories of cid
   
   if(is_numeric($value)) { //added in v0.8
	   $statement = "SELECT cname FROM {$config['table_prefix']}categories WHERE cid=$value";
	   $result = mysql_query($statement) or die(mysql_error()); $row = mysql_fetch_array($result);
	   $cname = $row['cname'];
	   if(mysql_num_rows($result) == 0)   $status = 0;
	   elseif(mysql_num_rows($result) > 0) {
		   $statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname='Other'";
		   $result = mysql_query($statement) or die(mysql_error()); $row = mysql_fetch_array($result);
		   $cid_to = $row['cid'];
		   
		   //move all pictures from current subdirectory to the other subdirectory
		   moveFiles($config, $value, $cid_to,0);
		   
		   //delete the subdirectory
		   $path = getPicturesPathFromCategory($config, $value, 1, "");
		   if(file_exists($path)) {
			  rmdir("$path");
		   }
		
		   //delete cname in table categories
		   $statement = "DELETE FROM {$config['table_prefix']}categories WHERE cid=$value";
		   $result = mysql_query($statement) or die(mysql_error());
		   if(!$result)   $status = 0;
				 
		   //ref. integrity.  Update all categories in table pictures with default category 'Other'
		   //$statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE cname='Other'";
		   //$result = mysql_query($statement) or die(mysql_error());
		   //$row = mysql_fetch_array($result);
		   //$cid = $row['cid'];
		   $statement = "UPDATE {$config['table_prefix']}pictures SET category='".$cid_to."' WHERE category='$value'";
		   $result = mysql_query($statement) or die(mysql_error());
		   if(!$result)   $status = 0;
		}
	}
}

//--------------------------------------------
// doAdminNewUser()
//--------------------------------------------
function doAdminNewUser($config) {
   //$username = htmlentities(charsEscaper($_POST['username'], $config['escape_chars']),ENT_QUOTES); //htmlentities new in v0.8
   //$password = htmlentities(charsEscaper($_POST['password'], $config['escape_chars']),ENT_QUOTES);
	$username = addslashes(dealWithSingleQuotes(charsEscaper($_POST['username'], $config['escape_chars'])));
	$password = addslashes(dealWithSingleQuotes(charsEscaper($_POST['password'], $config['escape_chars'])));
	$email = dealWithSingleQuotes($_POST['email']);
	
   //validate both.  If not alphanumeric, do nothing
   if(!validateAlphaNumericFields(array($username,$password)) || !validateEmailFields(array($email)) ||
   		strlen($username) == 0 || strlen($password) == 0 ||
		strlen($password) < 6 || strlen($password) > 12 || strlen($username) < 6 || strlen($username) > 20) {
		$status = 0;
	}
	else {
		$password_c = crypt($password);
		$activation_key = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
		$cookie = str_replace(array("$","/","."),array("9","Z","E"),crypt(mt_rand()));
		$admin_status = $_POST['admin_status'];
		
		if(strcasecmp($admin_status,'on')) $admin_status = 0;
		elseif(strcasecmp($admin_status,'off')) $admin_status = 1;
	
		$statement = "INSERT INTO {$config['table_prefix']}users (username, password, email, admin_status, approved, activated, activation_key, join_date, hide_email, cookie) VALUES ('$username', '$password_c', '$email', '$admin_status', '1', '1', '$activation_key', NOW(), '1', '$cookie')";
		$result = mysql_query($statement) or die(mysql_error());
	   
		if($result)   $status = $username;
		else   $status = 0;
   }
  
   return $status;
}

//--------------------------------------------
// doAdminUserMan()
//--------------------------------------------
function doAdminUserMan($config) {
   $status = 1;

   $admin_status_arr = $_POST['admin'];
   $password_arr = $_POST['password'];
	
	//print_r($admin_status_arr); echo "\n"; print_r($password_arr);

   foreach($admin_status_arr as $key => $value) {
   	$statement = "UPDATE {$config['table_prefix']}users SET admin_status='$value' WHERE uid='$key'";
   	$result = mysql_query($statement);
	if(!result)   $status = 0;
   }

   foreach($password_arr as $key => $value) {
   	if(strlen($value) >= 6 && validateAlphaNumericFields(array($value))) {
		$pass_c = crypt($value);
		$statement = "UPDATE {$config['table_prefix']}users SET password='$pass_c' WHERE uid='$key'";
	   	$result = mysql_query($statement);
		if(!result)   $status = 0;
	}
	elseif(strlen(trim($value)) != 0) {
		$status = 0;
		$statement = "SELECT username FROM {$config['table_prefix']}users WHERE uid='$key'";
	   	$result = mysql_query($statement);
		$row = mysql_fetch_array($result);
		$troublesome_users .= "{$row['username']} ";
	}
   }

   if(isset($troublesome_users))   $status = $troublesome_users;
  
   return $status;
}

//--------------------------------------------
// doAdminUserDel()
//--------------------------------------------
function doAdminUserDel($config, $uid) {
   $status = 0;
	  
   //delete uid in table users
   $statement = "DELETE FROM {$config['table_prefix']}users WHERE uid='$uid'";
   $result = mysql_query($statement) or die(mysql_error());
   if($result && isset($status))   $status = 1;
   else   $status = mysql_error();

   return $status;
}

//--------------------------------------------
// doAdminApprove()
//--------------------------------------------
function doAdminApprove($config) {
   foreach($_POST as $key => $value) {
      if(is_numeric($key)) {
		$statement = "SELECT pid, file_name FROM {$config['table_prefix']}pictures WHERE pid=$key";
		$result = mysql_query($statement);
		$row = mysql_fetch_array($result);

		//added in v0.9.4
		$at_server = getPicturesPath($config, $row['file_name'], 1, "/");		
		$bits_i = splitFilenameAndExtension($row['file_name']);
		$filename_thumb = $bits_i[0].$config['thumb_suffix'] . "." . $bits_i[1];
			
	     if($value == 0) {
			$statement = "UPDATE {$config['table_prefix']}pictures SET approved=1 WHERE pid=$key";
			$result = mysql_query($statement);
			
			if(file_exists($at_server.$row['file_name'].".{$config['pending_pics_suffix']}")) {
				//rename picture and thumbnail if necessary (added in v0.9.4)
				rename($at_server.$row['file_name'].".{$config['pending_pics_suffix']}", $at_server.$row['file_name']); //rename pic
				rename($at_server.$filename_thumb.".{$config['pending_pics_suffix']}", $at_server.$filename_thumb); //rename its thumb
			}
	     }
	     if($value == 1) {
	        if($row)  {
				$to_delete = $at_server . $row['file_name'];
				$to_delete_thumb = $at_server . $filename_thumb;
			}
  
	        $statement = "DELETE FROM {$config['table_prefix']}pictures WHERE pid=$key";
            $result = mysql_query($statement);
			@unlink("$to_delete");
			@unlink("$to_delete_thumb");
	     }
      }
   }
}

//--------------------------------------------
// doAdminApproveComment()
//--------------------------------------------
function doAdminApproveComment($config) {
   foreach($_POST as $key => $value) {
      if(is_numeric($key)) {
	     if($value == 0) {
	        $statement = "UPDATE {$config['table_prefix']}comments SET approved=1 WHERE comment_id=$key";
                $result = mysql_query($statement);
	     }
	     if($value == 1) { 
	        $statement = "DELETE FROM {$config['table_prefix']}comments WHERE comment_id=$key";
                $result = mysql_query($statement);
	     }
      }
   }
}

//--------------------------------------------
// doAdminApproveUser()
//--------------------------------------------
function doAdminApproveUser($config) {
   foreach($_POST as $key => $value) {
      if(is_numeric($key)) {
	     if($value == 0) {
	        $statement = "UPDATE {$config['table_prefix']}users SET approved='1', activated='1' WHERE uid=$key";
                $result = mysql_query($statement);
	     }
	     if($value == 1) { 
	        $statement = "DELETE FROM {$config['table_prefix']}users WHERE uid=$key";
                $result = mysql_query($statement);
	     }
      }
   }
}

//--------------------------------------------
// doAdminBatchDel()
//--------------------------------------------
function doAdminBatchDel($config, $lang) {
   //if the main (second) form is submitted
   if(sizeof($_POST['do_what']) > 0) { //if one or more pictures were marked for deletion
      $append = "&status=1";
      $success_counter = 0;

	  //print_r($_POST['file_name']);
	  //print_r($_POST['do_what']);

      foreach($_POST['do_what'] as $key => $value) {
	     //delete its files

		 //the lazy man's way of doing it
		 $file_name_arr = $_POST['file_name'];
		 $file_name_for_key = $file_name_arr[$key];
		
		 //$bits = splitFilenameAndExtensionMirror($file_name_for_key);
		 //$thumb_filename = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
		 //$to_delete = $config['upload_dir_server'] . $file_name_for_key;
         //$to_delete_thumb = $config['upload_dir_server'] . $thumb_filename;
		 $to_delete = getPicturesPath($config, $file_name_for_key, 1);
		 $bits = splitFilenameAndExtensionMirror($to_delete);
		 $to_delete_thumb = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
         @unlink("$to_delete"); @unlink("$to_delete_thumb");
		 
         //delete picture from table pictures
         $statement = "DELETE FROM {$config['table_prefix']}pictures WHERE pid=$key";
         $result = mysql_query($statement);
         if(!$result)   $append = "&status=0A";
         else   $success_counter++;

         //finally, remove all comments for this picture...
         $statement = "DELETE FROM {$config['table_prefix']}comments WHERE pid=$key";
         if(!$result)   $append = "&status=0B";
         $result = mysql_query($statement);
  
         //...and votes
         $statement = "DELETE FROM {$config['table_prefix']}ratings WHERE pid=$key";
         $result = mysql_query($statement);
         if(!$result)   $append = "&status=0C";
      }

      //finally, add the number of deleted pictures to the session
      $_SESSION['batchdel_success_counter'] = $success_counter;
   }

   //if the first form is submitted
	$catFriendly = strToHex($_POST['category']);//make cat name friendly
   if(isset($_POST['category']))   $append = "&c=".$catFriendly;
   if(isset($_POST['show_thumbs']))   $append .= "&st={$_POST['show_thumbs']}";
   return $append;
}

//--------------------------------------------
// getValueFromIPTC(): Returns a string value for the given IPTC type
// To figure out what IPTC_Type it is that you're after, edit the file lib/jpeg_metadata_toolkit/INSTRUCTIONS.php
// for a particular image (see its comments) and run it in your browser
//--------------------------------------------
function getValueFromIPTC($filename, $iptc_type) {
	//include the necessary files
	include_once(bugFixRequirePath("../lib/jpeg_metadata_toolkit/JPEG.php"));
	include_once(bugFixRequirePath("../lib/jpeg_metadata_toolkit/Photoshop_IRB.php"));
	include_once(bugFixRequirePath("../lib/jpeg_metadata_toolkit/PictureInfo.php"));
	include_once(bugFixRequirePath("../lib/jpeg_metadata_toolkit/EXIF.php"));
	
	//get the IPTC data for this image
	$huge_blob = var_export(get_Photoshop_IPTC(get_Photoshop_IRB(get_jpeg_header_data($filename))), true);
	
	//get the bit of interest
	$pattern = "/'IPTC_Type' => '".$iptc_type."'(.|\r|\n)*/";
	preg_match($pattern, $huge_blob, $matches);
	
	//return the RecData for that bit
	$pattern = "/'RecData' => '(.)*'/";
	preg_match($pattern, $matches[0], $matches);
	$recData = substr($matches[0],14,strlen($matches[0])-15);

	if(!$recData) return "-No IPTC data found-"; //if no data was found for the particular IPTC type, return this
	else return  $recData; //otherwise return the glorious recData
}


//--------------------------------------------
// doAdminBatchAdd()
//--------------------------------------------
function doAdminBatchAdd($config, $lang) {
	if(isset($config['script_timeout']) && !ini_get('safe_mode'))   set_time_limit($config['script_timeout']);
	
	//get arrays
	$title = $_POST['title'];
	$keyword = $_POST['keyword'];
	$filename_array = $_POST['filename'];
	$do_what = $_POST['do_what'];
	
	//declare image manipulator class
	include(bugFixRequirePath("../image_man.php"));
	$imn = new ImageManipulator($config, $lang);
   
	$allowed_file_types = array(1 => 'jpg','jpeg','png','gif');
	if(is_array($config['allowed_media_filetypes'])) { //make sure that it's first defined as an array
		$allowed_file_types = array_merge($allowed_file_types, $config['allowed_media_filetypes']); //add the media extensions specified in the admin cp to the array of allowed types
	}
	
	$success_counter = 0; $counter = 0; $delete_counter = 0;
	$path = getCurrentPath2("incoming/");
	
	$uploaded_category = $_POST['category'];

	//loop through all filenames
	foreach($filename_array as $key => $value) {
      if($do_what[$value] == 0) { //if current filename is to be approved
         //$extension_array = explode('.', $value);
	      //$extension = $extension_array[1];
			$bits = splitFilenameAndExtensionMirror($value);
			$extension = strtolower($bits[1]);
		   $filename_escape_chars = $config['escape_chars'];  $filename_escape_chars[] = ' ';
		   $filename = htmlentities(charsEscaper($value, $filename_escape_chars), ENT_QUOTES, "UTF-8");
		   //$final_upload_location_server = $config['upload_dir_server'] . $filename;
		   $final_upload_location_cat_server = getPicturesPathFromCategory($config, $uploaded_category, 1,"");
		   $final_upload_location_server = $final_upload_location_cat_server;
		   //$final_upload_location = $config['upload_dir'] . $filename;
		   $final_upload_location_cat = getPicturesPathFromCategory($config, $uploaded_category, 0,"");
		   $final_upload_location = $final_upload_location_cat;
		   $is_at = "$path$value"; //unstripped filename = $value
		   $ip = $_POST['ip'];
		   
		   //new in ZVG
			if(isVideo($config,"",$filename)) {
				$is_video = 1;
			}
		   
		   $uploaded_title = htmlentities(charsEscaper($title[$value], $config['escape_chars']), ENT_QUOTES, "UTF-8");
		   //$uploaded_title = htmlentities(charsEscaper(getValueFromIPTC($is_at,"2:120"), $config['escape_chars']), ENT_QUOTES, "UTF-8");
		   
		   $uploaded_keywords = htmlentities(charsEscaper($keyword[$value], $config['escape_chars']), ENT_QUOTES, "UTF-8");
		   //$uploaded_keywords = htmlentities(charsEscaper(getValueFromIPTC($is_at,"2:25"), $config['escape_chars']), ENT_QUOTES, "UTF-8");
		   
		   $filesize = filesize($is_at); //in bytes
		   if(function_exists("mime_content_type"))   $filetype = mime_content_type($is_at); //store mime type only if php is compiled with it
		   $any_errors = 0; $validation_error = 0; $counter++;
		 
		 //print_r($_POST);
		 //echo "<br />value is $value ---- title is $uploaded_title , keyword is $uploaded_keywords , file is $filename";

		 //001 -- validation check
		 if(strlen($uploaded_category) <= 0 || 
		 strlen($uploaded_title) <= 0 || 
		 strlen($uploaded_keywords) <= 0) {
		    $msg .= $lang['add_check1'];
			$any_errors = 1;
			$validation_error = 1;
		 }
		 
		 if(!function_exists("gd_info") && $config['create_thumbs'] == "1") {
			$add_msg .= "<br />".$lang['no_gd_msg'];
			$any_errors = 1;
			$validation_error = 1;
		}
			  
		 //if form did not validate, skip remaining checks
		 if(!$validation_error) {
	           //002 -- extension check
		   if (!in_array($extension, $allowed_file_types)) {
		      $msg_filetype .= "$filename ";
			  $any_errors = 1;
		   }
				
		   //003 -- valid image check
		   //new in ZVG (skip for videos)
			if (!$is_video) {
			   $usize = @getimagesize($is_at); //get dimensions of image
			   if($usize) { //is valid image check
				  $uploaded_pwidth = $usize[0];
				  $uploaded_pheight = $usize[1];
			   }
			   else {
				  $msg_valid .= "$filename ";
				  $any_errors = 1;
			   }
					  
			   //004 -- width/height check
			   $usize = @getimagesize($is_at); //get dimensions of image
			   if($uploaded_pwidth > $config['max_pic_width'] || $uploaded_pheight > $config['max_pic_width']) { 
				  $msg_dimensions .= "$filename ";
				  $any_errors = 1;
			   }
			}//end new in ZVG
			else { //set dimensions to thumb dimensions since it's a video
				$uploaded_pwidth = $config['thumb_width'];
				$uploaded_pheight = $config['thumb_height'];
			}
						  
			//005 -- file already exists check (on filename only)
			//$statement = "SELECT file_name FROM {$config['table_prefix']}pictures";
		    //$result = mysql_query($statement);
		    //if($result) {
		     // while($row = mysql_fetch_array($result, MYSQL_NUM))	{
			    // if($row[0]==$filename) { //check against the stripped filename
				    //$msg_file_exists .= "$filename "; //file exists stored seperately
					//$any_errors = 1;
				 //}
			  // }//end while
		    //}//end if $result
			
			//doesFileExist does the check and suggests new filename in case of one already existing - new in v0.9.2 DEV
			$dfe_bits = doesFileExist($config, $filename);
			$filename_new = $dfe_bits[0];
			$name_change_counter = $dfe_bits[1];
			$is_at = $final_upload_location_server.$filename_new;
			if($name_change_counter > 0) {
				$msg_file_exists .= "$filename ";
			}
			  
						  
			//006 -- file size check
			if($filesize > ($config['max_filesize']*1024)) {
			  $msg_filesize .= "$filename ";
			  $any_errors = 1;
			}
		}//end if !$validation_error
						
		 //If all went well...
		 if(!$any_errors) {
			//get submitter's name
			//$cookie_name = $config['cookie_prefix'] . 'username';
			//$cookie_name_trimmed = trim(str_replace(array("|","/","$",":","*","`","?",";"),"", $cookie_name));
			
			if(loggedIn($config)) {
			  $cookie_name_username = trim(str_replace(array("|","'","$",":","*","`","?",";"),"", $config['cookie_prefix'] . 'username'));
			  $submitter = $_COOKIE[$cookie_name_username];
			}
			//if(isset($_COOKIE[$cookie_name_trimmed]))   $submitter = $_COOKIE[$cookie_name_trimmed];

		    //Add to db
			 $statement = "INSERT INTO {$config['table_prefix']}pictures (file_name,file_size,file_type,title,date,pic_width,pic_height,keywords,approved,category,ip,is_video,submitter) 
			 VALUES ('$filename_new','$filesize','$filetype','$uploaded_title',NOW(),'$uploaded_pwidth','$uploaded_pheight','$uploaded_keywords','1','$uploaded_category','$ip','$is_video','$submitter')";
			 if($result = mysql_query($statement))   $success_counter++; //keep count of successful uploads
				
		    if($result) {
			   if(@rename("$path$filename", $final_upload_location_server.$filename_new)) {				
			     //now that the image is in the db and the uploads dir, create its thumbnail and delete the mini-thumbnail in incoming/thumbs
				  if($config['create_thumbs'] == 1 && !$is_video) { //new in ZVG (no need to create a thumb if the file is a video)
				      //$imn -> ImageManipulator($config, $lang);
					  //$imn -> decider($extension,$filename,$config['thumb_width'],$config['thumb_height'],$config['jpeg_quality'],$config['upload_dir'], $config['upload_dir_server'], $config['thumb_suffix'], $config['chmod_pics']); //filename,width,height,quality,path
				  	  $imn -> decider($extension,$filename_new,$config['thumb_width'],$config['thumb_height'],$config['jpeg_quality'],$final_upload_location_cat, $final_upload_location_cat_server, $config['thumb_suffix'], $config['chmod_pics'], $cofig, $lang); //filename,width,height,quality,path
				  }
				  $bits = splitFilenameAndExtensionMirror($filename);
				  $thumb_filename = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
				  @unlink($path."thumbs/$thumb_filename");
			   }
			   else {
			      $msg .= $lang['add_check6'] . " ";
				  if($config['debug_mode'] == 1)   $msg .= "<br />{$lang['debug_error_returned']} " . mysql_errno() . ' ' . mysql_error();
				
				  $pid = mysql_insert_id();
				  $statement = "DELETE FROM {$config['table_prefix']}pictures WHERE pid = $pid";
				  $result = mysql_query($statement);
			  }
		   }
		   else { //if !result
		      $msg .= $lang['add_check7'] . " ";
			   if($config['debug_mode'] == 1)   $msg .= "<br />{$lang['debug_error_returned']} " . mysql_errno() . ' ' . mysql_error();
	  	   }
		 } //any_errors check
		
		  //show batchadd report only if one or more pictures were marked for approval
		  $_SESSION['success_counter'] = $success_counter;
	     $_SESSION['counter'] = $counter;
   
	  } //end if approved
	  elseif($do_what[$value] == 1) { //if deleted
		  $to_delete = "$path$value";
	     @unlink("$to_delete");  //delete the file in 'incoming'
		  
		  $bits = splitFilenameAndExtensionMirror($value);
		  $thumb_filename = $bits[0] . $config['thumb_suffix'] . "." . $bits[1];
		  @unlink($path."thumbs/$thumb_filename");
		  
		  $delete_counter++; $_SESSION['delete_counter'] = $delete_counter;
      }
   } //end for

   //add error logs to session
   $_SESSION['msg'] = $msg;
   $_SESSION['msg_file_exists'] = $msg_file_exists;
   $_SESSION['msg_filesize'] = $msg_filesize;
   $_SESSION['msg_dimensions'] = $msg_dimensions;
   $_SESSION['msg_valid'] = $msg_valid;
   $_SESSION['msg_filetype'] = $msg_filetype;
   //echo "success counter is " . $success_counter . "counter is " . sizeof($filename_array); 
}

//--------------------------------------------
// doAdminRecalcUsers()
//--------------------------------------------
function doAdminRecalcUsers($config) {
	$status = 1;
	$user = getUsers($config);
	foreach($user as $key => $value) {
		$numrows = query_numrows("pid","{$config['table_prefix']}pictures","submitter='$value'");
		$result = mysql_query("UPDATE {$config['table_prefix']}users SET submissions='$numrows' WHERE uid='$key'");
		if(!$result)   $status = 0;
	}
	
	return "&status=$status";
}

//--------------------------------------------
// doAdminRebuildThumbs()
//--------------------------------------------
function doAdminRebuildThumbs($config, $lang, $cycleLength, $next) {
	if(isset($config['script_timeout']) && !ini_get('safe_mode'))   set_time_limit($config['script_timeout']);
	
	//declare image manipulator class
	if($imn == NULL) {
		include(bugFixRequirePath("../image_man.php"));
		$imn = new ImageManipulator($config, $lang);
	}

	$counter = 0;
		
	//get all pictures
	$pics = getFullSizePics($config, "uploads", "");
	$n = sizeof($pics);

	//calculate $num
	if($next == 0) {
		$num = $n;
		if($n > $cycleLength)   $num = $next + $cycleLength;
	}
	else {
		$num = $next + $cycleLength;
		if($num > $n) $num = $n;
	}

	$imn -> ImageManipulator($config, $lang);
	if(!isset($next))   $next = 0;
	$i = $next;
	//print_r($pics);
	while($i < $num) { //break when we reach the last picture to be processed
		$key = $i;
		$value = $pics[$key];
	
		$bits = splitFilenameAndExtensionMirror($value[1]);
		$extension = $bits[1];
		$filename_escape_chars = $config['escape_chars'];  //$filename_escape_chars[] = ' ';
		$filename = charsEscaper($value[1], $filename_escape_chars);
		
		$final_upload_location = str_replace($value[1],"",getPicturesPath($config, $value[1], 0));
		$final_upload_location_server = $value[0];
		
		if($config['create_thumbs'] == 1) { 
			$imn -> decider($extension,$filename,$config['thumb_width'],$config['thumb_height'],$config['jpeg_quality'], $final_upload_location, $final_upload_location_server, $config['thumb_suffix'], $config['chmod_pics'], $config, $lang); //filename,width,height,quality,path
			$_SESSION['last_to_be_added'] = $filename; //echo "key: $key value: $value filename: $filename <br />";
			$counter++;
		}
			
		++$i;
	} //end while

	$_SESSION['counter'] = $_SESSION['counter'] + $counter; 
	$next = $i;
	$append = "&next=$next&cycleLength=$cycleLength&n=$n";

	return $append;
}

//--------------------------------------------
// doAdminEditLogin()
//--------------------------------------------
function doAdminEditLogin($config) {
   $id = $_POST['uid'];
   $username_old = $_POST['username_old'];
   $username = $_POST['username'];
   $password_old = $_POST['password_old'];
   $password_new = $_POST['password_new'];
   $password_new2 = $_POST['password_new2'];

   $statement = "SELECT password FROM {$config['table_prefix']}users WHERE username='$username_old'";
   $result = mysql_query($statement);
   if($result)   $row = mysql_fetch_array($result);

   if($password_new == $password_new2 && crypt($password_old, $row['password']) == $row['password'] && 
   //if($password_new == $password_new2 && strcmp($row[0], $password_old) == 0 && //check that old password is correct and both new passwords are the same
      strlen($password_new) >= 6 && strlen($username) >= 3 && strlen($username) <= 20
	  && validateAlphaNumericFields(array($username,$password_new,$password_new2))) {
	  $crypted_password = crypt($password_new);
      $statement = "UPDATE {$config['table_prefix']}users SET username='$username', password='$crypted_password' WHERE uid=$id";
      $result = @mysql_query($statement);
      if($result) $status=1;
      
      //update cookie
      $cookie_time = 43200; //expire after 12 hours
      setcookie($config['cookie_prefix']."username", $username, time()-86400,"/");
      setcookie($config['cookie_prefix']."username", $username, time() + $cookie_time,"/");
   }
   else   $status = 0;

   return $status;
}
  
//--------------------------------------------
// doAdminOptimize()
//--------------------------------------------
function doAdminOptimize($config) {
   $statement = "OPTIMIZE TABLE 
   {$config['cookie_prefix']}password_hashes, 
   {$config['cookie_prefix']}banned, 
   {$config['cookie_prefix']}categories, 
   {$config['cookie_prefix']}pictures, 
   {$config['cookie_prefix']}users, 
   {$config['cookie_prefix']}comments, 
   {$config['cookie_prefix']}ratings, 
   {$config['cookie_prefix']}ud_fields";
   $result = @mysql_query($statement);
   if($result)   $status=1;
	else   $status=0;
  
   return $status;
}

//--------------------------------------------
// doAdminBan()
//--------------------------------------------
function doAdminBan($config) {
   $is_add = $_POST['submitAdd'];
   $is_remove = $_POST['submitRemove'];
   if(isset($is_add)) {
      $ip = charsEscaperAdmin($_POST['txtIp']); //get selected ip (with trimmed ws, ...)
	  if($ip != '') {
         $statement = "INSERT INTO {$config['table_prefix']}banned (ip_address) VALUES ('$ip')";
         $result = @mysql_query($statement) or die(mysql_error());
         if($result)   $status="1a";
      }
   }
   elseif(isset($is_remove)) {
      $status = 0;
	  if(sizeof($_POST['ip']) > 0) {
	     foreach ($_POST['ip'] as $key => $value) {
		    if($value != '') {
		       $statement = "DELETE FROM {$config['table_prefix']}banned WHERE bid='$value'";
		       $result = @mysql_query($statement);
		       if($result && isset($status))   $status = "1d";
                   else   $status = mysql_error();
		    }
	     }
      }
   }
   return $status;
}

//--------------------------------------------
// doAdminDefineNewFields()
//--------------------------------------------
function doAdminDefineNewFields($config) {
	$is_add = $_POST['submitAdd'];
	$is_remove = $_POST['submitRemove'];
	$udField = htmlentities(charsEscaperAdmin($_POST['txtUdField']), ENT_QUOTES, "UTF-8");
	
	if(isset($is_add)) {
		if($udField != '') {
			//add ud_field_name to ud_fields
			$statement = "INSERT INTO {$config['table_prefix']}ud_fields (ud_field_name) VALUES ('$udField')";
			$result = @mysql_query($statement) or die(mysql_error());
			if($result)   $status="1a";

			//alter the pictures table so that it includes the new field
			//currently, the id is stored in the table pictures (this may need to be changed)
			$statement = "ALTER TABLE {$config['table_prefix']}pictures ADD `udf_" . mysql_insert_id() . "` VARCHAR( 255 ) NULL;";
			$result = @mysql_query($statement) or die(mysql_error());
			if($result)   $status="1b";
		}
	}
	elseif(isset($is_remove)) {
		$status = 0;
		if(sizeof($_POST['ud_fields']) > 0) {
			foreach ($_POST['ud_fields'] as $key => $value) {
				if($value != '') {
					//remove ud_field_name from the table ud_fields
					$statement = "DELETE FROM {$config['table_prefix']}ud_fields WHERE fid='$value'";
					$result = @mysql_query($statement);
					if($result && isset($status))   $status = "1c";
					
					//alter the pictures table and remove the field
					$statement = "ALTER TABLE {$config['table_prefix']}pictures DROP `udf_$value`;";
					$result = @mysql_query($statement) or die(mysql_error());
					if($result)   $status="1d";
				}
			}
		}
	}
	return $status;
}

//--------------------------------------------
// doAdminLang()
//--------------------------------------------
function doAdminLang($config) {
	$final_upload_location = "lang/" . $_FILES['lang_upload']['name'];
	if(move_uploaded_file($_FILES['lang_upload']['tmp_name'], $final_upload_location))   $status = 1;
	else   $status = 0;
	
	return $status;
}

//--------------------------------------------
// charsEscaperAdmin()
//--------------------------------------------
function charsEscaperAdmin($data) {
	$escape_chars_array[] = "#";
    return str_replace("#","",trim($data));
}

//--------------------------------------------
// doAdminConfig()
//--------------------------------------------
function doAdminConfig($config, $sql_pass) {
	//get new settings
	$str_escape_chars = preg_split("//", $_POST['escape_chars'], -1, PREG_SPLIT_NO_EMPTY);
	$set_config['escape_chars'] = 'array(';
	for($i=0;$i<sizeof($str_escape_chars);$i++) {
		if($str_escape_chars[$i] == "\\" || $str_escape_chars[$i] == "\"") continue; //if we have an escaping slash or a ", don't add it
		$set_config['escape_chars'] .= "\"" . $str_escape_chars[$i] . "\","; //addslashes() to escape chars like \ and '
	}
	$set_config['escape_chars'] .= ')';
	
	//allowed media file types
	$str_allowed_media_filetypes = explode(",", $_POST['allowed_media_filetypes']);
	$set_config['allowed_media_filetypes'] = 'array(';
	for($i=0;$i<sizeof($str_allowed_media_filetypes);$i++) {
		$ignored = array("\\","\"","exe","php","htm","html","asp");
		if(in_array($str_allowed_media_filetypes[$i],$ignored)) continue; //don't add certain entries
		$set_config['allowed_media_filetypes'] .= "\"" . trim($str_allowed_media_filetypes[$i]) . "\","; //addslashes() to escape chars like \ and '
	}
	$set_config['allowed_media_filetypes'] .= ')';
	
	//should we update the sql password? (added in v0.8.5) 						
	if(trim($_POST['sql_pass']) == "")   $set_config['sql_pass'] = $sql_pass; //get the current sql_pass
	else   $set_config['sql_pass'] = charsEscaperAdmin($_POST['sql_pass']);  //use the new sql_pass
	
	//are the lang and stylesheet paths valid? (added in v0.8.6)
	$file_lang = charsEscaperAdmin($_POST['language']);
	if (!file_exists($file_lang)) {
		$set_config['language'] = $config['language']; //set it to the existing language
		$_SESSION["{$config['cookie_prefix']}error_lang_path"] = "style='color:red'";
	}
	else {
		$set_config['language'] = charsEscaperAdmin($_POST['language']);
	}
	
	//make sure the path uses forward and not backward slashes (added in v0.9 DEV)
	$_POST['upload_dir_server'] = str_replace("\\", "/", $_POST['upload_dir_server']);
	$_POST['upload_dir_server'] = str_replace("//", "/", $_POST['upload_dir_server']);
	
	$dir_stylesheet = charsEscaperAdmin($_POST['stylesheet']);
	if (!file_exists($dir_stylesheet)) {
		$set_config['stylesheet'] = $config['stylesheet']; //set it to the existing stylesheet
		$_SESSION["{$config['cookie_prefix']}error_stylesheet_path"] = "style='color:red'";
	}
	else {
		$set_config['stylesheet'] = charsEscaperAdmin($_POST['stylesheet']);
		$bits = explode("/", $set_config['stylesheet']); //update when Windows support is added
		setcookie($config['cookie_prefix']."skin", $bits[1], time() + $cookie_time,"/");
	}
	
	$set_config['title'] = charsEscaperAdmin(stripslashes($_POST['title']));
	$set_config['description'] = charsEscaperAdmin(stripslashes($_POST['description']));
	$set_config['admin_email'] = charsEscaperAdmin($_POST['admin_email']);
	$set_config['lastadded_perpage'] = charsEscaperAdmin($_POST['lastadded_perpage']);
	$set_config['perpage'] = charsEscaperAdmin($_POST['perpage']);
	$set_config['random_show'] = charsEscaperAdmin($_POST['random_show']);
	$set_config['guest_voting'] = charsEscaperAdmin($_POST['guest_voting']);
	$set_config['guest_comments'] = charsEscaperAdmin($_POST['guest_comments']);
	$set_config['comments_show'] = charsEscaperAdmin($_POST['comments_show']);
	$set_config['voting_show'] = charsEscaperAdmin($_POST['voting_show']);
	$set_config['views_show'] = charsEscaperAdmin($_POST['views_show']);
	$set_config['direct_path_show'] = charsEscaperAdmin($_POST['direct_path_show']);
	$set_config['exif_show'] = charsEscaperAdmin($_POST['exif_show']);
	$set_config['random_perpage'] = charsEscaperAdmin($_POST['random_perpage']);
	$set_config['timezone_offset'] = charsEscaperAdmin($_POST['timezone_offset']);
	$set_config['gallery_width'] = charsEscaperAdmin($_POST['gallery_width']);
	$set_config['sql_host'] = charsEscaperAdmin($_POST['sql_host']);
	$set_config['sql_user'] = charsEscaperAdmin($_POST['sql_user']);
	$set_config['db_name'] = charsEscaperAdmin($_POST['db_name']);
	$set_config['upload_dir'] = $_POST['upload_dir'];
	$set_config['upload_dir_server'] = $_POST['upload_dir_server'];
	$set_config['max_filesize'] = charsEscaperAdmin($_POST['max_filesize']);
	$set_config['max_pic_width'] = charsEscaperAdmin($_POST['max_pic_width']);
	$set_config['max_pic_height'] = charsEscaperAdmin($_POST['max_pic_height']);
	$set_config['allow_user_uploads'] = charsEscaperAdmin($_POST['allow_user_uploads']);
	$set_config['mail_on_add'] = charsEscaperAdmin($_POST['mail_on_add']);
	$set_config['jpeg_quality'] = charsEscaperAdmin($_POST['jpeg_quality']);
	$set_config['thumb_width'] = charsEscaperAdmin($_POST['thumb_width']);
	$set_config['thumb_height'] = charsEscaperAdmin($_POST['thumb_height']);
	$set_config['thumbs_perrow'] = charsEscaperAdmin($_POST['thumbs_perrow']);
	$set_config['thumb_suffix'] = charsEscaperAdmin($_POST['thumb_suffix']);
	$set_config['thumb_align'] = charsEscaperAdmin($_POST['thumb_align']);
	$set_config['thumb_details_align'] = charsEscaperAdmin($_POST['thumb_details_align']);
	$set_config['thumb_show_details'] = charsEscaperAdmin($_POST['thumb_show_details']);
	$set_config['create_thumbs'] = charsEscaperAdmin($_POST['create_thumbs']);
	$set_config['allow_registrations'] = charsEscaperAdmin($_POST['allow_registrations']);
	$set_config['show_members_list'] = charsEscaperAdmin($_POST['show_members_list']);
	$set_config['user_account_validation_method'] = charsEscaperAdmin($_POST['user_account_validation_method']);
	$set_config['short_date_format'] = charsEscaperAdmin($_POST['short_date_format']); 
	$set_config['long_date_format'] = charsEscaperAdmin($_POST['long_date_format']); 
	$set_config['cookie_prefix'] = trim(str_replace(array("|","/","$",":","*","`","?",";"),"", charsEscaperAdmin($_POST['cookie_prefix']))); 
	$set_config['script_timeout'] = charsEscaperAdmin($_POST['script_timeout']);
	$set_config['chmod_pics'] = charsEscaperAdmin($_POST['chmod_pics']);
	$set_config['registration_captcha'] = charsEscaperAdmin($_POST['registration_captcha']);
	$set_config['comments_captcha'] = charsEscaperAdmin($_POST['comments_captcha']);
	$set_config['debug_mode'] = charsEscaperAdmin($_POST['debug_mode']);
	$set_config['gallery_off'] = charsEscaperAdmin($_POST['gallery_off']);
	$set_config['private'] = charsEscaperAdmin($_POST['private']);
	$set_config['version'] = charsEscaperAdmin($_POST['version']);
	$set_config['table_prefix'] = charsEscaperAdmin($_POST['table_prefix']);
	$set_config['default_preview_pic_size'] = charsEscaperAdmin($_POST['default_preview_pic_size']);
	$set_config['default_search_box_style'] = charsEscaperAdmin($_POST['default_search_box_style']);
	$set_config['file_details_show'] = charsEscaperAdmin($_POST['file_details_show']);
	$set_config['submission_details_show'] = charsEscaperAdmin($_POST['submission_details_show']);
	$set_config['picture_details_show'] = charsEscaperAdmin($_POST['picture_details_show']);
	$set_config['keywords_show'] = charsEscaperAdmin($_POST['keywords_show']);
	$set_config['prevent_uploads_to_parents'] = charsEscaperAdmin($_POST['prevent_uploads_to_parents']);
	$set_config['main_page_show'] = charsEscaperAdmin($_POST['main_page_show']);
	$set_config['mod_queue_registered_users'] = charsEscaperAdmin($_POST['mod_queue_registered_users']);
	$set_config['mod_queue_guests'] = charsEscaperAdmin($_POST['mod_queue_guests']);
	$set_config['locale'] = charsEscaperAdmin($_POST['locale']);
	$set_config['marking_allowed'] = charsEscaperAdmin($_POST['marking_allowed']);
	$set_config['page_direction'] = charsEscaperAdmin($_POST['page_direction']);
	$set_config['fixed_sized_thumbs'] = charsEscaperAdmin($_POST['fixed_sized_thumbs']);
	$set_config['v094paths'] = charsEscaperAdmin($_POST['v094paths']);
	$set_config['pending_pics_suffix'] = charsEscaperAdmin($_POST['pending_pics_suffix']);
	
	//convert news bar double quotes to single quotes
	$set_config['news_bar'] = str_replace("\"", "'", stripslashes(charsEscaperAdmin(preg_replace("/\r\n|\n|\r/", "<br />", $_POST['news_bar'])))); //preg_replace(...) new in v0.9.2 DEV
	
	//do any required validation
	if($set_config['perpage'] < 2)   $set_config['perpage'] = 2;
	if($set_config['random_perpage'] < 1)   $set_config['random_perpage'] = 1;
	if($set_config['lastadded_perpage'] < 1)   $set_config['lastadded_perpage'] = 1;
	
	//update cookie if the cookie prefix was changed by logging out and allowing user to log back in
	if($set_config['cookie_prefix'] != $config['cookie_prefix']) {
		$cookie_name = $config['cookie_prefix'] . 'username';
		setcookie($cookie_name, '', time()-86400,"/"); //unset old cookie
	}
   
   //update settings in config file
   $filename = 'config.php';
   $content =
	"<?php\n" .
	"require_once('{$set_config['language']}');\n\n" . 
	'$sql_host' . " = \"{$set_config['sql_host']}\";\n" .
	'$sql_user' . " = \"{$set_config['sql_user']}\";\n" .
	'$sql_pass' . " = \"{$set_config['sql_pass']}\";\n" .
	'$db_name' . " = \"{$set_config['db_name']}\";\n" .
	'$config[\'title\']' . " = \"{$set_config['title']}\";\n" .
	'$config[\'description\']' . " = \"{$set_config['description']}\";\n" .
	'$config[\'upload_dir\']' . " = \"{$set_config['upload_dir']}\";\n" .
	'$config[\'upload_dir_server\']' . " = \"{$set_config['upload_dir_server']}\";\n" .
	'$config[\'max_filesize\']' . " = \"{$set_config['max_filesize']}\";\n" .
	'$config[\'max_pic_width\']' . " = \"{$set_config['max_pic_width']}\";\n" .
	'$config[\'max_pic_height\']' . " = \"{$set_config['max_pic_height']}\";\n" .
	'$config[\'allow_user_uploads\']' . " = \"{$set_config['allow_user_uploads']}\";\n" .
	'$config[\'mail_on_add\']' . " = \"{$set_config['mail_on_add']}\";\n" .
	'$config[\'jpeg_quality\']' . " = \"{$set_config['jpeg_quality']}\";\n" .
	'$config[\'thumb_width\']' . " = \"{$set_config['thumb_width']}\";\n" .
	'$config[\'thumb_height\']' . " = \"{$set_config['thumb_height']}\";\n" .
	'$config[\'thumbs_perrow\']' . " = \"{$set_config['thumbs_perrow']}\";\n" .
	'$config[\'thumb_suffix\']' . " = \"{$set_config['thumb_suffix']}\";\n" .
	'$config[\'thumb_align\']' . " = \"{$set_config['thumb_align']}\";\n" .
	'$config[\'thumb_details_align\']' . " = \"{$set_config['thumb_details_align']}\";\n" .
	'$config[\'thumb_show_details\']' . " = \"{$set_config['thumb_show_details']}\";\n" .
	'$config[\'create_thumbs\']' . " = \"{$set_config['create_thumbs']}\";\n" .
	'$config[\'allow_registrations\']' . " = \"{$set_config['allow_registrations']}\";\n" .
	'$config[\'show_members_list\']' . " = \"{$set_config['show_members_list']}\";\n" .
	'$config[\'user_account_validation_method\']' . " = \"{$set_config['user_account_validation_method']}\";\n" .
	'$config[\'lastadded_perpage\']' . " = \"{$set_config['lastadded_perpage']}\";\n" .
	'$config[\'perpage\']' . " = \"{$set_config['perpage']}\";\n" .
	'$config[\'guest_voting\']' . " = \"{$set_config['guest_voting']}\";\n" .
	'$config[\'guest_comments\']' . " = \"{$set_config['guest_comments']}\";\n" .
	'$config[\'comments_show\']' . " = \"{$set_config['comments_show']}\";\n" .
	'$config[\'voting_show\']' . " = \"{$set_config['voting_show']}\";\n" .
	'$config[\'views_show\']' . " = \"{$set_config['views_show']}\";\n" .
	'$config[\'direct_path_show\']' . " = \"{$set_config['direct_path_show']}\";\n" .
	'$config[\'exif_show\']' . " = \"{$set_config['exif_show']}\";\n" .
	'$config[\'random_show\']' . " = \"{$set_config['random_show']}\";\n" .
	'$config[\'random_perpage\']' . " = \"{$set_config['random_perpage']}\";\n" .
	'$config[\'timezone_offset\']' . " = \"{$set_config['timezone_offset']}\";\n" .
	'$config[\'stylesheet\']' . " = \"{$set_config['stylesheet']}\";\n" .
	'$config[\'gallery_width\']' . " = \"{$set_config['gallery_width']}\";\n" .
	'$config[\'language\']' . " = \"{$set_config['language']}\";\n" .
	'$config[\'admin_email\']' . " = \"{$set_config['admin_email']}\";\n" .
	'$config[\'short_date_format\']' . " = \"{$set_config['short_date_format']}\";\n" .
	'$config[\'long_date_format\']' . " = \"{$set_config['long_date_format']}\";\n" .
	'$config[\'news_bar\']' . " = \"{$set_config['news_bar']}\";\n" .
	'$config[\'cookie_prefix\']' . " = \"{$set_config['cookie_prefix']}\";\n" .
	'$config[\'chmod_pics\']' . " = \"{$set_config['chmod_pics']}\";\n" .
	'$config[\'script_timeout\']' . " = \"{$set_config['script_timeout']}\";\n" .
	'$config[\'escape_chars\']' . " = {$set_config['escape_chars']};\n" .
	'$config[\'registration_captcha\']' . " = \"{$set_config['registration_captcha']}\";\n" .
	'$config[\'comments_captcha\']' . " = \"{$set_config['comments_captcha']}\";\n" .
	'$config[\'debug_mode\']' . " = \"{$set_config['debug_mode']}\";\n" .
	'$config[\'gallery_off\']' . " = \"{$set_config['gallery_off']}\";\n" .
	'$config[\'private\']' . " = \"{$set_config['private']}\";\n" .
	'$config[\'version\']' . " = \"{$set_config['version']}\";\n" .
	'$config[\'table_prefix\']' . " = \"{$set_config['table_prefix']}\";\n" .
	'$config[\'default_preview_pic_size\']' . " = \"{$set_config['default_preview_pic_size']}\";\n" .
	'$config[\'default_search_box_style\']' . " = \"{$set_config['default_search_box_style']}\";\n" .
	'$config[\'file_details_show\']' . " = \"{$set_config['file_details_show']}\";\n" .
	'$config[\'submission_details_show\']' . " = \"{$set_config['submission_details_show']}\";\n" .
	'$config[\'picture_details_show\']' . " = \"{$set_config['picture_details_show']}\";\n" .
	'$config[\'keywords_show\']' . " = \"{$set_config['keywords_show']}\";\n" .
	'$config[\'prevent_uploads_to_parents\']' . " = \"{$set_config['prevent_uploads_to_parents']}\";\n" .
	'$config[\'main_page_show\']' . " = \"{$set_config['main_page_show']}\";\n" .
	'$config[\'mod_queue_registered_users\']' . " = \"{$set_config['mod_queue_registered_users']}\";\n" .
	'$config[\'mod_queue_guests\']' . " = \"{$set_config['mod_queue_guests']}\";\n" .
	'$config[\'locale\']' . " = \"{$set_config['locale']}\";\n" .
	'$config[\'marking_allowed\']' . " = \"{$set_config['marking_allowed']}\";\n" .
	'$config[\'page_direction\']' . " = \"{$set_config['page_direction']}\";\n" .
	'$config[\'allowed_media_filetypes\']' . " = {$set_config['allowed_media_filetypes']};\n" .
	'$config[\'fixed_sized_thumbs\']' . " = {$set_config['fixed_sized_thumbs']};\n" .
	'$config[\'v094paths\']' . " = {$set_config['v094paths']};\n" .
	'$config[\'pending_pics_suffix\']' . " = \"{$set_config['pending_pics_suffix']}\";\n" .
	"?>";
   $status = 1;

   //the message below will appear in the URI.  Please keep it in English.
   $warning_msg = "Cannot open file ($filename).  Make sure it exists and is chmoded to 666 or 777.";
   if (is_writable($filename)) {
      if (!$handle = fopen($filename, 'w')) {
	     $status = "$warning_msg";
		 exit;
	  }
	  if (fwrite($handle, $content) === FALSE) {
         $status = "$warning_msg";
	     exit;
	  }
	  fclose($handle);   
   }
   else {
      $status = "$warning_msg";
   }
   
   return $status;
}

//--------------------------------------------
// splitFilenameAndExtensionMirror()
//--------------------------------------------
function splitFilenameAndExtensionMirror($filename) {		
	$str = strrev($filename);
	$bits = explode(".",$str);
	$extension = strrev($bits[0]);
	$name = substr($filename,0,strlen($filename)-strlen($extension)-1);
	
	$arrBits[0] = $name; $arrBits[1] = $extension;

	return $arrBits;
}

//--------------------------------------------
// showRadioButtonAdminStyle()
//--------------------------------------------
function showRadioButtonAdminStyle($name, $description, $config, $lang, $set_no_by_default=0) {		
	echo "<tr>
	<td align='left' class='white_cell' width='60%'>{$lang[$name]}";
	if($description != "") {
		echo "<br /><span class='tiny_text'>{$lang[$description]}</span>";
	} 	
	echo "</td>
	<td align='left' class='white_cell' width='40%'>";		
	if($config["$name"] == '0')   echo "<input type='radio' name='$name' value='0' checked='checked' />{$lang['no']}&nbsp;<input type='radio' name='$name' value='1' />{$lang['yes']}";
	elseif($config["$name"] == '1')   echo "<input type='radio' name='$name' value='0' />{$lang['no']}&nbsp;<input type='radio' name='$name' value='1' checked='checked' />{$lang['yes']}";
	else {
		if($set_no_by_default == 0)   echo "<input type='radio' name='$name' value='0' />{$lang['no']}&nbsp;<input type='radio' name='$name' value='1' checked='checked' />{$lang['yes']}";
		else   echo "<input type='radio' name='$name' value='0' checked='checked' />{$lang['no']}&nbsp;<input type='radio' name='$name' value='1' />{$lang['yes']}";
	}
	echo "</td></tr>";
}

//--------------------------------------------
// showDropdownBoxAdminStyle()
//--------------------------------------------
function showDropdownBoxAdminStyle($name, $description, $arr, $config, $lang) {		
	echo "<tr>
	<td align='left' class='white_cell' width='60%'>{$lang[$name]}";
	if($description != "") {
		echo "<br /><span class='tiny_text'>{$lang[$description]}</span>";
	}
	
	echo "</td>
	<td align='left' class='white_cell' width='40%'>
	<select name='$name' class='dropdownBox'>
	";
	foreach($arr as $key => $value) {
		echo "<option value='$key'";
		if($key == $config["$name"])
			echo " selected='selected'";
		echo ">$value";
	}
	echo "</select>
	</td></tr>";
}	

//--------------------------------------------
// showTextBoxAdminStyle()
// when $clear == -1, textbox shows nothing
//--------------------------------------------
function showTextBoxAdminStyle($label, $name, $type, $maxlength, $description, $config, $lang, $clear=0, $custom_attribs='') {		
	echo "<tr><td align='left' class='white_cell' width='60%'>{$lang[$label]}";
	if($description != "") {
		echo "<br /><span class='tiny_text'>{$lang[$description]}</span>";
	} 	
	echo "</td>
	<td class='white_cell' width='40%'><input value=";
	if($clear == -1)   echo "\"\"";
	else   echo "\"{$config[$name]}\"";
	echo "type='$type' name='$name' maxlength='$maxlength' class='spoiltTextBox' ".$custom_attribs."/></td>";
	echo "</tr>";
}

//--------------------------------------------
// showTextAreaAdminStyle()
// when $clear == -1, textarea shows nothing
//--------------------------------------------
function showTextAreaAdminStyle($label, $name, $cols, $rows, $description, $config, $lang, $clear=0, $custom_attribs='') {		
	echo "<tr><td align='left' class='white_cell' width='60%'>{$lang[$label]}";
	if($description != "")   echo "<br /><span class='tiny_text'>{$lang[$description]}</span>";
	echo "</td>
	<td class='white_cell' width='40%'><textarea name='$name' rows='$rows' cols='$cols' class='spoiltTextBox' ".$custom_attribs.">";
	if($clear != -1)   echo $config[$name];
	echo "</textarea></td>";
	echo "</tr>";
}


?>