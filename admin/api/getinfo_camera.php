<?php
/****************
 *Validated on Nov-28,2017,
 * unbind camera API on admin
 * activation_date | bind_account | resolution | 
 *  service_package | days_storage | data_date 
 * return String or -1 or Fail(error param, id/pwd)
 * fixed MAC matching error, some site's billing report shows MAC only
 ** add parameter mode for instantly database query 
 * add api mac_list | bind_mac_list : mac list of all|bind mac from billing
 * add api online_mac_list : online mac list, 
 * add api online_mac_count | online_mac_date: online mac count, online data date (update if data is older than 30 minutes)
 * add api mac_stream_port | mac_stream_localip : get stream port from mac
 * add api recording_list : get last recording video path 
 * add api billing_array : get billing array of a mac
 * get_camera_blist (from billing report) Mac, ActDate, Account
 * get_camera_list (from database) Mac, Model, ActDate, Account
 ** camera_status to get camera recording days/resoltion/profile
 ** online_status to get camera currently online/Unregister/License and recording status     
//$ret = array();$ret['status'];$ret['error_msg']; echo json_encode($ret);
//{"status":"fail","error_msg":"Invalid MAC format"}          
 * @ /var/www/qlync_admin/html/api           
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("util.php");
require_once '/var/www/qlync_admin/plugin/billing/Classes/PHPExcel.php';
require_once '/var/www/qlync_admin/plugin/billing/Classes/PHPExcel/IOFactory.php';
//global $oem, $api_id, $api_pwd, $api_path;
define("DL_BILLING_PATH","/var/www/qlync_admin/plugin/billing/log/");
//after v3.x add COLLATE utf8_unicode_ci due to database utf8 change
$ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
if ($ref=="") //v2.x
	define("UTF8_FLAG",0);
else define("UTF8_FLAG",1); //v3.x 

function getlastExcel()
{
    $files = scandir(DL_BILLING_PATH, 1);
    for($i=0;$i<sizeof($files);$i++){
        if ($files[$i]=="..") continue;
        else if ($files[$i]==".") continue;
        else if (strpos($files[$i], '.xls') !== FALSE)
            return DL_BILLING_PATH.$files[$i];
        else continue; 
    } 
    return "";
}


function queryStream($mac,$type)
{
  if ( ($type=="mac_stream_port") or ($type=="mac_stream_localip"))
  	$sql ="select c1.device_uid as UID, c2.hostname as sHostname, c2.internal_address as s_internal_address,c2.external_port as s_external_port from isat.stream_server_assignment as c1 LEFT JOIN isat.stream_server as c2 on c1.stream_server_uid=c2.uid where c1.device_uid like '%".$mac."%' group by c1.device_uid";
  else if ($type=="recording_list")  
    $sql = "select path from isat.recording_list where device_uid like '%{$mac}'ORDER BY id DESC LIMIT 1";
  else return "-1"; 
  sql($sql,$result,$num,0);

  if ($num > 0){
      fetch($arr,$result,0,0);
      if ($type=="mac_stream_port")
        return $arr['s_external_port'];
      else if ($type=="mac_stream_localip")
        return $arr['s_internal_address'];
      else if ($type=="recording_list")
        return $arr['path'];
  }  
  return "-1";
}
function queryDB($mac,$type)
{//qlync.accont is not latest data 
//isat.device(isat.series_number) update_date => device lastest connection date
//isat.device reg_date => device bind date

/* 
  if (UTF8_FLAG)//new db
  	$sql = "select Account, c1.device_uid as CameraMac, ActivationDate,c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage  from isat.stream_server_assignment as c1 left outer join (select c3.id as c3id, c2.id as c2id, c2.uid,  DATE_FORMAT(FROM_UNIXTIME(c3.reg_date),'%Y-%m-%d') as ActivationDate, c2.Name as Account, c3.name from qlync.account_device as c2 inner join isat.device as c3 on c3.uid=c2.Uid COLLATE utf8_unicode_ci group by c3.uid) as c4 on c1.device_uid=c4.Uid COLLATE utf8_unicode_ci where c1.device_uid like '%{$mac}'";
  else
  	$sql = "select Account, c1.device_uid as CameraMac, ActivationDate,c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage  from isat.stream_server_assignment as c1 left outer join (select c3.id as c3id, c2.id as c2id, c2.uid,  DATE_FORMAT(FROM_UNIXTIME(c3.reg_date),'%Y-%m-%d') as ActivationDate, c2.Name as Account, c3.name from qlync.account_device as c2 inner join isat.device as c3 on c3.uid=c2.Uid group by c3.uid) as c4 on c1.device_uid=c4.Uid where c1.device_uid like '%{$mac}'";
    */
	$sql = "select c2.user_name as Account, c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage, c3.reg_date as ActivationDate from isat.query_info as c2 left join isat.stream_server_assignment as c1 on device_uid like '%{$mac}' left join isat.series_number as c3 on c2.mac_addr= c3.mac where c2.mac_addr='{$mac}' group by Account";
  sql($sql,$result,$num,0);
  if ($num > 0){
      fetch($arr,$result,0,0);
      /*
			if (is_null($arr['Account']))
			{//inner join isat.device as c3 on c3.uid=c2.uid
		  	$sql1 = "select c2.user_name as Account,DATE_FORMAT(FROM_UNIXTIME(c3.reg_date),'%Y-%m-%d') as ActivationDate, c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage  from isat.stream_server_assignment as c1 left join isat.query_info as c2 on c2.uid=c1.device_uid inner join isat.device as c3 on c3.uid=c2.uid where c2.mac_addr ='{$mac}' group by c2.mac_addr";
		  	sql($sql1,$result1,$num1,0);
		  	if ($num1>0){
		  		fetch($arr1,$result1,0,0);
		  		$arr['Account'] = $arr1['Account'];
		  		$arr['ActivationDate'] = $arr1['ActivationDate'];
		  	}

			} */
      if ($type=="activation_date")
        //return date('Y-m-d',strtotime(str_replace("/"," ",$arr['activation_date'])));
        return (($arr['ActivationDate']=="") or (is_null($arr['ActivationDate'])))? "-1":$arr['ActivationDate'];//$arr['ActivationDate'];  
      else if ($type=="bind_account")
        return ($arr['Account']=="")? "-1":$arr['Account']; 
      else if ($type=="resolution")
        return $arr['Resolution'];
      else if ($type=="service_package")
        return str_replace(' ', '_',$arr['ServicePackage']);
      else if ($type=="days_storage")
        return $arr['DaysStorage'];
      else if ($type=="camera_status") //new add
      	return "{$arr['Account']},{$arr['ActivationDate']},{$arr['ServicePackage']},{$arr['Resolution']},{$arr['DaysStorage']}";
  }else{ //update camera_status only
      $sql = "select recycle as DaysStorage,purpose as Resolution, dataplan as ServicePackage from isat.stream_server_assignment where device_uid like '%{$mac}'";
      sql($sql,$result,$num,0);
      if ($num > 0){
        fetch($arr,$result,0,0);
        if ($type=="camera_status")
            return "(NA),(NA),{$arr['ServicePackage']},{$arr['Resolution']},{$arr['DaysStorage']}";
        else if ($type=="resolution")
          return $arr['Resolution'];
        else if ($type=="service_package")
          return str_replace(' ', '_',$arr['ServicePackage']);
        else if ($type=="days_storage")
          return $arr['DaysStorage'];
      }//default camera setting
  }
  return "-1";
}

