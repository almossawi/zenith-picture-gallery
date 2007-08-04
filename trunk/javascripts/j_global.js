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
 
 File: j_global.php
 Description: -
 Last updated: July 8, 2007
 Random quote: "It is easy to be brave from a safe distance." -Aesop
 *******************************************************************/

//The block below is courtesy of
//Harald Hope, Tapio Markula, Websites: http://techpatterns.com/
//It is released under the terms of the LGPL
//function browser_detect() {
	var d, dom, ie, ie4, ie5x, moz, mac, win, lin, old, ie5mac, ie5xwin, op;
	d = document;
	n = navigator;
	na = n.appVersion;
	nua = n.userAgent;
	win = ( na.indexOf( 'Win' ) != -1 );
	mac = ( na.indexOf( 'Mac' ) != -1 );
	lin = ( nua.indexOf( 'Linux' ) != -1 );
	
	if ( !d.layers ){
		dom = ( d.getElementById );
		op = ( nua.indexOf( 'Opera' ) != -1 );
		konq = ( nua.indexOf( 'Konqueror' ) != -1 );
		saf = ( nua.indexOf( 'Safari' ) != -1 );
		moz = ( nua.indexOf( 'Gecko' ) != -1 && !saf && !konq);
		ie = ( d.all && !op );
		ie4 = ( ie && !dom );
		/*ie5x tests only for functionality. ( dom||ie5x ) would be default settings. 
		Opera will register true in this test if set to identify as IE 5*/
		ie5x = ( d.all && dom );
		ie5mac = ( mac && ie5x );
		ie5xwin = ( win && ie5x );
	}
//}

//--------------------------------------------
// hidify_showify()
//--------------------------------------------
function hidify_showify(e_table, e_img, img_path, alt_less, alt_more, img_or_span) {
	if(document.getElementById) {
		var id_table = document.getElementById(e_table).style;
		var id_img = document.getElementById(e_img);
	  
		//set the object to table-cell if the browser
		//is Mozilla and block if it's anything else.
		if(moz){
		  	if(id_table.display == "table-cell") {
			 id_table.display = "none";
			 if(img_or_span == "img") {
				 id_img.src = img_path+"/arrow_down.gif";
				 id_img.alt = alt_more;
				 id_img.title = alt_more;
			 }
			 else   id_img.innerHTML = alt_more;
			}
			else {
				id_table.display = "table-cell";
				//alert(id_table.display);
				if(img_or_span == "img") {
					id_img.src = img_path+"/arrow_up.gif";
					id_img.alt = alt_less;
					id_img.title = alt_less;
				}				
				else   id_img.innerHTML = alt_less;
				//alert(id_img.innerHTML);
			}
		}
		else {
		  if(id_table.display == "block") {
			 id_table.display = "none";
			 
			 if(img_or_span == "img") {
				 id_img.src = img_path+"/arrow_down.gif";
				 id_img.alt = alt_more;
				 id_img.title = alt_more;
			 }
			 else   id_img.innerHTML = alt_more;
		  }
		  else {
			 id_table.display = "block";
			 //alert(id_table.display);
			 if(img_or_span == "img") {
				 id_img.src = img_path+"/arrow_up.gif";
				 id_img.alt = alt_less;
				 id_img.title = alt_less;
			 }
			 else   id_img.innerHTML = alt_less;
		  }
		}
		return false;
	}
	else {
	  return true;
	}
}

//--------------------------------------------
// hidify_showify_skinny()
//--------------------------------------------
function hidify_showify_skinny(id_loader) {
   if(document.getElementById) {
      var e_loader = document.getElementById(id_loader).style;
	
	  if(navigator.userAgent.indexOf("Firefox")!=-1){
			if(e_loader.display == "none") {
			 e_loader.display = "table-cell";
			}
			else {
			 e_loader.display = "table-cell";
		  }
	  }
	  else {
		  if(e_loader.display == "block") {
			 e_loader.display = "none";
		  }
		  else {
			 e_loader.display = "block";
		  }
	  }
	  return false;
   }
   else {
      return true;
   }
}

