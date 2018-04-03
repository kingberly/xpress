<?php
/****************
 *Validated on Oct-18,2017,
 * log rtmp connection number in /var/tmp/tunnel_connection_t04.log
 * Sync with rtmpd_connlog_Const.php
 * fix monthly log switch     
 *Writer: JinHo, Chang
*****************/
define("LOG_FILE","tunnel_connection_t04.log");

ini_set('memory_limit', '128M');
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("LOG_PATH","/var/tmp/");
//define("LOG_FILE","tunnel_connection_const.log");
define("MAXLIST",20);
//30 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/taipei/tunnel_connlog_tpe.php"
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
if (!isset($tunnelArray)){  //remark this line to force tunnelArray update
  setTunList();
}else if ( isset($_REQUEST["setTunnel"]) ){
  $flag_writelog=false;
  setTunList();
}

  //if(date("jGi") == "1000") //first day 0 hour 0 minute will change file
    reset($tunnel_log[$tunnelArray[0]]);
    $first_date = key($tunnel_log[$tunnelArray[0]]);
  //if current date is not same month as first key date
    if ( substr($first_date,0,6) != substr($cur_date,0,6) ){
      fclose($fplog); //start a new file
      exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$cur_date);
      exec(" echo '' > ".LOG_PATH.LOG_FILE);
      chmod(LOG_PATH.LOG_FILE,0777);
      $fplog=fopen(LOG_PATH.LOG_FILE,"a+");
      setTunList();
    }

$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_tunnelserver.php';    
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

if (isset($tunnelArray) and $flag_writelog){
  foreach($tunnelArray as $tuid)
  {
      $params = '?command=get_channels&tunnelserver='.$tuid;
      curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
      $result = curl_exec($ch);
      $cn_camera=json_decode($result,true);
         $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$tuid}\"][\"{$cur_date}\"][\"device\"]='{$cn_camera[channels][devices]}';?>\n");
         $fwrite = fwrite($fplog,"<?\$tunnel_log[\"{$tuid}\"][\"{$cur_date}\"][\"viewer\"]='{$cn_camera[channels][viewers]}';?>\n");
  
  }
}
fclose($fplog);
curl_close($ch);

function setTunList()
{
  global $tunnelArray,$fplog;
  unset($tunnelArray);
  $tunnelArray = array(); //recheck every month
  findUidArray(TUNNEL_SRV, $tunnelArray);

  for ($i=0;$i<count($tunnelArray);$i++)
    $fwrite = fwrite($fplog,"<?\$tunnelArray[".$i."]='".$tunnelArray[$i]."';?>\n");
}

function createListTable($log,$uid)
{
  if ($uid=="") return "";
  $html = "<table class=table_main><tr class=topic_main><td>UID</td><td>Date</td><td>device / viewer</td></tr>";
  $html .= "<tr class=tr_2><td colspan=3>Latest ".MAXLIST." data only</td></tr>";
  $skip = sizeof($log[$uid])-MAXLIST;
  $i = 0;             
	foreach($log[$uid] as $key=>$values){
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
function findUidArray($type,&$Array)
{
    if ($type == STREAM_SRV)
        $sql = "select * from isat.stream_server";
    else if ($type == TUNNEL_SRV)
        $sql = "select * from isat.tunnel_server where purpose='TUNNEL'";
    sql($sql,$result,$num,0);
    if ($num > 0)
    {
      for($i=0;$i<$num;$i++){
          fetch($arr,$result,$i,0);
          if ($type == STREAM_SRV)
            $Array[$i] = $arr['internal_address'];
          else if ($type == TUNNEL_SRV)
            $Array[$i] = $arr['uid'];
      }
    }
}

?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>Tunnel connection log</font></b></div>
<div id="container">
<HR>
<?php
if ( !isset($_REQUEST["setTunnel"]) ){
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>"> 
<input type='hidden' name='setTunnel' value='1'>
<input type='submit' name='btnAction' value='Force Update Tunnel List'>
</form>
<?php
}//if Not setTunnel
if(isset($_REQUEST["setOnly"]) or isset($_REQUEST["setTunnel"]) ){ //reduce database query
    echo "tunnelID list: <br>";
    for ($i=0;$i<count($tunnelArray);$i++){
        echo $tunnelArray[$i]." ;<br>";
    }
    foreach($tunnelArray as $tuid)
      createListTable($tunnel_log,$tuid);
}
?>
</div>
 </body>
</html> 