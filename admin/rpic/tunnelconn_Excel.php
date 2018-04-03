<?php
/****************
 *Validated on Aug-15,2017
 * set MAX process tunnel to 19     
 *Writer: JinHo, Chang   
*****************/
#require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("DEBUG_FLAG",false); //true/false :false will only print out

 
require('/var/www/qlync_admin/plugin/debug/_excel.inc');
define("OEMID",$oem);
define("KEY_GEXCEL","產生即時報表");//GenerateExcel
define("KEY_READ","線上瀏覽");//Read
define("KEY_DOWNLOAD","下載");//Download
define("KEY_DELETE","Delete");//Delete
define("KEY_SITE","系統名稱");//Site
if (!empty($RPICAPP_USER_PWD[$oem][1]))  define("KEY_SITENAME",$RPICAPP_USER_PWD[$oem][1]);//$SITE_SERVER
else  define("KEY_SITENAME",$oem);//$SITE_SERVER
define("KEY_SERVERNUM","連線伺服器數"); //Server#
define("KEY_REP_MONTH","報告月份"); //Month
define("KEY_DATE","日期"); // Date
define("KEY_REPORT","報告"); // Report
define("KEY_MAX_REC_CAM","最多攝影機上線數"); // Max Recording Camera
define("KEY_MAX_VIEW_CAM","最多觀看人次"); //Max Viewing Camera


if ($oem == "T04"){
define("LOG_FILE","tunnel_connection_t04.log");
define("LOG_FILE2","rtmpd_connection_t04.log");
}else{
define("LOG_FILE","tunnel_connection_const.log");
define("LOG_FILE2","rtmpd_connection_const.log");
}


#Authentication Section
if  ($_REQUEST['key']!=$RPICAPP_USER_PWD[$oem][0])
  if (!isset($_SESSION["Email"]) ) exit(); 
############  Authentication Section End
if (DEBUG_FLAG) var_dump($_REQUEST);

//use $msgErr to print out
if($_REQUEST["step"]=="set_log_file")
{
  $SEL_LOG_FILE = $_REQUEST["logfile"];
  if($_REQUEST["btnAction1"]==KEY_DELETE){
     if ((preg_match("/[-:]/",$SEL_LOG_FILE)>0) and (strpos($SEL_LOG_FILE,"report")!== FALSE))   
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else if (strpos($SEL_LOG_FILE,".log.")!== FALSE)
        $result = deleteFile(LOG_PATH.$SEL_LOG_FILE);
      else $result = "FAIL";
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$SEL_LOG_FILE." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$SEL_LOG_FILE." delete FAIL!</font><br>\n";   

  }else if($_REQUEST["btnAction1"]==KEY_GEXCEL){
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

      $filename = createExcel($tunnel_log,$uid_list, LOG_FILE,$SEL_LOG_FILE);
      if ($filename !=""){
        $msgErr ="<font color=blue><b>".KEY_READ." {$filename}</b></font><br>";
        $msgErr .= readExcel(DL_REPORT_FOLDER.$filename);
      }
    }
  }//generateExcel if
}else if($_REQUEST["step"]=="set_excel_file")
{
  if($_REQUEST["btnAction1"]=="Delete"){
        $result = deleteFile(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
        if ($result!="FAIL"){
            $msgErr .= "<font color=blue>".$_REQUEST["excelfile"]." delete SUCCESS! ".$result."</font><br>\n";
        }else $msgErr .= "<font color=red>".$_REQUEST["excelfile"]." delete FAIL!</font><br>\n";   
  }else if($_REQUEST["btnAction1"]==KEY_READ){
        $msgErr ="<font color=blue><b>".KEY_READ." {$_REQUEST["excelfile"]}</b></font><br>";
        $msgErr .= readExcel(DL_REPORT_FOLDER.$_REQUEST["excelfile"]);
  }else if ($_REQUEST["btnAction1"]==KEY_DOWNLOAD){
        $msgErr = "<div style='display:none;'><iframe id='frmDld' src='https://".$_SERVER['SERVER_NAME'].":8080".WDL_REPORT_FOLDER.$_REQUEST["excelfile"]."'></iframe></div>";
  } 
  
}

function selectLogFile($tagName, $matchfile)
{
    $html = "<select name='{$tagName}'>";
    $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if ( $files[$i] == LOG_FILE )  
        //if  ((strpos($files[$i],LOG_FILE)!== FALSE) or (strpos($files[$i],LOG_FILE2)!== FALSE)  )
      {
            if ($files[$i] == $matchfile)
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
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
<table><tr>
<td>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectLogFile("logfile",$SEL_LOG_FILE);?>
<input type=hidden name=step value='set_log_file'>
<input type=submit name=btnAction1 value="<?php echo KEY_GEXCEL;?>">
<?php
	if (isset($_REQUEST['key']))
	echo "<input type=hidden name=key id=key value='{$_REQUEST['key']}'>";
?>
</form>
</td>
<td>&nbsp;&nbsp;&nbsp;</td>
<td>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectExcelFile("excelfile",$_REQUEST["excelfile"]);?>
<input type=hidden name=step value='set_excel_file'>
<input type=submit name=btnAction1 value="<?php echo KEY_READ;?>">
<!--input type=submit name=btnAction1 value="<?php echo KEY_DOWNLOAD;?>"-->
<input type=button name=btnAction1 value="<?php echo KEY_DOWNLOAD;?>" onclick="javascript:downloadfile(this.form.excelfile.options[this.form.excelfile.selectedIndex].text);">
<?php
  if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=submit name=btnAction1 value="<?php echo KEY_DELETE;?>">
<input type=hidden name=debugadmin value='1'>
<?php
  }
	if (isset($_REQUEST['key']))
	echo "<input type=hidden name=key id=key value='{$_REQUEST['key']}'>";
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