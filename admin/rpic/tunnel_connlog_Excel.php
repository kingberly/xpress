<?php
/****************
 *Validated on Oct-16,2015,  Excel output OK
 * required absolute file path of LOG_FILE to execute crontab
 * log file round up every year
 * add debugadmin for delete function
 * changed for setOnly feature
 * set MAX process tunnel to 17     
 *Writer: JinHo, Chang   
*****************/
#require_once ("/var/www/qlync_admin/doc/config.php");
include("../../header.php");
include("../../menu.php");
#Authentication Section same as Tech support right
if (!$_SESSION["ID_admin_qlync"]) exit(1); //only god admin can see 
############  Authentication Section End
define("EXCEL_EXT",".xlsx");
ini_set('display_errors', 'Off'); //used from coding
ini_set('memory_limit','64M');
require_once '../billing/Classes/PHPExcel.php';
require_once '../billing/Classes/PHPExcel/IOFactory.php';
include  '../billing/Classes/PHPExcel/Writer/Excel2007.php';
define("DL_REPORT_FOLDER","/var/www/qlync_admin/plugin/billing/log/");
define("WDL_REPORT_FOLDER","/plugin/billing/log/");
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","tunnel_connection_t04.log");
define("LOG_FILE2","rtmpd_connection_t04.log");
define("LOG_FILE3","stream_connection_t04.log");

