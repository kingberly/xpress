<?php
/****************
 *Validated on Jul-26,2016,
 * unbind camera API on admin
 * (parameter type, user) add bind camera (for mobile camera) 
 * return Success or Fail  
 * @ /var/www/qlync_admin/html/api           
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("util.php");

//global $oem, $api_id, $api_pwd, $api_path;

function unbindCamera ($mac,$ac) //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
    $sql		= "select * from qlync.license where binary Mac = '{$mac}'";
    sql($sql,$result_tmp,$num_tmp,0);
		if($num_tmp>0)
		{
			fetch($db_tmp,$result_tmp,0,0);
			$cid =$db_tmp["CID"];
			$pid =$db_tmp["PID"];
    }
    $import_data_array[0]=array('mac'=>"{$mac}",'ac'=>"{$ac}");//deal one by one
    $import_post = array('cid'=>"{$cid}",'pid'=>"{$pid}",'action'=>'unbind_device_order','data'=>json_encode($import_data_array));
//var_dump($import_post);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$import_target_url);
			curl_setopt($ch, CURLOPT_POST,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $import_post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //Fixes the HTTP/1.1 417 Expectation Failed Bug
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result=curl_exec($ch);
			curl_close($ch);
			$content=array();
			$content=json_decode($result,true);

      if($content['status']=="success")
        return true;
      if (isset($_REQUEST['debugadmin']))
        if($content[status]=="fail") var_dump($content);
      return false;
}
  
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if ( isset($_REQUEST["mac"]) and isset($_REQUEST["ac"]) )
  {
      /*if (isset($_REQUEST["type"]))
          if (bindCamera($_REQUEST["mac"],$_REQUEST["ac"],$_REQUEST["user"]) )
              echo 'Success';
          else  echo 'Fail';
      else*/
          if (unbindCamera($_REQUEST["mac"],$_REQUEST["ac"]) ){
            InsertLog($_REQUEST["mac"],"SUCCESS");
            echo 'Success';
          }else{
            InsertLog($_REQUEST["mac"],"FAIL");
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
