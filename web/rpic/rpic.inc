<?php
/****************
 * Validated on Feb-8,2018,
 * MUST INCLUDE AFTER global.php
 * include varible/file for web player
 * APP_USER_PWD, OEM_ID, IPv4_ADDR_PREFIX
 *sed -i -e 's|XNN|X02|' /var/www/SAT-CLOUDNVR/rpic.inc          
 *sudo sed -i -e 's|define(\x22DEBUG_FLAG\x22,\x22OFF\x22);|define(\x22DEBUG_FLAG\x22,\x22ON\x22);|' /var/www/SAT-CLOUDNVR/rpic.inc
 *sudo grep 'debug:' /var/log/lighttpd/error.log  
 *Writer: JinHo, Chang
*****************/
if ($oem_style_list['oem_id']!="N99")
	define("OEM_ID",$oem_style_list['oem_id']);
else define("OEM_ID","XNN");

define("USER_EMAIL_POSTFIX","@safecity.com.tw");
define("DEBUG_FLAG","OFF"); //ON ; OFF

$CamCID=[//CID, PREFIX
  "M04CC"=>"184E",
  "Z01CC"=>"001B",
  "B03CC"=>"0050",
  "A02CC"=>"3C49",
  "F01CC"=>"2C62",
  OEM_ID."MC"=>"M".OEM_ID
];
$RPICAPP_USER_PWD = [ //24digit
"T04"=>"Ea9M7gOu586UQaOtXJ3e6f51",
"T05"=>"tN8V8bMTtuKycj7BNW2Esp8p",
"K01"=>"ydEP6Ug6uBzWTXU28gfSV3hu",
"T06"=>"vzEG2Ea6fWzEGb5Ea235uZvm",
"C13"=>"Cqjf6NE2R5xY5unCMXVDqqmP",
//XXX"=>"skv9SjcwfFGgFzZduacc",
"X02"=>"ydEP6Ug6uBzWTXU28gfSV3hu",
"RPIC"=>"ydEP6Ug6uBzWTXU28gfSV3hu"
];
$errorMSG = [
  "101" => "Invalid user account",  //user
  "102" => "Dupliated name or register email",  //user
  "103" => "Unknown command.",  //user
  "104" => "User not found.",  //user
  "105" => "Account Format Error.",  //user
  "106" => "Account Exist.",  //user
  "107" => "Unauthorize to delete common user account",  //user
  "108" => "PipeGIS Account Not Exist.",  //K01 user
  "109" => "Invalid Username / Password.",
  "110" => "Please check your confirm letter first.",
  "111" => "Product unmatched.",
  "201" => "Device not found.",  //mac
  "202" => "Device is shared to requested account.",  //mac
  "203" => "Device is NOT shared to requested account.",  //mac
  "204" => "Invalid MAC format",  //mac
  "205" => "Device does not belong to any user",  //mac    
  "301" => "Invalid command",   //command
  "302" => "Invalid parameters.",
  "303" => "Can't connect to DB",
  "304" => "Share device failed.",
  "404" => "ErrorCode Matching Fail"
];

define("K01_FIREWALL","10.10.0.2");
if (OEM_ID=="X02")  define("IPv4_ADDR_PREFIX","192.168.");  
else define("IPv4_ADDR_PREFIX","192.168.1."); 

define("THUMBNAIL_TAG","THUMBNAIL");
mysql_connect(SIGNAL_DB_HOST,SIGNAL_DB_USERNAME,SIGNAL_DB_PASSWORD)or die("Mysql Connecting Failed..!!"); 
mysql_select_db(SIGNAL_DB_NAME);
$ref=exec("grep utf8 /var/www/SAT-CLOUDNVR/include/db_function.php");//correct
if ($ref!="")
  mysql_query("SET NAMES 'utf8'");	

if ($RPICAPP_USER_PWD[OEM_ID]==NULL)
	define("APP_USER_PWD",$RPICAPP_USER_PWD['RPIC']);
else define("APP_USER_PWD",$RPICAPP_USER_PWD[OEM_ID]);

