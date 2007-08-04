Zenith Picture Gallery v0.9.4 DEV
(c) Copyright 2004-2007 CyberiaPC.com
---------------------------------------

Thank you for downloading this pre-release version of Zenith Picture Gallery.


Requirements

* PHP 4.2 or higher
* GD (if you want the gallery to be able to create thumbnails)
* MySQL 3.22 or higher
* Linux or Windows

=======================================
FIRST-TIME/CLEAN INSTALL
=======================================

First
~~~~~~~~~~~~~
Make sure you have an empty MySQL database ready before proceeding.  Otherwise, you can
create a new one by running the following query either from your SQL command-line prompt
or in phpMyAdmin:

CREATE DATABASE zenith;



Second
~~~~~~~~~~~~~
Upload all the files and directories in the zip file to a directory in your server's public
directory.

Make sure you CHMOD config.php to 666.
Make sure you CHMOD the directories 'uploads', 'incoming', 'outgoing' and 'lang' to 777.



Third
~~~~~~~~~~~~~
Run installer.php to create and populate the required tables.  If all goes well, you will
receive no errors.  Otherwise, please ensure that your MySQL details are correct.



Finally
~~~~~~~~~~~~~
Run the index.php file from the path where you installed Zenith and you will see the main
page come up.  Initially, there will be no pictures in the library, so you will need to
add them by clicking on 'Add' from the main menu.

Make sure you delete the file installer.php!

Congratulations, you should now be running v0.9.2 DEV!


=======================================
v0.9.4 DEV CHANGELOG
=======================================

Features
- AJAXified the search page so that users can run multiple searches without having to set 
  all the options again every time (the rest will be done in the following release).
- Admin can choose whether to use category names (as before) or auto-generated numeric ids 
  for directory names and in paths, settable in the Admin CP (plus the admin has a new option 
  now that goes ahead and rebuilds the directory names) - I like this feature original.gif
- Uploaded pictures that are awaiting approval can now be appended with an admin-settable 
  suffix and hidden from directory listings. The feature can be turned on or off from the 
  Admin CP (suggested by malignwarlock)
- Allow the admin to determine how to order categories
- Allow the admin to set individual pictures to private or public
- Allow admins to choose between fixed-size and variable-sized thumbnails (settable in the 
  Admin CP)
- CAPTCHA support (integration of mod written by Hawk)
- Users can now switch between currently installed languages

Changes/minor additions
- A loading animation is displayed when medium or full-sized pictures are loading
- RSS feed now links to pictures' pages (pic_details.php) instead of directly to their 
  direct path
- Admins who were having problems with sendmail can now easily make a few changes in the 
  code and start using smtp instead
- Comments counter displayed in the welcome box on the main page
- Multi-line comments now allowed
- News bar now displayed in a text box and input field changed to a text area (integration 
  of mod written by Hawk)
- Medium picture size is now automatically corrected if it is ever greater than 1024px 
  (threshold can be modified by user; see the FAQ thread)
- Mini-thumbnails are now displayed for pending pictures and comments (integration of mod 
  written by Hawk)
- JPEG quality is now enforced for medium-sized pictures and watermarks (integration of mod 
  written by Hawk)
- category_no_pics.jpg and video.jpg images changed to .png ones (integration of mod written 
  by Hawk)
- Number of members and last registered member aren't shown on main page if user registration 
  is disabled (integration of mod written by Hawk)
- Additional stats shown in welcome box on main page
- Search updated to a "use any words" one (integration of mod here)
- Comments in pic_details.php are now numbered
- index.htm files placed in all directories (thanks Hawk)
- The php files in the tarball release now have all CR and end-of-line characters stripped
  (thanks Hawk)

Bugs fixed
- The year value wasn't remembered in register.php
- Comment counter only displayed the first digit when value was greater than 9
- Pictures in Last Added weren't restricted to only those from public categories
- "Not a valid image" bug during uploads fixed
- title set for all images in addition to alt to allow tooltips to show up in Firefox 
  (thanks Spuds)
- "Span spam" removed in showEntry() (thanks Spuds)
- Missing title, pictures in RSS feed's body fixed/improved, you decide
- Rebuilding thumbnails continues gracefully if a particular image can't be read for whatever 
  reason
- Deleting mod-queued pictures deletes their pictures (silly bug)
- A tiny potential security flaw fixed
- "Picture Info" box in pic_details.php got screwed up when direct path was hidden
- Minor XHTML validation issues fixed
- Minor right-to-left layout issues fixed


=======================================
UPGRADE FROM v0.9.2 DEV to v0.9.4 DEV
=======================================
1. Upload ALL the files [b][color=red]EXCEPT config.php and installer.php[/color][/b]

Since the last release came out so long ago, I couldn't find all the bits of paper that I wrote down the names of the updated files on.  To save some time though, you may skip the following files:
* everything in "lib" except phpmailer.php
* everything in skins/default/images except loading.gif and loading_small.gif
* everything in skins/nature/images except loading_small.gif
* everything in skins/techno/images except loading_small.gif

