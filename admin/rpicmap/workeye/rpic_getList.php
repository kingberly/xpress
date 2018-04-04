<?php
/****************
 ***get map json array from N99 map file
 *Validated on Feb-7,2018,
 *  http://workeyemap.megasys.com.tw/map/rpic_getList.php?userid=rpic&passwd=KZo3i6UJbKd0bb6B5Suv
 *  user_name / user_pwd 
 *  optional paramter: conntype: direct / file(default) 
 *  optional parameter: oemid
 *  hash("sha256",$RPICAPP_USER_PWD[$oem][1])          
 *Writer: JinHo, Chang
*****************/
require_once("rpic.inc");
header("Content-Type:application/json; charset=utf-8");
define("USERID","rpic");
define("PASSWD","KZo3i6UJbKd0bb6B5Suv");
$ret = array();
$ret["status"] = "success";
if (file_exists("/var/www/qlync_admin/doc/config.php")){
	require_once ("/var/www/qlync_admin/doc/config.php");
function VerifyUserWithPwdFromAdmin($name,$pwd){ //admin
    global $oem;
    global $api_id, $api_pwd, $api_ip;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_ip}/backstage_login.php?user_name={$name}&user_pwd={$pwd}&oem_id={$oem}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
      return true;
    global $ret;
    SetErrorState($ret,$content["error_msg"]);
    return false;
}

}else if (file_exists("/var/www/SAT-CLOUDNVR/include/index_title.php")){
	require_once ("/var/www/SAT-CLOUDNVR/include/index_title.php");
  $oem = $oem_style_list['oem_id'];
include_once( "/var/www/SAT-CLOUDNVR/include/global.php" );
include_once( "/var/www/SAT-CLOUDNVR/include/db_function.php" );
include_once( "/var/www/SAT-CLOUDNVR/include/log_db_function.php" );
include_once( "/var/www/SAT-CLOUDNVR/include/user_function.php" );
//include_once( "/var/www/SAT-CLOUDNVR/include/utility.php" ); //setErrorState
}else  $oem="N99";

if (! isset($oem))  SetErrorState($ret,"System Configure Not Found!");
//die("System Configure Not Found!!");
function SetErrorState( &$ret, $error_msg )
{
	$ret["error_msg"] = $error_msg;
	$ret["status"] = "fail";
}

//return json??
if (isset($_REQUEST['userid'])){ //fixed id/passwd
  if ( ($_REQUEST['userid']!=USERID) or ($_REQUEST['passwd']!=PASSWD)) SetErrorState($ret,"Invalid Username / Password.");
}else if (isset($_REQUEST['user_name'])){ //fixed id/passwd
  if (function_exists("VerifyUserWithPwdFromAdmin")){
    VerifyUserWithPwdFromAdmin($_REQUEST['user_name'], $_REQUEST['user_pwd']);
  }else{
    $data_db = new DataDBFunction();
    $verify_result = VerifyUserWithPwd($data_db, $_REQUEST['user_name'], $_REQUEST['user_pwd'], $user_info_row, $oem);
    if ($verify_result != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS)
      SetErrorState($ret,"Invalid Username / Password.");
  }
}else  SetErrorState($ret,"Authentication Fail!");

if (isset($ret["error_msg"])) die(json_encode( $ret ));
//for map oemid
if (isset($_REQUEST['oemid'])) define ("OEM_ID", $_REQUEST['oemid']);
else 
  if ($oem=="X02") define ("OEM_ID", "N99");
  else  define ("OEM_ID", $oem);
if ($_REQUEST['conntype'] =="direct"){
  $result=array();
  getMap(OEM_ID,$result);
}else{ //conntype file /default
  if (OEM_ID == "N99"){ //general check
    define (CACHE_FILE, $GIS_FILE[OEM_ID]);//use with writeMapFile
    $mergeFlag=false;
    if (!file_exists(CACHE_FILE)){
    		$mergeFlag = true;  
    }else{
    	$diff = time()-filemtime(CACHE_FILE); //in seconds 60
    	if ($diff > 900){ //over 15 minutes
    		$mergeFlag=true;
    	}
    }
    if ($mergeFlag) writeAllMapFile();
  }//OEM_ID N99
  $result=array();
  getMapFile(OEM_ID,$result);

}

//if (isset($_REQUEST['debugadmin'])) echo json_encode($content);
$content=array();
parseMap(OEM_ID,$result, $content);
$mapData = array();
parseMap2Array(OEM_ID,$content, $mapData);
echo json_encode($mapData);
 
?>
