<?php
//Admin_web_language=zh_TW.UTF-8 //en_US.UTF-8
if (strpos(getLang(),"zh_TW")!==FALSE){
  define("BTN_DOWNLOAD","下載");
  define("TXT_PLAYBACK","影片回放");
  define("BTN_AUTH","回放權限驗證");
}else{
  define("BTN_DOWNLOAD","Web Download");
  define("TXT_PLAYBACK","Playback");
  define("BTN_AUTH","Authenticate");
}

function getCamName($mac)
{
  //$sql="select * from isat.query_info where user_name='{$_SESSION["Contact"]}' group by mac_addr order by mac_addr";
  //mac_addr like '%{$mac}'";
  $sql="select * from isat.query_info where mac_addr='{$mac}'";
  sql($sql,$result_email,$num_email,0);
  for($i=0;$i<$num_email;$i++)
  {
    fetch($db_email,$result_email,$i,0);
    if ($db_email["mac_addr"] == $mac )
      return "";
    else  return $db_email["name"];
  }
  return null;
}
function getLang(){
  $sql="select Content from qlync.oem_info where Cat1='Admin Web Portal' and Cat2='Admin_web_language'";
  sql($sql,$result_lan,$num_lan,0);
  if ($num_lan > 0){
    fetch($db_lan,$result_lan,0,0);
    $lan=$db_lan["Content"];
    return $lan;
  }
  return "en_US";
}
if ($oem=="X02")  define("IPv4_ADDR_PREFIX","192.168.");  
else define("IPv4_ADDR_PREFIX",getsubLanIP(getLanIP()));
define("K01_FIREWALL","10.10.0.2");
function getsubLanIP($ipaddr)
{
  $ip=explode(".",$ipaddr);
  return "{$ip[0]}.{$ip[1]}.{$ip[2]}.";
}
function getLanIP()
{
  $res = shell_exec("/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'");
  $res= rtrim($res, "\n");
  return $res; 
}
function isLAN()
{
    $web_url = explode (":",$_SERVER['HTTP_HOST']);
    $publicip=gethostbyname($web_url[0]);//61.216.61.162
//echo $publicip;
    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" )
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
//echo $client_ip;
     if (strpos($client_ip, $publicip) !== FALSE) {//=public IP
       return true; //LAN
    }else if (strpos($client_ip, IPv4_ADDR_PREFIX) !== FALSE) {
       return true;
     //}else if (strpos($client_ip, K01_FIREWALL) === 0) {
     //   return true;
     }
     return false;
}

function getMonthPicker($tag, $url,&$datefolder)
{//alter $_REQUEST['datefolder'] <==>$datefolder
  $uid = "";
  if (strlen($url) <= 18){
      $uid = $url; //pass mac
  }else { //real url
  // vod/mac/date folder/video file name
    $req_var = explode("/",$url);
    $uid = $req_var[2];
    if ($datefolder=="")  $datefolder= $req_var[3];
  }
  if ($uid == "") return;
  $sql ="select substr(start,1,8) as date from isat.recording_list where path like '%{$uid}%' group by substr(start,1,8)";
    sql($sql,$result,$num,0);
  $html="<select name={$tag}  onchange=\"optionValue(this.form.{$tag}, this);this.form.submit();\"><option value=''></option>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    if ($datefolder == $arr['date'])
      $html.="<option value='".$arr['date']."' selected>".$arr['date']."</option>";
    else if (($datefolder == "") and ($i==($num-1)) ){ //default use last date
      $html.="<option value='".$arr['date']."' selected>".$arr['date']."</option>";
      $datefolder = $arr['date'];
    }else   $html.="<option value='".$arr['date']."'>".$arr['date']."</option>";
  }

  $html.="</select>";
  echo $html; 
}

