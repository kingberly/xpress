<?php
/****************
 *Validated on Dec-11,2016,
 * share API on web with log feature
 * add Zavio camera share feature 
 * add download feature: ?command=downloadid&recording_id=  
 * add download feature: ?command=download&path=  
 * /var/www/SAT-CLOUDNVR/manage  
 *Writer: JinHo, Chang 
*****************/
#for web
require_once('../include/global.php');
require_once('../include/db_function.php');
include_once('../include/utility.php');
//AddShare from backstage_share.php
//ProcessCommand from manage_user.php function
include_once('../include/license_db_function.php');
include_once('../include/log_db_function.php');
include_once('../include/password.php'); 
define("USER_EMAIL_POSTFIX","@safecity.com.tw");
define("USER_PWD","Ea9M7gOu586UQaOtXJ3e6f51");
//camera UID support
define("OEM_ID","T04");
 
define("MCID","M04CC");
define("MMAC_PREFIX","184E");
define("ZCID","Z01CC");
define("ZMAC_PREFIX","001B");
//define("BCID","B03CC");
//define("BMAC_PREFIX","0050");
define("IMCID",OEM_ID."MC"); //"T04MC"); 
define("IMMAC_PREFIX","M".OEM_ID);
//ivedaManageUser:ivedaManagePassword@
define('CUSTOMER_DB_NAME', 'customerservice');
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

//align with admin taipeiproj_cht.php

function checkSiteAccountRule($siteAccount)
{
    //if(preg_match("/^[0-9A-Z]{7,15}$/",$siteAccount) ){
        //echo "1234567890123A1";  //echo "123456789012A12";
    if (preg_match("/^[0-9A-Z]{8,15}$/",$siteAccount)) //Jan-11
    { //correct site format
        return true;
    }else if  (preg_match("/^[0-9-]{9,15}$/",$siteAccount)) //Jan-11  
    { //correct site format with hyphen 
        return true;
    }
    return false;
}

function customer_getLink()
{//SIGNAL_DB_XX was in the global.php
    $link=mysql_connect(SIGNAL_DB_HOST,SIGNAL_DB_USERNAME,SIGNAL_DB_PASSWORD);
    if(!$link)
      throw new Exception("Can't connect to DB");
    
    $db_selected=mysql_select_db(CUSTOMER_DB_NAME,$link);
    if(!$db_selected)
      throw new Exception("Can't open ".CUSTOMER_DB_NAME);
    return $link;
}

function customer_close_db($result,$link){
    mysql_close($link);
}
//from backstage_recording_list.php
function downloadByID($recording_id) {//($data_db, $recording_id, $user_id) {
    if (!isset($recording_id)) {
        httpError('404 Not Found', 'Invalid recording id');
    }
  $data_db = new DataDBFunction(); 
    $table = 'recording_list';
    $condition = 'recording_list.id = ?';
    $columns = 'recording_list.device_uid, recording_list.path';
    $params = $recording_id;
    $recording = $data_db->QueryRecordDataOne($table, $condition, $columns, $params);
    if (!$recording) {
        httpError('404 Not Found', 'Invalid recording id');
    }
    
    /*if ( !checkViewingPermission($data_db, $recording['device_uid'], $user_id) ) {
        httpError('403 Forbidden', 'You do not have the permission to access this file');
    }
    else*/ {
        $path = preg_replace( '/^\/vod/', 
                RECORDING_STORAGE, 
                $recording['path'] );
                
        if ( !file_exists($path) ) {
            httpError('404 Not Found', 'cannot find the recording file');
        }
        $name = basename($path);
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Length: ' . filesize($path));
        @readfile($path);
        exit();
    }
}
function httpError($code, $message) {
    header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
    echo $message . "\n";
    exit(0);
}
function download($path)
{
        $path = preg_replace( '/^\/vod/', 
                RECORDING_STORAGE, 
                $path );
                
        if ( !file_exists($path) ) {
            httpError('404 Not Found', 'cannot find the recording file');
        }
        $name = basename($path);
        header('Content-Type: video/mp4');
        header('Content-Disposition: attachment; filename=' . $name);
        header('Content-Length: ' . filesize($path));
        @readfile($path);
        exit();

}
//end of backstage_recording_list.php

