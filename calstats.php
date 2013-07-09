<?php

// Basic definitions
define("CALSTATS_VERSION", "CaLStats 0.1.2 improved");
define("LAST_UPDATE", "Last update ");

/* ******************************************************* *
  *                                                         *
  *  (c)2003 by Jan Krupa (krupaj@mobilnews.cz)             *
  *  http://www.mobilnews.cz/honza/                         *
  *                                                         *
  * Improved by Martin Petracek (martin@petracek.net), 2013 *
  *                                                         *
  * ******************************************************* */

function return_ap_index($comp_ar, $ap_name) {
// Return computer index in array

  if (array_key_exists($ap_name,$comp_ar)) return $ap_name;
  return -1;

}

function return_ip_state($state_ar, $state_type, $state_ip) {
// Return ip address state
// $state_type - 0 = availability, 1 = rrt

  if (array_key_exists($state_ip,$state_ar)) return $state_ar[$state_ip][1+$state_type];
  return -1;

}

function return_link_str1($in_s1) {
  $l_tmp_pat = "";
  $l_tmp_chr = '0'; if (strlen($in_s1) > 1) { $l_tmp_chr = substr($in_s1, (strlen($in_s1)-2), 1); }
  for ($j1 = 0; ($j1 < (substr($in_s1, (strlen($in_s1)-1), 1) + 1)); $j1++) {
    $l_tmp_pat = $l_tmp_pat . $l_tmp_chr;
  }

  return $l_tmp_pat;
}

function assign_color($im, $in_col, $image_bgc) {
// Assign defined color to line

  $ln_color=ImageColorExact($im, 180, 180, 180);
  switch ($in_col) {
    case '-' : $ln_color = ImageColorExact($im, $image_bgc[0], $image_bgc[1], $image_bgc[2]); break;
    case 1   : $ln_color = ImageColorExact($im,   0,   0,   0); break;
    case 2   : $ln_color = ImageColorExact($im, 180,   0,   0); break;
    case 3   : $ln_color = ImageColorExact($im,   0, 180,   0); break;
    case 4   : $ln_color = ImageColorExact($im,   0,   0, 180); break;
    case 5   : $ln_color = ImageColorExact($im, 180,   0, 180); break; // magenta
    case 6   : $ln_color = ImageColorExact($im, 180, 180,   0); break; // yellow
    case 7   : $ln_color = ImageColorExact($im,   0, 180, 180); break; // white blue
    case 8   : $ln_color = ImageColorExact($im, 255,   0,   0); break;
    case 9   : $ln_color = ImageColorExact($im,   0, 255,   0); break;
  }

  return $ln_color;

}