//--------------------------------------------
// filler(): used in add.php
//--------------------------------------------
function filler(box) {
   if (box == "")   return document.frmAdd.title.value;
   else   return box;
}

//--------------------------------------------
// selectOrClearAll(): select or clear all of an element's, umm...elements depending on its current state
// new in v0.9.2 DEV
//--------------------------------------------
function selectOrClearAll(field) {
   if(field[0].checked == false) {
      for (i=0;i<field.length;i++)
         field[i].checked=true;
   }
   else {
      for (i=0;i<field.length;i++)
         field[i].checked=false;
   }
}

//--------------------------------------------
// selectDropdownValue(): sets a dropdown box's value to val
//--------------------------------------------
function selectDropdownValue(field, val) {

   for (i=0;i<field.length;i++) { //check that the element is a dropdown box first
      if(field[i].type == "select-one" && field[i].name != "category")
         field[i].value=val; //set its value property to val
   }
}

//--------------------------------------------
// genPass(): generates random string (used in admincp.php)
//--------------------------------------------
function genPass() {
	var words = new Array(16);
	words[0] = "free"; words[1] = "base"; words[2] = "mac"; words[3] = "pose";
	words[4] = "high"; words[5] = "flag"; words[6] = "day"; words[7] = "book";
	words[8] = "do"; words[9] = "hem"; words[10] = "prop"; words[11] = "cap";
	words[12] = "yob"; words[13] = "tell"; words[14] = "spec"; words[15] = "rib";
	var rand1 = Math.floor(Math.random()*16);						
	var rand2 = Math.floor(Math.random()*16);
	return words[rand1]+Math.floor(Math.random()*1000)+words[rand2]+Math.floor(Math.random()*10);
}

//--------------------------------------------
// getShowValue()
//--------------------------------------------
function getShowValue() {
	var n = prompt("","10");
	if(n != null)   document.location.href = "admincp.php?do=batch-add&show=" + n;
}

//--------------------------------------------
// toggleRegisterButton()
//--------------------------------------------
function toggleRegisterButton() {
	if(document.frmRegister.agree.checked == true) document.frmRegister.submit.disabled = false;
	else document.frmRegister.submit.disabled = true;
}

//--------------------------------------------
// gotoPage()
//--------------------------------------------
function gotoPage(arg_name, arg_value, page) {
	var url = page+"?"+arg_name+"="+arg_value;
	window.open(url,'picman','width=600,height=150,resizable=yes');
}

//--------------------------------------------
// displayPrompt()
// displays a prompt with the given value
//--------------------------------------------
function displayPrompt(value) {
	temp = prompt("", value);
	return false;
}

//--------------------------------------------
// setText()
//--------------------------------------------
function setText(e, val) {
	if(document.getElementById(e) != null)
		document.getElementById(e).value = val

	return false;
}

//The functions below are courtesy of
//http://clagnut.com/sandbox/imagefades.php

//--------------------------------------------
// initImage()
//--------------------------------------------
function initImage() {
	imageId = 'thephoto';
	if(document.getElementById(imageId) == null) return false //added by author
	image = document.getElementById(imageId);
	setOpacity(image, 0);
	image.style.visibility = "visible";
	fadeIn(imageId,0);
}

//--------------------------------------------
// fadeIn()
//--------------------------------------------
function fadeIn(objId,opacity) {
	if (document.getElementById) {
		obj = document.getElementById(objId);
		if (opacity <= 100) {
			setOpacity(obj, opacity);
			opacity += 10;
			window.setTimeout("fadeIn('"+objId+"',"+opacity+")", 50);
		}
	}
}

//--------------------------------------------
// setOpacity()
//--------------------------------------------
function setOpacity(obj, opacity) {
	opacity = (opacity == 100)?99.999:opacity;
	// IE/Win
	obj.style.filter = "alpha(opacity:"+opacity+")";
	// Safari<1.2, Konqueror
	obj.style.KHTMLOpacity = opacity/100;
	// Older Mozilla and Firefox
	obj.style.MozOpacity = opacity/100;
	// Safari 1.2, newer Firefox and Mozilla, CSS3
	obj.style.opacity = opacity/100;
}
