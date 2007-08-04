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

if(is_numeric($_GET['m']))   $m = $_GET['m'];

echo "<table class='margin' cellspacing='1' cellpadding='1' style='border-width:0px;width:190px;border-style:solid;border-color:#cccccc;font-size:11px'>
<tr>
<td width='100%'>

<br><b>{$lang['my_nav_account_options']}</b><br>
<a href='my.php?m=$m&do=edit-profile' target='_top'>{$lang['my_nav_edit-profile']}</a><br>
<a href='my.php?m=$m&do=change-email' target='_top'>{$lang['my_nav_change-email']}</a><br>
<a href='my.php?m=$m&do=change-pass' target='_top'>{$lang['my_nav_change-pass']}</a><br>

</td></tr>
</table>";

?>