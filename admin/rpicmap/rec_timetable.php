<?php
/****************
 *Validated on Sep-19,2017
 * Excel(2003) Editor for RPIC Time table
 * Excel matrix = [Col][Row]
 * Add debugadmin email parameter       
 *Writer: JinHo, Chang   
*****************/
#require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("DEBUG_FLAG",false); //true/false :false will only print out

ini_set('memory_limit','64M'); //if server >20, memory will be over 64M
if (!file_exists("/var/www/qlync_admin/plugin/billing/Classes/PHPExcel.php")) die("PHPExcel Not exit!!\n");
require_once("/var/www/qlync_admin/plugin/billing/Classes/PHPExcel.php");
require_once("/var/www/qlync_admin/plugin/billing/Classes/PHPExcel/IOFactory.php");
require_once("/var/www/qlync_admin/plugin/billing/Classes/PHPExcel/Writer/Excel5.php");
define("EXCEL_EXT",".xls");
define("EXCEL_TYPE","Excel5");

#define("DL_REPORT_FOLDER","/var/www/qlync_admin/plugin/billing/log/");
#define("WDL_REPORT_FOLDER","/plugin/billing/log/");
define("DL_REPORT_FOLDER","/var/www/qlync_admin/plugin/rpic/log/");
define("WDL_REPORT_FOLDER","/plugin/rpic/log/");

define("OEMID",$oem);
define("MAX_COL","N"); //col M=10cam, use < 
define("TEMPLATE_FILE","rec_template.xls");
define("KEY_CREATE","產生報表");//GenerateExcel
define("KEY_VIEW","線上瀏覽");//Read
define("KEY_EDIT","編輯");//Read
define("KEY_SAVE","存檔");//Read
define("KEY_DOWNLOAD","下載");//Download

#Authentication Section
if (DEBUG_FLAG){
  if (!isset($_SESSION["Email"]) ){
  $_SESSION["Email"] = "allez";
  $_SESSION["Contact"] = "allez";
  }
}else  if (!isset($_SESSION["Email"]) ) exit();

//$_SESSION["Contact"] => bind_account
//$_SESSION["Email"] => admin_user
$thisAdminUser = $_SESSION["Email"];
$thisAdminContact = $_SESSION["Contact"];
if ($_SESSION["ID_admin_qlync"]) //god admin can view
{
  $thisAdminUser = $_REQUEST['email'];
  $thisAdminContact = $_REQUEST['email'];
}
 
############  Authentication Section End
if (DEBUG_FLAG) var_dump($_REQUEST);

