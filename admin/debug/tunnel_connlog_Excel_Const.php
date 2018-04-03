<?php
/****************
 *Validated on Jul-13,2016,  Excel output OK
 * required absolute file path of LOG_FILE to execute crontab
 * log file round up every year
 * add debugadmin for delete function
 * changed for setOnly feature
 * set MAX process tunnel to 19
 * set Auto GenerateExcel 1st each Month 5AM
 * sed -i -e "\$a5 5 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel" /etc/crontab       
 * sed -i -e "\$a5 8 1 * * root /usr/bin/php5 \x22/var/www/qlync_admin/plugin/debug/tunnel_connlog_Excel_Const.php\x22  GenerateExcel rtmp" /etc/crontab 
 *Writer: JinHo, Chang   
*****************/
include("/var/www/qlync_admin/header.php");

if ($oem == "T04"){
define("LOG_FILE","tunnel_connection_t04.log");
define("LOG_FILE2","rtmpd_connection_t04.log");
define("LOG_FILE3","stream_connection_t04.log");
}else{
define("LOG_FILE","tunnel_connection_const.log");
define("LOG_FILE2","rtmpd_connection_const.log");
define("LOG_FILE3","stream_connection_const.log");
}
 
require('/var/www/qlync_admin/plugin/debug/_excel.inc');
define("OEMID",$oem);
define("KEY_SITE","系統名稱");//Site
if (!empty($RPICAPP_USER_PWD[$oem][3]))  define("KEY_SITENAME",$RPICAPP_USER_PWD[$oem][3]);//$SITE_SERVER
else  define("KEY_SITENAME",$oem);//$SITE_SERVER
define("KEY_SERVERNUM","連線伺服器數"); //Server#
define("KEY_REP_MONTH","報告月份"); //Month
define("KEY_DATE","日期"); // Date
define("KEY_REPORT","報告"); // Report
define("KEY_MAX_REC_CAM","最多攝影機上線數"); // Max Recording Camera
define("KEY_MAX_VIEW_CAM","最多觀看人次"); //Max Viewing Camera

