<?php
/****************
 * Validated on Jul-3,2017,
 * include file for share API     
 *Writer: JinHo, Chang
*****************/
if (! defined("EMAIL_POSTFIX")) define("EMAIL_POSTFIX","@safecity.com.tw");
function getValueByType($type,$data,$outType="*")
{
  $sql="select {$outType} from customerservice.workeyegis WHERE ";
  if (($type == "id") or ($type == "is_installer") or ($type == "is_public")) $sql.="{$type}={$data}";
  else $sql.="{$type}='{$data}'";
  sql($sql,$result,$num,0);
  if ($num==1){
    fetch($arr,$result,0,0);
    return $arr[$outType]; 
  }
  return null;
}

function getValueByID($id,$type="")
{
  $sql="select * from customerservice.workeyegis where id={$id}";
  sql($sql,$result,$num,0);
  if ($num==1){
    fetch($arr,$result,0,0);
    if ($type!="")
    	return $arr[$type];
    else return $arr['ACNO']; 
  }
  return "";
}

function getUserID($name)
{
  $sql="select id from isat.user where name='{$name}'";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  if ($num > 0) return intval($arr['id']);
  return -1;
}
//SUCCESS, FAIL
define("MAP_ON","enable_map");
define("MAP_OFF","disable_map");
//$user_info['mac']="";$user_info['owner_id']="";$user_info['owner_name']="";
//$user_info['visitor_id']=$user['id'];$user_info['visitor_name']=$user['name'];
function InsertShareLog($user_info, $action, $action_result) {
    $user_agent= parse_user_agent();    
    if (!is_null($user_agent))    
      $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";
    else $user_agent_str = "";
    $client_ip = getClientIP();
		$sqlu = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'customerservice' AND TABLE_NAME = 'share_log' AND COLUMN_NAME = 'user_agent'";
		sql($sqlu,$resultu,$numu,0);
		if ($numu==0)
			$sql ="insert into customerservice.share_log 
			(mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr) values
    ('".$user_info["mac"]."','".$user_info['owner_id']."','".$user_info['owner_name']."','".$user_info['visitor_id']."','".$user_info["visitor_name"]."',  '{$action}','{$action_result}','{$client_ip}{$user_agent_str}' )";
    else $sql ="insert into customerservice.share_log 
		(mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr, user_agent) values
    ('".$user_info["mac"]."','".$user_info['owner_id']."','".$user_info['owner_name']."','".$user_info['visitor_id']."','".$user_info["visitor_name"]."',  '{$action}','{$action_result}','{$client_ip}','{$user_agent_str}' )";
    //echo $sql;
		sql($sql,$result,$num,0);
    if (!$result) die("InsertLog Database Link Fail"); 
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
function insertEndUserAPI ($name,$pwd="") //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
    $email=$name.EMAIL_POSTFIX;
    if ($pwd == "") $password=APP_USER_PWD; //if not set
    else $password=$pwd;
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
      if (!preg_match("/^[0-9a-zA-Z-]{4,32}/",$name)) return false;
      if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$password)) return false;
      $email = strtolower($email);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $params = array(
                'command'=>'add',
                'name'=>"{$name}",
                'pwd'=>"{$password}",
                'reg_email'=>"{$email}",
                'oem_id'=>"{$oem}" );
       
        $url = "http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?" . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        $content=json_decode($result,true);
        if($content["status"]=="success")
        {
            $sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','s','".date("Y-m-d H:i")."','{$oem}')";
            sql($sql,$result_tmp,$num_tmp,0);
            return true;
        }
if (isset($_REQUEST["debugadmin"])) var_dump($content);
      return false;
}
function deleteEndUserAPI ($name)
{
  global $oem, $api_id, $api_pwd, $api_path;
  $email=strtolower($name).EMAIL_POSTFIX;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$oem}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
    {
      $sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($email)."' and Oem_id='{$oem}'";
      sql($sql,$result_tmp,$num_tmp,0);
      return true;
    }
if (isset($_REQUEST["debugadmin"])) var_dump($content);
    return false;
}
//qlync API enabled after v3.x.x
function addShareDeviceAPI ($mac, $username)
{//add_share, disable_share, enable_share, remove_share
   global $oem, $api_id, $api_pwd, $api_path;
      $params = array(
              'command'               => 'add_share',
              'mac_addr'              => $mac,
              'user'              => $username,
      );
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_device.php?".http_build_query($params);
//if (isset($_REQUEST["debugadmin"])) echo $import_target_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
    if($content["status"]=="success")
      return true;
if (isset($_REQUEST["debugadmin"])) var_dump($content);
    return false;
}
function deleteShareDeviceAPI ($mac, $visitor_id)
{//add_share, disable_share, enable_share, remove_share
   global $oem, $api_id, $api_pwd, $api_path;
      $params = array(
              'command'               => 'remove_share',
              'mac_addr'              => $mac,
              'visitor_id'              => $visitor_id,
      );
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_device.php?".http_build_query($params);
//if (isset($_REQUEST["debugadmin"])) echo $import_target_url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
    if($content["status"]=="success")
      return true;
if (isset($_REQUEST["debugadmin"])) var_dump($content);
    return false;
}
function myShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=share_camera&mac={$mac}&user_name={$username}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
    if($content["status"]=="success")
      return true;
    return false;
}
function mydeleteShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=unshare_camera&mac={$mac}&user_name={$username}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
if (isset($_REQUEST["debugadmin"])) var_dump($content);
    if($content["status"]=="success")
      return true;
    return false;
}

function addSCIDShareDeviceAPI($uid,$scidid)
{
  if ($scidid=="") return false;
  $macarr = explode("-", $uid);
  //{$_REQUEST["scid"]}{$_REQUEST["aid"]}{$_REQUEST["rid"]}".str_pad($fid_tmp,3,"000",STR_PAD_LEFT)."1" //003311 xxx 1
  $group_str = $scidid."311";//temple 3, believer 11
  $group_id=intval($group_str);
  $sql="select name,LPAD(group_id,'10','0000000000')as group_str from isat.user where group_id like '{$group_id}%'";//find group user
  sql($sql,$result,$num,0);
  if (!$result) return false;
  echo "Processed account {$num}<br>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    if (addShareDeviceAPI($macarr[1],$arr['name']))
      echo $arr['name']." Shared<br>";
     else echo $arr['name']." Not shared<br>";
     
  }
  return true;
}
 
function insertGISLOG($service)
{
  $user_agent= parse_user_agent();    
  if (!is_null($user_agent))    
    $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";

	$sql = "INSERT INTO customerservice.gis_log SET
  bind_account='{$service['bind_account']}',
  ACNO='{$service['ACNO']}',
  DIGADD='{$service['DIGADD']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  action='{$service['action']}',
  result='{$service['result']}',
  ip_addr='{$_SERVER['REMOTE_ADDR']}',
  user_agent='{$user_agent_str}',
  mac='{$service['note']}'";
	sql($sql,$result,$num,0);
	return $result;
}

?>