//use $msgErr to print out
if($_REQUEST["step"]=="set_excel_file")
{
  if($_REQUEST["btnAction1"]==KEY_CREATE){
      if ($thisAdminContact!=""){
          $loadReport = copyExcelTemplate($thisAdminUser);
          if (is_null($loadReport))  $msgErr .= "<font color=red>無法建立報表!</font><br>\n";
          else{  
            $msgErr .= "<font color=blue>建立報表{$loadReport}</font><br>\n";
            writeMACHeader($loadReport,$thisAdminContact);
            $msgErr .= printExcel($loadReport);
          }
      }else $msgErr .= "<font color=red>無綁定帳號!</font><br>\n"; 
    
  }else if($_REQUEST["btnAction1"]=="Backup"){
    if (!is_null($loadReport = renameExcel(getExcel($thisAdminUser))) )
       $msgErr = "<font color=blue>備份{$loadReport}</font><br>\n";
    if ($thisAdminContact!=""){
          $loadReport = copyExcelTemplate($thisAdminUser);
          if (is_null($loadReport))  $msgErr .= "<font color=red>無法建立報表!</font><br>\n";
          else{  
            $msgErr .= "<font color=blue>建立報表{$loadReport}</font><br>\n";
            writeMACHeader($loadReport,$thisAdminContact);
            $msgErr .= printExcel($loadReport);
          }
    }else $msgErr .= "<font color=red>無綁定帳號!</font><br>\n";
  }else if($_REQUEST["btnAction1"]=="DeleteBackup"){
      if (removeFile(DL_REPORT_FOLDER . $_REQUEST['excelfile'])){
        $msgErr = "<font color=blue>".$_REQUEST['excelfile']." delete SUCCESS!</font><br>\n";
        if (file_exists(DL_REPORT_FOLDER . $_REQUEST['excelfile']))
          $msgErr .= "<font color=red>".$_REQUEST['excelfile']." delete FAIL!</font><br>\n";
      }
  }else if($_REQUEST["btnAction1"]==KEY_VIEW){ //js exitEdit make this useless
    $loadReport = getExcel($thisAdminUser);
    if ($loadReport!="")  $msgErr = printExcel($loadReport);
  
  }else if($_REQUEST["btnAction1"]==KEY_EDIT){
    $loadReport = getExcel($thisAdminUser);
    $msgErr = printExcel($loadReport,true);
  }else if($_REQUEST["btnAction1"]==KEY_SAVE){
    //parse html to excel
    $loadReport = $_REQUEST['loadReport']; 
    updateExcel($loadReport,$_REQUEST['pageexcel']);
    $msgErr = printExcel($loadReport);
    //$_REQUEST['loadReport'] = ""; //return to view state
  }else if($_REQUEST["btnAction1"]=="Delete"){
    $loadReport = getExcel($thisAdminUser);
    if (removeFile(DL_REPORT_FOLDER . $loadReport)){
      $msgErr = "<font color=blue>".$loadReport." delete SUCCESS!</font><br>\n";
      if (!file_exists(DL_REPORT_FOLDER . $loadReport))  $loadReport = "";
    }else $msgErr = "<font color=red>".$loadReport." delete FAIL!</font><br>\n";
  }else{//view
  $loadReport = getExcel($thisAdminUser);
  if ($loadReport!="")
    $msgErr = printExcel($loadReport);
  }
}else{//default load table
  $loadReport = getExcel($thisAdminUser);
  if ($loadReport!="")
    $msgErr = printExcel($loadReport);
}

function removeFile($path)
{
  if (!file_exists($path)) return false;
	$res = shell_exec("rm {$path}");
	$res= rtrim($res, "\n");
	if ($res !="") return false;
	return true;
}
function renameExcel($filename)
{
  $newfilename = "BACKUP_".$filename;
  $res = shell_exec("mv ".DL_REPORT_FOLDER . $filename." ".DL_REPORT_FOLDER.$newfilename);
  $res= rtrim($res, "\n");
  if ($res !="") return null;
  return $newfilename; 
}
function copyExcelTemplate($account)
{
  $CurrentDate = new DateTime();
  $filename =OEMID . "_{$account}-" . $CurrentDate->format('Y-m-d_H:i:s').EXCEL_EXT; 
  $filepath =  DL_REPORT_FOLDER . $filename; 
  $res = shell_exec("cp ".TEMPLATE_FILE." {$filepath}");
  chmod("{$filepath}",0777);
  $res= rtrim($res, "\n");
  if ($res !="") return null;
  return $filename;
}

function updateExcel($filename, $matrixArr)
{
  if ($filename == "") return false;
  $filepath = DL_REPORT_FOLDER . $filename;
  if (!file_exists($filepath)) return false; //no excel
  $inputFileType = PHPExcel_IOFactory::identify($filepath);
  $objReader = PHPExcel_IOFactory::createReader($inputFileType);
  $objPHPExcel   = $objReader->load($filepath);
  $objPHPExcel->setActiveSheetIndex(0);
  foreach ($matrixArr as $colkey=>$row) {
    foreach ($row as $key => $val) {
      if (!is_null($val)){
        //if (DEBUG_FLAG) echo "[{$colkey}][{$key}]={$val}\n";
        //$objPHPExcel->getActiveSheet()->SetCellValue("{$colkey}{$key}",$val);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("{$colkey}{$key}", $val,PHPExcel_Cell_DataType::TYPE_STRING);
      }
    }
  }
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, EXCEL_TYPE);
  $objWriter->save($filepath);
  return true;
}
function incrChar($val, $increment = 2)
{
    for ($i = 1; $i <= $increment; $i++) {
        $val++;
    }
    return $val;
}

