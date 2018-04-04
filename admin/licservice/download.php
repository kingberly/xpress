<?php
/****************
 *Validated on Nov-27,2014,  
 * To download as csv files
 *Writer: JinHo, Chang   
*****************/
require_once 'dbutil.php';
header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename='.$_REQUEST["file"]);
header('Pragma: no-cache');
//will return number or bytes in the end
//echo @readfile(substr(DL_PATH,1,4).$_REQUEST["file"]);
echo @file_get_contents(substr(DL_PATH,1,4).$_REQUEST["file"]);
?>