function queryCameraStatus($mac){
	$msg = "-1";
	//purpose=WBSS / tunnel ip, 
  //purpose=RVHI/RVME/RVLO, stream ip
  //$sql = "select * from isat.query_info where mac_addr='{$mac}' and purpose like 'RV%'";
  $sql = "select mac_addr,internal_ip_addr,is_signal_online,c2.ip_addr as ext_addr from isat.query_info as c1 left join isat.signal_server_online_client_list as c2 on c1.uid = c2.uid where mac_addr='{$mac}' and purpose like 'RV%'";
	sql($sql,$result,$num,0);
  if ($num > 0){
    fetch($arr,$result,0,0);
		if ($arr['is_signal_online'] == "true"){
			$msg ="Online ({$arr['ext_addr']}), ";
			$s_status = exec ("python /home/ivedasuper/admin/check/getStreamingStatus.py ".$arr['internal_ip_addr']." ".strtoupper($mac));
			$msg .= $s_status; 
		}else{
			$msg ="Offline, ".queryCameraDevice($mac);
		}
		//if ($type=="online_status") //new add
		//	return $msg; 
  }else{//if unregistered or no license
      //$sqlN = "select * from isat.device where mac_addr='{$mac}'";
      //sql($sqlN,$resultN,$numN,0);
      //if ($numN == 0){
        $sqlN = "select * from isat.series_number where mac='{$mac}'";
        sql($sqlN,$resultN,$numN,0);
        if ($numN > 0) $msg="Unregistered.";
        else $msg="License Not Exist.";
      //}
  }
  return $msg;
}
function queryCameraDevice($mac){
	$sql = "select update_date from isat.device where mac_addr='{$mac}' group by mac_addr";
	sql($sql,$result,$num,0);
  if ($num > 0){
    fetch($arr,$result,0,0);
	  $utcdate= new DateTime(date ("Y-m-d H:i:s", $arr['update_date']), new DateTimeZone('UTC')); 
    $arr['update_date'] = $utcdate->format("Y-m-d H:i:s");
    return $arr['update_date'];
	}
	return "-1";
}