function InsertShareLog($user_info, $action, $result) {
    if ($user_info === FALSE) {
        throw new Exception('Invalid parameters.');
    }
    $user_agent= parse_user_agent();    
    if (!is_null($user_agent))     
      $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";
    else $user_agent_str = "";
    $client_ip="";//limit 63 char = 4 x IP(15)
    if(isset($_SERVER['HTTP_CLIENT_IP']))
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_FORWARDED_FOR'];
    //pick one
    if(isset($_SERVER['HTTP_X_FORWARDED']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED'];

    if(isset($_SERVER['REMOTE_ADDR'])){
        $client_ip .= "/" . $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_VIA']) and ($_SERVER['HTTP_VIA']!=$_SERVER['REMOTE_ADDR']) )
            $client_ip .= "/" . $_SERVER['HTTP_VIA'];
    }
    //$client_ip .= " :". $user_agent_str; //T04 no user agent
    $link = customer_getLink();    
/*    $sql ="insert into customerservice.share_log (mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr, user_agent) values
    ('".$user_info["mac"]."','".$user_info['owner_id']."','".$user_info['owner_name']."','".$user_info['visitor_id']."','".$user_info["visitor_name"]."',  '{$action}','{$result}','{$client_ip}','{$user_agent_str}' )";
    */
  $sql ="insert into customerservice.share_log (mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr) values
    ('".$user_info["mac"]."','".$user_info['owner_id']."','".$user_info['owner_name']."','".$user_info['visitor_id']."','".$user_info["visitor_name"]."',  '{$action}','{$result}','{$client_ip}' )";
    //echo $sql;
    $result=mysql_query($sql,$link);
    //customer_close_db($result,$link);
}
  
function getErrorCode($errmsg)
{
  $errorMSG = [
    "101" => "Invalid user account",  //user
    "102" => "Dupliated name or register email",  //user
    "103" => "Unknown command.",  //user
    "104" => "User not found.",  //user
    "105" => "Account Format Error.",  //user
    "106" => "Account Exist.",  //user
    "107" => "Unauthorize to delete common user account",  //user
    "201" => "Device not found.",  //mac
    "202" => "Device is shared to requested account.",  //mac
    "203" => "Device is NOT shared to requested account.",  //mac
    "204" => "Invalid MAC format",  //mac
    "205" => "Device does not belong to any user",  //mac    
    "301" => "Invalid command"   //command
  ];
    foreach ($errorMSG as $key => $value)
    {
        if ($value == $errmsg)
            return $key; 
    }
    return "404";
}
//align with admin taipeiproj_cht.php
function getUID($mac)
{
  if ( preg_match("/^".MMAC_PREFIX."/",strtoupper($mac)) )
    $uid = MCID. "-".strtoupper($mac);
  else if ( preg_match("/^".ZMAC_PREFIX."/",strtoupper($mac)) )
    $uid = ZCID. "-".strtoupper($mac);
  else if ( preg_match("/^".IMMAC_PREFIX."/",strtoupper($mac)) )
    $uid = IMCID. "-".strtoupper($mac);
  else return $mac;
  return $uid;
}
//from manage_user.php
function ProcessCommand($command,$email,$name) {

    switch ($command) {
        case 'delete':

            $data_db = new DataDBFunction();

            // Get user
            $table = 'user';
            $condition = "reg_email=:reg_email AND oem_id=:oem_id";
            $params = array(':reg_email'=>$email,   ':oem_id'=>OEM_ID);
            $user = $data_db->QueryRecordDataOne($table, $condition, '*', $params);
            if ($user === false) {
                throw new Exception("Invalid user account");
            }

            // Get devices uid by owner_id.
            $table = 'device';
            $condition = "owner_id=?";
            $columns_to_get = 'DISTINCT uid';
            $devices = $data_db->QueryRecordDataArray($table, $condition, $columns_to_get, '', $user['id']);

            // Delete device
            foreach ($devices as $device) {
                // Delete device, position, memcache, and release series_number
                $data_db->DeleteDevicesByUid($device['uid']);
            }

            // Delete device_event_info
            $table = 'device_event_info';
            $condition = "owner_name=?";
            $data_db->DeleteRecordData($table, $condition, $user['name']);

            // Delete user
            $table = 'user';
            $condition = "id=?";
            $data_db->DeleteRecordData($table, $condition, $user['id']);

            // Save user log
            try {
                $log_db = new LogDBFunction();
                $log_db->InsertUserLog($user, 'DELETE', 'SUCCESS', ($user['google_openid']) ? 'google' : 'sat', NULL);
                // Save share user log
            }
            catch (Exception $e) {
                // Do nothing.
            }
            //add log for future API access
             try {
                  
                  //$userLogArr['mac']="";$userLogArr['owner_id']="";$userLogArr['owner_name']="";
                  $userLogArr['visitor_id']=$user['id'];
                  $userLogArr['visitor_name']=$user['name'];
                  InsertShareLog($userLogArr, "DELETE S_ACCT", "SUCCESS" );             }
              catch (Exception $e) {
                  // Do nothing.
                  //echo $e->getMessage();
              }
            break;

        case 'add':
            $data_db = new DataDBFunction();

            // Get user
            $table = 'user';
            $condition = "reg_email=:reg_email OR name=:name";
            $params = array(':reg_email'=>$email,   ':name'=>$name);
            $user = $data_db->QueryRecordDataOne($table, $condition, '*', $params);
            if ($user !== false) {
                throw new Exception("Dupliated name or register email");
            }
            
                $data_db->InsertUser(1, // caller group id
                        "", // not from user_reg
                        1, // normal user
                        $name, 
                        password_hash(USER_PWD, PASSWORD_DEFAULT), 
                        $email, // required data
                        3, // matrix display mode
                        "", // reg date
                        "", // expire date
                        OEM_ID );
            //add log for future API access
             try { //always fail
                   //$userLogArr['mac']="";$userLogArr['owner_id']="";$userLogArr['owner_name']="";$userLogArr['visitor_id']="";
                  $userLogArr['visitor_name']=$name;
                  InsertShareLog($userLogArr, "ADD S_ACCT", "SUCCESS");
              }
              catch (Exception $e) {
                  // Do nothing.
                  //echo $e->getMessage();
              }         
            break;

        default:
            throw new Exception("Unknown command.");
    }
}
//from backstage_share.php
function AddShare($owner_id, $uid, $visitor_name) {
    $data_db = new DataDBFunction();
    $params = array(':uid'=>$uid, ':owner_id'=>$owner_id);
    $condition_device = "uid = :uid AND owner_id = :owner_id AND purpose in ('RVLO','WBMJ')";
    if ( ( $device_row = $data_db->QueryRecordDataOne('device', $condition_device, 'id', $params ) ) == false) {
        throw new Exception('Device not found.');
    }
    else {
        $device_id = $device_row['id'];
    }
    
    $condition_visitor = "name = ?";
    if ( ( $visitor_row = $data_db->QueryRecordDataOne('user', $condition_visitor, 'id', $visitor_name ) ) == false) {
        throw new Exception('User not found.');
    }
    else {
        $visitor_id = $visitor_row['id'];
    }
  
  //jinho added for duplicate mac assign check
  $condition_unique = "uid = :uid AND visitor_id = :visitor_id";
  $params = array(':uid'=>$uid, ':visitor_id'=>$visitor_id);
    if ( ( $visitor_row = $data_db->QueryRecordDataOne('device_share', $condition_unique, 'id', $params ) ) == true)
  {
        throw new Exception('Device is shared to requested account.');
    }
  //jinho added end
    
    $dbi = new DbInsert($data_db->db);
    $dbi->setErrorMessage('Share device failed.');
    $dbi->setTable('device_share');
    $dbi->add('owner_id', $owner_id);
    $dbi->add('uid', $uid);
    $dbi->add('visitor_id', $visitor_id);
    $dbi->execute();
    
    $dbi->clear();
    $dbi->setTable('position');
    $dbi->add('owner_id', $visitor_id);
    $dbi->add('device_id', $device_id);
    $dbi->execute();
  
  //add log for future API access
   try {
        $userLogArr['mac']=$uid;
        $userLogArr['owner_id']=$owner_id;
        $userLogArr['owner_name']=getUserName($owner_id);
        $userLogArr['visitor_id']=$visitor_id;
        $userLogArr['visitor_name']=$visitor_name;
        InsertShareLog($userLogArr, "ADD", "SUCCESS");
    }
    catch (Exception $e) {
        // Do nothing.
        //echo $e->getMessage();
    }
} 
 
function getEmail($username) {
  // Get user
/*  $data_db = new DataDBFunction();
  $table = 'user';
  $condition = "name=:name";
  $params = array(':name'=>$username);
  $email = $data_db->QueryRecordDataOne($table, $condition, 'reg_email', $params);
*/
  $email = strtolower($username).USER_EMAIL_POSTFIX;    
  return $email;
}

function getUserName($id) {
  // Get user
  $data_db = new DataDBFunction();
  $table = 'user';
  $condition = "id=:id";
  $params = array(':id'=>$id);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'name', $params);
  
  return $user_id["name"];
}

function getUserID($username) {
  // Get user
  $data_db = new DataDBFunction();
  $table = 'user';
  $condition = "name=:name";
  $params = array(':name'=>$username);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'id', $params);
  
  return $user_id["id"];
}

