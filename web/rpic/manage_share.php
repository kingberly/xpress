<?php
/****************
 *Validated on May-22,2017
 * share API on web with log feature
 * add Zavio camera share feature
 * add download feature: ?command=downloadid&recording_id=  
 * add download feature: ?command=download&path=
 * add K01 special share API for pipegisN and pipegisadmin, add $throwErrFlag  
 * /var/www/SAT-CLOUDNVR/manage (automatic required PHP_AUTH_USER)
* include shared library /rpic.inc   
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
require_once('../include/streamserver.php');  //required for authenticate
include_once( "../include/index_title.php" ); //oem_id
include_once( "../rpic.inc" ); 
//ivedaManageUser:ivedaManagePassword@
define('CUSTOMER_DB_NAME', 'customerservice');
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');


function customer_getLink()
{//SIGNAL_DB_XX was in the global.php
    $link=mysql_connect(SIGNAL_DB_HOST,SIGNAL_DB_USERNAME,SIGNAL_DB_PASSWORD);
    if(!$link) throw new Exception("303");
    
    $db_selected=mysql_select_db(CUSTOMER_DB_NAME,$link);
    if(!$db_selected) throw new Exception("Can't open ".CUSTOMER_DB_NAME);
    return $link;
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

function InsertShareLog($user_info, $action, $action_result) {
    if ($user_info === FALSE) {
        throw new Exception("302");
    }
    $user_agent= parse_user_agent();    
    if (!is_null($user_agent))    
      $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";
    else $user_agent_str = "";
    $client_ip = getClientIP();
		$sqlu = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'customerservice' AND TABLE_NAME = 'share_log' AND COLUMN_NAME = 'user_agent'";
		sql($sqlu,$resultu,$numu,0);
		if ($numu==0)  //if (OEM_ID == "T04")
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
                throw new Exception("101");
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
                throw new Exception("102");
            }
            
                $data_db->InsertUser(1, // caller group id
                        "", // not from user_reg
                        1, // normal user
                        $name, 
                        password_hash(APP_USER_PWD, PASSWORD_DEFAULT), 
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
            throw new Exception("103");
    }
}
//from backstage_share.php
function AddShare($owner_id, $uid, $visitor_name,$throwErrFlag) {
    $data_db = new DataDBFunction();
    $params = array(':uid'=>$uid, ':owner_id'=>$owner_id);
    $condition_device = "uid = :uid AND owner_id = :owner_id AND purpose in ('RVLO','WBMJ')";
    if ( ( $device_row = $data_db->QueryRecordDataOne('device', $condition_device, 'id', $params ) ) == false) {
        if (!$throwErrFlag) return;//skip all exception
        throw new Exception("201");
    }
    else {
        $device_id = $device_row['id'];
    }
    
    $condition_visitor = "name = ?";
    if ( ( $visitor_row = $data_db->QueryRecordDataOne('user', $condition_visitor, 'id', $visitor_name ) ) == false) {
        if (!$throwErrFlag) return;//skip all exception
        throw new Exception("104");
    }
    else {
        $visitor_id = $visitor_row['id'];
    }
  
  //jinho added for duplicate mac assign check
  $condition_unique = "uid = :uid AND visitor_id = :visitor_id";
  $params = array(':uid'=>$uid, ':visitor_id'=>$visitor_id);
    if ( ( $visitor_row = $data_db->QueryRecordDataOne('device_share', $condition_unique, 'id', $params ) ) == true)
  {
        if (!$throwErrFlag) return;//skip all exception
        throw new Exception("202");
    }
  //jinho added end
    
    $dbi = new DbInsert($data_db->db);
    $dbi->setErrorMessage('304');//$dbi->setErrorMessage('Share device failed.');
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
        if (!$throwErrFlag) return;//skip all exception
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

function checkSiteAccount($username)
{
  //check username format
  if ( checkSiteAccountRule(OEM_ID,$username) ) 
  { //bypass 
  }else{
        throw new Exception("105");
  }

    try {//check exist
      if (isCustomAccount(OEM_ID))
        if (!checkCustomAccount($oem_id,$siteAccount)) throw new Exception("108"); 
      if ( getUserID ($username)!="" ) throw new Exception("106");
      $email = strtolower($username).USER_EMAIL_POSTFIX;
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
        //throw new Exception("202");
  }else{
    throw new Exception("203");//throw new Exception('Device is NOT shared to requested account.');
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
        if ( checkSiteAccountRule(OEM_ID,$username) )  
      {
        //bypass
      }else{
            throw new Exception("107");
        }
      $visitor_id = getUserID($username);
      if ( !is_numeric($visitor_id) )
        throw new Exception("101");//throw new Exception("Invalid user account");      
      $email = getEmail($username);

        ProcessCommand("delete",$email,$username);
    }
    catch(Exception $retParam){ 
        throw new Exception($retParam->getMessage());
    }
}

function assignShareDevice($mac, $username) {
    //get mac userid
    if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))  throw new Exception("204");
    $owner_id = getUserIDByMAC($mac);
    if ( !is_numeric($owner_id) )
      throw new Exception("205");//throw new Exception("Device does not belong to any user");

    $uid = getUID($mac);
    checkSiteAccount($username);
    //add first to avoid 202 error skip
    if (isCustomAccount(OEM_ID))//auto add camera to CUST_ADMIN_K01 for common user 
      if (!checkCustomAccount(OEM_ID,$username)) 
        AddShare($owner_id,$uid,CUST_ADMIN_K01,false);//dont throw error
    try {
        AddShare($owner_id,$uid,$username,true);
    }catch(Exception $retParam){ 
        throw new Exception($retParam->getMessage()); 
    }
 
}

function deleteShareDevice($mac, $username) {
    if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))  throw new Exception("204");
    $owner_id = getUserIDByMAC($mac);

    $visitor_id = getUserID($username);
    $uid = getUID($mac);
    if ( !is_numeric($owner_id))
      throw new Exception("205");//throw new Exception("Device does not belong to any user");
    if (!is_numeric($visitor_id) ){
      if (DEBUG_FLAG == "ON") error_log("debug:RemoveShare:visitor_id={$visitor_id}/{$username};uid={$uid}");
      throw new Exception("108");
    }
    if (isCustomAccount(OEM_ID)){
      try {//always delete common_share first
      if (!checkCustomAccount(OEM_ID,$username)) {//auto delete camera to CUST_ADMIN_K01 for common user 
        $visitor_ida = getUserID(CUST_ADMIN_K01);
        RemoveShare ($owner_id, $uid, $visitor_ida );
      }
      }catch(Exception $retParam){ //proceed to next delete 
          //throw new Exception($retParam->getMessage()); 
      }
    }
    
    try {
      if (DEBUG_FLAG == "ON") error_log("debug:RemoveShare:owner_id=".$owner_id.";visitor_id={$visitor_id}/{$username};uid={$uid}");    
      RemoveShare ($owner_id, $uid, $visitor_id );
    }catch(Exception $retParam){ 
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
        if (DEBUG_FLAG == "ON") error_log("debug:share_camera:".$_REQUEST['mac'].";".$_REQUEST['user_name'].";");
        assignShareDevice($_REQUEST['mac'], $_REQUEST['user_name']);
        break;
    case 'add_account':
      checkSiteAccount($_REQUEST['user_name']);
      break;
    case 'delete_account':
      deleteSiteAccount($_REQUEST['user_name']);
      break;
    case 'unshare_camera':
      if (DEBUG_FLAG == "ON") error_log("debug:unshare_camera:".$_REQUEST['mac'].";".$_REQUEST['user_name'].";");
      deleteShareDevice($_REQUEST['mac'], $_REQUEST['user_name']);
      break;
    case 'downloadid':
      downloadByID($_REQUEST['recording_id']);
      break;
    case 'download':
      download($_REQUEST['path']);
      break;
    case 'authenticate': //for godwatch
      $user_id = getUserID($_REQUEST['user_name']);
      $ss = new StreamServer();
      $token = $ss->grantAuthForUser($user_id);
      $ret['key'] = $token['key'];
      $ret['expire'] = $token['expire'];
      break;
    default:
      throw new Exception("301");
      break;
      }
}catch (Exception $e) {
  $ret['status'] = 'fail';
  //$ret['error_msg'] = $e->getMessage();
  $errkey = $e->getMessage();
  $ret['error_msg'] = $errorMSG[$errkey];
 
  if ($errkey=="202" ){//update for SOP
      $ret['status'] = 'success';
      $elog ="SUCCESS:".$errkey; 
      
  }else{
      $elog ="FAIL:".$errkey;
  }
  $userLogArr['mac']=$_REQUEST["mac"];
  //$userLogArr['owner_id']="";$userLogArr['owner_name']="";$userLogArr['visitor_id']="";
  $userLogArr['visitor_name']=$_REQUEST["user_name"];
  InsertShareLog($userLogArr, $_REQUEST["command"], $elog);
}
echo json_encode($ret);
?>