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
 
 File: search.php
 Description: -
 Created: Who knows
 Last updated: July 8, 2007
 Random quote: "He who has a thousand friends has not a friend to spare,
 he who has one enemy will meet him everywhere."  
*******************************************************************/

session_start();
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');
require_once('functions/f_search.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);
 
//head_aware is used to add code to the head of a page
$head_aware['onload'] = "document.frmSearch.searchterm.focus();";
if($config['default_search_box_style'] == "1") {
	$path_skin = getSkinElement($config['stylesheet'], "images");
	$head_aware['onload'] .= "hidify_showify('search_options','search_arrow','$path_skin', '{$lang['less_options']}', '{$lang['advanced_options']}');";
}

require_once('head.php');

?>
<script language="javascript" type="text/javascript">
//coming_from_where E {1="form_submit", 2="get"}
function processSearch(coming_from_where) {
	//get variables
    var searchtype = "";
	
	var searchquery = encodeURIComponent(document.getElementById("q").value);
	var searchauthor = encodeURIComponent(document.getElementById("a").value);
	//hat tip to dangrossman.info/2007/05/25/handling-utf-8-in-javascript-php-and-non-utf8-databases
	
	//var st = 0; //if we're pressing the search button then this will always be 0
	//var p = 1; //if we're pressing the search button then this will always be 1
	
	var st = document.getElementById("st").value;
	var p = document.getElementById("p").value;
	
	var strength = document.getElementById("strength").value;
	if(isNaN(strength))   strength = 0;
	
	var sort_results_by = document.getElementById("by").value;
	if(isNaN(sort_results_by))   sort_results_by = 0;
	
	var sort_results_order = document.getElementById("order").value;
	if(isNaN(sort_results_order))   sort_results_order = 0;
	
	//cat needs some extra work, it needs to be converted to a set and then passed to the url
	var cat = document.getElementById("cat_dropdown");
	var cat_as_set;

	//after we're done with the loop below, go back and select the deselected options via their ids, which stores
	//their order in the select box
	var selected_options = new Array();
	var i = 0;
	
	cat_as_set = "{";
	while (cat.selectedIndex != -1) {
		if (cat.selectedIndex != 0) {
			cat_as_set += cat.options[cat.selectedIndex].value + ",";
		}
		//alert("setting " + cat.options[cat.selectedIndex].id + " to false");
		selected_options[i] = cat.options[cat.selectedIndex].id; //in order to restore the selection later
		cat.options[cat.selectedIndex].selected = false;
		
		i++;
	}
	if(cat_as_set.charAt(cat_as_set.length-1) == ',') //get rid of the last comma
		cat_as_set = cat_as_set.substring(0, cat_as_set.length-1)
	cat_as_set += "}";
	
	for(i = 0; i < selected_options.length; i++) {
		cat.options[selected_options[i]].selected = true;
		//alert("setting " + selected_options[i] + " to true");
	}
	
	var url = "search_ajax.php?t=" + searchtype + "&q="+ searchquery + "&a=" + searchauthor + "&strength=" + strength + "&cat=" + cat_as_set + 
	"&by=" + sort_results_by + "&order=" + sort_results_order;
	
	//if this is a new search query, ignore the bounds stored in our address
	if(coming_from_where == "1")   url += "&st=" + st + "&p=" + p;
	
	return url;
}
</script>
<?php

//output search box
echo "<form name='frmSearch' enctype='multipart/form-data' method='post' style='margin:0;padding:0'>
<input id='p' type='hidden' name='p' value='{$_GET['p']}' />
<input id='st' type='hidden' name='st' value='{$_GET['st']}' />
<table class='table_layout_sections' style='width:{$config['gallery_width']}' cellpadding='2' cellspacing='0' "; if($config['page_direction'] == "1")   echo "dir='rtl'"; echo ">
<tr><td style='background-image:url(" . getSkinElement($config['stylesheet'], "images/td_back_mid.gif") . ")' class='cell_header' colspan='3' valign='middle' "; if($config['page_direction'] == "1")   echo "align='right'"; echo ">
<img src='" . getSkinElement($config['stylesheet'], "images/arrow.gif") . "' border='0' alt='' /> {$lang['head_search']}</td></tr>
<tr><td width='100%'>
<table width='100%' border='0'> 
<tr><td width='100%'><input id='q' type='text' name='searchterm' class='textBox' maxlength='128' style='width:99%' value='{$_GET['q']}' /></td>
<td>
";
	
//more/less options button
$path_skin = getSkinElement($config['stylesheet'], "images");
echo "<a href='#' onclick=\"return hidify_showify('search_options','search_arrow','$path_skin', '{$lang['less_options']}', '{$lang['advanced_options']}', 'img');\">
<img src='" . getSkinElement($config['stylesheet'], "images/arrow_down.gif") . "' alt='{$lang['advanced_options']}' title='{$lang['advanced_options']}'border='0' id='search_arrow'/></a>
</td>
</tr>
</table>
</td></tr>
";

//are we showing advanced options?
//sort_results_by E {0=alphabetically, 1=last_added}
//sort_results_order E = {0=asc,1=desc}
echo "<tr><td width='100%' id='search_options'>
<fieldset id='half'><legend>{$lang['options']}&nbsp;</legend>

<div class='columnLeft'>
<div style='margin-bottom:20px'>{$lang['search_strength']}</div>
{$lang['sort_results']}
</div>

<div class='columnRight'>
";

//strength
echo "<select id='strength' name='strength' class='spoiltDropdownBox'>
";

echo "<option value='0'";
if($_GET['strength'] == "0") echo " selected='selected'" ;
echo ">{$lang['words_some']}</option>";

echo "<option value='1'";
if($_GET['strength'] == "1") echo " selected='selected'" ;
echo ">{$lang['words_all']}</option>";

echo "
</select><br /><br />
";

//sort results by
echo "<select id='by' name='sort_results_by' class='spoiltDropdownBox'>
";

echo "<option value='0'";
if($_GET['by'] == "0") echo " selected='selected'" ;
echo ">{$lang['by_last_added']}</option>";

echo "<option value='1'";
if($_GET['by'] == "1") echo " selected='selected'" ;
echo ">{$lang['alphabetically']}</option>";

echo "
</select><br /><br />
";

//results order
echo "<select id='order' name='sort_results_order' class='spoiltDropdownBox'>
";

echo "<option value='0'";
if($_GET['order'] == "0") echo " selected='selected'" ;
echo ">{$lang['desc']}</option>";

echo "<option value='1'";
if($_GET['order'] == "1") echo " selected='selected'" ;
echo ">{$lang['asc']}</option>";

echo "
</select>
";

echo "</div>
</fieldset>

<fieldset id='and_half'><legend>{$lang['filter']}&nbsp;</legend>
<div class='columnLeft'>
<div style='margin-bottom:115px'>{$lang['by_cat']}</div>
{$lang['search_for_by']}
</div>
";

$cat_as_an_arr = set2array($_GET['cat']); //convert the set of cats in the url to an array if it exists (new in v0.9.4)
$cats = getCatsTreeView(0,0,$config,$lang);
echo "<div class='columnLeft'>
<select id='cat_dropdown' name='cat[]' class='spoiltDropdownBox' size='8' multiple='multiple'>
";
$i = 0;
foreach($cats as $key => $value) {
	if($key == 0) { //modded in v0.9.4
		echo "<option value='$key' id='$i'";
		if($cat_as_an_arr[0] == "")   echo "selected='selected'"; //if no cats were selected, select this default option (v0.9.4)
		echo ">{$lang['search_all_cats']}</option>
		";
	}
	else {
		echo "<option value='$key' id='$i'";
		if(in_array($key, $cat_as_an_arr) || $key == 0) { //select the value if it's in our url
			echo " selected='selected'";
		}
		echo ">$value</option>
		";
	}
	
	$i++;
}

$please_wait_msg = "'<div align=\'center\'><img src=\'" . getSkinElement($config['stylesheet'], "images/loading_small.gif") . "\' title=\'\' alt=\'\' width=\'16px\' height=\'16px\' /> {$lang['loading_please_wait']}</div>'";

echo "</select><br /><br />
<input id='a' type='text' name='searchauthor' maxlength='32' class='spoiltTextBox' value='{$_GET['a']}' />
</div>
</fieldset>
</td></tr>
<tr><td width='100%' align='center'><br /><input type='submit' name='submit' value='{$lang['button_search']}' class='submitButton2' onClick=\"JavaScript:return(xmlhttpPost(processSearch('0'),'search_results_div','search_results_div', $please_wait_msg));\" /><br /><br /></td></tr>
</table> <!-- end of main table -->
</form>
";

//this is where the results via xmlhttprequest are displayed
echo "<div id='search_results_div' style='width:{$config['gallery_width']};margin-left:auto;margin-right:auto;text-align:left'>";
echo "</div>";

//if query data is in the URI, requery table using that data instead of showing a blank page
//this is to allow users to access search results via a full address since results are no displayed using xmlhttprequest (v0.9.4)
if(isset($_GET['q'])) {	
	?>
	<script language="javascript" type="text/javascript">
	xmlhttpPost(processSearch("1"),'search_results_div','search_results_div', ""); //change last arg to $please_wait_msg
	</script>
	<?php
}
	
showJumpToCatForm($config, $lang);
	
mysql_close($connection);
include('foot.php');
  
?>