2. Run upgrade_tools/upgrader9.php from your browser to update your MySQL tables.

3. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

Congratulations, you should now be running v0.9.4 DEV!


=======================================
UPGRADE FROM v0.9 DEV to v0.9.2 DEV
=======================================

1. Upload ALL the files EXCEPT config.php and installer.php

Sorry, but because the way the gallery interacts with skins was modified, all the .php and .js 
files had to be updaed.

2. Run upgrade_tools/upgrader8.php from your browser to update your MySQL tables.

3. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

Congratulations, you should now be running v0.9.2 DEV!


=======================================
UPGRADE FROM v0.8.8 DEV to v0.9 DEV
=======================================

1. Upload the following files:

add.php
admincp.php
image_man.php
marked_pics.php
pic_details.php
thumbify.php
zipfile.php

watermark.OFF.png*

functions/f_admin.php
functions/f_cats.php
functions/f_global.php
functions/f_search.php

docs*

lib/jpeg_metadata_toolkit*

lang/english.php


*: new file or directory

To use the watermark, simply rename the file watermark.OFF.png to watermark.png.  If you want 
to replace the file with your own, be sure it's saved as PNG-8.  I know, the restriction sucks;
there'll be a more flexible solution in an upcoming release.

2. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

Congratulations, you should now be running v0.9 DEV!


=======================================
UPGRADE FROM v0.8.6 DEV to v0.8.8 DEV
=======================================

1. Upload the following files (or, if you want, everything except config.php and installer.php):

add.php
admincp.php
comment_man.php
display.php
edit.php
edit_field.php
head.php
index.php
image_man.php
login.php
marked_pics.php*
my.php
pic_details.php
profile.php
search.php
register.php
rss.php
show.php
thumbify.php

functions/f_admin.php
functions/f_cats.php
functions/f_global.php
functions/f_search.php

javascripts/j_global.js

lang/english.php

upgrade_tools/

skins:
images/category_no_pics.jpg
images/save.gif
category_name
news_bar


*: new file

If you don't want to upload everything in upgrade_tools to your server, you can safely
delete everything in upgrade_tools.  The code rearrangement in v0.8.8 DEV broke all
upgraders prior to upgrader6.php.

2. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

Congratulations, you should now be running v0.8.8 DEV!


=======================================
UPGRADE FROM v0.8.5 to v0.8.6 DEV
=======================================

Note: A lot has changed in the stylesheets.  Therefore, it is advisable to recreate your existing 
stylesheets based on the new ones.

1. Upload EVERYTHING except config.php.
2. Run upgrade_tools/upgrader7.php from your browser to update your MySQL tables.
3. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

Congratulations, you should now be running v0.8.6 DEV!


=======================================
UPGRADE FROM v0.8.4 to v0.8.5
=======================================

1. Upload the following files to your main Zenith directory:

add.php
admin_functions.php
admincp.php
comment_man.php
display.php
functions.php
head.php
image_man.php
login.php
logout.php
members.php
my.php
pic_details.php
register.php
rss.php

lang/english.php (new in english.php: description_sql_pass, show_members_list, members_list_blocked_msg)
upgrade_tools/upgrader6.php


2. Run the file upgrade_tools/upgrader6.php from the browser to update your MySQL tables.

3. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

You'll be logged out after the upgrade.  Logging in again shouldn't be a problem.  If it is, clear
all the cookies for your domain name and then try again.

Congratulations, you should now be running v0.8.5!


=======================================
UPGRADE FROM v0.8 to v0.8.4
=======================================

1. Upload the following files to your main Zenith directory. To save time, you can just
upload everything again with the exception of installer.php, config.php and functions/f_db.php.php.
Please backup your existing skins if necessary.

add.php
admin_functions.php
admin_navbar.php
admincp.php
comment_man.php
delete.php
display.php
edit.php
edit_user.php *
foot.php
functions.php
head.php
image_man.php
index.php
login.php
logout.php
lost_password.php *
members.php
my.php
my_navbar.php
pic_details.php
profile.php
redirecting.php
register.php
rss.php *
search.php
show.php
thumbify.php
zipfile.php

lang/english.php
skins/default (new in stylesheet: cell_foot, pic_cell, pic_details_cell  new in images: rss.gif)
skins/techno
skins/nature
upgrade_tools/upgrader5.php

* = new file

If you have customized your default skin, please back it up because you will need to overwrite
its stylesheet file. Upload all the directories in skins to your skins folder.

2. Run the file upgrade_tools/upgrader5.php from the browser to update your MySQL tables.

3. Log in to your Admin CP, scroll to the bottom and click on 'Update Settings'.