if($_REQUEST["step"]=="set_log_file")
{
  $SEL_LOG_FILE = $_REQUEST["logfile"];
  if($_REQUEST["btnAction1"]=="Read"){
        if (!file_exists(LOG_PATH.$SEL_LOG_FILE))
          $msgErr .= "<font color=blue>".$SEL_LOG_FILE." Read FAIL! </font><br>\n";
        else
          $msgErr = readLog(LOG_PATH.$SEL_LOG_FILE);
  }else if($_REQUEST["btnAction1"]=="Delete"){
     if ((preg_match("/[-:]/",$SEL_LOG_FILE)>0) and (strpos($SEL_LOG_FILE,".report")!== FALSE))   
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else if (strpos($SEL_LOG_FILE,".log.")!== FALSE)
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else $result = "FAIL";
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$SEL_LOG_FILE." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$SEL_LOG_FILE." delete FAIL!</font><br>\n";   

  }else if($_REQUEST["btnAction1"]=="GenerateExcel"){
    if (file_exists(LOG_PATH.$SEL_LOG_FILE))
      include_once(LOG_PATH.$SEL_LOG_FILE); 
  //global value
    if (strpos($SEL_LOG_FILE,LOG_FILE2)!== FALSE)
    {//rtmp
      if (!isset($rtmpArray)){
        $sql="select uid from isat.tunnel_server where purpose ='RTMPD'";
        sql($sql,$result_list,$num_list,0);
        for($j=0;$j<$num_list;$j++)
        {
              fetch($db_list,$result_list,$j,0);
              $uid_list[]=$db_list["uid"];
        }
      }else
         $uid_list = $rtmpArray;
      $msgErr =  createStateTable($rtmp_log,$uid_list, LOG_FILE2)." Created.";

    }else if (strpos($SEL_LOG_FILE,LOG_FILE3)!== FALSE){
    }else if (strpos($SEL_LOG_FILE,LOG_FILE)!== FALSE){
      if (!isset($tunnelArray)){  
          $sql="select uid from isat.tunnel_server where purpose ='TUNNEL'";
          sql($sql,$result_list,$num_list,0);
          for($j=0;$j<$num_list;$j++)
          {
                fetch($db_list,$result_list,$j,0);
                $uid_list[]=$db_list["uid"];
          }
      }else
          $uid_list = $tunnelArray;
     //local testing
     /*
      $uid_list = array("d27634c0947d4964ba896980463d9c29",
      "17ce31da14484161a8320d5eeb839bdd",
      "1967a4c7f8bb46978675f297e57ac0a7",
      "1c2583f10af441b8b1467c8318746b96",
      "582eae43327e4cea8aeac191360eeb1f",
      "651b17e1d2f64a718280069bc3e2bba2",
      "663731a3a0ca425f851178b86b0ea3fb",
      "93914ac579054408ba2fa3c88257b73c"); 
      */
      $filename = createExcel($tunnel_log,$uid_list, LOG_FILE);
    }
  }//generateExcel if
}else if($_REQUEST["step"]=="set_excel_file")
{
  if($_REQUEST["btnAction1"]=="Delete"){
    if (strpos($_REQUEST["excelfile"],".report")!== FALSE)
      $result = deleteFile(LOG_PATH.$_REQUEST["excelfile"]);  
    else
      $result = deleteFile(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$_REQUEST["excelfile"]." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$_REQUEST["excelfile"]." delete FAIL!</font><br>\n";   
  }else if($_REQUEST["btnAction1"]=="Read"){
      if (strpos($_REQUEST["excelfile"],".report")!== FALSE)
        $msgErr = readLog(LOG_PATH.$_REQUEST["excelfile"]);
      else
        $msgErr = readExcel(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
        
  }else if ($_REQUEST["btnAction1"]=="Download"){
        $msgErr = "<div style='display:none;'><iframe id='frmDld' src='https://".$_SERVER['SERVER_NAME'].":8080".WDL_REPORT_FOLDER.$_REQUEST["excelfile"]."'></iframe></div>";
  } 
  
}

function selectLogFile($tagName, $matchfile)
{
    $html = "<select name='{$tagName}'>";
    $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if  ((strpos($files[$i],LOG_FILE)!== FALSE) or (strpos($files[$i],LOG_FILE2)!== FALSE)
           or (strpos($files[$i],LOG_FILE3)!== FALSE) )
      {
            if ($files[$i] == $matchfile)
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      /*}else if ((preg_match("/[-]/",$files[$i])>0) and (strpos($files[$i],".report")!== FALSE))
      {
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      */
      } 
    }//for
  $html .= "</select>\n";   //add table end
  echo $html;
}

function selectExcelFile($tagName, $matchfile)
{
    global $oem;
    $html = "<select name='{$tagName}'>";
    $files = scandir(DL_REPORT_FOLDER, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      //if ((preg_match("/[-:]/",$files[$i])>0) and  (strpos($files[$i],EXCEL_EXT)!== FALSE)   ){
    if ( (preg_match("/^[A-Z]{1}[0-9]{2}_[0-9]{6}-[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])_[0-9]{2}:[0-9]{2}:[0-9]{2}/",$files[$i])) and (strpos($files[$i], EXCEL_EXT) !== FALSE) ){
            if ($files[$i] == $matchfile)
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for
    $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if ((preg_match("/[-:]/",$files[$i])>0) and (strpos($files[$i],".report")!== FALSE)) {
            if ($files[$i] == $matchfile)
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for

  $html .= "</select>\n";   //add table end
  echo $html;
}
function deleteFile($filepath)
{
    if(!file_exists($filepath)) return "FAIL";
    return exec("rm -rf ".$filepath); 
}

function createExcel($log, $uid_list,$logfilename)
{
    error_reporting(E_ALL);
    global $oem,$SEL_LOG_FILE;
    $CurrentDate = new DateTime();
    //tunnel_connection_t04.log.201510010030  ,T04_201510-2015-10-01_01-48-25.xls
    $REPORT_DATE = substr ($SEL_LOG_FILE,strpos($SEL_LOG_FILE,$logfilename)+strlen($logfilename)+1,8); //20151001
    if ($REPORT_DATE=="") $REPORT_DATE=$CurrentDate->format('Ym'); 
    else{
      $REPORT_DATE = substr($REPORT_DATE,0,4)."-".substr($REPORT_DATE,4,2)."-".substr($REPORT_DATE,6,2);
      $prevDate = strtotime($REPORT_DATE.' -1 day');
      $REPORT_DATE = date('Ym',$prevDate);
    }
    $SITE_SERVER = $oem." / Taipei";
    $filename = "{$oem}_" . $REPORT_DATE ."-".$CurrentDate->format('Y-m-d_H:i:s').".xls";     
    $filepath = DL_REPORT_FOLDER. $filename; 
//echo $filepath;
    $objPHPExcel = new PHPExcel();
//echo date('H:i:s') . " Set properties\n";
    $objPHPExcel->getProperties()->setCreator($oem);
    $objPHPExcel->getProperties()->setTitle("{$oem} Connection Log");
    $objPHPExcel->getProperties()->setSubject("{$oem} Connection Log");
    $objPHPExcel->getProperties()->setDescription("{$oem} Connection Log, generated using PHP classes.");
//echo date('H:i:s') . " Add some data\n";
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle("Report {$oem}-{$REPORT_DATE}");
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);

    //$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('A1:A256')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('B1:B256')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle('C1:C256')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Site');
    $objPHPExcel->getActiveSheet()->SetCellValue('B2', $SITE_SERVER);
    $objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Month');
    $objPHPExcel->getActiveSheet()->SetCellValue('B3', $REPORT_DATE);

    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Date');
    $objPHPExcel->getActiveSheet()->getStyle('A5')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', 'Max Recording Camera');
    $objPHPExcel->getActiveSheet()->getStyle('B5')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('C5', 'Max Viewing Camera');
    $objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    //$objPHPExcel->getActiveSheet()->getStyle('C5')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'fac090'))));
    $excelIndex = 6;
    $header = "TUID";
/*    $deviceArray = array($header => array("","","","","","","",""));
    $viewerArray = array($header => array("","","","","","","",""));
    for ($i=0;$i<count($uid_list);$i++)
    {
        $deviceArray[$header][$i] = $uid_list[$i];
        $viewerArray[$header][$i] = $uid_list[$i];
    }
*/    //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
unset ($deviceArray);
unset ($viewerArray);
$deviceArray = array();
$viewerArray = array();
    for ($i=0;$i<count($uid_list);$i++)
    {
        foreach($log[$uid_list[$i]] as $datekey=>$values){
            //check if date array exist
            if (! isset($deviceArray[$datekey])) {
                if (count($uid_list)==1)
                  $dateArray1 = array($datekey => array(0));
                else if (count($uid_list)==2)
                  $dateArray1 = array($datekey => array(0,0));
                else if (count($uid_list)==3)
                  $dateArray1 = array($datekey => array(0,0,0));
                else if (count($uid_list)==4)
                  $dateArray1 = array($datekey => array(0,0,0,0));
                else if (count($uid_list)==5)
                  $dateArray1 = array($datekey => array(0,0,0,0,0));
                else if (count($uid_list)==6)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0));
                else if (count($uid_list)==7)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0));
                else if (count($uid_list)==8)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0));
                else if (count($uid_list)==9)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==10)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==11)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==12)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==13)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==14)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==15)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==16)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==17) //17
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==18) //18
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else //19
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));

                $deviceArray = $deviceArray+ $dateArray1;
                $viewerArray = $viewerArray+ $dateArray1;
            }
            //scan all value
            $deviceArray[$datekey][$i] = $values['device'];
            $viewerArray[$datekey][$i] = $values['viewer'];  
