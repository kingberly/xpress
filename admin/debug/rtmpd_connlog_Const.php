<?php
/****************
 *Validated on Oct-18,2018,
 * log rtmp connection number in /var/tmp/rtmp_connection_xxx.log
 * use setOnly to reduce database query
 * fix rtmpID write log error
 * fix monthly log switch    
 *Writer: JinHo, Chang
*****************/
ini_set('memory_limit', '64M');
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("RTMP_SRV","3");
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","rtmpd_connection_const.log");
define("MAXLIST",20);
//30 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/taipei/rtmpd_connlog_tpe.php"
if (file_exists(LOG_PATH.LOG_FILE))
  include_once(LOG_PATH.LOG_FILE);
else{
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);  
}
//$crontab_status = exec("grep '".$_SERVER['PHP_SELF']."' /etc/crontab");  
$fplog=fopen(LOG_PATH.LOG_FILE,"a+");
$cur_date=date("YmdHi");

$flag_writelog=true;
if ( isset($_REQUEST["setOnly"]) ){
  $flag_writelog=false;
}
if (!isset($rtmpArray)){
  setList();
}else if ( isset($_REQUEST["setRtmpd"]) ){
  $flag_writelog=false;
  setList();
}  
  //if(date("jGi") == "1000") //first day 0 hour 0 minute will change file
    reset($rtmp_log[$rtmpArray[0]]);
    $first_date = key($rtmp_log[$rtmpArray[0]]);
  //if current date is not same month as first key date
    if ( substr($first_date,0,6) != substr($cur_date,0,6) ){
      fclose($fplog); //start a new file
      exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
      exec(" echo '' > ".LOG_PATH.LOG_FILE);
      chmod(LOG_PATH.LOG_FILE,0777);
      $fplog=fopen(LOG_PATH.LOG_FILE,"a+");
      setList();
    }

if (isset($rtmpArray) and $flag_writelog){
    for ($i=0;$i<count($rtmpArray);$i++)
    {
      $count = countRtmp($rtmpID[$i]);  
      $fwrite = fwrite($fplog,"<?\$rtmp_log[\"{$rtmpArray[$i]}\"][\"{$cur_date}\"][\"device\"]='{$count}';?>\n");
    }
}
fclose($fplog);
curl_close($ch);

function setList()
{
  global $rtmpArray, $rtmpID,$fplog;
  unset ($rtmpArray);
  unset ($rtmpID);
  $rtmpArray = array();
  $rtmpID = array();
  findUidArray(RTMP_SRV, $rtmpArray,$rtmpID);
//write database array $rtmpArray into file
    for ($i=0;$i<count($rtmpArray);$i++){
      $fwrite = fwrite($fplog,"<?\$rtmpArray[".$i."]='".$rtmpArray[$i]."';?>\n");
      $fwrite = fwrite($fplog,"<?\$rtmpID[".$i."]='".$rtmpID[$i]."';?>\n");
    }
}
function countRtmp ($id)
{
   // this will show all ivedamoible connections 
   //$sql = "select COUNT(*) as count from isat.signal_server_online_client_list where uid like '%MC-M%'"; //T04MC-MT045xxxxx , V04MC-MV04
   $sql = "select COUNT(*) as count from isat.signal_server_online_client_list where uid like '%MC-M%' and signal_server_id={$id}";
    sql($sql,$result,$num,0);
    if ($result){
        fetch($arr,$result,0,0);
        return (int)$arr['count'];
    }else return -1;    
}
function createListTable($log,$uid)
{
  if ($uid=="") return "";
  $html = "<table class=table_main><tr class=topic_main><td>UID</td><td>Date</td><td>device</td></tr>";
  $html .= "<tr class=tr_2><td colspan=3>Latest ".MAXLIST." data only</td></tr>";
  $skip = sizeof($log[$uid])-MAXLIST;
  $i = 0;             
	foreach($log[$uid] as $key=>$values){
    //$rtmp_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
    if ($skip < ++$i){
  		$html.= "\n<tr class=tr_2>\n";
      $html.= "<td>{$uid}</td>\n";
      $html.= "<td>{$key}</td>\n";
      $html.= "<td>{$values['device']}</td>\n";
    }
	}
  $html.= "</tr></table>\n";
	echo $html;
}

function findUidArray($type,&$Array, &$ID)
{
    if ($type == STREAM_SRV)
        $sql = "select * from isat.stream_server";
    else if ($type == TUNNEL_SRV)
        $sql = "select * from isat.tunnel_server where purpose='TUNNEL'";
    else if ($type == RTMP_SRV)
        $sql = "select * from isat.tunnel_server where purpose='RTMPD'";
    sql($sql,$result,$num,0);
    if ($num > 0)
    {
      for($i=0;$i<$num;$i++){
          fetch($arr,$result,$i,0);
          $Array[$i] = $arr['uid'];
          $ID[$i] = $arr['id'];
      }
    }
}

?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>RTMP connection log</font></b></div>
<div id="container">
<HR>
<?php
if ( !isset($_REQUEST["setRtmpd"]) ){
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>"> 
<input type='hidden' name='setRtmpd' value='1'>
<input type='submit' name='btnAction' value='Force Update RTMPD List'>
</form>
<?php
}//if not set
if(isset($_REQUEST["setOnly"]) or isset($_REQUEST["setRtmpd"]) ){ //reduce database query
    echo "rtmpID list: <br>";
    for ($i=0;$i<count($rtmpArray);$i++){
        echo $rtmpID[$i].":".$rtmpArray[$i]." ;<br>";
    }

    foreach($rtmpArray as $tuid)
      createListTable($rtmp_log,$tuid);
}
?>
</div>
</body>
</html>