#Authentication ############
if (!isset($argv)){ //web browse required permission
  include("/var/www/qlync_admin/menu.php");
  if (!$_SESSION["ID_admin_qlync"]) exit(1); //only god admin can see
}else{ //crontab running GenerateExcel
  if (!isset($argv[1])) die("No parameter Input (GenerateExcel)!!\n");
  $_REQUEST["step"]="set_log_file";
  $_REQUEST["btnAction1"]=$argv[1];
  $CurrentDate = new DateTime();
  if (!isset($argv[2])){
      $_REQUEST["logfile"] = getPrevLogName(LOG_FILE.".".$CurrentDate->format('Ym'),".log");
      //delete previous temp excel file pattern
      $REPORT_DATE = ltrim($_REQUEST["logfile"],LOG_FILE); //201708010000
      $prevDate = strtotime($REPORT_DATE.' -1 month');
      $REPORT_DATE = date('Ym',$prevDate);      
//echo OEMID."_".substr($REPORT_DATE,0,6)."-".substr($REPORT_DATE,0,4)."-".substr($REPORT_DATE,4,2)."\n";
      cleanPrevExcel(OEMID."_".substr($REPORT_DATE,0,6)."-".substr($REPORT_DATE,0,4)."-".substr($REPORT_DATE,4,2));//clean T05_201708-2017-08 excel
  }else{ //run rtmpd
      $_REQUEST["logfile"] = getPrevLogName(LOG_FILE2.".".$CurrentDate->format('Ym'),".log");
  }
  
  
}
############  Authentication Section End
if($_REQUEST["step"]=="set_log_file")
{
  $SEL_LOG_FILE = $_REQUEST["logfile"];
  if($_REQUEST["btnAction1"]=="Read"){
        if (!file_exists(LOG_PATH.$SEL_LOG_FILE))
          $msgErr .= "<font color=blue>".$SEL_LOG_FILE." Read FAIL! </font><br>\n";
        else
          $msgErr = readLog(LOG_PATH.$SEL_LOG_FILE);
  }else if($_REQUEST["btnAction1"]=="Delete"){
     if ((preg_match("/[-:]/",$SEL_LOG_FILE)>0) and (strpos($SEL_LOG_FILE,"report")!== FALSE))   
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else if (strpos($SEL_LOG_FILE,".log.")!== FALSE)
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else $result = "FAIL";
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$SEL_LOG_FILE." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$SEL_LOG_FILE." delete FAIL!</font><br>\n";   

  }else if($_REQUEST["btnAction1"]=="GenerateExcel"){
  //global value
    if (file_exists(LOG_PATH.$SEL_LOG_FILE)) include_once(LOG_PATH.$SEL_LOG_FILE);
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
      $msgErr =  createStateTable($rtmp_log,$uid_list, LOG_FILE2,$SEL_LOG_FILE)." Created.";

    }else if (strpos($SEL_LOG_FILE,LOG_FILE3)!== FALSE){

      if (isset($STREAM_UID) )
        $streamArray[0] = $STREAM_UID;
      if (isset($STREAM_UID1) )
        $streamArray[1] = $STREAM_UID1;
      if (isset($STREAM_UID2) )
        $streamArray[2] = $STREAM_UID2;
      if (!isset($streamArray)){  
          $sql="select uid from isat.stream_server";
          sql($sql,$result_list,$num_list,0);
          for($j=0;$j<$num_list;$j++)
          {
                fetch($db_list,$result_list,$j,0);
                $uid_list[]=$db_list["uid"];
          }
      }else    $uid_list = $streamArray;
      $filename = createExcel($stream_log,$uid_list, LOG_FILE3,$SEL_LOG_FILE);

    }else if (strpos($SEL_LOG_FILE,LOG_FILE)!== FALSE){
      if (isset($TUNNEL_UID) )
           $tunnelArray[0] = $TUNNEL_UID;
      if (isset($TUNNEL_UID1) )
          $tunnelArray[1] = $TUNNEL_UID1;
      if (isset($TUNNEL_UID2) )
          $tunnelArray[2] = $TUNNEL_UID2;
      if (!isset($tunnelArray)){  
          $sql="select uid from isat.tunnel_server where purpose ='TUNNEL'";
          sql($sql,$result_list,$num_list,0);
          for($j=0;$j<$num_list;$j++)
          {
                fetch($db_list,$result_list,$j,0);
                $uid_list[]=$db_list["uid"];
          }
      }else   $uid_list = $tunnelArray;
      if (!isset($tunnel_log)) die("LOG FILE Not Exist!! {$SEL_LOG_FILE} \n");
      $filename = createExcel($tunnel_log,$uid_list, LOG_FILE,$SEL_LOG_FILE);
    }
  }//generateExcel if
}else if($_REQUEST["step"]=="set_excel_file")
{
  if($_REQUEST["btnAction1"]=="Delete"){
        $result = deleteFile(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$_REQUEST["excelfile"]." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$_REQUEST["excelfile"]." delete FAIL!</font><br>\n";   
  }else if($_REQUEST["btnAction1"]=="Read"){
        $msgErr = readExcel(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
  }else if ($_REQUEST["btnAction1"]=="Download"){
        $msgErr = "<div style='display:none;'><iframe id='frmDld' src='https://".$_SERVER['SERVER_NAME'].":8080".WDL_REPORT_FOLDER.$_REQUEST["excelfile"]."'></iframe></div>";
  } 
  
}

if (isset($argv[1])) die("========{$argv[1]}==========\n");

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
      }else if ((preg_match("/[-]/",$files[$i])>0) and (strpos($files[$i],"report")!== FALSE))
      {
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
<script type="text/javascript">
function downloadfile(filename){
 var basedir = "<?php echo WDL_REPORT_FOLDER; ?>";
 window.location=basedir + filename;
}
</script>
<font color=black>
<?php if ($oem == "T04"){?>
Required <a href='/plugin/taipei/tunnel_connlog_tpe.php?setOnly'>tunnel</a>&nbsp;&nbsp;
<a href='/plugin/taipei/rtmpd_connlog_tpe.php?setOnly'>rtmpd</a>&nbsp;&nbsp;
<?php }else{?>
Required <a href='tunnel_connlog_Const.php?setOnly'>tunnel</a>&nbsp;&nbsp;
<a href='rtmpd_connlog_Const.php?setOnly'>rtmpd</a>&nbsp;&nbsp;
<?php }?>
<a href='_connchartConst.php' target=_blank>Chart</a>
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
<!--input type=submit name=btnAction1 value="Download"-->
<input type=button name=btnAction1 value="Download" onclick="javascript:downloadfile(this.form.excelfile.options[this.form.excelfile.selectedIndex].text);">
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
if (isset($msgErr))
  echo $msgErr;
?> 
</font>
</body>
</html>