function queryDB_CamList()
{
  //account_device only update once per day
  //$sql = "SELECT mac_addr, model,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%b/%d/%Y') as ActivationDate, c2.Name FROM isat.device left JOIN qlync.account_device as c2 ON mac_addr=Mac group by Mac order by c2.Name Desc";
  //$sql = "SELECT mac_addr, model,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d') as ActivationDate, c2.Name FROM isat.device left JOIN qlync.account_device as c2 ON mac_addr=Mac group by Mac order by c2.Name Desc";
  $sql = "SELECT c1.mac_addr, model, DATE_FORMAT(FROM_UNIXTIME(c2.reg_date),'%Y-%m-%d') as ActivationDate, c1.user_name from isat.query_info as c1 left join isat.device as c2 ON c1.mac_addr=c2.mac_addr group by mac_addr;";

  sql($sql,$result,$num,0);
  $textList="";
  for($i=0;$i<$num;$i++)
  {
      fetch($arr,$result,$i,0);
      if ($arr['ActivationDate'] == "") $arr['ActivationDate']="NULL";
      //else $arr['ActivationDate']= date('Y-m-d',strtotime(str_replace("/"," ",$arr['ActivationDate'])));
      if ($arr['model'] == "") $arr['model']="NULL";
      if ($arr['user_name'] == "") $arr['user_name']="NULL";  
      $textList.= $arr['mac_addr'].",".$arr['model'].",".$arr['ActivationDate'].",".$arr['user_name']."\n";
  }
  $textList = rtrim($textList, "\n");
  return $textList;
}

function readExcel($filepath,$mac,$type)
{
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel 	= $objReader->load($filepath);
	   $last_num	= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

/*     $BILL_SERVER = $objPHPExcel->getActiveSheet()->getCell("B2")->getValue();
     $BILL_DATE = $objPHPExcel->getActiveSheet()->getCell("B3")->getValue();
     $QUERY_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B4")->getValue();
     $CAM_ADDED_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B5")->getValue();
*/
     for($i=9;$i<=$last_num+9;$i++)
     {
        $rowmac = $objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue();
        if (strlen($rowmac) == 18)
          $rowmac = substr($objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue(),6,12);
 
        if ( $rowmac== $mac)
        {
            if ($type=="activation_date")
              //return $objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue();
              return date('Y-m-d',strtotime(str_replace("/"," ",$objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue())));  
            else if ($type=="bind_account")
              return $objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue(); 
            else if ($type=="resolution")
              return $objPHPExcel->getActiveSheet()->getCell("E{$i}")->getValue(); 
            else if ($type=="service_package")
              //return $objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue(); 
              return str_replace(' ', '_',$objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue());
            else if ($type=="days_storage")
              return $objPHPExcel->getActiveSheet()->getCell("D{$i}")->getValue();
            else if ($type=="billing_array")
              //return $objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue().",".$objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue().",".$objPHPExcel->getActiveSheet()->getCell("D{$i}")->getValue().",".$objPHPExcel->getActiveSheet()->getCell("E{$i}")->getValue().",".str_replace(' ', '_',$objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue()); 
              return $objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue().",".date('Y-m-d',strtotime(str_replace("/"," ",$objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue()))).",".$objPHPExcel->getActiveSheet()->getCell("D{$i}")->getValue().",".$objPHPExcel->getActiveSheet()->getCell("E{$i}")->getValue().",".str_replace(' ', '_',$objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue());

        }
      }
      return "-1";
}

function readExcelList($filepath,$type)
{
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel 	= $objReader->load($filepath);
	   $last_num	= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();
      $textList = "";

     for($i=9;$i<=$last_num+9;$i++)
     {
        $rowmac = $objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue();
        if (strlen($rowmac) == 18)
          $rowmac = substr($objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue(),6,12);
        
        if ( ($type=="bind_mac_list")               
        and ($objPHPExcel->getActiveSheet()->getCell("A{$i}")=="") ) break;
        
        if ( ($type=="bind_mac_list") or ($type=="mac_list") )
          $textList.= $rowmac.",\n";
        else if ($type=="get_camera_blist"){
          $d = date('Y-m-d',strtotime(str_replace("/"," ",$objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue())));
          $textList.= $rowmac.",".$d.",".$objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue()."\n";
        }
     }
    if ( ($type=="bind_mac_list") or ($type=="mac_list") )
      $textList = rtrim($textList, ",\n");
    else if ($type=="get_camera_blist")     
      $textList = rtrim($textList, "\n");
      return $textList;     
}

