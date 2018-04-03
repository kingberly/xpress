<?php
/****************
 *Validated on Nov-6,2017,
 * delete un-used servers from database 
 * listed server status
 * add debugadmin feature to hide remove button
 *     auto add Remove for unaccessible server
 * add rtmpd server version, fix rtmpcount
 * add ping to reduce waiting time of nonAlive server
 * check region/rtmp exist or not 
 * add web Mailer configuration
 * fix onePM inquiry, fix LB LANIP issue           
 *Writer: JinHo, Chang
*****************/
include("../../header.php");
include("../../menu.php");
require_once '_auth_.inc';
include("_iveda.inc"); //under /plugin/debug/
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("WEB_SRV","3");
define("RTMP_SRV","4");
define("NTP_IVEDA_SERVER","poweredbyiveda.net");
define("NTP_TW_SERVER","tw.pool.ntp.org");
//oem=> url, admin pwd, api pwd, LB pwd 
$SiteInfo=[
"X02" => array("xpress.megasys.com.tw","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"X01" => array("xpress2.megasys.com.tw","qwedcxzas","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"T04" => array("rpic.taipei","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"T05" => array("rpic.tycg.gov.tw","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"K01" => array("kreac.kcg.gov.tw","1qazxdr56yhN","megasysManageUser:amICAnCeDiNgEntAtiDE","ivedasuper:1qazxdr56yhN"),
"P04" => array("videomonitoring.pldtcloud.com","1qazxdr56yhN","pldtManageUser:pldtManagePassword","ivedasuper:1qazxdr56yhN"),
"V03" => array("camera.vinaphone.vn","qwedcxzas","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"V04" => array("sentirvietnam.vn","qwedcxzas","ivedaManageUser:ivedaManagePassword","ivedasuper:1qazxdr56yhN"),
"Z02" => array("zee.ivedaxpress.com","37n3nls8!","ivedaManageUser:7iUut4fmysRe0Qw3J9Vr","ivedasuper:1qazxdr56yhN")
];


function ping($host)
{
        exec(sprintf('ping -c 1 -W 1 %s', escapeshellarg($host)), $res, $rval);
        return $rval === 0;
}

function isDomainAvailible($domain)
{
       //initialize curl
       $curlInit = curl_init($domain);
       curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
       curl_setopt($curlInit,CURLOPT_HEADER,true);
       curl_setopt($curlInit,CURLOPT_NOBODY,true);
       curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

       //get answer
       $response = curl_exec($curlInit);

       curl_close($curlInit);

       if ($response) return true;

       return false;
}

$bNewServer = TRUE; // for new server /rtmp 
$bOneServer = FALSE;//(one pm XMLRPC web/stream is same as new server) web ip = local ip

$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'isat' AND TABLE_NAME = 'tunnel_server' AND COLUMN_NAME = 'purpose'";
sql($sql,$result,$num,0);
if ($num==0) $bNewServer = FALSE;
$bRegion = TRUE; // for new server /rtmp
$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'isat' AND TABLE_NAME = 'device_reg' AND COLUMN_NAME = 'region'";
sql($sql,$result,$num,0);
if ($num==0) $bRegion = FALSE;

if($_REQUEST["step"]=="delstream")
{
    $result = deleteServerByID(STREAM_SRV,$_REQUEST["id"]);
    if ($result){
        $msg_err = "<font color=blue>delete Stream server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
    }else $msg_err = "<font color=red>delete Stream server ".$_REQUEST["id"]. " FAIL!</font><br>\n";    
}else if($_REQUEST["step"]=="deltunnel")
{
    $result = deleteServerByID(TUNNEL_SRV,$_REQUEST["id"]);
    if ($result){
        $msg_err = "<font color=blue>delete Tunnel server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
    }else $msg_err = "<font color=red>delete Tunnel server ".$_REQUEST["id"]. " FAIL!</font><br>\n";    
}else if($_REQUEST["step"]=="delweb")
{
    if ($_POST['btnAction']=='Remove'){
        $result = deleteServerByID(WEB_SRV,$_REQUEST["id"]);
        if ($result){
            $msg_err = "<font color=blue>delete Web server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Web server ".$_REQUEST["id"]. " FAIL!</font><br>\n";
    }else if ($_POST['btnAction']=='Set Interval'){
        $result = exec ("wget -q -O - http://".$_REQUEST['webip']."/Mailer/configuration.php?interval=".$_REQUEST['interval']);
        $msg_err = "<font color=blue>Set Mailer : {$result}</font><br>\n";
    }    
}else if($_REQUEST["btnSYSLOG"]=="Clean sys_log web_server_event"){
	if (deleteSYS_LOG("web_server_event"))
			$msg_err = "<font color=blue>delete sys_log web_server_event SUCCESS!</font><br>\n";
  else $msg_err = "<font color=red>delete sys_log web_server_event FAIL!</font><br>\n";
	
}

function deleteSYS_LOG($type="web_server_event")
{
	$sql="delete from qlync.sys_log where Cat1='{$type}'";
	sql($sql,$result,$num,0);
  if ($result) return true;
  return false;
}
function deleteServerByID ($type, $id)
{
    if ($type == STREAM_SRV)
        $sql="delete from isat.stream_server where id='{$id}'";
    else if ($type == TUNNEL_SRV)
        $sql="delete from isat.tunnel_server where id='{$id}'";
    else if ($type == WEB_SRV)
        $sql="delete from isat.web_server where id='{$id}'";     
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
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


function createServiceTable($type)
{
  require_once('/var/www/qlync_admin/html/server/xmlrpc_client.php');
  global $api_id, $api_pwd, $api_ip, $api_port, $bNewServer, $bRegion,$bOneServer; 
  $web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";

    if ($type == STREAM_SRV)
        $sql = "select * from isat.stream_server";
    else if ($type == TUNNEL_SRV)
      if ($bNewServer)
        $sql = "select * from isat.tunnel_server where purpose='TUNNEL'";
      else $sql = "select * from isat.tunnel_server";
    else if ($type == RTMP_SRV)
        $sql = "select * from isat.tunnel_server where purpose='RTMPD'";
    else if ($type == WEB_SRV)
        $sql = "select * from isat.web_server";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for

    //$html = '';
    $html ="<table class=table_main><tr class=topic_main>";
    if ($type == WEB_SRV)
      $html .="<td>ID</td><td>R.</td><td>Hostname</td><td>Addr.</td><td>UID</td><td>CPU / MEM, Mailer</td><td></td></tr>\n";
    else
      $html .="<td>ID</td><td>R.</td><td>Hostname</td><td>Addr.</td><td>UID</td><td>CPU / MEM, #</td><td></td></tr>\n";

    foreach($services as $service)
    {
        if (! ping($service['internal_address']) )
            $serverOnline = false;
        else $serverOnline = true;
        if ($type == TUNNEL_SRV)
        {
          if ($serverOnline){
              $path = '/manage/manage_tunnelserver.php';
          		#API A-7
          		$ch = curl_init();
          		curl_setopt($ch, CURLOPT_HEADER, false);
          		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      
          		$params = '?command=get_stats&tunnelserver='.$service['uid'];
          		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
          		$result = curl_exec($ch);
          		flush($cn_cpu);
          		$cn_cpu=json_decode($result,true);
              $cpu = round($cn_cpu[stats][cpu],2);
              $mem = (round(1-$cn_cpu[stats][memfree]/$cn_cpu[stats][memtotal],4)*100);
    	        curl_close($ch);
          }else{
             $cpu="NA";
             $mem="NA";
          }
        }else if ($type == RTMP_SRV)
        {
          if ($serverOnline){
             $cpu = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$service['internal_address']." CPU");
             $mem = exec ("python /home/ivedasuper/admin/check/getCPUMEM.py ".$service['internal_address']." MEM");
          }else{
             $cpu="NA";
             $mem="NA";
          }
        }else{ //non-tunnel cpu mem
             //xml to get cpu/mem 
             if ($type == WEB_SRV)
                $s = array('id'=> $service['id'],
                                  'uid'=> $service['uid'],
                                  'internal_address'=> $service['internal_address'],
                                  'hostname'=> $service['hostname']);     
             else if ($type == STREAM_SRV)
                $s = array('id'=> $service['id'],
                                  'uid'=> $service['uid'],
                                  'internal_address'=> $service['internal_address'],
                                  'hostname'=> $service['hostname'],
                                  'license_id'=> $service['license_id']);
             /*else if ($type == TUNNEL_SRV)
                $s = array('id'=> $service['id'],
                                  'uid'=> $service['uid'],
                                  'internal_address'=> $service['internal_address'],
                                  'hostname'=> $service['hostname'],
                                  'license_id'=> $service['license_id'],
                                  'port'=> $service['external_port']);*/
            $arguments = array('history' => 1);
             if ($type == STREAM_SRV){ ////v2.0.7 8087
                if ($_SERVER['SERVER_ADDR'] == $service['internal_address']) $bOneServer = TRUE;
                if ($bNewServer or $bOneServer)
            		  $stats = array('port' => 8087, 'path' => '/', 'function' => 'getStats', 'arguments' => $arguments );
                else $stats = array('port' => 8088, 'path' => '/', 'function' => 'getStats', 'arguments' => $arguments );      
             //}else if ($type == TUNNE_SRV){
            //		$stats = array('port' => 8088, 'path' => '/RPC2', 'function' => 'getStats', 'arguments' => null );     
             }else if ($type == WEB_SRV){ //v2.0.7 8089
                if ($_SERVER['SERVER_ADDR'] == $service['internal_address']) $bOneServer = TRUE;
                if ($bNewServer or $bOneServer)
                    $stats = array('port' => 8089, 'path' => '/', 'function' => 'getStats', 'arguments' => $arguments );
                else  $stats = array('port' => 8088, 'path' => '/', 'function' => 'getStats', 'arguments' => $arguments );
             }                           
                try {
                  if ($serverOnline) {
                 	 $rpc = new xmlrpc_client($s, $stats);
              	   $resource[$s[uid]]=$rpc->call();
                 	 //print_r($rpc->call());
                   $cpu = round($resource[$s[uid]][0][cpu],2);
                   $mem = round((1-$resource[$s[uid]][0][memfree]/$resource[$s[uid]][0][memtotal])*100,2);
                  }else{
                     $cpu="NA";
                     $mem="NA";
                  }
                }
                catch (Exception $e) { $cpu="NA";$mem="NA";}
              //end of xml rpc call
         }//non-tunnel cpu/mem
      		$html.= "\n<tr class=tr_2>\n";
          $html.= "<td>{$service['id']}</td>\n";
          if ($bRegion)
              $html.= "<td>{$service['region']}</td>\n";//added after 1.2-89
          else $html.= "<td></td>\n";
          $html.= "<td>{$service['hostname']}</td>\n";
          $html.= "<td>";
          if ($type != WEB_SRV)
                $html.= "{$service['external_address']}:{$service['external_port']}<br>\n";
          $html.= "{$service['internal_address']}</td>\n";
          $html.= "<td>{$service['uid']}</td>\n";
          if ($type == STREAM_SRV){
              //$strver = exec ("python /home/ivedasuper/admin/check/getVersion.py ".$service['internal_address']." stream");
              //$strver = exec ("python /home/ivedasuper/admin/check/getStreamVersion.py ".$service['internal_address']);
              //strver is fixed after v2.0.4
            //$html.= "<td>({$strver}) {$cpu} / {$mem} , ";
            $html.= "<td>{$cpu} / {$mem} , ";
            if ($service['license_id']!=""){
              if ($serverOnline)
                  $count = exec ("python /home/ivedasuper/admin/check/getStreamingCount.py ".$service['internal_address']);
              if ($count !="")  $html.= $count;
              else $html .= "NA";
            }else $html .= "Disabled"; 
            $html.= "</td>\n";
          }else if ($type == TUNNEL_SRV){
            $html.= "<td>{$cpu} / {$mem} , ";
            if ($serverOnline) {
            //if ($service['purpose'] == "TUNNEL"){
                if ($service['license_id']!=""){
                      $path = '/manage/manage_tunnelserver.php';
                  		$ch = curl_init();
                  		curl_setopt($ch, CURLOPT_HEADER, false);
                  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      
                  		$params = '?command=get_channels&tunnelserver='.$service['uid'];
                  		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                  		$result = curl_exec($ch);
                      $result = curl_exec($ch);
                      $cn_camera=json_decode($result,true);
                      if ($cn_camera[channels][devices]!="") $html .= $cn_camera[channels][devices];
                      else $html .= "NA";
                  }else $html .= "Disabled";
                  $html.= "</td>\n";
         	        curl_close($ch);
              }else{
                  $html.= "NA</td>\n";
              }
            }else if ($type == RTMP_SRV){
                if ($serverOnline)
                 $strver = exec ("python /home/ivedasuper/admin/check/getVersion.py ".$service['internal_address']." rtmpd");
                 $html.= "<td>({$strver}) {$cpu} / {$mem} , ";
                 //$html.= "<td>{$cpu} / {$mem} , ";
                 $html .= countRtmp($service['id']);
                  $html.= "</td>\n";                  
          }else{      
              if ($serverOnline)
                 $webmailer = exec ("wget -q -O - http://".$service['internal_address']."/Mailer/configuration.php?getinterval");
              $html.= "<td>{$cpu} / {$mem} , {$webmailer}</td>\n";
          }
          if (isset($_REQUEST["debugadmin"]) or ($mem=="NA") ){
              $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
              $html.= "<input type=submit name='btnAction' value=\"Remove\" class=\"btn_2\">\n";
              if ($type == STREAM_SRV)
                  $html.= "<input type=hidden name='step' value=\"delstream\" >\n";
              else if (($type == TUNNEL_SRV) or ($type == RTMP_SRV))
                  $html.= "<input type=hidden name='step' value=\"deltunnel\" >\n";
              else if ($type == WEB_SRV){
                $html.= "<input type=hidden name='step' value=\"delweb\" >\n";
                if ($serverOnline){ //add mailer feature
                  $html.= "<br><input type=submit name='btnAction' value=\"Set Interval\">\n";
                  $html.= "<input type=hidden name=webip value='{$service['internal_address']}' size='1'>\n";
                  $html.= "<input type=text name=interval value='60' size='1'>\n";
                }
              }
              $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
              $html.= "<input type=hidden name='debugadmin' value=\"1\" >\n";

              $html.= "</form></td>\n";
          }else $html.= "<td></td>\n";    
          $html.= "</tr>\n";
  	}
    $html .="</table><br>\n";
	  echo $html;
}
?>
<!--html>
<head>
</head>
<body-->
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<div align=center><b><font size=5>Maintain DB</font></b></div>
<div id="container">
<?php
if (isset($msg_err))
  echo $msg_err."<hr>";
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=submit name="btnSYSLOG" value="Clean sys_log web_server_event"><br>
<select name="server_filter" id="server_filter" onchange="optionValue(this.form.server_filter, this);this.form.submit();">
<option value="" ></option>
<option value="(STREAM)" <?php if($_REQUEST['server_filter'] =="(STREAM)" ) echo "selected";?>>(STREAM)</option>
<option value="(TUNNEL-RTMP)" <?php if($_REQUEST['server_filter'] =="(TUNNEL-RTMP)" ) echo "selected";?>>(TUNNEL-RTMP)</option>
<option value="(WEB)" <?php if($_REQUEST['server_filter'] =="(WEB)" ) echo "selected";?>>(WEB)</option>
<option value="(ALL)" <?php if($_REQUEST['server_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<?php
if(($_REQUEST['server_filter'] =="(STREAM)") or ($_REQUEST['server_filter'] =="(ALL)")  )
 createServiceTable(STREAM_SRV);
if(($_REQUEST['server_filter'] =="(TUNNEL-RTMP)") or ($_REQUEST['server_filter'] =="(ALL)") )
 createServiceTable(TUNNEL_SRV);
if ($bNewServer)
  if(($_REQUEST['server_filter'] =="(TUNNEL-RTMP)") or ($_REQUEST['server_filter'] =="(ALL)") )
    createServiceTable(RTMP_SRV);
  if(($_REQUEST['server_filter'] =="(WEB)") or ($_REQUEST['server_filter'] =="(ALL)") )
    createServiceTable(WEB_SRV);

if ((!isset($_REQUEST['server_filter'])) and (!isset($_REQUEST["debugadmin"])) )
{ 
  if (! isset($msg_err) ) {
	  if (($oem=="K01") or ($oem=="T06") or ($oem=="T04") or ($oem=="T05") or ($oem=="X02") ){
	exec("ntpdate -q ".NTP_TW_SERVER,$res, $rval);
	echo "Camera NTP statue (".NTP_TW_SERVER."): <br><small>".$res[0]."<br>". $res[1]."</small><br>";
		}else{
	exec("ntpdate -q ".NTP_IVEDA_SERVER,$res, $rval);
	echo "Camera NTP statue (".NTP_IVEDA_SERVER."): <br><small>".$res[0]."<br>". $res[1]."</small><br>";
		}
  }//msg_err
if (strpos($_SERVER['HTTP_HOST'],IPv4_ADDR_PREFIX)!==FALSE)
  $publicip=getsubLanIP(getLanIP())."100";
else $publicip=gethostbyname(explode(":",$_SERVER['HTTP_HOST'])[0]);
echo "<a href='http://{$publicip}/haproxy?stats' target=lb>LB stats</a> <small>({$SiteInfo[$oem][3]})</small><br>";
echo "<a href='http://{$publicip}/state/status.html' target=lb1>HA web status</a><br>";
//echo "<a href='".getWebURL("http://",true)."/haproxy?stats' target=lb>LB stats</a> <small>({$SiteInfo[$oem][3]})</small><br>";
//echo "<a href='".getWebURL("http://")."/state/status.html' target=lb1>HA web status</a><br>";
}
?>
	</div>
</body>
</html>