<?php
/****************
 *Validated on Dec-20,2016
 *Get Video file from stream server URL  
 *Writer: JinHo, Chang
*****************/
function httpError($code, $message) {
	header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
	echo $message . "\n";
	exit(0);
}

if ($_REQUEST['URL']=="") 
  httpError('404 Not Found', 'cannot find the recording file');

$path="/var/tmp/tmp.mp4";
if ( file_exists($path) ){//file lock check
  if (flock(($fp=fopen($path, "r+")), LOCK_EX)){
    exec("wget ".urldecode($_REQUEST['URL'])." -O {$path}",$r);
  }else{//file in use
    $iTag=rand (0,9 );
    $path="/var/tmp/tmp{$iTag}.mp4";
    if (flock(($fp=fopen($path, "r+")), LOCK_EX)){
      exec("wget ".urldecode($_REQUEST['URL'])." -O {$path}",$r);
    }else{
      httpError('403 Forbidden', 'download file has been locked.'.print_r($r,true));
    }
  }
}else exec("wget ".urldecode($_REQUEST['URL'])." -O {$path}",$r);
if ( !file_exists($path) ){
  httpError('404 Not Found', 'cannot find the recording file.'.print_r($r,true));
}
//$name = basename($path);
$name = basename(urldecode($_REQUEST['URL']));
header('Content-Type: video/mp4');
header('Content-Disposition: attachment; filename=' . $name);
header('Content-Length: ' . filesize($path));
@readfile($path);
flock($fp, LOCK_UN);
fclose($fp);
exit();
?> 

