<?php
/****************
 *Validated on Oct-15,2015,
 * log stream connection number in /var/tmp/stream_connection.log
 * 2 stream supported
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
define("LOG_FILE","stream_connection.log");
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

$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_streamserver.php';    
#API A-8
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
if (($STREAM_UID!="") and $flag_writelog)
{
    $params = '?command=get_recording_status&streamserver='.$STREAM_UID;
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    $result = curl_exec($ch);
    $content=json_decode($result,true);
    $streaming_count =0;
    $nostreaming_count =0;
		foreach($content[recording][record] as $key_rec=>$value_rec){
       //echo $value_rec[t]. " ". $value_rec[status];
       if ($value_rec[status]=="Streaming") $streaming_count++;
       else  $nostreaming_count++;
			//$rec[$value_rec[uid]][status]=$value_rec[status];
			//$rec[$value_rec[uid]][t]=date("Y-m-d H:i:s",$value_rec[t]);
		}      
    if ($streaming_count!=0)
    {
      $cur_date=date("YmdHi");
       $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"status\"]='{$streaming_count}';?>\n");
       $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID}\"][\"{$cur_date}\"][\"nostatus\"]='{$nostreaming_count}';?>\n");
     }
}
if (($STREAM_UID1!="") and $flag_writelog)
{
    $params = '?command=get_recording_status&streamserver='.$STREAM_UID1;
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    $result = curl_exec($ch);
    $content=json_decode($result,true);
    $streaming_count =0;
    $nostreaming_count =0;
		foreach($content[recording][record] as $key_rec=>$value_rec){
       //echo $value_rec[t]. " ". $value_rec[status];
       if ($value_rec[status]=="Streaming") $streaming_count++;
       else  $nostreaming_count++;
			//$rec[$value_rec[uid]][status]=$value_rec[status];
			//$rec[$value_rec[uid]][t]=date("Y-m-d H:i:s",$value_rec[t]);
		}      
    if ($streaming_count!=0)
    {
      $cur_date=date("YmdHi");
       $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"status\"]='{$streaming_count}';?>\n");
       $fwrite = fwrite($fplog,"<?\$stream_log[\"{$STREAM_UID1}\"][\"{$cur_date}\"][\"nostatus\"]='{$nostreaming_count}';?>\n");
     }
}
curl_close($ch);
fclose($fplog);
    
function createListTable($log,$uid)
{
  if ($uid=="") return;
  $html = '<table class=table_main><tr class=topic_main><td>UID</td><td>Date</td><td>streaming</td></tr>';
  $html .= "<tr class=tr_2><td colspan=3>Latest ".MAXLIST." data only</td></tr>";
  $skip = sizeof($log[$uid])-MAXLIST;
  $i = 0;               
	foreach($log[$uid] as $key=>$values){
    //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["status]='31';
    if ($skip < ++$i){
  		$html.= "\n<tr class=tr_2>\n";
      $html.= "<td>{$uid}</td>\n";
      $html.= "<td>{$key}</td>\n";
      $html.= "<td>{$values['status']} / {$values['nostatus']}</td>\n";
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

?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>Stream connection log</font></b></div>
<div id="container">
<?php
if ($crontab_status =="")
{
  echo "<h2>Connection log is NOT Enabled.</h2>";
  //echo '<input type=submit name=btnAction2 value="Enable" class="btn_2"><br>';
}//else
  //echo '<input type=submit name=btnAction2 value="Disable" class="btn_2"><br>';
?>
Primary Tracking UUID: <?php echo $STREAM_UID;?><br>
Second Tracking UUID: <?php echo $STREAM_UID1;?><br>
<?php
if(isset($_REQUEST["setOnly"])){ //reduce database query
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
  <?php 
  selectUidAssign(STREAM_SRV,"stream_uid");
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
 createListTable($stream_log,$STREAM_UID);
 createListTable($stream_log,$STREAM_UID1);
} //reduce database query 
?>
</div>
</body>
</html>