/* ///////////test for pause
            print_r($deviceArray);
            echo "pause, type Y";
            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'Y'){
            echo "ABORTING!\n";
            exit;
*/
        }
    }
    //calculate sum of every timestamp, max of day sum
    unset ($dayArray);
    $dayArray = array(0);
    $dayKey = "";
    $arrayIndex = 0;
    $fpdevice=fopen(LOG_PATH.$SEL_LOG_FILE.".log","w+");
    ksort($deviceArray);
     foreach($deviceArray as $key=>$values){
        if ($key==$header) continue;
        if ($dayKey != substr($key,0,8)) {
          if ($dayKey != "")
          {
              $objPHPExcel->getActiveSheet()->SetCellValue("A{$excelIndex}", $dayKey);
              $objPHPExcel->getActiveSheet()->SetCellValue("B{$excelIndex}", max($dayArray));
              $excelIndex++;
              fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
          }
          $dayKey =  substr($key,0,8);
          unset ($dayArray);
          $dayArray = array(0);
        }
        fwrite($fpdevice,$key.",\t");
        $sum = 0;
        for ($i=0;$i<count($values);$i++){
            fwrite($fpdevice,$values[$i].",\t");
            $sum += $values[$i];
        }
          $dayArray = array_merge($dayArray, array($sum));

        fwrite($fpdevice,"sum=".$sum."\n");
        if ((count($deviceArray)-1)==$arrayIndex){
              $objPHPExcel->getActiveSheet()->SetCellValue("A{$excelIndex}", $dayKey);
              $objPHPExcel->getActiveSheet()->SetCellValue("B{$excelIndex}", max($dayArray));
              fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
        }
        $arrayIndex++;
     }
    unset ($dayArray);
    $dayArray = array(0);
    $dayKey = "";
    $excelIndex = 6;
    $arrayIndex=0;
    fwrite($fpdevice,"\n======Viewer Table=====\n");
    ksort($viewerArray);
     foreach($viewerArray as $key=>$values){
        if ($key==$header) continue;
        if ($dayKey != substr($key,0,8)) {
          if($dayKey != "")
              if ($objPHPExcel->getActiveSheet()->getCell("A{$excelIndex}")->getValue() == $dayKey) //validate
              {
                  $objPHPExcel->getActiveSheet()->SetCellValue("C{$excelIndex}", max($dayArray));
                  $excelIndex++;
              }else  //fine correct index
                 fwrite($fpdevice,"{$dayKey} cannot match to deviceArray index\n");

          fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
          $dayKey =  substr($key,0,8);
          unset ($dayArray);
          $dayArray = array(0);
        }
        fwrite($fpdevice,$key.",\t");
        $sum = 0;
        for ($i=0;$i<count($values);$i++){
            fwrite($fpdevice,$values[$i].",\t");
            $sum += $values[$i];
        }
          $dayArray = array_merge($dayArray, array($sum));

        fwrite($fpdevice,"sum=".$sum."\n");
        if ((count($viewerArray)-1)==$arrayIndex){
              if ($objPHPExcel->getActiveSheet()->getCell("A{$excelIndex}")->getValue() == $dayKey) //validate              
                  $objPHPExcel->getActiveSheet()->SetCellValue("C{$excelIndex}", max($dayArray));
              else fwrite($fpdevice,"{$dayKey} cannot match to viewerArray index\n");
              fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
        }
        $arrayIndex++;
     }     
    fclose($fpdevice);

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($filepath);
//echo date('H:i:s') . " Done writing file.\r\n";
    return $filename;              
}

