<?php

/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com
 
 Modifications:
 * Marcin Krol <hawk@limanowa.net>, jpeg quality enforced for medium 
   size previews and watermarking output; code type corrected 
   (imagepng() accepts only two parameters), v0.9.4 DEV

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: image_man.php
 Description: -
 Random quote: "Plato is boring." -Friedrich Nietzsche
 
 This file needs some serious optimization (January 3, 2006)
 Looks better now (May 12, 2006)
*******************************************************************/

class ImageManipulator {
	//----------------------
	//constructor
	//----------------------
	function ImageManipulator($config, $lang) {
		$this -> checkGD($config, $lang);
	}
	
	//----------------------
	//checkGD(): check for GD support
	//----------------------
	function checkGD($config, $lang) {
		if (!function_exists("gd_info"))   doBox("error", $lang['no_gd_msg'], $config, $lang);
	}
	
	//----------------------
	//decider(): Manage image depending on type
	//this isn't really needed anymore.  In my normal way of always delaying things, I'll delay
	//optimizing this further until the next release :)
	//----------------------
	function decider($image_type, $filename, $width, $height, $quality, $path, $path_server, $suffix, $mode, $config, $lang) {
		$image_type = strtolower($image_type);
		$this -> doIt($filename, $width, $height, $quality, $path, $path_server, $suffix, $mode, $config, $image_type);
	}
	
	//----------------------
	//doIt(): do stuff to the image
	//----------------------
	function doIt($filename, $width, $height, $quality, $path, $path_server, $suffix, $mode, $config, $type) {
		$msg = "1"; //initially, assume all is well
		
		$bits = $this -> splitFilenameAndExtensionMirror($filename);
		$thumb_filename = $bits[0] . $suffix . "." . $bits[1]; //name.suffix.extension
		
		$path_thumb = $path_server . $thumb_filename;
		$path_server .= $filename . $awaiting_approval_suffix;
			
		$size = @getimagesize($path_server); //get width/height  (path support from PHP 4.05)
		
		//add watermark to original (new in v0.9 DEV)
		$image = $this -> addWatermark($image, $path_server, $type, $quality);
		
		//then create thumbnail as normal
		if($type == "jpg" || $type == "jpeg")   $image = @imagecreatefromjpeg($path_server);
		elseif($type == "png")   $image = @imagecreatefrompng($path_server);
		elseif($type == "gif")   $image = @imagecreatefromgif($path_server);
		
		//resize only if bigger than than the allowed thumbnail size
		if($size[0]>$width || $size[1]>$height) {
			if ($size[0]>=$size[1]) { //if width is greater than height or equal
				$sizemin[0]=$width;
				//set height relative to picture's height
				$relative_ratio = $width / $size[0];
				$sizemin[1]=$relative_ratio * $size[1];
			}
			if ($size[1]>$size[0]) { //if height is greater than width
				$sizemin[1]=$height;
				//set width relative to picture's width
				$relative_ratio = $height / $size[1];
				$sizemin[0]=$relative_ratio * $size[0];
			}
		}
		else {
			$sizemin[0]=$size[0];
			$sizemin[1]=$size[1];
		}
		
		if($config['fixed_sized_thumbs'] == 1) {
			//start of fixed-size-background-for-thumbs mod (April 16, 2007)
			$thumbnail = imagecreatetruecolor($config['thumb_width'], $config['thumb_height']);
			imagefill($thumbnail, 0, 0, 0x000000); //change 3rd parameter if you want (e.g. 0xFFFFFF for white)
			@imagecopyresampled($thumbnail, $image, ($config['thumb_width']-$sizemin[0])/2, ($config['thumb_height']-$sizemin[1])/2, 0, 0, $sizemin[0], $sizemin[1], $size[0], $size[1]); //resize and resample image
			//end of fixed-size..lalalala...thumbs mod
		}
		else { //new in v0.9.4
			$thumbnail = imagecreatetruecolor($sizemin[0],$sizemin[1]);
			@imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $sizemin[0], $sizemin[1], $size[0], $size[1]); //resize and resample image
		}
				
		//chmod original file to 0777 temporarily, then return to 0644
		if(!@chmod($path_server,0777))
        	if($config['debug_mode'] == 1)   echo "Cannot change the mode of file ($path_server)";

		@imagedestroy($image); //free memory
		
		// try to save thumbnail image
		if($type == "jpg" || $type == "jpeg") {
			if (!@imagejpeg($thumbnail, $path_thumb, $quality))   $msg .= $lang['image_man_check'];
		}
		elseif($type == "png") {
			if (!@imagepng($thumbnail, $path_thumb))   $msg .= $lang['image_man_check'];
		}
		elseif($type == "gif") {
			if (!@imagegif($thumbnail, $path_thumb))   $msg .= $lang['image_man_check'];
		}
		
