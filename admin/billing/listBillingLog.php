<?php
/****************
 *Validated on Feb-26,2015,  
 * Billing.log output from empty file or resume from last date with absolute file path
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
include  'Classes/PHPExcel/Writer/Excel2007.php';
//global value oem
require_once ("/var/www/qlync_admin/doc/config.php");
require_once 'dbutil.php';
if(!file_exists(HOME_PATH."/".LOG_FILE)){
    echo "CANNOT OPEN LOG FILE!";
    exec(" echo '' > ".HOME_PATH."/".LOG_FILE);
    chmod(HOME_PATH."/".LOG_FILE,0777);               
}else{
  echo "Resume latest Log";
  include_once("billing.log");
}
$END_DATE = "20150101";
if (sizeof($billing_data)>0){ 
  end($billing_data[$oem]);
  $END_DATE =key($billing_data[$oem]);  
}   
//scandir(DL_PATH, SCANDIR_SORT_DESCENDING), SCANDIR_SORT_DESCENDING=1
$files = scandir(HOME_PATH."/".DL_PATH, SCANDIR_SORT_ASCENDING);
readAllExcel($files, $END_DATE);


function readAllExcel($files, $END_DATE)
{
error_reporting(E_ALL);
    global $oem;
  $fplog=fopen(HOME_PATH."/".LOG_FILE,"a+");
  $LOG_DATE="";
  //$file = X01_201502241626.xls
  foreach ($files as $file){
    $fdate = substr($file,4,8);
    if ((int)$fdate>(int)$END_DATE) echo "fdate{$fdate} is latest\n";
    else echo "ENE date is the latest\n";
    if ((($file!="..") or ($file!=".")) and ((int)$fdate>(int)$END_DATE)) {
      echo "write log\n";
      $filepath= HOME_PATH."/".DL_PATH.$file;
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel 	= $objReader->load($filepath);
	$last_num	= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

     $BILL_DATE = $objPHPExcel->getActiveSheet()->getCell("B3")->getValue();
     $NEXT_LOG_DATE=substr($BILL_DATE,0,4).substr($BILL_DATE,5,2).substr($BILL_DATE,8,2);
     if (($NEXT_LOG_DATE!="") and ($LOG_DATE!=$NEXT_LOG_DATE)){
       $LOG_DATE=substr($BILL_DATE,0,4).substr($BILL_DATE,5,2).substr($BILL_DATE,8,2);
       $QUERY_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B4")->getValue();
       $CAM_ADDED_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B5")->getValue();
       fwrite($fplog,"<?\$billing_data[\"{$oem}\"][\"{$LOG_DATE}\"][\"lic\"]={$QUERY_TOTAL};?>\n");
       fwrite($fplog,"<?\$billing_data[\"{$oem}\"][\"{$LOG_DATE}\"][\"binded\"]={$CAM_ADDED_TOTAL};?>\n");
      }
      unset($objPHPExcel);
      unset($objReader);     
    }//if file
  }//foreach file
  fclose($fplog);
}

?>

<html>
<head>
</head>
<body>
<font color=#000000>
Billing LOG Generated after <?php echo $END_DATE;?>.
</font>
</body>
</html>