function writeMACHeader($filename, $bind_account)
{
  $camArray = array();
  getCamArray($bind_account, $camArray);
  $excelArray = array();
  $colkey='D';
  $maxcolkey=incrChar($colkey,sizeof($camArray)+1);
  $rowkey =2;
  $index = 0;
  for ($i=$colkey;$i< $maxcolkey ;$i++){
    $excelArray[$i][$rowkey] = $camArray[$index];
    $index ++;      
  }
  return updateExcel($filename,$excelArray);
}

function getExcel($admin_account)
{
  $listrule = OEMID."_{$admin_account}-";
    $files = scandir(DL_REPORT_FOLDER, SCANDIR_SORT_DESCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if ( (preg_match("/^{$listrule}/",$files[$i])) and (strpos($files[$i], EXCEL_EXT) !== FALSE) ){
        return $files[$i];
      }
    }//for
    return "";
}
function getCamArray($bind_account,&$myArr){
    $sql = "select name, mac_addr from isat.query_info where user_name = '{$bind_account}' group by mac_addr";
    sql($sql,$result,$num,0);
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      array_push($myArr,$arr['mac_addr']);
    }
}
/*
function StringToDateTime($string) {
global $time_zone;
	$date_string = substr($string, 0, 4) . '-' .
			substr($string, 4, 2) . '-' .
			substr($string, 6, 2) . ' ' .
			substr($string, 8, 2) . ':' .
			substr($string, 10, 2) . ':' .
			substr($string, 12, 2);
  $utcdate= new DateTime($date_string, new DateTimeZone('UTC'));
  $utcdate->setTimezone( new DateTimeZone($time_zone) ); 
  return $utcdate->format("Y-m-d H:i:s"); 
}
*/
function String2UTCStr($str){
  global $time_zone;
  $utctime = gmdate('YmdHis',strtotime($str));
  if ($utctime) return $utctime;
  return 0;
}
function getStartVideoPath($mac,$start,$end)
{
  if (!strtotime($start)) return "";
  if (!strtotime($end)) return "";
  $sql = "select start, end, path from isat.recording_list where device_uid like '%{$mac}' and CAST(start AS UNSIGNED) >= ".String2UTCStr($start)." order by start asc limit 1";
  sql($sql,$result,$num,0);
  if ($num > 0){
    fetch($arr,$result,0,0);
    if ( (int)$arr['end'] <= (int)gmdate('YmdHis',strtotime($end.' +1 hour')) ) //video time is not end within one hour
      return $arr['path'];
  } 
  /*$tmpstr = "";
  for ($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $tmpstr.=  $arr['path'].";";
    //if (DEBUG_FLAG) echo $arr['path']."\n";
  }
  return rtrim($tmpstr,";");*/
  return "";
}
function getVideoDate($input){
  $time = strtotime($input);
  if ($time) return date('Ymd',$time); 
  return false;
}
function printExcel($filename,$bEdit=false)
{
  global $thisAdminUser, $thisAdminContact;
  $filepath = DL_REPORT_FOLDER. $filename;
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel   = $objReader->load($filepath);
     $lastrow  = $objPHPExcel->setActiveSheetIndex(0)->getHighestDataRow();  //getHighestDataRow / getHighestRow
     $lastcol  = $objPHPExcel->setActiveSheetIndex(0)->getHighestDataColumn();
if (DEBUG_FLAG) echo "row={$lastrow};col={$lastcol}";
     $html .="<table border=1 style='color:black;'>\n<tr><td colspan='13'>雲端即時影像監控系統施工錄影時程紀錄表</td></tr>\n";
     $html .="<tr><td colspan='3'>攝影機MAC</td>";
     for($j='D';$j<=$lastcol;$j++){ //mac print out
      if ( ($mac=$objPHPExcel->getActiveSheet()->getCell("{$j}2")->getValue()) != "")
        $html.= "<td><a target='player' href='/plugin/debug/playback_list.php?debugadmin&user={$thisAdminContact}&mac={$mac}' onclick=\"javascript: openPlayer(this.href);return false;\">{$mac}</a></td>";
        //$html .="<td><a href=\"javascript: window.open('/plugin/debug/playback_list.php?debugadmin&user={$_SESSION['Contact']}&mac={$mac}','',config='height=650,width=600');\">{$mac}</a></td>";  //IE not working
      else $html .="<td></td>";
     }
     $html .="</tr>\n"; //add camera number td
     $html .="<tr><td colspan='3'>施工期間</td><td colspan='10' rowspan='2'>施工地點</td></tr>\n";
     $html .="<tr><td width=80px>日期</td><td width=80px>開始時間</td><td width=80px>結束時間</td></tr>\n"; 
     $html .="<tr><td colspan='13'></td></tr>\n";
  if ($bEdit)  $lastrow += 5   ; //add 5 lines for new editing
  for($i=5;$i<=$lastrow;$i++){ //$lastrow
    $html.= "<tr>\n";
    $tmpDateS="";$tmpDateE="";
    for($j='A';$j<MAX_COL;$j++){ //$lastcol, character
      if ( ($excelvalue=$objPHPExcel->getActiveSheet()->getCell("{$j}{$i}")->getValue()) != ""){//has value
          if ($bEdit){//edit mode
            $html.= "<td align=center><input ";
            if (($j=='A') or ($j=='B') or ($j=='C') ) //date smaller
              $html.= "size=6";
            $html.= "type='text' name='pageexcel[{$j}][{$i}]' value='{$excelvalue}' onchange='javascript:trackChange(\"change\");'></td>\n";
          }else{//view only, playback_list timestamp check?
            //$html.= "<td>{$value}</td>\n";
            $html.= "<td>";
            if (($j=='A') or ($j=='B') or ($j=='C') ){//datetime
              if ($j=='A') {
                $date=$excelvalue;$tmpDateS= "{$excelvalue} ";$tmpDateE= "{$excelvalue} ";
                $datefolder=getVideoDate($date);
              }else if ($j=='B') {$tmpDateS.= "{$excelvalue}";if (!strtotime($tmpDateS)) $tmpDateS="";}
              else if ($j=='C') {$tmpDateE.= "{$excelvalue}";if (!strtotime($tmpDateE)) $tmpDateE="";}
              $html.= "{$excelvalue}"; //datetime data  
            }else{//col >C, location, link to video
              $mac =  $objPHPExcel->getActiveSheet()->getCell("{$j}2")->getValue();
              //if (DEBUG_FLAG) echo "date={$date};mac={$mac}\n";
              //test if can get video start point
              if (($url=getStartVideoPath($mac,$tmpDateS,$tmpDateE))!=""){
                  $html.= "<a target='player' href='/plugin/debug/playback_list.php?debugadmin&user={$thisAdminContact}&mac={$mac}&url={$url}' onclick=\"javascript: openPlayer(this.href);return false;\">{$excelvalue}</a></td>";
                  //$html.="<a href=\"javascript: window.open('/plugin/debug/playback_list.php?debugadmin&user={$_SESSION['Contact']}&mac={$mac}&url={$url}','',config='height=650,width=600');\">{$excelvalue}</a>";
              }else if ($datefolder){
                //video exist??
                if (getStartVideoPath($mac,$datefolder,$tmpDateE)!="")
                  $html.= "<a target='player' href='/plugin/debug/playback_list.php?debugadmin&user={$thisAdminContact}&mac={$mac}&datefolder={$datefolder}' onclick=\"javascript: openPlayer(this.href);return false;\">{$excelvalue}</a></td>";
                  //$html.= "<a href=\"javascript: window.open('/plugin/debug/playback_list.php?debugadmin&user={$_SESSION['Contact']}&mac={$mac}&datefolder={$datefolder}','',config='height=650,width=600');\">{$excelvalue}</a>"; //IE fail
                else  $html.= "{$excelvalue}";
              }else  $html.= "{$excelvalue}";
              //if (DEBUG_FLAG) echo "start={$tmpDateS};end={$tmpDateE}\n";
            }
            
            $html.="</td>\n";
          }
      }else{//no value 
          if ($bEdit)  $html.= "<td align=center><input type='text' name='pageexcel[{$j}][{$i}]' value='' onchange='javascript:trackChange(\"change\");'></td>\n";
          else $html.= "<td></td>\n";
      }
      if ($j == $lastcol) break; //leave after print current column
    }
    $tmpDateS="";$tmpDateE="";//set timestamp back
    $html.= "</tr>\n";
  }
  $html.="</table>";
  if ($bEdit)  $html.="<input type='hidden' name='loadReport' value='{$filename}'>";
  return $html;                 
}

