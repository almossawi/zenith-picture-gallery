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
 
 File: f_misc.php
 Description: Contains misc. functions that other functions files might need
 Random quote: "The present is theirs, the future, for which I really
 worked, is mine." - Nikola Tesla 
*******************************************************************/

//--------------------------------------------
// bugFixRequirePath(): fixes the relative path bug
// courtesy: bmessenger at 3servicesolution dot com
//--------------------------------------------
if (!function_exists('bugFixRequirePath')) {
  function bugFixRequirePath($newPath) {
    $stringPath = dirname(__FILE__);

    if (strstr($stringPath,":")) $stringExplode = "\\";
    else $stringExplode = "/";

    $paths = explode($stringExplode,$stringPath);
    $newPaths = explode("/",$newPath);

    if (count($newPaths) > 0) {
      for($i=0;$i<count($newPaths);$i++) {
        if ($newPaths[$i] == "..") array_pop($paths);
      }

      for($i=0;$i<count($newPaths);$i++) {
        if ($newPaths[$i] == "..") unset($newPaths[$i]);
      }

      reset($newPaths);

      $stringNewPath = implode($stringExplode,$paths).
      $stringExplode.implode($stringExplode,$newPaths);

      return $stringNewPath;
    }
  } 
}

//--------------------------------------------
// msort(): Sorts on id
// credit: php.net/sort
//--------------------------------------------
function msort($array, $id="id") {
	$temp_array = array();
	while(count($array)>0) {
		$lowest_id = 0;
		$index=0;
		foreach ($array as $item) {
			if (isset($item[$id]) && $array[$lowest_id][$id]) {
				if ($item[$id]<$array[$lowest_id][$id]) {
					$lowest_id = $index;
				}
			}
			$index++;
		}
		$temp_array[] = $array[$lowest_id];
		$array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
	}
	return $temp_array;
}

?>