function getUserIDByMAC($mac) {
  // Get user
  $uid = getUID($mac);
  $data_db = new DataDBFunction();
  $table = 'device';
  $condition = "uid=:uid";
  $params = array(':uid'=>$uid);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'owner_id', $params);
  
  return $user_id["owner_id"];
}

function checkSiteAccount($username) {
  //check username format
    if ( checkSiteAccountRule($username) ) 
  {
    //bypass
  }else{
        throw new Exception('Account Format Error.');
    }
  
  
  $email = strtolower($username).USER_EMAIL_POSTFIX;
    try {
      //check exist
      if ( getUserID ($username)!="" ) throw new Exception('Account Exist.');
      ProcessCommand("add",$email,$username);
    }
    catch(Exception $retParam){ 
        //echo $retParam->getMessage();
    }         
}

//from backstage_share.php
function RemoveShare ($owner_id, $uid, $visitor_id) {
    $data_db = new DataDBFunction();
  //jinho added for duplicate mac assign check
  $condition_unique = "uid = :uid AND visitor_id = :visitor_id";
  $params = array(':uid'=>$uid, ':visitor_id'=>$visitor_id);
    if ( ( $visitor_row = $data_db->QueryRecordDataOne('device_share', $condition_unique, 'id', $params ) ) == true)
  {  //exist, bypass
        //throw new Exception('Device is shared to requested account.');
    }else{
    throw new Exception('Device is NOT shared to requested account.');
  }
  //jinho added end
    $table = 'position';
    $condition = 'owner_id = :visitor_id AND device_id IN (SELECT device.id FROM device WHERE device.uid = :uid)';
    $params = array(':visitor_id'=>$visitor_id, ':uid'=>$uid);  
    $data_db->DeleteRecordData($table, $condition, $params);

    $table = 'device_share';
    $condition = 'owner_id = :owner_id AND visitor_id = :visitor_id AND uid = :uid';
    $params[':owner_id'] = $owner_id;
    $data_db->DeleteRecordData($table, $condition, $params);
}

