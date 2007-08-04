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
 
 File: j_ajax.php
 Description: -
 Created: July 5, 2007
 Last updated: July 9, 2007
 Random quote: "Success is the ability to go from one failure to another
 without any losse of enthusiasm." -Winston Churchill
 *******************************************************************/

//--------------------------------------------
// xmlhttpPost()
//--------------------------------------------
function xmlhttpPost(strURL, id, id_for_loading_msg, msg_to_show) {
	document.getElementById(id_for_loading_msg).innerHTML = msg_to_show;

	var request = null;
	try {
	   request = new XMLHttpRequest(); //Mozilla, Safari
	}
	catch (trymicrosoft) { //Microsoft
	   try {
		 request = new ActiveXObject("Msxml2.XMLHTTP");
	   }
	   catch (othermicrosoft) {
		 try {
		   request = new ActiveXObject("Microsoft.XMLHTTP");
		 }
		 catch (failed) {
		   request = null;
		 }
	   }
	}//end catch
	
	 request.open("GET", strURL, true);
	 request.onreadystatechange =  function() {
		 if (request.readyState == 4) {
				document.getElementById(id).innerHTML = request.responseText;
		}
	 }
	 
	 request.send(null);

	return false;
}