<?php

/*******************************************************************
 Zenith Picture Gallery
 http://www.cyberiapc.com

 This file uses an API written by Evan Hunter.
 See lib/jpeg/metadata_toolkit/ for more.
*******************************************************************/

include('JPEG.php');
include('Photoshop_IRB.php');
include('PictureInfo.php');
include('EXIF.php');

/**
* 1. Change $filename to the location of the file you want to scan for IPTC data. 
* 2. Run this file in your browser and note down the value of IPTC type for the field
* you're interested in.
* 3. Finally, edit doAdminBatchAdd() in functions/f_admin.php (line 465)
*
* For example, if you want to set the keywords field for each of your pictures to the 
* Keywords IPTC field (IPTC Type 2:25), you'd change the line 
*
* $uploaded_keywords 	= htmlentities(charsEscaper($keyword[$value], $config['escape_chars']), ENT_QUOTES, "UTF-8");
*
* to
*
* $uploaded_keywords 	= htmlentities(charsEscaper(getValueFromIPTC($is_at,"2:25"), $config['escape_chars']), ENT_QUOTES, "UTF-8");
*
* It will then get the keywords value from the picture's IPTC data, if it exists, and ignore whatever you
* type in the keywords field for it in the Admin CP.
*
* Alternatively, you can see the whole list of IPTC types and name mappings in lib/jpeg_metadata_toolkit/IPTC.php
*/

$filename = "file1.jpg";

var_dump( get_Photoshop_IPTC( get_Photoshop_IRB( get_jpeg_header_data( $filename ) ) ), true );

?>

