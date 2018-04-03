<?php
/****************
 *Validated on Oct-5,2017,
 * unregister camera API on admin
 * parameter: command=list, command=clean&mac= 
 * return Success or Fail  
 * @ /var/www/qlync_admin/html/api           
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("util.php");

//global $oem, $api_id, $api_pwd, $api_path;

function deleteDeviceRegByMAC ($mac)
{
    $sql="delete from isat.device_reg where mac_addr='{$mac}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}
function listDeviceReg ()
{
    $sql="select uid, DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d %H:%i:%s') as reg_date_format from isat.device_reg group by mac_addr";
    $textList="";
    sql($sql,$result,$num,0);
    if ($num>0) {
      for($i=0;$i<$num;$i++){
        fetch($arr,$result,$i,0);
        $textList.= $arr['uid'].",".$arr['reg_date_format']."\n"; 
      }
      $textList = rtrim($textList, "\n");
    }
    return ($textList=="")? "-1" :$textList ;
}

  
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if ( isset($_REQUEST["command"]) )
  {
    $command = $_REQUEST["command"];
    if ($command=="list") {
      echo listDeviceReg();
      InsertLog("unregister_camera List","SUCCESS");
    }else if (($command=="clean") and ( isset($_REQUEST["mac"])) )
    {

          if (deleteDeviceRegByMAC($_REQUEST["mac"])) {
            InsertLog("unregister_camera Delete ".$_REQUEST["mac"],"SUCCESS");
            echo 'Success';
          }else{
            InsertLog("unregister_camera Delete ".$_REQUEST["mac"],"FAIL");
            echo 'Fail';
          }
    }else{
      InsertLog("unregister_camera command: {$command}","FAIL");
      echo 'Fail';
    }
  }else{//print ' Please Enter Corrent Parameters !';
    InsertLog("Incorrect Parameter","FAIL");
    echo 'Fail';
  }
}else{ 	//echo 'Please Enter Correct ID/ PWD !';
  InsertLog("ID/Pwd","FAIL");
  echo 'Fail';
}

?>
