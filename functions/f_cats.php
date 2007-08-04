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
 
 File: f_cats.php
 Description: Contains functions that manipulate or display categories
 Last updated: July 8, 2007
 Random quote: "Cash is more important than your mother." -Steve Jobs  
*******************************************************************/

require_once("f_misc.php");

//--------------------------------------------
// getCats() if $wo is 1, only valid cats will be displayed, if 2, first will be blank, if 0, first will be 'Select a Category'
//--------------------------------------------
function getCats($wo, $config, $lang) {
   //first one is empty
   if($wo != 1)   $cat[0] = "";
	$statement = "SELECT cid, cname FROM {$config['table_prefix']}categories WHERE cshow=1";
	$result = mysql_query($statement);
	while($row = mysql_fetch_array($result)) {
	   if($row['cname'] == 'Other')   continue;  //skip Other category  
	   $cat[$row['cid']] = $row['cname'];
	}
	
	//sort
	if($wo == 0) {
		natcasesort($cat); 
		$cat[] = 'Other';
		$cat[0] = $lang['select_a_category'];
	}
	elseif($wo == 2) {
		natcasesort($cat); 
		$cat[] = 'Other';
	}
	
	return $cat;
}

//--------------------------------------------
// justGetTheDamnCats(): gets all cats
//--------------------------------------------
function justGetTheDamnCats($sort, $evenHidden, $config, $lang) {
	if($evenHidden == 1)   $statement = "SELECT cid, cname, position FROM {$config['table_prefix']}categories";
	else   $statement = "SELECT cid, cname, position FROM {$config['table_prefix']}categories WHERE cshow=1";
	$result = mysql_query($statement);
	
	$cat = array();
	while($row = mysql_fetch_array($result)) {
		$cid = $row['cid'];
		$cname = $row['cname'];
		$position = $row['position']; //v0.9.4
	
		//if($cname == 'Other')   $other_id = $cid;
		//else   $cat[$cid] = $cname;
		
		$cat[$cid] = array("cname" => $cname, "position" => $position);
	}
	//if($sort == 1)   natcasesort($cat);  //asort() added in v0.8
	if($sort == 1)   msort($cat, "cname"); //v0.9.4

	return $cat;
}

//--------------------------------------------
// getCatsTreeView() (see v0.8 notes for comments and trace)
//--------------------------------------------
function getCatsTreeView($with_pics, $with_hidden, $config, $lang) {
	//if($with_hidden == 1)   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories ORDER BY cname";
	//else   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories WHERE cshow=1 ORDER BY cname";
	
	if($with_hidden == 1)   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories ORDER BY parent,position,cname ASC";
	else   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories WHERE cshow=1 ORDER BY parent,position,cname ASC";
	
	$result = mysql_query($statement);
	
	$parent = array();
	$cats = array();
	$cats_parents = array();
	
	while($row = mysql_fetch_array($result)) {
		if($row['parent'] == -1) {
			$e = $row['cid'];
			if(!isset($parent[$e]))   $parent[$e] = array();
		}
		else {
			$parent_cat = $row['parent'];
			$parent[$parent_cat][] = $row['cid'];
		}

		$cats[$row['cid']] = $row['cname'];
		$cats_parents[$row['cid']] = $row['parent'];
	}
	
	$sorted_parent = array();
	foreach($parent as $key => $value) {
		if($cats_parents[$key] == -1) {
			$sorted_parent[$key] = $parent[$key];
		}
	}

	foreach($parent as $key => $value) {
		if($cats_parents[$key] != -1) {
			$sorted_parent[$key] = $parent[$key];
		}
	}
	$parent = $sorted_parent;

	$catsTreeView = array();
	$catsTreeView[] = '';

	$updated_parent = $parent;
	foreach($parent as $key_parent => $value) {
		if(array_key_exists($key_parent,$updated_parent)) {
			//echo "updated parent: ";print_r($updated_parent); echo "<br />";
			//echo "PARENT: $key_parent <br />"; //output the parent (without indenting)
			
			if ($with_pics == 1)   $catsTreeView[$key_parent] = "<img src='" . getSkinElement($config['stylesheet'], "images/folder.gif") . "' border='0' alt='' /> ".$cats[$key_parent];
			else {
				//if($config['prevent_uploads_to_parents'])   $catsTreeView[$key_parent] = ".".$cats[$key_parent]; //prefix added in v0.9.4
				//else   $catsTreeView[$key_parent] = $cats[$key_parent];
				$catsTreeView[$key_parent] = $cats[$key_parent];
			}
			$indent = "&nbsp;&nbsp;&nbsp;";
			$updated_parent = outputItsChildren($key_parent, $updated_parent, $indent, $with_pics, $config['stylesheet'], $cats, $catsTreeView);
		}
	}
	
	//print_r($catsTreeView);
	return $catsTreeView;
}