		if(!@chmod($path_server, 0644))
        	if($config['debug_mode'] == 1)   echo "Cannot change the mode of file ($path_server)";
			
		return $msg;
	}
	
	//----------------------
	//addWatermark(): adds a watermark to an uploaded image
	//(new in v0.9)
	//watermark.jpg must be saved in PNG-8
	//----------------------
	function addWatermark($image, $path, $type) {
		//if watermark.jpg doesn't exist in the root, just skip this step
		if(!@file_exists('watermark.png'))   return $image;
	
		$watermark = imagecreatefrompng('watermark.png');  
		$watermark_width = imagesx($watermark);  
		$watermark_height = imagesy($watermark);  
		$image = imagecreatetruecolor($watermark_width, $watermark_height);  
		
		if($type == "jpg" || $type == "jpeg")   $image = imagecreatefromjpeg($path);
		elseif($type == "png")   $image = imagecreatefrompng($path);
		elseif($type == "gif")   $image = imagecreatefromgif($path);
		
		$size = getimagesize($path);  
		$dest_x = $size[0] - $watermark_width - 5;  
		$dest_y = $size[1] - $watermark_height - 5;  
		imagecopymerge($image, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);  
		
		if($type == "jpg" || $type == "jpeg")   imagejpeg($image, $path, $quality);
		elseif($type == "png")   imagepng($image, $path);
		elseif($type == "gif")   imagegif($image, $path);
		
		imagedestroy($image);  
		imagedestroy($watermark);
		
		return $image;
	}

	//----------------------
	//doItToBrowser()
	//----------------------
	function doItToBrowser($pic, $path, $width, $height, $quality, $smart_resize, $crop="0", $pending_pics_suffix="") {
		//strip extension from value
		//$bits = explode(".", substr($pic,-6,6)); $extension = $bits[1]; //get the extension
		$bits = $this -> splitFilenameAndExtensionMirror($pic);
		$type = $bits[1];

		//if logged in as admin and suffixing for pending pics has been enabled
		//added in v0.9.4
		if(file_exists($path.".$pending_pics_suffix"))   $path .= ".$pending_pics_suffix";

		//first create a blank image
		$size = @getimagesize($path) or die("Couldn't read image, make sure it exists"); //get width/height  (path support from PHP 4.05)
		
		if(strcasecmp($type, "jpeg") == 0 || strcasecmp($type, "jpg") == 0)
			$image = imagecreatefromjpeg($path); //attempt to open original image
		elseif(strcasecmp($type, "gif") == 0)
			$image = imagecreatefromgif($path); //attempt to open original image
		elseif(strcasecmp($type, "png") == 0)
			$image = imagecreatefrompng($path); //attempt to open original image
		//else //we probably have a media type (e.g. video or audio file), new in v0.9.2 DEV
			//$image = getSkinElementMirror($config['stylesheet'], "images/video.jpg");
		
		if($smart_resize == 1) {
			//resize only if bigger than than the allowed thumbnail size
			if ($size[0]>=$size[1]) { //if width is greater than height or equal
					$sizemin[0]=$width;
					//set height relative to picture's height
					$relative_ratio = $width / $size[0];
					$sizemin[1]=$relative_ratio * $size[1];
			}
			if ($size[1]>$size[0]) { //if height is greater than width
					$sizemin[1]=$height;
					//set width relative to picture's width
					$relative_ratio = $height / $size[1];
					$sizemin[0]=$relative_ratio * $size[0];
			}
		}
		else {
			$sizemin[0]=$width;
			$sizemin[1]=$height;
		}

		$thumbnail = imagecreatetruecolor($sizemin[0],$sizemin[1]);
		
		//new in v0.8.8 DEV
		if($crop == "1")   imagecopyresampled($thumbnail, $image, 0, 0, $size[0]/2, $size[1]/2, $sizemin[0], $sizemin[1], $sizemin[0], $sizemin[1]); //resize and resample image
		else   imagecopyresampled($thumbnail, $image, 0, 0, 0, 0, $sizemin[0], $sizemin[1], $size[0], $size[1]); //resize and resample image
		
		header("Content-type: image/jpeg");		
		imagejpeg($thumbnail, "", $quality);
		imagedestroy($thumbnail);
	}

	//----------------------
	//splitFilenameAndExtensionMirror()
	//----------------------
	function splitFilenameAndExtensionMirror($filename) {
		$str = strrev($filename);
		$bits = explode(".",$str);
		$extension = strrev($bits[0]);
		$name = substr($filename,0,strlen($filename)-strlen($extension)-1);
		$arrBits[0] = $name; $arrBits[1] = $extension;

		return $arrBits;
	}
	
}//end class

?>