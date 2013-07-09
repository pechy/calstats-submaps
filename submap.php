<?php
function sum_state($filename) {
  $file_in = fopen($filename, "r");
  if (!$file_in) { exit; }
  while (!feof($file_in)) {
  $fin_line=rtrim(fgets($file_in, 1024));
  if(!strlen($fin_line)) continue; //skip empty lines
    strtok($fin_line, ";");
    $status=strtok(";");
    if($status==0) {
      fclose($file_in);
      return 0;
    }
  }
  fclose($file_in);
  return 1;
}
if ($argc!=2) die("usage: php submap.php datadir\n");
$datadir = $argv[1];
//TODO: add/remove / in the end
$files = scandir($datadir);
$state=array();
foreach ($files as $f) {
  if (substr($f,-6)!='submap') continue;
  $submap=array();
  $file_in = fopen($datadir.$f, "r");
  if (!$file_in) { exit; }
  while (!feof($file_in)) {
    $fin_line=rtrim(fgets($file_in, 1024));
    if(!strlen($fin_line)) continue; //skip empty lines
    $tmp = strtok($fin_line, ";");
    $submap[$tmp]=strtok(";");
    $state[$submap[$tmp]]=sum_state($datadir.$submap[$tmp].".state");
  }
  $file_in = fopen($datadir.substr($f,0,-6)."comp", "r");
  if (!$file_in) { exit; }
  $ip_map=array();
  while (!feof($file_in)) {
    $fin_line=rtrim(fgets($file_in, 1024));
    if(!strlen($fin_line)) continue; //skip empty lines
    $name = strtok($fin_line, ";");
    if (!array_key_exists($name,$submap)) continue;
    $ip=strtok(";");
    $ip_map[$ip]=$name;
  }
  fclose($file_in);
  $cont="";
  $file_state = fopen($datadir.substr($f,0,-6)."state", "r");
  if (!$file_state) { exit; }
  while (!feof($file_state)) {
    $fin_line=rtrim(fgets($file_state, 1024));
    if(!strlen($fin_line)) continue; //skip empty lines
    $ip = strtok($fin_line, ";");
    if (array_key_exists($ip,$ip_map)) {
      $status=strtok(";");
      if ($state!=0) {
	if(!$state[$submap[$ip_map[$ip]]]) $status=3;
      }
      $rtt=strtok(";");
      $fin_line="$ip;$status;$rtt";
    }
    $cont.=$fin_line."\n";
  }
  file_put_contents($datadir.substr($f,0,-6)."state",$cont);
}
?>