function createStateTable($log, $uid_list, $logfilename)
{
    global $oem,$SEL_LOG_FILE;
    $CurrentDate = new DateTime();
    //tunnel_connection_t04.log.201510010030  ,T04_201510-2015-10-01_01-48-25.xls
    $REPORT_DATE = substr ($SEL_LOG_FILE,strpos($SEL_LOG_FILE,$logfilename)+strlen($logfilename)+1,8); //20151001
    if ($REPORT_DATE=="") $REPORT_DATE=$CurrentDate->format('Ym'); 
    else{
      $REPORT_DATE = substr($REPORT_DATE,0,4)."-".substr($REPORT_DATE,4,2)."-".substr($REPORT_DATE,6,2);
      $prevDate = strtotime($REPORT_DATE.' -1 day');
      $REPORT_DATE = date('Ym',$prevDate);
    }
    $SITE_SERVER = $oem." / Taipei";
    $filename = "{$oem}_" . $REPORT_DATE ."-".$CurrentDate->format('Y-m-d_H:i:s').".report";     
    $filepath = LOG_PATH. $filename; 
    $fpReport=fopen($filepath,"w+");

    $header = "TUID";
//    $deviceArray = array($header => array("","","","","","","",""));
    fwrite($fpReport,$header."\t");
    for ($i=0;$i<count($uid_list);$i++)
    {
//        $deviceArray[$header][$i] = $uid_list[$i];
          fwrite($fpReport,$uid_list[$i]."\t");
    }
    fwrite($fpReport,"\n");
    fwrite($fpReport,"Date\tMax Recording Camera\n");
    //$rtmp_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
unset ($deviceArray);
$deviceArray = array();
    for ($i=0;$i<count($uid_list);$i++)
    {
        foreach($log[$uid_list[$i]] as $datekey=>$values){
            //check if date array exist
            if (! isset($deviceArray[$datekey])) {
                if (count($uid_list)==1)
                  $dateArray1 = array($datekey => array(0));
                else if (count($uid_list)==2)
                  $dateArray1 = array($datekey => array(0,0));
                else if (count($uid_list)==3)
                  $dateArray1 = array($datekey => array(0,0,0));
                else if (count($uid_list)==4)
                  $dateArray1 = array($datekey => array(0,0,0,0));
                else if (count($uid_list)==5)
                  $dateArray1 = array($datekey => array(0,0,0,0,0));
                else if (count($uid_list)==6)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0));
                else if (count($uid_list)==7)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0));
                else if (count($uid_list)==8)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0));
                else if (count($uid_list)==9)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==10)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==11)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==12)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==13)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==14)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==15)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==16)
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==17) //17
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else if (count($uid_list)==18) //18
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                else //19
                  $dateArray1 = array($datekey => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0));
                $deviceArray = $deviceArray+ $dateArray1;
            }
            //scan all value
            $deviceArray[$datekey][$i] = $values['device'];
        }
    }
    //calculate sum of every timestamp, max of day sum
    unset ($dayArray);
    $dayArray = array(0);
    $dayKey = "";
    $arrayIndex = 0;
    $fpdevice=fopen(LOG_PATH.$SEL_LOG_FILE.".log","w+");
    ksort($deviceArray);
     foreach($deviceArray as $key=>$values){
        if ($key==$header) continue;
        if ($dayKey != substr($key,0,8)) {
          if ($dayKey != "")
          {
              fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
              fwrite($fpReport,"{$dayKey}\t".max($dayArray)."\n");
          }
          $dayKey =  substr($key,0,8);
          unset ($dayArray);
          $dayArray = array(0);
        }
        fwrite($fpdevice,$key.",\t");
        $sum = 0;
        for ($i=0;$i<count($values);$i++){
            fwrite($fpdevice,$values[$i].",\t");
            $sum += $values[$i];
        }
          $dayArray = array_merge($dayArray, array($sum));

        fwrite($fpdevice,"sum=".$sum."\n");
        if ((count($deviceArray)-1)==$arrayIndex){
              fwrite($fpdevice,"{$dayKey} MaxValue=".max($dayArray)."\n");
              fwrite($fpReport,"{$dayKey}\t".max($dayArray)."\n");
        }
        $arrayIndex++;
     }

    fclose($fpdevice);
    fclose($fpReport);
    return $filename;              
}