function placeAP ($im, $x1, $y1, $ap_name, $ap_ip, $ap_type, $ap_state, $ap_lat) {
// Place computer on the map

  $ap_showip = 1;
  if ($ap_ip == "127.0.0.1") { $ap_showip = 0; }

  // ! ... hide ip and packet latency information
  // $ ... hide ip
  // % ... hide packet latency information

  $ap_name_show = $ap_name;
  if ( (substr($ap_name, 0, 1) == "!") || (substr($ap_name, 0, 1) == "$") ) {
    $ap_showip = 0;
    $ap_name_show = substr($ap_name, 1, (strlen($ap_name)-1));
  }

  $ap_lat_show = $ap_lat;
  if ( (substr($ap_name, 0, 1) == "!") || (substr($ap_name, 0, 1) == "%") ) {
    $ap_lat_show = 0;
    $ap_name_show = substr($ap_name, 1, (strlen($ap_name)-1)); // not needed if ! (already done in previous step)
  }

  $ap_xsize = 100;
  $ap_ysize = 35;
  if ($ap_showip == 0) { $ap_ysize=17; }
  $ap_name_size = ImageFontWidth(3)*strlen($ap_name_show);
  $ap_ip_size = ImageFontWidth(2)*strlen($ap_ip);
  $ap_lat_size = ImageFontWidth(2)*strlen(round($ap_lat_show))+6;
  if ($ap_lat_show == 0) { $ap_lat_size = 1; }

  if (($ap_name_size + $ap_lat_size + 10) > ($ap_xsize)) {
    $ap_xsize = $ap_name_size + $ap_lat_size+10;
    /*
    if (($ap_ip_size + 10) > $ap_xsize) {
      $ap_xsize = $ap_ip_size + 10;
    }
    */
  }

  switch ($ap_state) {
    case 0 : $ap_color = ImageColorExact($im, 255, 0, 0); $ap_color2=ImageColorExact($im, 255, 140, 140); break;
    case 1 : $ap_color = ImageColorExact($im, 0, 255, 0); $ap_color2=ImageColorExact($im, 140, 255, 140); break;
    case 2 : $ap_color = ImageColorExact($im, 180, 180, 180); $ap_color2=ImageColorExact($im, 220, 220, 220); break;
    case 3 : $ap_color = ImageColorExact($im, 255, 255, 0); $ap_color2=ImageColorExact($im, 255, 255, 140); break;
  }

  ImageRectangle($im, ($x1-($ap_xsize/2)), ($y1-($ap_ysize/2)), ($x1+($ap_xsize/2)), ($y1+($ap_ysize/2)), ImageColorExact($im, 0, 0, 0));
  ImageFilledRectangle($im, ($x1-($ap_xsize/2-1)), ($y1-($ap_ysize/2-1)), ($x1+($ap_xsize/2-$ap_lat_size)), ($y1-($ap_ysize/2-16)), $ap_color);
  if ($ap_showip == 1) {
    ImageLine($im, ($x1-($ap_xsize/2-1)), ($y1-($ap_ysize/2-17)), ($x1+($ap_xsize/2-1)), ($y1-($ap_ysize/2-17)), ImageColorExact($im, 140, 140, 140));
  }

  if ($ap_lat_show !=0 ) {
    ImageFilledRectangle($im, ($x1+($ap_xsize/2-$ap_lat_size+2)), ($y1-($ap_ysize/2-1)), ($x1+($ap_xsize/2-1)), ($y1-($ap_ysize/2-16)), $ap_color2);
    ImageLine($im, ($x1+($ap_xsize/2-$ap_lat_size+1)), ($y1-($ap_ysize/2-1)), ($x1+($ap_xsize/2-$ap_lat_size+1)), ($y1-($ap_ysize/2-16)),ImageColorExact($im, 140, 140, 140));
    ImageString($im, 2, ($x1+($ap_xsize/2)-$ap_lat_size+4), $y1-($ap_ysize/2-2), round($ap_lat_show), ImageColorExact($im, 0, 0, 0));
  }

  ImageFilledRectangle($im, ($x1-($ap_xsize/2-1)), ($y1-($ap_ysize/2-18)), ($x1+($ap_xsize/2-1)), ($y1+($ap_ysize/2-1)), ImageColorExact($im, 220, 220, 220));

  for ($i=1; $i<=$ap_type; $i++) {
    ImageRectangle($im, ($x1-($ap_xsize/2)-$i), ($y1-($ap_ysize/2)-$i), ($x1+($ap_xsize/2)+$i), ($y1+($ap_ysize/2)+$i), ImageColorExact($im, 0, 0, 0));
  }

  ImageString($im, 3, ($x1-($ap_name_size/2)-($ap_lat_size/2)+2), $y1-($ap_ysize/2-2), $ap_name_show, ImageColorExact($im, 0, 0, 0));
  if ($ap_showip == 1) {
    ImageString($im, 2, ($x1-strlen($ap_ip)*3), ($y1+1), $ap_ip, ImageColorExact($im, 0, 0, 0));
  }

}

function placeLN($im, $x1, $y1, $x2, $y2, $lnd, $image_bgc) {
// Place link onto map

  // Find out how to draw lines
  $ln_dir = 0; // 0 = horizontal, 1 = verical
  $tmp_x = abs($x1 - $x2);
  $tmp_y = abs($y1 - $y2);
  if ($tmp_x > $tmp_y) {
    $ln_dir = 1;
  }

  if ($ln_dir == 0) {
  // Horizontal
    $tmp_s1 = $x1 - round(strlen($lnd) / 2);
    for ($ix1 = $tmp_s1; $ix1 < ($tmp_s1 + strlen($lnd)); $ix1++) {
      ImageLine($im, $ix1, $y1, ($x2 - $x1 + $ix1), $y2, assign_color($im, substr($lnd, ($ix1 - $tmp_s1), 1), $image_bgc));
    }
  }
  else {
  // Vertical
    $tmp_s1 = $y1 - round(strlen($lnd) / 2);
    for ($ix1 = $tmp_s1; $ix1 < ($tmp_s1 + strlen($lnd)); $ix1++) {
      ImageLine($im, $x1, $ix1, $x2, ($y2 - $y1 + $ix1), assign_color($im, substr($lnd, ($ix1 - $tmp_s1), 1), $image_bgc));
    }
  }

}