function readCSVList($filepath,$type)
{
    $item=8;
    $content=fread(fopen($filepath,"r"),filesize($filepath));
    $content=str_replace("\n",",",$content);
    $total=explode(",",$content);
    //$lines       = file($filepath);
    $textList = "";
    
    if ($type=="online_mac_count"){
        $count=sizeof($total)/$item;
        return $count;
    }
     
    for($i=0;$i<sizeof($total);$i++)
    {
      //$account[$i]= $total[$i*$item];
      //if ($type=="online_mac_list")
    	//$uid[$i]    = $total[$i*$item+1];
      $textList.= $total[$i*$item+1].",\n";
    }
    $textList = rtrim($textList, ",\n");
    return $textList;     
}

 
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  $filename = getlastExcel();
  if ( isset($_REQUEST["mac"]) )
  {
    if (ereg("[A-Za-z0-9]{12}",$_REQUEST["mac"]))
    {//correct 12 digit MAC
      if ( ($_REQUEST["type"] =="mac_stream_port"  )
        or ($_REQUEST["type"] =="mac_stream_localip")
        or ($_REQUEST["type"] =="recording_list") )
      {
         InsertLog($_REQUEST["type"],"SUCCESS");
         echo queryStream($_REQUEST["mac"],$_REQUEST["type"]);
      }else if ( !isset($_REQUEST["mode"]) )
      {
        if ($filename != "")
        {
          if ( ($_REQUEST["type"] =="activation_date") 
          or ($_REQUEST["type"] =="bind_account"  )
          or ($_REQUEST["type"] =="resolution")
          or ($_REQUEST["type"] =="service_package") //replace space with underscore
          or ($_REQUEST["type"] =="days_storage")
          or ($_REQUEST["type"] =="billing_array")
         ){
              InsertLog($_REQUEST["type"],"SUCCESS");
              echo readExcel($filename,$_REQUEST["mac"],$_REQUEST["type"]); 
          }else if ($_REQUEST["type"] =="data_date"  ){
              InsertLog($_REQUEST["type"],"SUCCESS");
              //echo date ("Y/M/d H:i:s", filemtime($filename));
              echo date ("Y-m-d H:i:s", filemtime($filename));
          }else{
            InsertLog($_REQUEST["type"],"FAIL");
            echo 'Fail';
          }
        }else{ echo "-1"; }//filename
      }else{ //mode for database quiry
          InsertLog($_REQUEST["type"]." SQL","SUCCESS");
          if ($_REQUEST["type"]=="online_status")
          	echo queryCameraStatus($_REQUEST["mac"]);
          else
						echo queryDB($_REQUEST["mac"],$_REQUEST["type"]);
      }
     
    }else{//mac format error
      InsertLog($_REQUEST["type"]." MAC format","FAIL");
      echo 'Fail';
    }  	
  }else if ( ($_REQUEST["type"] =="mac_list"  )
       or ($_REQUEST["type"] =="bind_mac_list"  )
       or ($_REQUEST["type"] =="get_camera_blist"  )  
  ){
    InsertLog($_REQUEST["type"],"SUCCESS");
     echo readExcelList($filename,$_REQUEST["type"]);
  }else if ($_REQUEST["type"] =="get_camera_list"  ){
     InsertLog($_REQUEST["type"],"SUCCESS");
     echo queryDB_CamList();
  }else if ( ($_REQUEST["type"] =="online_mac_list")
      or ($_REQUEST["type"] =="online_mac_count"  )   
  ){
     InsertLog($_REQUEST["type"],"SUCCESS");
     echo readCSVList("{$api_temp}/online_list.csv",$_REQUEST["type"]);
  }else if ($_REQUEST["type"] =="online_mac_date"  ){
      $fileLastModifyTime=new DateTime();
      $fileLastModifyTime->setTimestamp(filemtime("{$api_temp}/online_list.csv"));
      $now = new DateTime();
      $diff=$now->diff($fileLastModifyTime);
      if ($diff->i > 30){    //more than ? minutes
        $url="http://{$api_id}:{$api_pwd}@{$api_path}/fetch_online_clients.php";
        exec("wget ".$url." -O {$api_temp}/online_list.csv");
        chmod("{$api_temp}/online_list.csv",0777);
      }
     //echo date ("Y/M/d H:i:s", filemtime("{$api_temp}/online_list.csv"));
     InsertLog($_REQUEST["type"],"SUCCESS");
     echo date ("Y-m-d H:i:s", filemtime("{$api_temp}/online_list.csv"));
  }else{//print ' Please Enter Correct Parameters !';
    InsertLog("Incorrect Parameter","FAIL");
    echo 'Fail';
  }
}else{ 	//echo 'Please Enter Correct ID/ PWD !';
  InsertLog("ID/Pwd","FAIL");
  echo 'Fail';
}

?>