4. You might have left over pictures in your 'uploads' folder that weren't moved to any
directories or subdirectories. These are orphaned pictures that weren't deleted when their
records were, possibly because of a bug in earlier releases. Make sure you confirm that you
don't want these pictures and delete them. Otherwise, the Rebuild Thumbnails tool in the
Admin CP will report an error.

Congratulations, you should now be running v0.8.4!


=======================================
UPGRADE FROM v0.7.2 to v0.8
=======================================

1. Upload the following files to your main Zenith directory:

add.php
admin_functions.php
admin_navbar.php
admincp.php
comment_man.php
delete.php
display.php
edit.php
functions.php
head.php
image_man.php
index.php
login.php
logout.php
members.php
my.php
my_navbar.php
pic_details.php
profile.php
redirecting.php
register.php
search.php
show.php
thumbify.php
zipfile.php

lang/english.php
skins/default/stylesheet.css (large_text_dark, copyright, submitButton, rowcolor1, rowcolor2, submitButtonTiny)
skins/default/images/folder.gif
upgrade_tools/upgrader4.php

If you have customized your default skin, please back it up because you will need to
overwrite its stylesheet file.

2. Run the file upgrade_tools/upgrader4.php from the browser to update your MySQL tables.

3. Log in to your Admin CP and in 'Setup and Configuration' click on 'Recalculate user
stats'.  Click on 'Go'.  Now click on 'Setup and Configuration', scroll to the bottom
and click on 'Update Settings'.

4. Finally, download and run the file zenith08_update_join_dates_etc.php from the address
below to update all your current users so that their join dates aren't the epoch year of
1970. Please make sure you remove the file after you run it in order to prevent anyone
else from running it again.

http://www.cyberiapc.com/forums/index.php?act=Attach&type=post&id=2396


Congratulations, you should now be running v0.8!


=======================================
UPGRADE FROM v0.7 to v0.7.2
=======================================

Upload the following files to your main Zenith directory:

add.php
admin_functions.php
admin_navbar.php
admincp.php
display.php
edit.php
functions.php
image_man.php
index.php
login.php
pic_details.php
search.php
thumbify.php
lang/english.php
skins/default/stylesheet.css (a.copyright color changed to black)

If you have customized your default skin, please back it up because you will need to
overwrite its files.


=======================================
UPGRADE FROM v0.6.1 to v0.7
=======================================

Important Note: Please read the following carefully. Because passwords are now stored differently,
you will need to go through a few steps to successfully update your current password and those of
your other user accounts:

1. Make sure you are logged in and viewing the 'Setup and Configuration' page in the Admin CP before
   uploading any of the updated files.
2. Upload all the files.
3. Refresh your browser window.
4. Click on 'Manage User Groups' on the left and re-type your current password. Do the same for any
   other user accounts you may have.
5. Click on 'Update'.

You will now be able to log out and then log back in again. If for any reason, that is not the case or you forget to login beforehand, upload the attached file (login_temp.php) to your main Zenith directory, login as usual, update your passwords and then logout. Please remove that file once you are done. 



1. Upload the following files to your main Zenith directory:

add.php
admincp.php
admin_functions.php
admin_navbar.php
comment_man.php
display.php
functions.php
head.php
index.php
login.php
pic_details.php
search.php
zipfile.php
lang/english.php
upgrade_tools/upgrader3.php


skins/default/stylesheet.css
skins/default/images/*.* (everything)

If you have customized your default skin, please back it up because you will need to
overwrite its files.

2. Run the file upgrade_tools/upgrader3.php from the browser to update your MySQL tables.

3. Finally, log in to your Admin CP and in 'Setup and Configuration', make the following changes:

For 'Short Date Format' type %B %d, %G and for 'Long Date Format' type %B %d, %G %r


Click on the button 'Update Settings'.


=======================================
UPGRADE FROM v0.6 to v0.6.1
=======================================

Upload the following files to your main Zenith directory:

comment_man.php
search.php
display.php
logout.php
functions.php
admin_functions.php
admincp.php


=======================================
UPGRADE FROM v0.5 TO v0.6
=======================================

To upgrade from v0.4.3, upload all the files and drectories EXCEPT:

config.php
installer.php
functions/f_db.php.php

Then run the file upgrader2.php from the browser to update your MySQL tables.

CHMOD incoming/thumbs to 777.

Finally, log in to your Admin CP and in 'Setup and Configuration', click on the button 'Update Settings'.

Make sure you remove the file upgrader2.php from your server.  That is it!


=======================================
UPGRADE FROM v0.4.3 TO v0.5
=======================================

To upgrade from v0.4.3, upload all the files including those in lang and skins EXCEPT:

config.php
installer.php
functions/f_db.php.php

Then run the file upgrader1.php from the browser to update your MySQL tables.

Finally, log in to your Admin CP and in 'Setup and Configuration', click on the button 'Update Settings'.

Make sure you remove the file upgrader1.php from your server.  That is it!



Problems, comments, feedback, suggestions?
http://www.cyberiapc.com/forums