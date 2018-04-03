<?php
require_once('./include/global.php');//mount point
require_once('./include/index_title.php');//$oem_style_list  
//for key validation
require_once("rpic.inc"); //$RPICAPP_USER_PWD
 
function httpError($code, $message) {
    header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
    echo $message . "\n";
    exit(0);
}
function download($path)
{
        $path = preg_replace( '/^\/vod/', 
                RECORDING_STORAGE, 
                $path );
                
        if ( !file_exists($path) ) {
            httpError('404 Not Found', 'cannot find the recording file');
        }
        $name = basename($path);
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Length: ' . filesize($path));
        @readfile($path);
        exit();

}

if  ($_REQUEST['key']!=$RPICAPP_USER_PWD[OEM_ID]){
  httpError('403 Forbidden', 'You do not have the permission to access this file');
}
if (isset($_REQUEST['path']))
  download($_REQUEST['path']);

?>