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
*******************************************************************/

require_once('config.php');
require_once('functions/f_db.php');
require_once('functions/f_global.php');
require_once('functions/f_cats.php');

//what's the state of the gallery?
isIpBlacklisted(getenv("REMOTE_ADDR"), $config, $lang);
if($config['gallery_off'] && !adminStatus($config))   doBox("error", $lang['gallery_off_msg'], $config, $lang);
elseif($config['private'] && !loggedIn($config))   doBox("error", $lang['private_msg'], $config, $lang);

if(isset($_GET['n']) && is_numeric($_GET['n']))   $n = $_GET['n'];
else   $n = 10;

$statement = "SELECT pid, cid, file_name, title, description, date AS d, approved, category, counter, cshow FROM {$config['table_prefix']}pictures, {$config['table_prefix']}categories WHERE approved=1 AND cshow=1 AND cid=category ORDER BY date DESC LIMIT $n";
$result = mysql_query($statement);

while($row = mysql_fetch_array($result)) {
	$timestamp = $row['d'];
	$dateInRFC822Format = date('r', strtotime($timestamp));

	//new in v0.9.4 DEV, uncomment first line and comment second if you'd rather show links to pictures' direct paths
	$is_at_img = getPicturesPath($config, $row['file_name'], 0);
	$is_at = getCurrentInternetPath2($config, "") . "pic_details.php?pid={$row['pid']}";

	$buffer .= "<item>";
	$buffer .= "<title>{$row['title']}</title>";
	$buffer .= "<description>".preg_replace("/(\r\n|\n|\r)/"," ",$row['description'] . "&lt;br /&gt; &lt;img src='$is_at_img' alt='{$row['title']}' title='{$row['title']}' /&gt;")."</description>";
	$buffer .= "<guid isPermaLink=\"false\">{$row['pid']}</guid>";
	$buffer .= "<link>$is_at</link>";
	$buffer .= "<pubDate>$dateInRFC822Format</pubDate>";
	$buffer .= "</item>\n";
} 

//credit: rbotzer at yahoo dot com (php.net html_entities user comments)
function htmlentities2unicodeentities ($input) {
  $htmlEntities = array_values (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
  $entitiesDecoded = array_keys  (get_html_translation_table (HTML_ENTITIES, ENT_QUOTES));
  $num = count ($entitiesDecoded);
  for ($u = 0; $u < $num; $u++) {
   $utf8Entities[$u] = '&#'.ord($entitiesDecoded[$u]).';';
  }
  return str_replace ($htmlEntities, $utf8Entities, $input);
}

//flush buffer
header("content-type: application/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<rss version=\"2.0\">

<channel>
	<title>{$config['title']}</title>
	<description>{$config['description']}</description>
</channel>

<channel>";
$buffer = htmlentities2unicodeentities($buffer);
echo html_entity_decode(str_replace("&","&amp;",utf8_encode($buffer)));
echo "</channel></rss>";

?>