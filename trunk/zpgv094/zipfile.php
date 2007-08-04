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

 This class is a modified version of the one found at:
 http://www.zend.com/zend/spotlight/creating-zip-files3.php and
 constructs a ZIP file using its complex structure and does the
 necessary checksums on it.
 
 File: zipfile.php
 Description: Creates a ZIP file of an entire category or selected pictures.
 Random quote: "What you cannot enforce, do not command." -Socrates
*******************************************************************/

class zipfile 
{ 
	var $datasec = array(); 
	var $ctrl_dir = array(); 
	var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00"; 
	var $old_offset = 0; 
	
	function add_dir($name) { 
		$name = str_replace("\\", "/", $name); 
		
		$fr = "\x50\x4b\x03\x04"; 
		$fr .= "\x0a\x00"; 
		$fr .= "\x00\x00"; 
		$fr .= "\x00\x00"; 
		$fr .= "\x00\x00\x00\x00"; 
		
		$fr .= @pack("V",0); 
		$fr .= @pack("V",0); 
		$fr .= @pack("V",0); 
		$fr .= @pack("v", strlen($name) ); 
		$fr .= @pack("v", 0 ); 
		$fr .= $name; 
		$fr .= @pack("V", 0); 
		$fr .= @pack("V", 0); 
		$fr .= @pack("V", 0); 
		
		$this -> datasec[] = $fr;
		$new_offset = strlen(implode("", $this->datasec)); 
		
		$cdrec = "\x50\x4b\x01\x02"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x0a\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x00\x00\x00\x00"; 
		$cdrec .= @pack("V",0); 
		$cdrec .= @pack("V",0); 
		$cdrec .= @pack("V",0); 
		$cdrec .= @pack("v", strlen($name) ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$ext = "\x00\x00\x10\x00"; 
		$ext = "\xff\xff\xff\xff"; 
		$cdrec .= @pack("V", 16 ); 
		$cdrec .= @pack("V", $this -> old_offset ); 
		$cdrec .= $name; 
		
		$this -> ctrl_dir[] = $cdrec; 
		$this -> old_offset = $new_offset; 
		return; 
	} 
	
	function add_file($data, $name) { 
		$fp = fopen($data,"r") or die();
		$data = fread($fp,filesize($data));
		fclose($fp);
		$name = str_replace("\\", "/", $name); 
		$unc_len = strlen($data); 
		$crc = crc32($data); 
		$zdata = @gzcompress($data); 
		$zdata = substr ($zdata, 2, -4); 
		$c_len = strlen($zdata); 
		$fr = "\x50\x4b\x03\x04"; 
		$fr .= "\x14\x00"; 
		$fr .= "\x00\x00"; 
		$fr .= "\x08\x00"; 
		$fr .= "\x00\x00\x00\x00"; 
		$fr .= @pack("V",$crc); 
		$fr .= @pack("V",$c_len); 
		$fr .= @pack("V",$unc_len); 
		$fr .= @pack("v", strlen($name) ); 
		$fr .= @pack("v", 0 ); 
		$fr .= $name; 
		$fr .= $zdata; 
		$fr .= @pack("V",$crc); 
		$fr .= @pack("V",$c_len); 
		$fr .= @pack("V",$unc_len); 
		
		$this -> datasec[] = $fr; 
		$new_offset = strlen(implode("", $this->datasec)); 
		
		$cdrec = "\x50\x4b\x01\x02"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x14\x00"; 
		$cdrec .="\x00\x00"; 
		$cdrec .="\x08\x00"; 
		$cdrec .="\x00\x00\x00\x00"; 
		$cdrec .= @pack("V",$crc); 
		$cdrec .= @pack("V",$c_len); 
		$cdrec .= @pack("V",$unc_len); 
		$cdrec .= @pack("v", strlen($name) ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("v", 0 ); 
		$cdrec .= @pack("V", 32 ); 
		$cdrec .= @pack("V", $this -> old_offset ); 
		
		$this -> old_offset = $new_offset; 
		
		$cdrec .= $name; 
		$this -> ctrl_dir[] = $cdrec; 
	} 
	
	function file() { 
		$data = implode("", $this -> datasec); 
		$ctrldir = implode("", $this -> ctrl_dir); 
		
		return 
			$data . 
			$ctrldir . 
			$this -> eof_ctrl_dir . 
			@pack("v", sizeof($this -> ctrl_dir)) . 
			@pack("v", sizeof($this -> ctrl_dir)) . 
			@pack("V", strlen($ctrldir)) . 
			@pack("V", strlen($data)) . 
			"\x00\x00"; 
	} 
}

//--------------------------------------------
// zipCategory()
//--------------------------------------------
function zipCategory($cid, $category, $config) {
   $path_outgoing = "outgoing/";

   $zipFile = new zipfile(); //instantiate it
   require_once('functions/f_global.php');
   require_once('functions/f_cats.php');
   $path = getPicturesPathFromCategory($config, $cid, 1, "");
   
   $statement = "SELECT file_name FROM {$config['table_prefix']}pictures WHERE category='$cid' AND approved=1";
   $result = mysql_query($statement);
   while ($row = mysql_fetch_array($result)) {
      if(file_exists("$path"."{$row['file_name']}"))   $zipFile->add_file("$path"."{$row['file_name']}", "{$row['file_name']}");
   }

   $filename = "$path_outgoing$category".".zip";
   $fd = fopen ($filename, "wb") or die();
   $out = fwrite ($fd, $zipFile -> file());
   fclose ($fd);
   
   header("Location: $filename");
   exit();

   return $category.".zip";
}

//--------------------------------------------
// zipMarkedPictures()
//--------------------------------------------
function zipMarkedPictures($paths, $config) {
   $path_outgoing = "outgoing/";

   $zipFile = new zipfile(); //instantiate it
   require_once('functions/f_global.php');
   require_once('functions/f_cats.php');
   
   foreach($paths as $key => $value) {
      if(file_exists($value))	$zipFile->add_file($value, basename($value));
   }

   $filename = "$path_outgoing". time() .".zip";
   $fd = fopen ($filename, "wb") or die();
   $out = fwrite ($fd, $zipFile -> file());
   fclose ($fd);
   
   header("Location: $filename");
   exit();

   return $category.".zip";
}

require('functions/f_db.php');
require_once('functions/f_global.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

//are we zipping marked pictures?
if(isset($_GET['m']) && $_GET['m'] == 1) {
	//print_r($_GET['paths']);
	$arr = set2array($_GET['paths']);
	$zip = zipMarkedPictures($arr, $config);
}
//or are we zipping an entire category?
elseif(isset($_GET['c']) && is_numeric($_GET['c'])) {
	//cid -> cname
	$statement = "SELECT cname FROM {$config['table_prefix']}categories WHERE cid={$_GET['c']}";
	$result = mysql_query($statement); 
	$row = mysql_fetch_array($result);
	$cname = $row['cname'];
	
	$row = mysql_fetch_array(mysql_query("SELECT downloadable FROM {$config['table_prefix']}categories WHERE cname='{$row['cname']}'"));
	if($config['private'] == 1) {
		if($row[0] == 1 && loggedIn($config))   $zip = zipCategory($_GET['c'], $cname, $config);
		else   doBox("error", $lang['not_authorized_msg'], $config, $lang);
	}
	else {
		if($row[0] == 1)   $zip = zipCategory($_GET['c'], $cname, $config);
		else   doBox("error", $lang['not_authorized_msg'], $config, $lang);
	}
}

?>