//--------------------------------------------
// outputItsChildren()
// @key_parent: integer
// @parent: array
//--------------------------------------------
function outputItsChildren($key_parent, $parent, $indent, $with_pics, $stylesheet_path, $cats, &$catsTreeView) {
	foreach($parent[$key_parent] as $key_child => $value_child) {
		if ($with_pics == 1)   $indentToShow = $indent."<img src='$stylesheet_path/images/folder.gif' alt='' border='0' /> ";
		else   $indentToShow = $indent;
		if(isset($parent[$value_child])) {
			$catsTreeView[$value_child] = $indentToShow.$cats[$value_child];
			$indent .= '&nbsp;&nbsp;&nbsp;';
			$parent = outputItsChildren($value_child, $parent, $indent, $with_pics, $stylesheet_path, $cats, $catsTreeView);
			
			$indent = substr($indent,0,strlen($indent)-(3*6));
			unset($parent[$value_child]);
		}
		else {
			$catsTreeView[$value_child] = $indentToShow.$cats[$value_child];
		}
	}

	return $parent;
}




//--------------------------------------------
// getCatsTreeView() (see v0.8 notes for comments and trace)
//--------------------------------------------
function getCatsTreeView2($with_pics, $with_hidden, $config, $lang) {
	//if($with_hidden == 1)   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories ORDER BY cname";
	//else   $statement = "SELECT cid, cname, parent, cshow FROM {$config['table_prefix']}categories WHERE cshow=1 ORDER BY cname";
	
	if($with_hidden == 1)   $statement = "SELECT cid, cname, parent, position, cshow FROM {$config['table_prefix']}categories ORDER BY parent,position,cname ASC";
	else   $statement = "SELECT cid, cname, parent, position, cshow FROM {$config['table_prefix']}categories WHERE cshow=1 ORDER BY parent,position,cname ASC";
	
	$result = mysql_query($statement);
	
	$parent = array();
	$cats = array();
	$cats_parents = array();
	
	while($row = mysql_fetch_array($result)) {
		if($row['parent'] == -1) {
			$e = $row['cid'];
			if(!isset($parent[$e]))   $parent[$e] = array();
		}
		else {
			$parent_cat = $row['parent'];
			$parent[$parent_cat][] = $row['cid'];
		}

		$cats[$row['cid']] = array("cat_name" => $row['cname'], "cat_position" => $row['position']); //v0.9.4
		$cats_parents[$row['cid']] = $row['parent'];
	}
	
	$sorted_parent = array();
	foreach($parent as $key => $value) {
		if($cats_parents[$key] == -1) {
			$sorted_parent[$key] = $parent[$key];
		}
	}

	foreach($parent as $key => $value) {
		if($cats_parents[$key] != -1) {
			$sorted_parent[$key] = $parent[$key];
		}
	}
	$parent = $sorted_parent;

	$catsTreeView = array();
	$catsTreeView[] = '';

	$updated_parent = $parent;
	foreach($parent as $key_parent => $value) {	
		if(array_key_exists($key_parent,$updated_parent)) {
			//echo "updated parent: ";print_r($updated_parent); echo "<br />";
			//echo "PARENT: $key_parent <br />"; //output the parent (without indenting)
			
			if ($with_pics == 1)   $catsTreeView[$key_parent] = "<img src='" . getSkinElement($config['stylesheet'], "images/folder.gif") . "' border='0' alt='' /> ".$cats[$key_parent]['cat_name']; //v0.9.4
			else {
				//if($config['prevent_uploads_to_parents'])   $catsTreeView[$key_parent] = ".".$cats[$key_parent]; //prefix added in v0.9.4
				//else   $catsTreeView[$key_parent] = $cats[$key_parent];
				$catsTreeView[$key_parent] = $cats[$key_parent]['cat_name']; //v0.9.4
			}
			$indent = "&nbsp;&nbsp;&nbsp;";
			$updated_parent = outputItsChildren2($key_parent, $updated_parent, $indent, $with_pics, $config['stylesheet'], $cats, $catsTreeView);
		}
	}
	
	//print_r($catsTreeView);
	return $catsTreeView;
}