function deleteSiteAccount($username) {
    //get email and perform delete in manage_user
    //$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$oem}";
    try {
      //check username format
        if ( checkSiteAccountRule($username) )  
      {
        //bypass
      }else{
            throw new Exception('Unauthorize to delete common user account');
        }
      $visitor_id = getUserID($username);
      if ( !is_numeric($visitor_id) )
        throw new Exception("Invalid user account");      
      $email = getEmail($username);

        ProcessCommand("delete",$email,$username);
    }
    catch(Exception $retParam){ 
        throw new Exception($retParam->getMessage());
    }
}

function assignShareDevice($mac, $username) {
    //get mac userid
    if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))  throw new Exception("Invalid MAC format");
    $owner_id = getUserIDByMAC($mac);
    if ( !is_numeric($owner_id) )
      throw new Exception("Device does not belong to any user");
    //$visiter_id = getUserID($username);
    $uid = getUID($mac);
    checkSiteAccount($username);
   //echo $owner_id. " ".$visiter_id." ". $uid;
    try {
        AddShare($owner_id,$uid,$username);
    }
    catch(Exception $retParam){ 
        throw new Exception($retParam->getMessage()); 
    }
}

function deleteShareDevice($mac, $username) {
    if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))  throw new Exception("Invalid MAC format");
    $owner_id = getUserIDByMAC($mac);
    //$visiter_id = getUserID($username);
    $visitor_id = getUserID($username);
    $uid = getUID($mac);
    if ( !is_numeric($owner_id) or !is_numeric($visitor_id) )
      throw new Exception("Device does not belong to any user");
    try {    
    RemoveShare ($owner_id, $uid, $visitor_id );
    }
    catch(Exception $retParam){ 
        throw new Exception($retParam->getMessage()); 
    }
  //add log for future API access
   try {
        $userLogArr['mac']=$uid;
        $userLogArr['owner_id']=$owner_id;
        $userLogArr['owner_name']=getUserName($owner_id);
        $userLogArr['visitor_id']=$visitor_id;
        $userLogArr['visitor_name']=$username;
        InsertShareLog($userLogArr, "DELETE", "SUCCESS");
    }
    catch (Exception $e) {
        // Do nothing.
        //echo $e->getMessage();
    }
}