function isCustomAccount($oemid)
{
  if  ($oemid=="K01") return true;
}
define("CUST_ADMIN_K01","pipegisadmin");
define("CUST_ADMIN_C13","rpic");
function checkCustomAccount($oemid, $username)
{//check if custom account follow rule and existed
  if  ($oemid=="K01"){ 
    if ((preg_match("/^pipegis[0-9]{1,4}/",$username))
		  or ($username==CUST_ADMIN_K01) ) 
        if (getUserID ($username)=="") throw new exception("108");
        else return true;
    else return false; //not match
  }else if  ($oemid=="C13"){ 
    if ($username==CUST_ADMIN_C13) 
        if (getUserID ($username)=="") throw new exception("108");
        else return true;
    else return false; //not match
  }else{
    return true;
  }   
}

function checkSiteAccountRule($oemid,$siteAccount)
{
  if ($oemid=="K01"){ 
    if (preg_match("/^[0-9A-Z'-]{5,15}$/",$siteAccount)) 
    { //correct site format
        return true;
    }else if (checkCustomAccount($oemid,$siteAccount)) 
    {
    	  if (DEBUG_FLAG == "ON") error_log("debug:checkCustomAccount:".$siteAccount.";");
        return true;
    }
  }else if ($oemid=="T05"){
    if (preg_match("/^[0-9A-Z'-]{5,15}$/",$siteAccount))
    { //correct site format
        return true;
    }
  }else if ($oemid=="T04"){
    if (preg_match("/^[0-9A-Z'-]{6,15}$/",$siteAccount)) //2017Aug1
    { //correct site format
        return true;
    } 
  }else if ($oemid=="C13"){
    if (preg_match("/^[0-9]{3,}-[0-9]{2,}$/",$siteAccount))
      return true;
    else if (checkCustomAccount($oemid,$siteAccount)){
      if (DEBUG_FLAG == "ON") error_log("debug:checkCustomAccount:".$siteAccount.";"); 
      return true;
    }
  }else{
    if (preg_match("/^[0-9A-Za-z'-]{5,32}$/",$siteAccount))
    { //correct site format
        return true;
    }
  }
  return false;
}

function getUID($mac)
{
	global $CamCID;
	if (strlen($mac)==18) return $mac;
  $uid="";
  foreach ($CamCID as $cid => $prefix)
  {
     if ( preg_match("/^".$prefix."/",strtoupper($mac)) )
      $uid = $cid. "-".strtoupper($mac);
  }
  if ($uid=="") $uid=$mac;
  return $uid;
}

function isLAN()//default internet
{
    //need to debug why judge fail in T04
    $web_url = explode (":",$_SERVER['HTTP_HOST']);
    $publicip=gethostbyname($web_url[0]);//61.216.61.162

    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" ) 
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
if (isset($_REQUEST['debugadmin'])) echo "pubIP: {$publicip};cIP:{$client_ip}";
    if ( (strpos($client_ip, IPv4_ADDR_PREFIX) !== FALSE) and ((strrpos($client_ip, ".101") !== FALSE) or (strrpos($client_ip, ".102") !== FALSE) or (strrpos($client_ip, ".100") !== FALSE) ) ) {//Load Balancer found
        return false; //internet, X02 lb detect as 1.100
    }else if (strpos($client_ip, $publicip) !== FALSE) {//=public IP
       return true; //LAN
    }else if (strpos($client_ip, IPv4_ADDR_PREFIX) !== FALSE) {
       return true;
    }else if (strpos($client_ip, K01_FIREWALL) !== FALSE) { //K01 FW issue
        return true;
    }
    return false;
}

function getCamName($uid)
{
  $sql="select * from isat.query_info where uid like '%{$uid}'";
  sql($sql,$result_email,$num_email,0);
  for($i=0;$i<$num_email;$i++)
  {
          fetch($db_email,$result_email,$i,0);
          if ($db_email["uid"] == $uid ){
              $CameraNAME = $db_email["name"];
              if ($CameraNAME == $db_email["mac_addr"]) $CameraNAME=""; 
              break;
          }
  }
  return $CameraNAME; 
}

function getURL($uid,$key,$type=""){
  if (isLAN() ){
      $mac_streamserver=getStreamServerInfo($uid,"LAN");
      if ($mac_streamserver!="")
      	if ($type==THUMBNAIL_TAG)
        		$videosrc = "http://".$mac_streamserver."/vod/".$uid."/thumbnail.jpg";
        else
            $videosrc = "http://".$mac_streamserver."/".$uid;
  }else{
      if ($key!=""){
        $mac_streamserver=getStreamServerInfo($uid,"INTERNET");
        if ($mac_streamserver!="")
        		if ($type==THUMBNAIL_TAG)
        			$videosrc = "http://".$mac_streamserver."/vod/".$uid."/thumbnail.jpg"."?key={$key}";
        		else
              $videosrc = "http://".$mac_streamserver."/".$uid."?key={$key}";
      }
  }
  return $videosrc;
}