function getToken($user,$pwd)
{
  global $api_id, $api_pwd, $api_ip;//$api_path;
  //$web_url = explode (":",$_SERVER['HTTP_HOST']);
  $url="http://{$api_id}:{$api_pwd}@{$api_ip}/backstage_token_auth.php?user={$user}&pwd={$pwd}&command=authenticate";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
    {
      $_SESSION["stream_token_key_expire"] = $content["expire"]; 
      $_SESSION["stream_token_key"] = $content["key"];
      return $content["key"];
    }else return "";
}
function getStreamServerInfo($uid,$type)
{//uid or mac
    if (strlen($uid)==18)
      $sql="select * from isat.query_info where uid='{$uid}' and internal_port=5544";
    else $sql="select * from isat.query_info where mac_addr='{$uid}' and internal_port=5544";
    sql($sql,$result,$num,0);
    if ($result){
        fetch($arr,$result,0,0);
        if ($type=="LAN"){
          //if ($arr['is_signal_online']=="true") //online
            return $arr["internal_ip_addr"].":5544";
        }else{
            //if ($arr['is_signal_online']=="true")
            return $arr["ip_addr"].":".$arr["port"];
        }
    }
    return "";
}
function createMyList($url, $param)
{//get id from isat.cloud_event
    $sql = "select * from isat.recording_list {$param}";
    sql($sql,$result,$num,0);
    $html="<select name=url onchange=\"optionValue(this.form.url, this);this.form.submit();\">>";
    $html.="<option value=''></option>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($url == $arr['path'])
        $html.="<option value='".$arr['path']."' selected>".StringToDateTime($arr['start'])."-".StringToDateTime($arr['end'])."</option>";
      else
      $html.="<option value='".$arr['path']."'>".StringToDateTime($arr['start'])."-".StringToDateTime($arr['end'])."</option>";
    }
    $html.="</select>";
    echo $html;
    //echo StringToDateTime($arr['start']);
}
function StringToDateTime($string) {
global $time_zone;
  if ($string == "") return "";
  $date_string = substr($string, 0, 4) . '-' .
      substr($string, 4, 2) . '-' .
      substr($string, 6, 2) . ' ' .
      substr($string, 8, 2) . ':' .
      substr($string, 10, 2) . ':' .
      substr($string, 12, 2);
    
  //return new DateTime($date_string, new DateTimeZone('UTC'));
  $utcdate= new DateTime($date_string, new DateTimeZone('UTC'));
  //$server_tz = date_default_timezone_get();
  //echo $server_tz;
  //date_default_timezone_set($server_tz);
  $utcdate->setTimezone( new DateTimeZone($time_zone) ); 
  return $utcdate->format("Y-m-d H:i:s"); 
}
function getRecordingID($url)
{
  $sql = "select id from isat.recording_list where path ='{$url}'";
  sql($sql,$result,$num,0);
  if ($result){
    fetch($arr,$result,0,0);
    return $arr['id'];
  }else{
    return "";
  }
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

function chkSourceFromMegasys(&$cip)//true / false
{
  $MegasysIP = [
  "118.163.90.31",
  "59.124.70.86",
  "59.124.70.90",
  "125.227.139.173",
  "125.227.139.174",
  "123.193.125.132",
  "61.216.61.162"
  ];
    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" ) 
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
    for ($i=0;$i<sizeof($MegasysIP);$i++){
      if (strpos($client_ip, $MegasysIP[$i]) !== FALSE){
        $cip=$MegasysIP[$i];
        return true;
      }
    }
    return false;
}

function getWebURL($protocol,$bLocal=false)
{
    if ($bLocal){  // if (isLAN() ){
      return $protocol.getsubLanIP(getLanIP())."100";
      //global $api_path;
      //$ipsub= explode ("/",$api_path);
      //return $protocol.$ipsub[0];
    }else{
      $loginpage = explode (":",$_SERVER['HTTP_HOST']);
      return $protocol.$loginpage[0];
    }
}

function createOnlineList()
{
  global $api_temp,$userPwd;
    $onlinecsv="{$api_temp}/online_list.csv";
    $csv = array_map('str_getcsv', file($onlinecsv));
    $html="<select name=mac><option value=''>--NA--</option>";
    foreach ($csv as $csvline)
    {
      //if (array_key_exists($csvline[0],$userPwd)){
          $html.="<option value='".$csvline[0]."_".$csvline[1]."'>".$csvline[1]."</option>";
      
      //}else $html.="<option value='".$csvline[0]."_".$csvline[1]."' disabled>".$csvline[1]."</option>";
    }
    $html.="</select>";
    echo $html;

}
?>