$ret = array();
$ret['status'] = 'success';
$ret['error_msg'] = '';

try {
    switch($_REQUEST['command']) {
    case 'share_camera':
        assignShareDevice($_REQUEST['mac'], $_REQUEST['user_name']);
        break;
    case 'add_account':
    checkSiteAccount($_REQUEST['user_name']);
        break;
    case 'delete_account':
    deleteSiteAccount($_REQUEST['user_name']);
        break;
    case 'unshare_camera':
    deleteShareDevice($_REQUEST['mac'], $_REQUEST['user_name']);
    break;
  case 'downloadid':
    downloadByID($_REQUEST['recording_id']);
    break;
  case 'download':
    download($_REQUEST['path']);
    break;
    default:
        throw new Exception('Invalid command');
        break;
    }
}catch (Exception $e) {
  $ret['status'] = 'fail';
    $ret['error_msg'] = $e->getMessage();
  if (getErrorCode($ret['error_msg'])=="202" ){//update for SOP
      $ret['status'] = 'success';
      $err ="SUCCESS:".getErrorCode($ret['error_msg']); 
      
  }else{
      $err ="FAIL:".getErrorCode($ret['error_msg']);
  }
  $userLogArr['mac']=$_REQUEST["mac"];
  //$userLogArr['owner_id']="";$userLogArr['owner_name']="";$userLogArr['visitor_id']="";
  $userLogArr['visitor_name']=$_REQUEST["user_name"];
  InsertShareLog($userLogArr, $_REQUEST["command"], $err);
}
echo json_encode($ret);
?>