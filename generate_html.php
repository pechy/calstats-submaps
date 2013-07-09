<?php
$main=$argv[1];
$datapath=$argv[2];
if (substr($datapath,-1)!="/") $datapath.="/";

print '<html>
<head>
    <title>'.$main.' - calstats</title>
    <META HTTP-EQUIV="Refresh" CONTENT="300">
    <META HTTP-EQUIV="Cache-Control" content="no-cache">
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<style>
html, body {margin: 0; padding: 0;}
</style>
</head>
<body>';
echo "<img src=\"$main.png\">\n";
function get_dimensions_from_config($filename) {
  $file_in = fopen($filename . ".config", "r");
    if (!$file_in) { exit("could not open ".$filename . ".config"); }
  $fin_line=rtrim(fgets($file_in, 1024));
  strtok($fin_line, ";");
  $img_width=strtok(";");
  $img_height=strtok(";");
  fclose($file_in);
  return array($img_width,$img_height);
}
function calc_submap_position($main_width,$main_height,$submap,$item_x,$item_y,$item_width,$item_height) { //try to place submap to not excced the size of main map
  $dim_submap=get_dimensions_from_config($submap);
  $x=$item_x+$item_width+5; //left of...
  $y=$item_y+5;
  if ($x+$dim_submap[0]<=$main_width &&  $y+$dim_submap[1]<=$main_height) return array($x,$y);
  $x=$item_x-(($dim_submap[0]-$item_width)/2); //top of...
  $y=$item_y-10-$dim_submap[1];
  if ($x+$dim_submap[0]<=$main_width &&  $y-$dim_submap[1]>0) return array($x,$y);
  $x=$item_x-$dim_submap[0]-10; //right of...
  $y=$item_y+5;
  if ($x+$dim_submap[0]<=$main_width &&  $y+$dim_submap[1]<=$main_height) return array($x,$y);
  $x=$item_x-(($dim_submap[0]-$item_width)/2); //down of...
  $y=$item_y+5+$item_height;
  if ($x+$dim_submap[0]<=$main_width &&  $y+$dim_submap[1]<=$main_height) return array($x,$y);
}

// --- Load computer data - begin -----------------------------------------
$file_in = fopen($datapath . "/".$main.".comp", "r");
  if (!$file_in) { exit; }

while (!feof($file_in)) {
  $fin_line=rtrim(fgets($file_in, 1024));
  if (!strlen($fin_line)) continue; //skip empty line
  $name=strtok($fin_line, ";");
  $a_comp[$name][0] = strtok(";"); // ip
  strtok(";"); // type
  $a_comp[$name][1] = strtok(";"); // x1
  $a_comp[$name][2] = strtok(";"); // y1
}

fclose($file_in);
// --- Load computer data - end -------------------------------------------
$arr=get_dimensions_from_config($datapath.$main);
$img_width=$arr[0];
$img_height=$arr[1];
  

// --- Load submap data - begin -----------------------------------------
$file_in = fopen($datapath.$main.".submap", "r");
  if (!$file_in) { exit; }

while (!feof($file_in)) {
  $fin_line=rtrim(fgets($file_in, 1024));
  if (!strlen($fin_line)) continue; //skip empty line
  $name=strtok($fin_line, ";");
  $submap = strtok(";");
  if (!array_key_exists($name,$a_comp)) exit("Computer $name doesn't exists");
  $ap_xsize = 100;
  $ap_ysize = 35;
  $ap_name_size = ImageFontWidth(3)*strlen($name);
  $ap_ip_size = ImageFontWidth(2)*strlen($a_comp[$name][0]);
  $ap_lat_size = ImageFontWidth(2)+6;

  if (($ap_name_size + $ap_lat_size + 10) > ($ap_xsize))  $ap_xsize = $ap_name_size + $ap_lat_size+10;
  $pos=calc_submap_position($img_width,$img_height,$datapath.$submap,round($a_comp[$name][1]-$ap_xsize/2),round($a_comp[$name][2]-$ap_ysize/2),$ap_xsize,$ap_ysize);

  echo "<div id='$submap' name='$submap' style='position: absolute; left: ".$pos[0]."px; top:".$pos[1]."px; z-index:3; visibility: hidden;'><img src='$submap.png'></div>\n";
  echo "<div style='position: absolute; left: ".round($a_comp[$name][1]-$ap_xsize/2)."px; top:".round($a_comp[$name][2]-$ap_ysize/2)."px; width:{$ap_xsize}px; height:{$ap_ysize}px; z-index:2; visibility: visible;' onmouseover='$submap.style.visibility=\"visible\"' onmouseout='$submap.style.visibility=\"hidden\"'></div>\n";
}

fclose($file_in);
// --- Load submap data - end -------------------------------------------


echo "</body></html>\n";
?>