//--------------------------------------------
// outputItsChildren()
// @key_parent: integer
// @parent: array
//--------------------------------------------
function outputItsChildren2($key_parent, $parent, $indent, $with_pics, $stylesheet_path, $cats, &$catsTreeView) {
	foreach($parent[$key_parent] as $key_child => $value_child) {
		if ($with_pics == 1)   $indentToShow = $indent."<img src='$stylesheet_path/images/folder.gif' alt='' border='0' /> ";
		else   $indentToShow = $indent;
		if(isset($parent[$value_child])) {
			$catsTreeView[$value_child] = $indentToShow.$cats[$value_child]['cat_name'];
			$indent .= '&nbsp;&nbsp;&nbsp;';
			$parent = outputItsChildren2($value_child, $parent, $indent, $with_pics, $stylesheet_path, $cats, $catsTreeView);
			
			$indent = substr($indent,0,strlen($indent)-(3*6));
			unset($parent[$value_child]);
		}
		else {
			$catsTreeView[$value_child] = $indentToShow.$cats[$value_child]['cat_name'];
		}
	}

	return $parent;
}





//--------------------------------------------
// getParents(): Returns all the parents of a given category ID
// modified in v0.9.4 to return cname_for_url as a third arg (June 13, 2007)
//--------------------------------------------
function getParents($config, $cid, $is_what = "is_not_rebuilding_directories") {
	//make sure the cat isn't missing its parent for some reason (in the db)
	assertActivate();
	assert($cid != "");
	
	$statement = "SELECT parent FROM {$config['table_prefix']}categories WHERE cid=$cid";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	$parent_id = $row['parent'];
	
	//make sure the cat isn't missing its parent for some reason (in the db)
	//I'm currently unable to reproduce it, but in certain cases, a cat in the db
	//might have a parent of null or one that's non-existent.  Change to -1 to fix
	//this problem.
	assertActivate();
	assert($parent_id != "");
	
	//echo "<br /> {$row['parent']}";
	if($parent_id != '-1') {
		$statement = "SELECT cname, cname_for_url FROM {$config['table_prefix']}categories WHERE cid=$parent_id";
		$result = mysql_query($statement);
		$row = mysql_fetch_array($result);
		//$parent_name = ($config['v094paths'] == 0) ? $row['cname'] : $row['cname_for_url']; //v0.9.4
		
		if($is_what == "rebuilding_directories") {
			if($config['v094paths'] == 1)   $parent_name = $row['cname'];
			else   $parent_name = $row['cname_for_url']; //v0.9.4
		}
		else {
			if($config['v094paths'] == 1)   $parent_name = $row['cname_for_url'];
			else   $parent_name = $row['cname']; //v0.9.4
		}
		
		//$parent_name_for_url = $row['cname_for_url'];
	    $arr = getParents($config,$parent_id, $is_what);
	}
	
	//echo $parent_id."<br />";
	$arr[] = array($parent_id, $parent_name); 
	
	return $arr;
}

//--------------------------------------------
// getHierarchyOfCategories()
// @added in response to user request (September 19, 2005)
//--------------------------------------------
function getHierarchyOfCategories($config, $cid) {
	$statement = "SELECT cname FROM {$config['table_prefix']}categories WHERE cid='".$cid."'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	$cname = $row['cname'];
	$arr = getParents($config, $cid);
	
	//now buld the picture's path
	foreach ($arr as $key => $value) {
		if($key == 0)   continue;
		
		$pics_in_cat = getNumberOfPicturesInCategory($config, $value[0]); //new in v0.9 DEV
		$path .= "<a href='display.php?t=bycat&amp;q=".$value[0]."&amp;nr=".$pics_in_cat."&amp;st=0&amp;upto=".$config['perpage']."&amp;p=1'><span style='color:#fff'>" . $value[1] . "</span></a> > ";
	}
	
	$pics_in_cat = getNumberOfPicturesInCategory($config, $cid); //new in v0.9 DEV
	$path .= "<a href='display.php?t=bycat&amp;q=".$cid."&amp;nr=".$pics_in_cat."&amp;st=0&amp;upto=".$config['perpage']."&amp;p=1'><span style='color:#fff'>" . $cname . "</span></a>";
	
	return $path;
}

//--------------------------------------------
// getNumberOfPicturesInCategory()
// (new in v0.9)
//--------------------------------------------
function getNumberOfPicturesInCategory($config, $cid) {
	$statement = "SELECT COUNT(pid) as n FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE {$config['table_prefix']}pictures.category={$config['table_prefix']}categories.cid AND category='$cid' AND {$config['table_prefix']}pictures.approved='1'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);

	return $row['n'];
}

