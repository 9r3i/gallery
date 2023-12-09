<?php
$file='open-sans-origin.css';
$output='open-sans.css';
$content=file_get_contents($file);
preg_match_all('/url\(([^\)]+)\)/',$content,$akur);
//print_r($akur[1]);
/* download */
foreach($akur[1] as $ak){
  //echo basename($ak)."\r\n";
  //$get=file_get_contents($ak);
  //$put=file_put_contents('fonts/'.basename($ak),$get);
}
/* rename */
$rename=preg_replace_callback('/url\(([^\)]+)\)/',function($m){
  $basename=basename($m[1]);
  return "url('fonts/{$basename}')";
},$content);
print_r($rename);
echo file_put_contents($output,$rename);

