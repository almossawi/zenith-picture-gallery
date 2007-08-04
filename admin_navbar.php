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

echo "<table class='margin' cellspacing='1' cellpadding='1' style='border-width:0px;width:190px;border-style:solid;border-color:#cccccc;font-size:11px'>
<tr>
<td width='100%'>

<br /><b>{$lang['admincp_nav_admin_options']}</b><br />
<a href='admincp.php?do=config' target='_top'>{$lang['admincp_nav_basic_config']}</a><br />
<a href='admincp.php?do=edit-login' target='_top'>{$lang['admincp_nav_login']}</a><br />
<a href='admincp.php?do=ban' target='_top'>{$lang['admincp_nav_blacklist']}</a><br />
<a href='admincp.php?do=optimize' target='_top'>{$lang['admincp_nav_optimize']}</a><br />
<a href='admincp.php?do=define-new-fields' target='_top'>{$lang['admincp_nav_define_new_fields']}</a><br />

<br /><b>{$lang['admincp_nav_users']}</b><br />
<a href='admincp.php?do=new-user' target='_top'>{$lang['admincp_nav_new_user']}</a><br />
<a href='admincp.php?do=user-del' target='_top'>{$lang['admincp_nav_userdel']}</a><br />
<a href='admincp.php?do=user-man' target='_top'>{$lang['admincp_nav_userman']}</a><br />
<a href='admincp.php?do=approve-users' target='_top'>{$lang['admincp_nav_pending_users']}</a><br />
<a href='admincp.php?do=recalc-users' target='_top'>{$lang['admincp_nav_recalculate_user_stats']}</a><br />

<br /><b>{$lang['admincp_nav_pictures']}</b><br />
<a href='admincp.php?do=approve' target='_top'>{$lang['admincp_nav_pending_pics']}</a><br />
<a href='admincp.php?do=approve-comments' target='_top'>{$lang['admincp_nav_pending_comments']}</a><br />
<a href='admincp.php?do=batch-add&show=10' target='_top'>{$lang['admincp_nav_batchadd']}</a><br />
<a href='admincp.php?do=batch-del' target='_top'>{$lang['admincp_nav_batchdel']}</a><br />
<a href='admincp.php?do=rebuild-thumbs' target='_top'>{$lang['admincp_nav_rebuild_thumbs']}</a><br />

<br /><b>{$lang['admincp_nav_categories']}</b><br />
<a href='admincp.php?do=cat-man' target='_top'>{$lang['admincp_nav_catman']}</a><br />
<a href='admincp.php?do=cat-edit' target='_top'>{$lang['admincp_nav_catedit']}</a><br />
<a href='admincp.php?do=cat-permissions' target='_top'>{$lang['admincp_nav_permissions']}</a><br />
<a href='admincp.php?do=cat-rebuild' target='_top'>{$lang['admincp_nav_cat_rebuild']}</a><br />
<a href='admincp.php?do=cat-order' target='_top'>{$lang['admincp_nav_cat_order']}</a><br />

<br /><b>{$lang['admincp_nav_languages']}</b><br />
<a href='admincp.php?do=lang-man' target='_top'>{$lang['admincp_nav_langman']}</a><br />
</td></tr>
</table>
<br />";

?>