//--------------------------------------------
// moveFiles()
// @what_should_i_do_with_directories: 0 make them children to the root cat
//									   1 just move them over normally
//--------------------------------------------
function moveFiles($config, $cid_from, $cid_to, $what_should_i_do_with_directories) {
	$path_from = getPicturesPathFromCategory($config, $cid_from, 1, "");
	$path_to = getPicturesPathFromCategory($config, $cid_to, 1, "");
	$path_root = $config['upload_dir_server'];
	
	$dir = $path_from;
	if($dh = opendir($dir)) {
		// iterate over file list
		while (false !== ($filename = readdir($dh))) {
			if (($filename != ".") && ($filename != "..") && !is_dir($filename)) {
				$filelist[$filename] = $path_from . $filename;
			}
		}
		
		// close directory
		closedir($dh);
	}
	
	//move all pics if there are any
	if(sizeof($filelist) > 0) {
		foreach($filelist as $key => $value) {
			if($what_should_i_do_with_directories == '0' && is_dir($value)) { //if it's a directory, make it a parent and move it to /uploads
				$statement = "UPDATE {$config['table_prefix']}categories SET parent='-1' WHERE cname='".$key."'";
				$result = mysql_query($statement) or die(mysql_error());
				rename($value, $path_root . $key);
			}
			//elseif($what_should_i_do_with_directories == '1' && is_dir($value)) {
			//}
			else {
				rename($value, $path_to . $key);
			}
		}
	}
}

//--------------------------------------------
// showJumpToCatForm(): Displays a form that allows the contents of a category to be displayed
//--------------------------------------------
function showJumpToCatForm($config, $lang) {
	if(!isset($cats))   $cats = getCatsTreeView(0,0,$config,$lang);
	echo "<form name='frmCatOnlySearch' enctype='multipart/form-data' action='display.php' method='post' style='margin:0;padding:0'>
	<table class='table_layout_main' style='width:{$config['gallery_width']}' cellpadding='3' cellspacing='2'>
	<tr><td valign='middle' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
	<select name='byCat' class='dropdownBox' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">";
	foreach($cats as $key => $value) {
		if($key == 0) {
			$key  = -1;
			echo "<option value='$key'>{$lang['select_a_category']}";
		}
		//else { echo "<option value='$key'>$value"; }
		//echo "</option>";
		
		else {
			//if($value[0] == ".") //if it's a parent category (v0.9.4)
				//echo "<optgroup value='$key' label='".substr($value,1)."'>$value";
			//else
				echo "<option value='$key'>$value";
		}
		
		if($value[0] == ".")   echo "</optgroup>"; //if it's a parent category
		else   echo "</option>";
	}
	echo "</select>&nbsp;<input type='submit' name='submit' value='{$lang['button_go']}' class='submitButtonTiny' />
	</td></tr>
	</table></form>";
}

//--------------------------------------------
// catHasChildren(): Checks whether a category has any children
//--------------------------------------------
function catHasChildren($cat, $config) {
	$statement = "SELECT COUNT(cid) as n FROM {$config['table_prefix']}categories WHERE parent='".$cat."'";
	$result = mysql_query($statement);
	$row = mysql_fetch_array($result);
	
	return $row['n'];
}

//--------------------------------------------
// getChildren(): Returns a category's children (new in v0.8.8 DEV, modified substantially in v0.9.2 DEV)
// format E {sql_in, set, array}
// currently, only "sql_in" is implemented
// currently, deep is ignored
//--------------------------------------------
function getChildren($cat, $config, $format, $include_parent_in_collection, $deep, $cats="") {
	if($format == "sql_in") {
		$arr_cats = __getChildren($cat, $config, $format, $include_parent_in_collection, $deep, $cats);
		$str_cats = "(";
		foreach ($arr_cats as $key => $value) {
			//because I'm now using an array in __getChildren(), convert it to a string and then return it
			$str_cats .= $key . ",";
		}
		$str_cats = substr($str_cats,0,strlen($str_cats)-1) . ")";
	}
		
	//echo "cats string is $str_cats";
	//echo "cats array is " . print_r($arr_cats);
	return $str_cats;
}

//--------------------------------------------
// __getChildren(): Returns a category's children (new in v0.9.2 DEV)
// recursive function, only called by getChildren(), never directly by user
//--------------------------------------------
function __getChildren($cat, $config, $format, $include_parent_in_collection, $deep, $cats="") {
	//create a set of categories
	if($format == "sql_in") { //returns aaa,bbb,ccc,...
		$statement = "SELECT cid FROM {$config['table_prefix']}categories WHERE parent='".$cat."'";
		$result = mysql_query($statement);
		$kids = array();
		
		//populate $kids
		while($row = mysql_fetch_array($result)) {
			$kids[] = $row['cid'];
		}

		if(sizeof($kids) > 0) {
			foreach($kids as $key => $cid) {
				//using an array now instead of a string to avoid "out of memory" error (nov. 5, 2006)
				$cats = __getChildren($cid, $config, $format, $include_parent_in_collection, $deep, $cats); //recurse
			}
		}
		
		//$cats .= "$cat,";
		$cats["$cat"] = $cat;
		
		//are we including the parent in the collection?
		//if($include_parent_in_collection == "1")   $cats .= "$cat,";
	}	
	
	//echo "--about to return ($cat); cats string is $cats";
	return $cats;
}

?>