function selectExcelFile($tagName, $listrule="")
{
  global $thisAdminUser, $thisAdminContact;
    //$listrule = OEMID."_{$_SESSION['Email']}-";
    if ($listrule=="")
        $listrule = "BACKUP_".OEMID."_{$thisAdminUser}-[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])_[0-9]{2}:[0-9]{2}:[0-9]{2}";
    $html = "<select name='{$tagName}'>";
    $files = scandir(DL_REPORT_FOLDER, SCANDIR_SORT_DESCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if ( (preg_match("/^{$listrule}/",$files[$i])) and (strpos($files[$i], EXCEL_EXT) !== FALSE) ){
          $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for
  $html .= "</select>\n";   //add table end
  echo $html;
}
?>

<!--html>
<head>
</head>
<body-->
<!--script src="../user_log/js/jquery-1.11.1.min.js"></script>
$("#input-id").on("change keyup paste", function(){
})
--->
<script type="text/javascript">
var isExcelChanged = false;
function trackChange(type){
  //alert(type);
  isExcelChanged = true;
}
function exitEdit(){
  if (isExcelChanged){
    if (confirm("表格資料已經修改, 不儲存離開?"))
      return true;
    else return false;
  }
  return true;
}
function downloadfile(filename){
 var basedir = "<?php echo WDL_REPORT_FOLDER; ?>";
 if (filename=="") return false;
 window.location=basedir + filename;
}
function openPlayer(url){
  window.open(url,'player','height=650,width=600');
}
</script>
<style type="text/css">
.buttonView{
	background-color: #99FF99;
	/*font-weight:bold;*/
}
.buttonEdit{
	background-color: #0066FF; /*Aquamarine*/
	color:white;
	width: 100px;
	text-align: center;
	vertical-align: middle;
	font-weight:bold;
}
</style>
<table><tr>
<td>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=hidden name=step value='set_excel_file'>
<?php
  if ( $loadReport == ""){
?>
<input type=submit name=btnAction1  class='buttonEdit' value="<?php echo KEY_CREATE;?>">
<?php
  }else{
    echo "<input type=button name=btnAction1 class='buttonView' value='".KEY_VIEW."' onclick=\"javascript:if (exitEdit()) this.form.submit();\">&nbsp;&nbsp;&nbsp;";
    if ( $_REQUEST['btnAction1']!= KEY_EDIT )
      echo "<input type=submit name=btnAction1 class='buttonEdit' value='".KEY_EDIT."'>";
    else
      echo "<input type=submit name=btnAction1 class='buttonEdit' value='".KEY_SAVE."'>";
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type=button name=btnAction1 class='buttonView' value="<?php echo KEY_DOWNLOAD;?>" onclick="javascript:downloadfile('<?php echo $loadReport;?>');">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
  if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=submit name=btnAction1 style='background-color:red;color:white;' value="Delete">&nbsp;&nbsp;
<input type=submit name=btnAction1 style='background-color:red;color:white;' value="Backup">
<?php selectExcelFile("excelfile");?>
<input type=button name=btnAction1 value="DownloadBackup" onclick="javascript:downloadfile(this.form.excelfile.options[this.form.excelfile.selectedIndex].text);">&nbsp;&nbsp
<input type=submit name=btnAction1 style='background-color:red;color:white;' value="DeleteBackup">
<input type=hidden name=debugadmin value='1'>
<?php
      if (isset($_REQUEST["email"]))  echo "<input type=hidden name=email value='{$_REQUEST["email"]}'>";
    } //debugadmin 
  }//excelExist
?>
</td>
</tr></table>
<?php
if (isset($msgErr))
  echo $msgErr;
?>
</form> 
</font>
</body>
</html>