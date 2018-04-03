<?php
/****************
 *Validated on Sep-26,2016,
 * Add camera API on admin
 * (parameter type, user) add bind camera (for mobile camera) 
 * return Success or Fail  
 * @ /var/www/qlync_admin/html/api
 * https://xpress2.megasys.com.tw:8080/html/api/add_camera.php?id=root&pwd=1qazxdr5&mac=VI0200089371&ac=wwBccAcAA67A&cid=X02            
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("util.php");
//global $oem, $api_id, $api_pwd, $api_path;
function hash4($cid,$pid,$mac,$ac) {
		$key1 = 'love';
		$key2 = 'qlync';
		$text = "{$cid}{$pid}{$mac}{$ac}";
		$hash = md5($key1 . $text . $key2);
		return $hash;
}

function addCamera ($mac,$ac,$cid) //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
    $mac = strtoupper($mac);
    $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
    //$import_data_array[] = array('mac' => "{$mac}", 'ac' => "{$ac}",'user' => "{$user}");
    //$data = array('action'=>'bind_device_order','data'=>json_encode($import_data_array),'cid' => "{$cid}",'pid' => "{$pid}");
     //CC or MC if MAC starts with M
     if ($mac[0]=="M" ) $pid = "MC"; 
     else $pid = "CC";
     $hash=hash4($cid,$pid,$mac,$ac);
    $import_data_array[] = array('mac' => "{$mac}", 'ac' => "{$ac}",'cid' => "{$cid}",'pid' => "{$pid}",'hash' => "{$hash}");
    //before v2.3, T04 does not have region
    if (($oem=="T04") or ($oem=="T05"))
      $import_post = array('action'=>'import_encap_order','data'=>json_encode($import_data_array));
    else if ($oem=="X02") //1 Production, 2 Engineer
      $import_post = array('action'=>'import_encap_order','region'=>'1','data'=>json_encode($import_data_array));
    else 
      $import_post = array('action'=>'import_encap_order','region'=>'0','data'=>json_encode($import_data_array));
     
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$import_target_url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $import_post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result=curl_exec($ch);
			curl_close($ch);
			$content=array();
			$content=json_decode($result,true);
      if($content['status']=="success"){
        $sql="insert into qlync.license(Mac,Code,CID,PID) values ('{$mac}','{$ac}','{$cid}','{$pid}' )";
        sql($sql,$result,$num,0);  
        return true;
      }
      if($content[status]=="fail") var_dump($content);
      //if(sizeof($content[fail_data])>0) var_dump($content); 

      return false;
}

if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if ( isset($_REQUEST["mac"]) and isset($_REQUEST["ac"]) and isset($_REQUEST["cid"]))
  {
          if (addCamera($_REQUEST["mac"],$_REQUEST["ac"],$_REQUEST["cid"]) ){
              InsertLog("Add ".$_REQUEST["cid"]."-".$_REQUEST["mac"],"SUCCESS");
              echo 'Success';
          }else{
            InsertLog("Add ".$_REQUEST["cid"]."-".$_REQUEST["mac"],"FAIL");
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
