<?php
/****************
 *Validated on Oct-15,2015,
 * log stream ffmpeg number, cpu, mem, loadaverage in /tmp/stream_status.log
 * 2 stream IP supported
 * filter server not available string error issue
 * automatically wrap log files every monday midnight    
 * use setOnly to reduce database query 
 *Writer: JinHo, Chang
*****************/
ini_set('memory_limit', '64M');
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","stream_status.log");
define("MAXLIST",20);
define("MAXTID",2);  //3 = tunnel2
//*/3 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/stream_connlog.php"
//$TUNNEL_UID='5284b02873234e1d86e101e8fc3f5b22';
if (file_exists(LOG_PATH.LOG_FILE))
  include_once(LOG_PATH.LOG_FILE);
else{
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);  
}
$crontab_status = exec("grep '".$_SERVER['PHP_SELF']."' /etc/crontab"); 
$cur_date=date("YmdHi");  
$fplog=fopen(LOG_PATH.LOG_FILE,"a+");

$flag_writelog=true;
if ( isset($_REQUEST["setOnly"]) ){
  $flag_writelog=false;
}

if(date("NHi") == "10000") //monday, 0:00
{//change a new file every Monday
   fclose($fplog);
    exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);
    $fplog=fopen(LOG_PATH.LOG_FILE,"a+");    
    $fwrite = fwrite($fplog,"<?\$STREAM_UID='".$STREAM_UID."';?>\n");
    if ($STREAM_UID1!="")
        $fwrite = fwrite($fplog,"<?\$STREAM_UID1='".$STREAM_UID1."';?>\n");
}

if($_REQUEST["step"]=="set_stream_uid")
{ //write specific tunnel_uid
  if ($_REQUEST["tid"]=="0")
  {//start a new file
      fclose($fplog);
      exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
      exec(" echo '' > ".LOG_PATH.LOG_FILE);
      chmod(LOG_PATH.LOG_FILE,0777);
      $fplog=fopen(LOG_PATH.LOG_FILE,"a+");    
      $fwrite = fwrite($fplog,"<?\$STREAM_UID='".$_REQUEST['stream_uid']."';?>\n");
  }else
      $fwrite = fwrite($fplog,"<?\$STREAM_UID".$_REQUEST['tid']."='".$_REQUEST['stream_uid']."';?>\n");
  echo "set uid ".$_REQUEST['tid']." ".$_REQUEST['stream_uid'];
}

if (($STREAM_UID!="") and $flag_writelog)
{
    //echo "check {$STREAM_UID}\n";
    $s_status = exec ("python /home/ivedasuper/admin/check/getStreamingCount.py ".$STREAM_UID);
    $s_loadavg = exec ("python /home/ivedasuper/admin/check/getLoadAvg.py ".$STREAM_UID);
    if (!ctype_digit($s_status))  {
         $s_status = "0";
         $s_loadavg = "0";
         $s_cpu = "0";
         $s_mem = "0";
    }else if (!is_numeric($s_loadavg)) {
         $s_status = "0";
         $s_loadavg = "0";
         $s_cpu = "0";
         $s_mem = "0";
    }else{
        $s_cpu = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$STREAM_UID." CPU");
        $s_mem = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$STREAM_UID." MEM");
    }
    //echo "python command OK :{$s_status}:{$s_loadavg}:{$s_cpu}:{$s_mem}\n";
      $cur_date=date("YmdHi");
      $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"status\"]='{$s_status}';?>\n");
      $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"loadavg\"]='{$s_loadavg}';?>\n");
        $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"cpu\"]='{$s_cpu}';?>\n");
        $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"mem\"]='{$s_mem}';?>\n");
    
}
if (($STREAM_UID1!="") and $flag_writelog)
{
    $s_status = exec ("python /home/ivedasuper/admin/check/getStreamingCount.py ".$STREAM_UID1);
    $s_loadavg = exec ("python /home/ivedasuper/admin/check/getLoadAvg.py ".$STREAM_UID1);
    if (!ctype_digit($s_status))  {
         $s_status = "0";
         $s_loadavg = "0";
         $s_cpu = "0";
         $s_mem = "0";
    }else if (!is_numeric($s_loadavg)) {
         $s_status = "0";
         $s_loadavg = "0";
         $s_cpu = "0";
         $s_mem = "0";
    }else{
      $s_cpu = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$STREAM_UID1." CPU");
      $s_mem = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$STREAM_UID1." MEM");
    }
      $cur_date=date("YmdHi");
      $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"status\"]='{$s_status}';?>\n");
      $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"loadavg\"]='{$s_loadavg}';?>\n");
        $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"cpu\"]='{$s_cpu}';?>\n");
        $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"mem\"]='{$s_mem}';?>\n");

}

