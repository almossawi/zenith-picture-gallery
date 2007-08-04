<?php
/*******************************************************************
 Zenith Picture Gallery
 Written by and copyright (c) Ali Almossawi
 http://www.cyberiapc.com
 
 Modifications:
 * Marcin Krol <hawk@limanowa.net>, Unprintable characters stripped
   from exif data shown on picture preview page; only exif info that
   is known to exist in almost all cameras is now displayed), 
   v0.9.4 DEV

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: f_lib.php
 Description: Contains functions used by 3rd party modules in lib/
 Random quote: "Nothing will ever be attempted, if all possible
 objections must first be overcome." -Samuel Johnson 
*******************************************************************/

require_once("f_misc.php");

// stripUnprintableChars()
function stripUnprintableChars($string) {
    return preg_replace("/[^\w!@#$%\^&*()-=+\[\]\{\};:'\",<.>\/>\\|`~ ]/", "", $string);
}


//--------------------------------------------
// showExif()
//added in v0.8.6 on November 13, 2005.  Credit: qwerti
//--------------------------------------------
function showExif($filename, $config, $lang) {
	//added in v0.8.6 on November 13, 2005.  Credit: qwerti
	if($config['exif_show'] == 1) {
		// exif1_5 ---------------------------------------------
		//include_once('../lib/exifer1_5/exif.php');
		include_once(getCurrentPath2("lib/exifer1_5/exif.php"));
		$path= getPicturesPath($config, $filename, 1);
		$verbose = 0;
		$exif_info = read_exif_data_raw($path,$verbose);
		if ($exif_info['IFD0']){
			// exif info
			echo "<tr><td colspan='4' class='light_cell' width='80%'><b>{$lang['exif_data']}</b></td><td align='right' class='light_cell' width='20%'>";
			// a place for something like reveal hidden button -------------------------------------------
			echo "</td></tr><tr><td colspan='5' class='cell_highlight' width='100%'>";
			echo "<table>
			<tr>
			 <td width='30%'><span class='header'>Camera:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['IFD0']['Make'])." ".stripUnprintableChars($exif_info['IFD0']['Model'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Date Taken:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['IFD0']['DateTime'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Shutter Speed:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['SubIFD']['ShutterSpeedValue'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Exposure Time:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['SubIFD']['ExposureTime'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Aperture:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['SubIFD']['ApertureValue'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Exposure Bias:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['SubIFD']['ExposureBiasValue'])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Focal Length:</span></td>
			 <td width='70%'>";
			$flen = stripUnprintableChars($exif_info['SubIFD']['FocalLength']);
				   printf("%.1f", $flen);
				   echo " mm</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>ISO:</span></td>
			 <td width='70%'>ISO ".stripUnprintableChars($exif_info['SubIFD'][ISOSpeedRatings])."</td>
			</tr>
			<tr>
			 <td width='30%'><span class='header'>Flash mode:</span></td>
			 <td width='70%'>".stripUnprintableChars($exif_info['SubIFD'][Flash])."</td>
			</tr>";
			echo "</table>";
			
			// look for more infos? uncomment this
			/*echo "<pre>"; 
			print_r($exif_info); 
			echo "</code>";
			*/
			echo "</td></tr>";
		}
	}
}

//--------------------------------------------
// zenithMailer(), new in v0.9.2 DEV
// Used for batch emailing (uncomment add.php:192 and comment add.php:191 to enable)
// Final version will use something like swift mailer to make the batch sending more efficient
//--------------------------------------------
function zenithMailer($config, $to, $subject, $body, $headers) {
	$recipients = array(); //Container for recipients
	$statement = "SELECT username, email FROM {$config['table_prefix']}users";
	$result = mysql_query($statement);
	while ($row = mysql_fetch_array($result)) $recipients[] = array($row['username'], $row['email']); //get all our emails
	 
	foreach($recipients as $value) {
		if(!@mail($value[1], $subject, $body, $headers))   return 0; //if something wrong happened, quit
	}
	
	return 1;
}

?>