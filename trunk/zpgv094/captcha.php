<?php

/*******************************************************************
 Zenith Picture Gallery
 CAPTCHA mod
 Written by Marcin Krol <hawk@limanowa.net>

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, a copy of 
 which is made available to you with this package.
 This program is distributed in the hope that it will be useful, but
 WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A  PARTICULAR PURPOSE.
 
 File: captcha.php (new in v0.9.4 DEV, written by hawk@limanowa.net)
 Description: Simple PHP based CAPTCHA
*******************************************************************/

session_start();

// image width (wider image = longer code, 17 pixels per character, 32 chars maximum)
$width = 84;
// image height (try to keep it >= 20)
$height = 18;
// black & white captcha (0 - no, 1 - yes)
$bw = 0;
// draw lines (0 no, 1 - horizontal, 2 - vertical, 3 - random)
$draw_lines = 3;
// how many random lines
$lines = 50;
// draw random pixels (0 - no, 1 - yes)
$draw_pixels = 1;
// how many random pixels
$pixels = 250;
// image background color range
$bg_color_min = 150;
$bg_color_max = 250;
// line color range
$ln_color_min = 100;
$ln_color_max = 250;
// line color range
$px_color_min = 100;
$px_color_max = 150;
// text color range
$txt_color_min = 30;
$txt_color_max = 50;

// list of characters allowed in captcha
$char_table = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

// create image, fill with random color
$image = imagecreatetruecolor($width, $height);
if($bw)
  {
  $color_code = rand($bg_color_min, $bg_color_max);
  $color = imagecolorallocate($image, $color_code, $color_code, $color_code);
  }
else $color = imagecolorallocate($image, rand($bg_color_min,$bg_color_max), rand($bg_color_min,$bg_color_max), rand($bg_color_min,$bg_color_max));
imagefill($image, 0, 0, $color);
// draw lines if required
if($draw_lines == 1)
  {
  // horizontal lines all over image
  for($i=0; $i<$height; $i++)
    {
    if($bw)
      {
      $color_code = rand($ln_color_min, $ln_color_max);
      $color = imagecolorallocate($image, $color_code, $color_code, $color_code);
      }
    else $color = imagecolorallocate($image, rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max));
    imageline($image, 0, $i, $width, $i, $color);
    }
  }
elseif($draw_lines == 2)
  {
  // vertical lines all over image
  for($i=0; $i<$width; $i++)
    {
    if($bw)
      {
      $color_code = rand($ln_color_min, $ln_color_max);
      $color = imagecolorallocate($image, $color_code, $color_code, $color_code);
      }
    else $color = imagecolorallocate($image, rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max));
    imageline($image, $i, 0, $i, $height, $color);
    }
  }
elseif($draw_lines == 3)
  {
  // random lines
  for($i=0; $i<$lines; $i++)
    {
    if($bw)
      {
      $color_code = rand($ln_color_min, $ln_color_max);
      $color = imagecolorallocate($image, $color_code, $color_code, $color_code);
      }
    else $color = imagecolorallocate($image, rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max), rand($ln_color_min,$ln_color_max));

    imageline($image, rand(0,$width), rand(0,$height), rand(0,$width), rand(0,$height), $color);
    }
  }
// draw random pixes if required
if($draw_pixels)
  {
  for($i=0; $i < $pixels; $i++)
    {
    if($bw)
      {
      $color_code = rand($px_color_min, $px_color_max);
      $color = imagecolorallocate($image, $color_code, $color_code, $color_code);
      }
    else $color = imagecolorallocate($image, rand($px_color_min,$px_color_max), rand($px_color_min,$px_color_max), rand($px_color_min,$px_color_max));
    imagesetpixel($image, rand(0,$width), rand(0,$height), $color);
    }
  }
// generate random string and put it on image
$num_chars = floor($width / 16);
$string = "";
for($i=0; $i<$num_chars; $i++) $string .= $char_table{rand(0,strlen($char_table)-1)};
$color = imagecolorallocate($image, rand($txt_color_min,$txt_color_max), rand($txt_color_min,$txt_color_max), rand($txt_color_min,$txt_color_max));
for($i=0, $j=1; $i<$num_chars; $i++, $j+=9)
  {
  imagestring($image, (rand(0,1) ? 3 : 5), rand($i*8+$j,$i*8+$j+8), rand(-2,$height-14), $string{$i}, $color);
  }
// store code in session
$_SESSION['authcode'] = $string;

// output image
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>