fclose($fplog);
    
function createListTable($log,$uid, $bListLimit)
{
  if ($uid=="") return;
  $html = '<table class=table_main><tr class=topic_main><td>UID</td><td>Date</td><td>streaming / cpu / mem / LoadAvg</td></tr>';
  if ($bListLimit)
    $html .= "<tr class=tr_2><td colspan=3>Latest ".MAXLIST." data only</td></tr>";
  else
    $html .= "<tr class=tr_2><td colspan=3>All data</td></tr>";
  if ($bListLimit)
    $skip = sizeof($log[$uid])-MAXLIST;
  
  $i = 0;               
	foreach($log[$uid] as $key=>$values){
    //$tunnel_log["192.168.1.xxx"]["201503070100"]["status]='31';
    if ($skip < ++$i){
  		$html.= "\n<tr class=tr_2>\n";
      $html.= "<td>{$uid}</td>\n";
      $html.= "<td>{$key}</td>\n";
      $html.= "<td>{$values['status']} / {$values['cpu']} / {$values['mem']} / {$values['loadavg']}</td>\n";
    }
	}
  $html.= "</tr></table>\n";
	echo $html;
}
function findUid($type)
{
    if ($type == STREAM_SRV)
        $sql = "select DISTINCT stream_server_uid from isat.stream_server_assignment";
    else if ($type == TUNNEL_SRV)
        $sql = "select DISTINCT tunnel_server_uid from isat.tunnel_server_assignment";
    sql($sql,$result,$num,0);
    if ($num==1)
    {
      fetch($arr,$result,0,0);
      if ($type == STREAM_SRV)
          return $arr['stream_server_uid'];
      else if ($type == TUNNEL_SRV)      
          return $arr['tunnel_server_uid'];
    }
    return "";
}



function selectUidAssign($type, $tagName)
{
    if ($type == STREAM_SRV)
        $sql = "select DISTINCT stream_server_uid from isat.stream_server_assignment";
    else if ($type == TUNNEL_SRV)
        $sql = "select DISTINCT tunnel_server_uid from isat.tunnel_server_assignment";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($type == STREAM_SRV)
          $html.= "\n<option value='{$arr['stream_server_uid']}'>{$arr['stream_server_uid']}</option>";
      else if ($type == TUNNEL_SRV)      
          $html.= "\n<option value='{$arr['tunnel_server_uid']}'>{$arr['tunnel_server_uid']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectUid($type, $tagName)
{
    if ($type == STREAM_SRV)
        $sql = "select DISTINCT uid from isat.stream_server";
    else if ($type == TUNNEL_SRV)
        $sql = "select DISTINCT uid from isat.tunnel_server";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $html.= "\n<option value='{$arr['uid']}'>{$arr['uid']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectIP($type, $tagName)
{
    if ($type == STREAM_SRV)
        $sql = "select DISTINCT internal_address from isat.stream_server";
    else if ($type == TUNNEL_SRV)
        $sql = "select DISTINCT internal_address from isat.tunnel_server";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $html.= "\n<option value='{$arr['internal_address']}'>{$arr['internal_address']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>Stream Status log</font></b></div>
<div id="container">
<?php
if ($crontab_status =="")
{
  echo "<h2>Status log is NOT Enabled.</h2>";
}
?>
Primary Tracking UUID: <?php echo $STREAM_UID;?><br>
Second Tracking UUID: <?php echo $STREAM_UID1;?><br>
<?php
if(isset($_REQUEST["setOnly"])){ //reduce database query
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
  <?php
  selectIP(STREAM_SRV,"stream_uid");
  echo "<select name=tid>";
  for($i=0;$i<MAXTID;$i++){
    echo "\n<option value='{$i}'>{$i}</option>";
  }//for
  echo "</select>";
  ?>
<input type=hidden name=step value='set_stream_uid'>
<input type=submit name=btnAction1 value="Set Tracking Stream" class="btn_1">
</form>
<HR>
<?php
 createListTable($stream_log,$STREAM_UID, $flag_writelog);
 createListTable($stream_log,$STREAM_UID1, $flag_writelog);
} //reduce database query
?>
</div>
</body>
</html>