function readExcel($filepath)
{
    error_reporting(E_ALL);
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel   = $objReader->load($filepath);
     $last_num  = $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
     
     $html ="<table>\n<tr>";
     $html.= "<td>". $objPHPExcel->getActiveSheet()->getCell("A2")->getValue()."</td>\n";
     $html.= "<td>". $objPHPExcel->getActiveSheet()->getCell("B2")->getValue()."</td><td></td></tr>\n"; 
     $html.= "<tr><td>". $objPHPExcel->getActiveSheet()->getCell("A3")->getValue()."</td>\n";
     $html.= "<td>". $objPHPExcel->getActiveSheet()->getCell("B3")->getValue()."</td><td></td></tr>\n"; 
     $html.="<tr></tr><tr bgcolor=gray><th>Date</th><th>Max Recording Camera</th><th>Max Viewing Camera</th></tr>\n";
     for($i=6;$i<=$last_num;$i++)
     {
        if ($objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue() == "") break;
          $html.= "<tr><td align=center>". $objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue()."</td>\n";
          $html.= "<td align=center>". $objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue()."</td>\n";
          $html.= "<td align=center>". $objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue()."</td></tr>\n";
     }
    $html.="</table>";
    return $html;                 
}
function readLog($filepath)
{
    $html = file_get_contents($filepath);
    $html = str_replace("<?"," ",$html);
    $html = "<pre>".$html."</pre>";
    return $html;
}
?>

<!--html>
<head>
</head>
<body-->
<font color=black>
Required <a href='tunnel_connlog_tpe.php?setOnly'>tunnel</a>&nbsp;&nbsp;
<a href='rtmpd_connlog_tpe.php?setOnly'>rtmpd</a>&nbsp;&nbsp;
<a href='stream_connlog_tpe.php?setOnly'>stream</a>&nbsp;&nbsp;
<br>
<?php
if ((isset($filename)) and (file_exists(DL_REPORT_FOLDER. $filename))){
?>
 <a href='<?php echo "https://".$_SERVER['SERVER_NAME'].":8080".WDL_REPORT_FOLDER.$filename;?>' download>Download <?php echo $filename;?></a>
<?php
}
?>
<table><tr>
<td>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectLogFile("logfile",$SEL_LOG_FILE);?>
<input type=hidden name=step value='set_log_file'>
<input type=submit name=btnAction1 value="Read">
<input type=submit name=btnAction1 value="GenerateExcel">
<?php
  if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=submit name=btnAction1 value="Delete">
<input type=hidden name=debugadmin value='1'>
<?php
  }
?>
</form>
</td><td>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectExcelFile("excelfile",$_REQUEST["excelfile"]);?>
<input type=hidden name=step value='set_excel_file'>
<input type=submit name=btnAction1 value="Read">
<input type=submit name=btnAction1 value="Download">
<?php
  if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=submit name=btnAction1 value="Delete">
<input type=hidden name=debugadmin value='1'>
<?php
  }
?>
</form>
</td>
</tr></table>
<?php
if ($msgErr !="")
  echo $msgErr;
?> 
</font>
</body>
</html>