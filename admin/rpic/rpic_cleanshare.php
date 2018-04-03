<?php
/****************
 * NOT Validated on mar-8,2018,
 * clean offline shared camera from account: 
 *   php /var/www/qlync_admin/plugin/rpic/rpic_cleanshare.php <user_name>
 * 5 0 * * * root  /usr/bin/php5  "/var/www/qlync_admin/plugin/rpic/rpic_cleanshare.php" K01 
 *  $argv[0] is script name
 *  $argv[1] user_name (specific name or prefix) (specific rule account)
 *   if user_name is not exist, clean share camera from prefix
 *   if user_name=K01, clean K01 site rule account
 *  $argv[2] FORCE: force to remove online share camera   
 *  include in /etc/crontab
 ** add TEST        
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
header('Content-type: application/json');
define("DEBUG_FLAG","ON"); //OFF ON
$TEST_CAM=[
array("ivedatest","001BFE0723F2"),//C13
array("ivedatest","001BFE06AC2B") //K01
]; //always clean TESTCAM


$ret = array();
$ret['status'] = 'success';
$ret['error_msg'] = '';

if (!isMaster()) die("Not Master Admin! Stop Running\n");
if (isset($_REQUEST['user_name']))
  define ("VISITOR_NAME", $_REQUEST['user_name']);
else if (isset($argv[1]))
  define ("VISITOR_NAME", $argv[1]);
else{
  if (isset($oem))  define ("VISITOR_NAME", $oem);
  else{
  $ret['status'] = 'fail';
  $ret['error_msg'] = 'No parameter.';
  echo json_encode($ret);
  exit;
  }
}
if (isset($_REQUEST['force_clean']))
  define ("FORCE_CLEAN", 1);
else if (isset($argv[2])) //force clean account
  define ("FORCE_CLEAN", 1);
else define ("FORCE_CLEAN", 0);

function isMaster()
{
  $hastat = exec ("ps -ax | grep [h]eartbeat");
  if ($hastat != ""){ //use LB
    //stdout = os.popen("ifconfig | grep eth0:0 | wc -l")
    //isMaster = stdout.read().replace("\n", "")
    $stat = exec ("ifconfig | grep eth0:0 | wc -l");
    $stat = trim ( $stat , " \t\n\r\0\x0B");
    if (intval($stat) == 1)
      return true;
  }else return true;  //single Admin
  return false;
}

function syncShareDevice($ACNO){
  $sql = "UPDATE customerservice.workeyegis SET note='', is_public=0 where ACNO='{$ACNO}'";
  if (DEBUG_FLAG=="OFF") sql($sql,$result,$num,0);
  else echo "\nDEBUG:{$sql}";
  return $result;
}

function getUserIDArray($username,&$userArr, $mac="")
{
  if ($username == "K01"){
    define("K01ADMIN","pipegis");//K01 special prefix
    //with A-Z and 0-9, -
    $sql = "SELECT id,name FROM isat.user WHERE name REGEXP BINARY '^[A-Z0-9\-]{5,15}$' or name like '".K01ADMIN."%'";
  }else if ($username == "T04"){
    $sql = "SELECT id,name FROM isat.user WHERE name REGEXP BINARY '^[A-Z0-9\-]{6,15}$'";
  }else if ($username == "T05"){
    $sql = "SELECT id,name FROM isat.user WHERE name REGEXP BINARY '^[A-Z0-9\-]{5,15}$'";
  }else if ($username == "C13"){
    $sql = "SELECT id,name FROM isat.user WHERE name REGEXP BINARY '^[0-9]{3,}-[0-9]{2,}$'";
  }else
    if ($mac!="")  $sql="select visitor_id as id, name from isat.query_share where user_name='{$username}' and mac_addr='{$mac}' group by visitor_id";
    else  $sql = "select id,name from isat.user where name like '{$username}%'";
  sql($sql,$result,$num,0);
if (DEBUG_FLAG == "ON") echo "\nuserIDArray:($num):".$sql;
  if ($num >0){
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //array_push($userArr,$arr['id']);
      $userArr[$i]['id'] = $arr['id'];
      $userArr[$i]['name'] = $arr['name']; 
    }
    return true;
  } 
  return false;
}
function getUserID($username)
{
  $sql = "select id from isat.user where name='{$username}'";
  sql($sql,$result,$num,0);
  if ($num >0){
    fetch($arr,$result,0,0);
    return $arr['id'];
  } 
  return "-1";
}
function cleanShareByMAC($visitor_id,$mac){
    $sql="select c1.id as targetid, c1.uid,c1.owner_id,c1.visitor_id, c2.id as device_id,c3.user_name from isat.device_share as c1 left join isat.device as c2 on c1.uid = c2.uid left join isat.query_share as c3 on (c1.uid=c3.uid and c1.visitor_id=c3.visitor_id) where c1.visitor_id='{$visitor_id}' and c2.mac_addr='{$mac}' and c2.purpose in ('RVLO','WBMJ') group by uid";
  sql($sql,$result,$num_share,0);
  if (DEBUG_FLAG == "ON") echo "\ndevice_shareMAC:($num_share):".$sql;
  $dev_str="";$tar_str="";
  if ($result){
      for($i=0;$i<$num_share;$i++){
        fetch($arr,$result,$i,0);
        $dev_str.=$arr['device_id'].",";
        $tar_str.=$arr['targetid'].",";
      }
      $dev_str =rtrim($dev_str,",");
      $tar_str =rtrim($tar_str,",");
  }
  if ($dev_str!=""){
  $sql = "delete from isat.position where owner_id='{$visitor_id}' and device_id IN ({$dev_str})";
    if (DEBUG_FLAG == "OFF") sql($sql,$result,$num,0);
    else echo "\nDEBUG:{$sql}";
  }
  return $tar_str;
}

function cleanShareByVID($visitor_id)
{
  if (FORCE_CLEAN)
    $sql="select c1.id as targetid, c1.uid,c1.owner_id,c1.visitor_id, c2.id as device_id,c3.user_name from isat.device_share as c1 left join isat.device as c2 on c1.uid = c2.uid left join isat.query_share as c3 on (c1.uid=c3.uid and c1.visitor_id=c3.visitor_id) where c1.visitor_id='{$visitor_id}' and c2.purpose in ('RVLO','WBMJ') group by uid";
  else //get offline share account
  $sql="select c1.id as targetid, c1.uid,c1.owner_id,c1.visitor_id, c2.id as device_id,c3.user_name from isat.device_share as c1 left join isat.device as c2 on c1.uid = c2.uid left join isat.query_share as c3 on (c1.uid=c3.uid and c1.visitor_id=c3.visitor_id) where c3.is_signal_online='false' and c1.visitor_id='{$visitor_id}' and c2.purpose in ('RVLO','WBMJ') group by uid";
  sql($sql,$result,$num_share,0);
  //if (DEBUG_FLAG == "ON") echo "\ndevice_share:($num_share):".$sql;
  $dev_str="";$tar_str="";
  if ($result){
      for($i=0;$i<$num_share;$i++){
        fetch($arr,$result,$i,0);
        $dev_str.=$arr['device_id'].",";
        $tar_str.=$arr['targetid'].",";
      }
      $dev_str =rtrim($dev_str,",");
      $tar_str =rtrim($tar_str,",");
  }
  if ($dev_str!=""){
    /*if (DEBUG_FLAG == "ON") 
    {
      $sql ="select * from isat.position where owner_id='{$visitor_id}' and device_id IN ({$dev_str})";
      sql($sql,$result,$num_pos,0); 
      echo "\nposition:($num_pos):".$sql;
      if ($result){
        for($i=0;$i<$num_pos;$i++){
          fetch($arr,$result,$i,0);
          print_r($arr);
        }
      }
    }*/
    $sql = "delete from isat.position where owner_id='{$visitor_id}' and device_id IN ({$dev_str})";
    if (DEBUG_FLAG == "OFF") sql($sql,$result,$num,0);
    else echo "\nDEBUG:{$sql}";
  }
  return $tar_str;
}

