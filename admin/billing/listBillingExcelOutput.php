<?php
/****************
 *Validated on Apr-21,2015,  Excel output OK
 * required absolute file path of LOG_FILE to execute crontab
 * log file round up every year  
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
include  'Classes/PHPExcel/Writer/Excel2007.php';
include("/var/www/qlync_admin/header.php");
require_once ("/var/www/qlync_admin/doc/config.php");
require_once 'dbutil.php';
if(!file_exists(HOME_PATH."/".LOG_FILE))
{
    echo "CANNOT OPEN LOG FILE!";
    exec(" echo '' > ".HOME_PATH."/".LOG_FILE);
    chmod(HOME_PATH."/".LOG_FILE,0777);        
}
include_once(HOME_PATH."/".LOG_FILE);
if (sizeof($billing_data)>0){ 
  end($billing_data[$oem]);
  $LASTYEAR =substr(key($billing_data[$oem]),0,4);
  if (date("Y")!=$LASTYEAR){
      exec("mv ".HOME_PATH."/".LOG_FILE." ".HOME_PATH."/".LOG_FILE.".".$LASTYEAR);
      exec(" echo '' > ".HOME_PATH."/".LOG_FILE);
      chmod(HOME_PATH."/".LOG_FILE,0777);
  }
} 

//global value  $ServerArray from dbutil.php
$CurrentDate = new DateTime();

$QUERY_TOTAL =0;
$CAM_ADDED_TOTAL =0;
$BILL_DATE = $CurrentDate->format('Y-m-d H:i:s'). "(" . date_default_timezone_get() . ")";
$BILL_SERVER = $oem." / ".getSiteName($oem);

 $services = query();
 if (!is_null($services)){
 $filepath = createExcel($services);
 
 $fplog=fopen(HOME_PATH."/".LOG_FILE,"a+");
 $fwrite = fwrite($fplog,"<?\$billing_data[\"{$oem}\"][\"{$CurrentDate->format('Ymd')}\"][\"lic\"]={$QUERY_TOTAL};?>\n");
 if ($fwrite === false) echo "fwrite All lic error\n"; else echo "fwrite All license\n";
 $fwrite = fwrite($fplog,"<?\$billing_data[\"{$oem}\"][\"{$CurrentDate->format('Ymd')}\"][\"binded\"]={$CAM_ADDED_TOTAL};?>\n");
 if ($fwrite === false) echo "fwrite binded lic error\n"; else echo "fwrite binded license\n";
 fclose($fplog);

  }
?>

<html>
<head>
</head>
<body>
<font color=#000000>
<?php
if (file_exists($filepath)){
  echo "Excel Report {$filepath} created!!\n";
}else echo "Error Output\n";
?>
</font>
</body>
</html>