function getStreamServerInfo($uid,$type)
{
    if (strlen($uid)==18)
      $sql="select is_signal_online,ip_addr,port,internal_ip_addr from isat.query_info where uid='{$uid}' and internal_port=5544";
    else $sql="select is_signal_online,ip_addr,port,internal_ip_addr from isat.query_info where mac_addr='{$uid}' and internal_port=5544";
    sql($sql,$result,$num,0);
    if ($result){
        fetch($arr,$result,0,0);
        if ($type=="LAN"){
          //if ($arr['is_signal_online']=="true")
            return $arr["internal_ip_addr"].":5544";
        }else{
            //if ($arr['is_signal_online']=="true")
            return $arr["ip_addr"].":".$arr["port"];
        }
    }
    return "";
} 

function getToken($user,$pwd)
{
  if (file_exists("/var/www/SAT-CLOUDNVR/include/index_title.php")){
    //$url="http://".$_SERVER['HTTP_HOST']."/backstage_token_auth.php?user={$user}&pwd={$pwd}&command=authenticate";
    $url="http://127.0.0.1/backstage_token_auth.php?user={$user}&pwd={$pwd}&command=authenticate";
/*  }else if (file_exists("/var/www/qlync_admin/doc/config.php")){
    require_once ("/var/www/qlync_admin/doc/config.php");
    global $api_id, $api_pwd, $api_ip;//$api_path;
    $url="http://{$api_id}:{$api_pwd}@{$api_ip}/backstage_token_auth.php?user={$user}&pwd={$pwd}&command=authenticate";
*/  }else return "";
   	$ch = curl_init();
  	curl_setopt($ch, CURLOPT_URL,$url);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  	$result=curl_exec($ch);
  	curl_close($ch);
  	$content=array();
  	$content=json_decode($result,true);
  	if($content["status"]=="success")
  	{
      return $content["key"];
  	}
    return "";
} 

function getErrorCode($errmsg)
{
	global $errorMSG;
    foreach ($errorMSG as $key => $value)
    {
        if ($value == $errmsg)
            return $key; 
    }
    return "404";
}

function httpError($code, $message) {
    header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
    echo $message . "\n";
    exit(0);
}

function sql(&$sql,&$result,&$num,$err)
{
		if($err ==""|| err=="1")
		{
			$result=mysql_query($sql);
			if(!$result)
			{
				$num="";
				echo mysql_error();
			}
			else
			{
        if (is_bool($result)=== false) //jinho added, resource to proceed
				  $num=mysql_num_rows($result);
			}
		}
		if($err>0)
			echo "<BR>{$sql}<BR>";
}
function fetch(&$db, &$result,$i)
{
		mysql_data_seek($result,$i);
		$db=mysql_fetch_array($result,MYSQL_BOTH);
}

function isMobile()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
	return true;
else if (strstr($useragent,'iPhone') || strstr($useragent,'iPad') )
	return true;
else return false;
}

function printMobileMeta()
{
  $meta = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=2.0\" />";
  echo $meta;
}
function getClientIP()
{
    $client_ip=""; //limit 63 char = 4 x IP(15)
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) )
			if ($_SERVER['HTTP_CLIENT_IP']!=$_SERVER['HTTP_X_FORWARDED_FOR']) 
        $client_ip .= "/" . $_SERVER['HTTP_X_FORWARDED_FOR'];
    $client_ip = ltrim($client_ip,"/");
    //pick one
    if (isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED'];
    $client_ip = ltrim($client_ip,"/");
    if(isset($_SERVER['REMOTE_ADDR'])){
    		if ($client_ip != $_SERVER['REMOTE_ADDR'])
        	$client_ip .= "/" . $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_VIA']) and ($_SERVER['HTTP_VIA']!=$_SERVER['REMOTE_ADDR']) )
            $client_ip .= "/" . $_SERVER['HTTP_VIA'];
    }
    $client_ip = ltrim($client_ip,"/");
    return $client_ip;
}
?>