if (preg_match("/^[A-Z]{1}[0-9]{2}$/",VISITOR_NAME)) { //XNN
  $myArr = array();
  $tar_str = "";
  if (getUserIDArray(VISITOR_NAME,$myArr))
  {
    for ($i=0;$i<sizeof($myArr);$i++){
      if ( ($tmp = cleanShareByVID($myArr[$i]['id']))!= "" ){
        $tar_str .= ",{$tmp}";
        if (VISITOR_NAME=="C13") syncShareDevice($myArr[$i]['name']);
      }
    }
  }
}else{
  $visitor_id=getUserID(VISITOR_NAME);
  if ($visitor_id != -1)
    $tar_str = cleanShareByVID($visitor_id);
  else{ //array, prefix delete
    $myArr = array();
    $tar_str = "";
    if (getUserIDArray(VISITOR_NAME,$myArr))
    {
      for ($i=0;$i<sizeof($myArr);$i++){
      if ( ($tmp = cleanShareByVID($myArr[$i]['id']))!= "" )
        $tar_str .= ",{$tmp}";
      }
    }
  }
}

if (DEBUG_FLAG == "ON") echo "\ndev_id_list:{$tar_str}";

if (sizeof($TEST_CAM)>0){
  $tArr = array();
  for ($i=0;$i<sizeof($TEST_CAM);$i++){
     if (getUserIDArray($TEST_CAM[$i][0],$tArr,$TEST_CAM[$i][1])){
//if (DEBUG_FLAG == "ON") var_dump($tArr);
      for ($j=0;$j<sizeof($tArr);$j++){
        if ( ($tmp = cleanShareByMAC($tArr[$j]['id'],$TEST_CAM[$i][1]))!= "" )
          $tar_str .= ",{$tmp}";
      }
     }//if
  }
  if (DEBUG_FLAG == "ON") echo "\nadded TEST_CAM list:{$tar_str}";
}
if ($tar_str!=""){
  $tar_str=ltrim($tar_str,",");
  $tar_str=rtrim($tar_str,",");
  $sql="delete from isat.device_share where id IN ({$tar_str})";
  if (DEBUG_FLAG == "OFF") sql($sql,$result,$num,0);
  else echo "\nDEBUG delete device_share::{$sql}";
  $num_share = sizeof(explode(",",$tar_str)); 
  if ($result)
    //$ret['error_msg']=  "({$num_share}|{$num_pos})CleanUp Offline Shared camera from ".VISITOR_NAME;
    if (FORCE_CLEAN) $ret['error_msg']=  "({$num_share})CleanUp Shared camera from ".VISITOR_NAME;
    else $ret['error_msg']=  "({$num_share})CleanUp Offline Shared camera from ".VISITOR_NAME;
  else //$ret['error_msg']=  "({$num_share}|{$num_pos})Fail to CleanUp Shared camera from ".VISITOR_NAME;
    $ret['error_msg']=  "({$num_share})Fail to CleanUp Shared camera from ".VISITOR_NAME;
}else $ret['error_msg']= "No Shared Camera.";
echo json_encode($ret);
?>