// ------------------------------------------------------------------------
function makeMap ($image_filename, $output_filename) {
  // Date
  $output_filename.=".png";
  $a_date = getdate();
  $a_date_minutes = $a_date['minutes']; if ($a_date_minutes < 10) { $a_date_minutes = "0" . $a_date_minutes; }
  $a_date_seconds = $a_date['seconds']; if ($a_date_seconds < 10) { $a_date_seconds = "0" . $a_date_seconds; }
  $image_date =  LAST_UPDATE . $a_date['mday'] . " " . $a_date['month'] . " " . $a_date['year'] . " "
				. $a_date['hours'] . ":" . $a_date_minutes . ":" . $a_date_seconds;

  // --- Load config file - begin -------------------------------------------
  $file_in = fopen($image_filename . ".config", "r");
    if (!$file_in) { exit; }

  $fin_line=rtrim(fgets($file_in, 1024));
    $image_title = strtok($fin_line, ";");
    $image_xsize = strtok(";");
    $image_ysize = strtok(";");
    $image_legend = strtok(";"); // legend location
    $image_border = strtok(";"); // display border
    $image_bg = strtok(";"); // image background AABBCC

    if ($image_bg == "") $image_bg="FFFFFF";

    $image_bgc[0] = hexdec($image_bg[0].$image_bg[1]);
    $image_bgc[1] = hexdec($image_bg[2].$image_bg[3]);
    $image_bgc[2] = hexdec($image_bg[4].$image_bg[5]);

  fclose($file_in);
  // --- Load config file - end ---------------------------------------------

  // --- Load computer data - begin -----------------------------------------
  $file_in = fopen($image_filename . ".comp", "r");
    if (!$file_in) { exit; }

  while (!feof($file_in)) {
    $fin_line=rtrim(fgets($file_in, 1024));
    if (!strlen($fin_line)) continue; //skip empty line
    $name=strtok($fin_line, ";");
    $a_comp[$name][0] = strtok($fin_line, ";"); // name
    $a_comp[$name][1] = strtok(";"); // ip
    $a_comp[$name][2] = strtok(";"); // type
    $a_comp[$name][3] = strtok(";"); // x1
    $a_comp[$name][4] = strtok(";"); // y1
  }

  fclose($file_in);
  // --- Load computer data - end -------------------------------------------

  // --- Load legend data - begin -------------------------------------------
  $legend_show = 1;
  if (file_exists($image_filename . ".legend")) {
    $file_in = fopen($image_filename . ".legend", "r");
      if (!$file_in) { exit; }

    $legend_num = 0;
    while (!feof($file_in)) {
      $fin_line=rtrim(fgets($file_in, 1024));
	$a_legend[$legend_num][0] = strtok($fin_line, ";"); // name
	$a_legend[$legend_num][1] = strtok(";"); // ip
      if ($a_legend[$legend_num][0][0]!='#') $legend_num++;
    }
    $legend_num--;

    fclose($file_in);
  }
  else {
    $legend_show = 0;
  }
  // --- Load legend data - end ---------------------------------------------

  // --- Load links data - begin --------------------------------------------
  $link_show = 1;
  $a_link=array();
  if (file_exists($image_filename . ".link")) {
    $file_in = fopen($image_filename . ".link", "r");
      if (!$file_in) { exit; }

    while (!feof($file_in)) {
      $fin_line=rtrim(fgets($file_in, 1024));
	$link_tmp_type = strtok($fin_line, ";"); // link_type

	$link_tmp_from = strtok(";");
	for ($link_tmp_to = strtok(";"); $link_tmp_to != ""; $link_tmp_to = strtok(";")) {
	  $key=$link_tmp_from.";".$link_tmp_to;
	  if (!array_key_exists($key,$a_link)) {
	    $a_link[$key][0]=return_link_str1($link_tmp_type); //type of link
	    $a_link[$key][1]=0; // was it drawed on the screen
	  } else {
	    $a_link[$key][0].= "----".return_link_str1($link_tmp_type); //append new link type
	  }
	} 
    }

    fclose($file_in);
  }
  else {
    $link_show=0;
  }
  // --- Load links data - end ----------------------------------------------

  // --- Load links state - begin -------------------------------------------
  $file_in = fopen($image_filename . ".state", "r");
    if (!$file_in) { exit; }

  while (!feof($file_in)) {
    $fin_line=rtrim(fgets($file_in, 1024));
      $ip=strtok($fin_line, ";"); // ip
      $a_state[$ip][0]=$ip;
      $a_state[$ip][1]=strtok(";");
      $a_state[$ip][2]=strtok(";");
  }

  fclose($file_in);
  // --- Load links state - end ---------------------------------------------


  $im = ImageCreate($image_xsize, $image_ysize);

  $im_white = ImageColorAllocate($im, 255, 255, 255);
  $im_black = ImageColorAllocate($im,   0,   0,   0);

  $im_bg    = ImageColorAllocate($im, $image_bgc[0], $image_bgc[1], $image_bgc[2]);

  $im_gray1    = ImageColorAllocate($im, 140, 140, 140);
  $im_gray2    = ImageColorAllocate($im, 180, 180, 180);
  $im_gray3    = ImageColorAllocate($im, 200, 200, 200);
  $im_gray4    = ImageColorAllocate($im, 220, 220, 220);
  $im_gray5    = ImageColorAllocate($im, 240, 240, 240);
  $im_red      = ImageColorAllocate($im, 255,   0,   0);
  $im_green    = ImageColorAllocate($im,   0, 255,   0);
  $im_blue     = ImageColorAllocate($im,   0,   0, 255);
  $im_yellow   = ImageColorAllocate($im, 255, 255,   0);
  $im_red2     = ImageColorAllocate($im, 180,   0,   0);
  $im_green2   = ImageColorAllocate($im,   0, 180,   0);
  $im_blue2    = ImageColorAllocate($im,   0,   0, 180);
  $im_magenta2 = ImageColorAllocate($im, 180,   0, 180);
  $im_yellow2  = ImageColorAllocate($im, 180, 180,   0);
  $im_wblue2   = ImageColorAllocate($im,   0, 180, 180);
  $im_red3     = ImageColorAllocate($im, 255, 140, 140);
  $im_green3   = ImageColorAllocate($im, 140, 255, 140);
  $im_blue3    = ImageColorAllocate($im, 140, 140, 255);
  $im_yellow3  = ImageColorAllocate($im, 255, 255, 140);

  ImageFilledRectangle($im, 0, 0, ($image_xsize-1), 15, $im_black);
  ImageFilledRectangle($im, 0, 16, ($image_xsize-1), ($image_ysize-1), $im_bg);
  if ($image_border == 1) ImageRectangle($im, 0, 0, ($image_xsize-1), ($image_ysize-1), $im_black);
  ImageString($im, 3, 5, 1, $image_title, $im_white);
  ImageString($im, 2, ($image_xsize-ImageFontWidth(2)*strlen($image_date)-5), 1, $image_date, $im_gray4);
  ImageStringUp($im, 1, ($image_xsize-10), ($image_ysize-5), CALSTATS_VERSION, $im_gray2);

  // Place links
  if ($link_show == 1) {
    foreach ($a_link as $i=>$v) {
      if ($v[1] == 0) {
	$v[1] = 1;
	$from=strtok($i,';'); //extract from from index
	$to=strtok(';'); //extract to

	$i_l1 = return_ap_index($a_comp, $from);
	$i_l2 = return_ap_index($a_comp, $to);

	placeLN($im, $a_comp[$i_l1][3], $a_comp[$i_l1][4], $a_comp[$i_l2][3], $a_comp[$i_l2][4], $v[0], $image_bgc);
      }
    }
  }

  // Place computers
    foreach ($a_comp as $v) {
    $comp_tmp1 = return_ip_state($a_state, 0, $v[1]);
    $comp_tmp2 = return_ip_state($a_state, 1, $v[1]);
    if ($v[1] == "127.0.0.1") {
      $comp_tmp1 = 2;
      $comp_tmp2 = 0;
    }

    PlaceAP($im, $v[3], $v[4], $v[0], $v[1], $v[2], $comp_tmp1, $comp_tmp2);
  }

  // Draw legend
  if ( ($legend_show == 1) && ($image_legend != 0) ) {
    $l_num_string = 0;
    for ($i1 = 0; ($i1 < $legend_num); $i1++) {
      if (strlen($a_legend[$i1][1]) > $l_num_string) {
	$l_num_string = strlen($a_legend[$i1][1]);
      }
    }

    if ( ($image_legend == 1) || ($image_legend == 3) ) { $l_xloc = 12; } else { $l_xloc = ($image_xsize-37-($l_num_string*ImageFontWidth(2))); }
    if ( ($image_legend == 1) || ($image_legend == 2) ) { $l_yloc = 27; } else { $l_yloc = ($image_ysize-12-($legend_num*15)); }

    ImageRectangle($im, $l_xloc, ($l_yloc-2), ($l_xloc+25+($l_num_string*ImageFontWidth(2))), ($l_yloc + ($legend_num*15) + 1), $im_gray3);
    ImageFilledRectangle($im, ($l_xloc+1), ($l_yloc-1), ($l_xloc+24+($l_num_string*ImageFontWidth(2))), ($l_yloc + ($legend_num*15)), $im_gray5);

    for ($i1 = 0; ($i1 < $legend_num); $i1++) {
      placeLN($im, ($l_xloc+5), ($l_yloc+7), ($l_xloc+15), ($l_yloc+7), return_link_str1($a_legend[$i1][0]), 2);
      ImageString($im, 2, ($l_xloc+20), ($l_yloc), $a_legend[$i1][1], $im_black);
      $l_yloc+=15;
    }
  }

  ImagePng ($im,$output_filename,9);
  ImageDestroy($im);
  usleep(1000);
}

if (substr($argv[1],-1)!="/") { //single file
  makeMap($argv[1],$argv[2]);
} else { //directory
  $files = scandir($argv[1]);
  foreach ($files as $f) {
    if (substr($f,-4)=="comp") makeMap($argv[1].substr($f,0,-5),$argv[2].substr($f,0,-5));
  }
}
?>
