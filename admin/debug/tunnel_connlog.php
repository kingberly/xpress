<?php
/****************
 *Validated on Oct-15,2015,
 * log tunnel connection number in /tmp/tunnel_connection.log
 * Google API bar charts
 * use setOnly to reduce database query     
 *Writer: JinHo, Chang
*****************/
ini_set('memory_limit', '64M');
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","tunnel_connection.log");
define("MAXLIST",20);
define("MAXTID",3);  //3 = tunnel2
//*/3 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/tunnel_connlog.php"
//$TUNNEL_UID='5284b02873234e1d86e101e8fc3f5b22';
if (file_exists(LOG_PATH.LOG_FILE))
  include_once(LOG_PATH.LOG_FILE);
else{
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);  
}
$crontab_status = exec("grep '".$_SERVER['PHP_SELF']."' /etc/crontab");  
$fplog=fopen(LOG_PATH.LOG_FILE,"a+");
$cur_date=date("YmdHi");

$flag_writelog=true;
if ( isset($_REQUEST["setOnly"]) ){
  $flag_writelog=false;
}

//if(date("dHi") == "010000") //1th 0:00 
if ( (date("dHi") == "100000") or (date("dHi") == "200000") or (date("dHi") == "300000") )//0:00 on 10th, 20th, 30th
{//change a new file every Monday
   fclose($fplog);
    exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);
    $fplog=fopen(LOG_PATH.LOG_FILE,"a+");    
    $fwrite = fwrite($fplog,"<?\$TUNNEL_UID='".$TUNNEL_UID."';?>\n");
    if ($TUNNEL_UID1!="")
        $fwrite = fwrite($fplog,"<?\$TUNNEL_UID1='".$TUNNEL_UID1."';?>\n");
    if ($TUNNEL_UID2!="")
        $fwrite = fwrite($fplog,"<?\$TUNNEL_UID2='".$TUNNEL_UID2."';?>\n");
}

if($_REQUEST["step"]=="set_tunnel_uid")
{ //write specific tunnel_uid
  if ($_REQUEST["tid"]=="0")
  {//start a new file
      fclose($fplog);
      exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
      exec(" echo '' > ".LOG_PATH.LOG_FILE);
      chmod(LOG_PATH.LOG_FILE,0777);
      $fplog=fopen(LOG_PATH.LOG_FILE,"a+");
      $fwrite = fwrite($fplog,"<?\$TUNNEL_UID='".$_REQUEST['tunnel_uid']."';?>\n");
  }else
      $fwrite = fwrite($fplog,"<?\$TUNNEL_UID".$_REQUEST['tid']."='".$_REQUEST['tunnel_uid']."';?>\n");
  echo "set uid ".$_REQUEST['tid']." ".$_REQUEST['tunnel_uid'];
}

$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_tunnelserver.php';    
#API A-8
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
if (($TUNNEL_UID!="") and $flag_writelog)
{
    $params = '?command=get_channels&tunnelserver='.$TUNNEL_UID;
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    $result = curl_exec($ch);
    $cn_camera=json_decode($result,true);
    if ($cn_camera[channels][devices]!="")
    {
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID}\"][\"{$cur_date}\"][\"device\"]='{$cn_camera[channels][devices]}';?>\n");
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID}\"][\"{$cur_date}\"][\"viewer\"]='{$cn_camera[channels][viewers]}';?>\n");
     }
}
if (($TUNNEL_UID1!="") and $flag_writelog)
{
    $params = '?command=get_channels&tunnelserver='.$TUNNEL_UID1;
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    $result = curl_exec($ch);
    $cn_camera=json_decode($result,true);
    if ($cn_camera[channels][devices]!="")
    {
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID1}\"][\"{$cur_date}\"][\"device\"]='{$cn_camera[channels][devices]}';?>\n");
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID1}\"][\"{$cur_date}\"][\"viewer\"]='{$cn_camera[channels][viewers]}';?>\n");
     }
}
if (($TUNNEL_UID2!="") and $flag_writelog)
{
    $params = '?command=get_channels&tunnelserver='.$TUNNEL_UID2;
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    $result = curl_exec($ch);
    $cn_camera=json_decode($result,true);
    if ($cn_camera[channels][devices]!="")
    {
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID2}\"][\"{$cur_date}\"][\"device\"]='{$cn_camera[channels][devices]}';?>\n");
       $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$TUNNEL_UID2}\"][\"{$cur_date}\"][\"viewer\"]='{$cn_camera[channels][viewers]}';?>\n");
     }
}
fclose($fplog);
curl_close($ch);


function createListTable($log,$uid)
{
  if ($uid=="") return;  
  $html = "<table class=table_main><tr class=topic_main><td>UID</td><td>Date</td><td>device / viewer</td></tr>";
  $html .= "<tr class=tr_2><td colspan=3>Latest ".MAXLIST." data only</td></tr>";
  $skip = sizeof($log[$uid])-MAXLIST;
  $i = 0;             
	foreach($log[$uid] as $key=>$values){
    //$billing_data["X02"]["20150307"]["lic"]=31;
    //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
    if ($skip < ++$i){
  		$html.= "\n<tr class=tr_2>\n";
      $html.= "<td>{$uid}</td>\n";
      $html.= "<td>{$key}</td>\n";
      $html.= "<td>{$values['device']} / {$values['viewer']}</td>\n";
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

function createChartData($tunnel_log,$uid){
	$html = '';   // ['201501111111',25],
  //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
	foreach($tunnel_log[$uid] as $key=>$values){
         $num = $values["device"];
         $html.="['$key',$num],\n";
  }
  $html = rtrim($html, ","); //remove last,
	echo $html;
} 
?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>Tunnel connection log</font></b></div>
<div id="container">
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php
if ($crontab_status =="")
{
  echo "<h2>Connection log is NOT Enabled.</h2>";
  //echo '<input type=submit name=btnAction2 value="Enable" class="btn_2"><br>';
}//else
  //echo '<input type=submit name=btnAction2 value="Disable" class="btn_2"><br>';
?>
Primary Tracking UUID: <?php echo $TUNNEL_UID;?><br>
Second Tracking UUID: <?php echo $TUNNEL_UID1;?><br>
Third Tracking UUID: <?php echo $TUNNEL_UID2;?><br>
<?php
if(isset($_REQUEST["setOnly"])){ //reduce database query
  selectUidAssign(TUNNEL_SRV,"tunnel_uid");
  echo "<select name=tid>";
  for($i=0;$i<MAXTID;$i++){
    echo "\n<option value='{$i}'>{$i}</option>";
  }//for
  echo "</select>";
  echo "<input type=hidden name=setOnly>";
  ?>
  <input type=hidden name=step value='set_tunnel_uid'>
  <input type=submit name=btnAction1 value="Set Tracking Tunnel" class="btn_1">
  </form>
  <HR>
  <?php
   createListTable($tunnel_log,$TUNNEL_UID);
   createListTable($tunnel_log,$TUNNEL_UID1);
   createListTable($tunnel_log,$TUNNEL_UID2);
}
  ?>
</div>
</body>
</html>