<?php
/****************
 *Validated on Aug-10,2016,
 * delete un-used license/device from database
 * add debugadmin feature to hide remove button
 * add device_reg query table  
 * check region exist or not
 * Fix bug due to form misplace   
 *Writer: JinHo, Chang
*****************/
include("../../header.php");
include("../../menu.php");
require_once '_auth_.inc';
define("DB_DEVICE","1");
define("DB_SERIES","2");
define("DB_LICENSE","3");

$bRegion = TRUE; // for new server /rtmp
$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'isat' AND TABLE_NAME = 'device_reg' AND COLUMN_NAME = 'region'";
sql($sql,$result,$num,0);
if ($num==0) $bRegion = FALSE;
//var_dump($_REQUEST);
if (isset($_REQUEST['btnDelDeviceMAC']) and  ($_REQUEST["mac"] != "Z01CC-00"))
{
        //$result = deleteDeviceByMAC($_REQUEST["mac"]);
        $result = deleteCamByMAC(DB_DEVICE,$_REQUEST["mac"]);
        if ($result){
            $msg_err = "<font color=blue>delete device MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";


}else if (isset($_REQUEST['btnDelSerialMAC']) ) 
{
        //$result = deleteSerialByMAC($_REQUEST["mac"]);
        $result = deleteCamByMAC(DB_SERIES,$_REQUEST["mac"]);
        if ($result){
            $msg_err = "<font color=blue>delete series MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";
     
}else if (isset($_REQUEST['btnLicenseMAC']) )
{
        $result = deleteCamByMAC(DB_LICENSE,$_REQUEST["mac"]);
        if ($result){
            $msg_err = "<font color=blue>delete license MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";
    
}else if (isset($_REQUEST['btnCameraShare']))
{
    if(isset($_REQUEST['uid_owner_id']))
    {
        $req_var = explode("_", $_POST['uid_owner_id']);
        $result = addShareDevice($req_var[0],$req_var[1],$_REQUEST["visitor_id"]);
        if ($result){
          $msg_err = "<font color=blue>add share ".$req_var[0]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>add share ".$req_var[0]. " FAIL!</font><br>\n";
    }
}else if($_REQUEST["step"]=="delsharevisitor")  //if (isset($_REQUEST['btnCameraUnshareFromVisitor']))  
{
    if(isset($_POST['visitor_id_name']))
    {
      $req_var = explode(":", $_POST['visitor_id_name']);
      $result = deleteUser($req_var[1]);
      if ($result){
          $msg_err = "<font color=blue>delete Share To Visitor ".$req_var[0].":".$req_var[1]. " SUCCESS!</font><br>\n";
      }else $msg_err = "<font color=red>delete Share To Visitor ".$req_var[0].":".$req_var[1]. " FAIL!</font><br>\n";
    }
}else if($_REQUEST["step"]=="delsharecamera")  //if (isset($_REQUEST['btnCameraUnshareByMAC'])) 
{
   $result = deleteShareByUID($_REQUEST["uid"]);
    if ($result){
        $msg_err = "<font color=blue>delete Camera Share ".$_REQUEST["uid"]. " SUCCESS!</font><br>\n";
    }else $msg_err = "<font color=red>delete Camera Share".$_REQUEST["uid"]. " FAIL!</font><br>\n";
}else if($_REQUEST["step"]=="delshare")
{
   $result = deleteShare($_REQUEST["id"],$_REQUEST["uid"],$_REQUEST["visitor_id"]);
    if ($result){
        $msg_err = "<font color=blue>delete Share ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
    }else $msg_err = "<font color=red>delete Share".$_REQUEST["id"]. " FAIL!</font><br>\n";
}else if($_REQUEST["step"]=="delete_device_reg")
{
   //$result = deleteDeviceRegByID($_REQUEST["id"]);
   $result = deleteDeviceRegByMAC($_REQUEST["mac"]);
    if ($result){
        $msg_err = "<font color=blue>delete device_reg ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
    }else $msg_err = "<font color=red>delete device_reg".$_REQUEST["mac"]. " FAIL!</font><br>\n";

}else if  (isset($_REQUEST['btnCameraPos'])){
  if ($_REQUEST["owner_id"]!="")
    $msg_err = findPositionList($_REQUEST["owner_id"]); 
}else if  (isset($_REQUEST['btnDeletePosByID'])){
    if ($_REQUEST["id"]!="")
      if (deleteSharePostionByID($_REQUEST["id"]))
        $msg_err = "<font color=blue>delete position ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
      else $msg_err = "<font color=red>delete position".$_REQUEST["id"]. " FAIL!</font><br>\n";
}else if  (isset($_REQUEST['btnDeleteShareByID'])){
    if ($_REQUEST["id"]!="")
      if (deleteShareByID($_REQUEST["id"]))
        $msg_err = "<font color=blue>delete device_share ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
      else $msg_err = "<font color=red>delete device_share".$_REQUEST["id"]. " FAIL!</font><br>\n";
}else if (isset($_REQUEST['btnPosNULL'])){
  $sql="delete from isat.position where owner_id is null";
  if (execSQL($sql))
    $msg_err = "<font color=blue>exec ({$sql}) SUCCESS!</font><br>\n";
      else $msg_err = "<font color=red>exec ({$sql}) FAIL!</font><br>\n";
}else if (isset($_REQUEST['btnPosOwnerID'])){
  if ($_REQUEST["owner_id"]!="")
    $msg_err = ListPositionTable($_REQUEST["owner_id"]);
}

function execSQL($sql)
{
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;  
}
function deleteDeviceRegByMAC ($mac)
{
    $sql="delete from isat.device_reg where mac_addr='{$mac}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function deleteDeviceRegByID ($id)
{
    $sql="delete from isat.device_reg where id='{$id}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function deleteUser ($username)
{
   global $oem, $api_id, $api_pwd, $api_path;
   	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=delete_account&user_name={$username}";
      	$ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL,$import_target_url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$result=curl_exec($ch);
      	curl_close($ch);
      	$content=array();
      	$content=json_decode($result,true);
  	if($content["status"]=="success")
  	{
  		$sql="delete from qlync.end_user_list where Email='{$username}@safecity.com.tw'";
  		sql($sql,$result_tmp,$num_tmp,0);
      return true;
  	}
    return false;
}

function deleteCamByMAC ($type,$uid)
{
    if (strlen($uid)<15) return false; //XXXXX-MX0257203122
    $mac = substr($uid,strpos($uid,"-")+1);
    $CID = substr($uid,0,strpos($uid,"C-")-1); //T04MC-MT, Z01CC-00, M04CC-00
    if ($type == DB_DEVICE)
        $sql="delete from isat.device where uid like '{$uid}%'";
    else if ($type == DB_SERIES)
        $sql="delete from isat.series_number where mac like '{$mac}%'";
    else if ($type == DB_LICENSE){
        $sql="delete from qlync.license where CID='{$CID}' and Mac like '{$mac}%'";
    }        
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function getDeviceIDByUID ($uid)
{
    if (strlen($uid)!=18) return false;
    $sql = "select id from isat.device where uid='{$uid}' AND purpose in ('RVLO','WBMJ')";
    sql($sql,$result,$num,0);
    if ($result) {
        fetch($arr,$result,0,0);
        return $arr['id'];
    }else return -1;
}

function addSharePostion ($device_id, $visitor_id)
{
    $sql = "insert into isat.position (owner_id,device_id) values ({$visitor_id},{$device_id})";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;    
}

function deleteSharePostion ($device_id, $visitor_id)
{
    $sql = "delete from isat.position where device_id ={$device_id} and owner_id ={$visitor_id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;    
}

function deleteSharePostionByID ($id)
{
    $sql = "delete from isat.position where id ={$id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;    
}

function addShareDevice ($uid, $owner_id,$visitor_id)
{
    if (strlen($uid)!=18) return false;
    if ($owner_id == $visitor_id) return false;    
    $sql = "insert into isat.device_share (uid,owner_id,visitor_id) values ('{$uid}',{$owner_id},{$visitor_id})";
    sql($sql,$result,$num,0);
    $result2= addSharePostion (getDeviceIDByUID($uid),$visitor_id);
    if ($result2) return true;
    return false;
}

function deleteShare ($id,$uid,$visitor_id)
{
  if (strlen($uid)!=18) return false;
    $sql="delete from isat.device_share where id='{$id}'";
    sql($sql,$result,$num,0);
    $devid = getDeviceIDByUID($uid);
    $sql="delete from isat.position where device_id={$devid} and owner_id={$visitor_id}";
    sql($sql,$result2,$num,0);
    if ($result2) return true;
    return false;
}


function deleteShareByUID ($uid)
{
  if (strlen($uid)!=18) return false;
    $sql="delete from isat.device_share where uid='{$uid}'";
    sql($sql,$result,$num,0);
    $devid = getDeviceIDByUID($uid);
    $sql="delete from isat.position where device_id={$devid}";
    sql($sql,$result2,$num,0);
    if ($result2) return true;
    return false;
}

function deleteShareByID ($id)
{
    $sql="delete from isat.device_share where id='{$id}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function deleteCamSerialByID ($id)
{
    $sql="delete from isat.series_number where id='{$id}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function createDeviceRegTable()
{
   if ($bRegion)
      $sql = "select c1.id, c1.uid, c1.name, c1.purpose, c1.url_prefix, c1.ip_addr, c1.port, c1.url_path, c1.reg_date, c1.internal_ip_addr, c1.internal_port, c1.region, c2.name as regionname from isat.device_reg as c1 left join isat.regions as c2 on c1.region = c2.id";
   else
      //$sql = "select c1.id, c1.uid, c1.name, c1.url_prefix, c1.ip_addr, c1.port, c1.url_path, c1.reg_date, c1.internal_ip_addr, c1.internal_port from isat.device_reg";
      $sql = "select * from isat.device_reg"; 
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['reg_date'] = date ("Y-m-d H:i:s", $arr['reg_date']); //change format
      $services[$index] = $arr;
    	$index++;
    }//for
  $html = "<b>device_reg</b> Total: {$num}";
  $html .= "\n<table id='tbl41' class=table_main><tr class=topic_main><td>ID</td><td>uid / name</td>"; //add table header 3
  if ($bRegion)
    $html .= "<td>purpose</td><td>url_prefix / ip_addr / port / url_path</td><td>reg_date</td><td>internal_ip_addr / internal_port</td><td>region</td></tr>";
  else
    $html .= "<td></td><td>url_prefix / ip_addr / port / url_path</td><td>reg_date</td><td>internal_ip_addr / internal_port</td><td></td></tr>";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['uid']} / {$service['name']}</td>\n";
    if ($bRegion)
        $html.= "<td>{$service['purpose']}</td>\n";
    else  $html.= "<td></td>\n";
    $html.= "<td>{$service['url_prefix']} / {$service['ip_addr']} / {$service['port']} / {$service['url_path']}</td>\n";
    $html.= "<td>{$service['reg_date']}</td>\n";
    $html.= "<td>{$service['internal_ip_addr']} / {$service['internal_port']}</td>\n";
    //if ($bRegion)
    //    $html.= "<td>{$service['region']} / {$service['regionname']}</td>\n";
    if (isset($_REQUEST['debugadmin']) ){
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btnAction' value=\"Remove {$service['mac_addr']}\" class=\"btn_1\">\n";
      $html.= "<input type=hidden name='step' value=\"delete_device_reg\" >\n";
      //$html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
      $html.= "<input type=hidden name='mac' value=\"{$service['mac_addr']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>\n";
      $html.= "</form></td>\n";
    }else   $html.= "<td></td>\n";
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function createDeviceTable($nLimit)
{
    //$sql = "select * from isat.device";
  if ($nLimit==0)
    $sql = "select c1.id,  c1.uid, c1.name as camera_name, c1.owner_id, c2.name as owner_name, c1.update_date ,c1.expire_date from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id";
  else $sql = "select c1.id,  c1.uid, c1.name as camera_name, c1.owner_id, c2.name as owner_name, c1.update_date ,c1.expire_date from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id'] . " : ". $arr['owner_name'];
      $arr['update_date'] = date ("Y-m-d H:i:s", $arr['update_date']);
      //$arr['date'] = date ("Y-m-d H:i:s", mysql_result($result,$i,'reg_date')). "/" . date ("Y-m-d H:i:s",mysql_result($result,$i,'update_date')) . "/" .date ("Y-m-d H:i:s",mysql_result($result,$i,'expire_date'));
      //$arr['version'] = mysql_result($result,$i,'version')."/".mysql_result($result,$i,'auth');
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from isat.device";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>device</b> Total: {$arr['total']}";

  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>ID</td><td>uid / name</td><td>owner name</td>"; //add table header 3
  $html .= "<td>reg/update date</td></tr>";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['uid']} / {$service['camera_name']}</td>\n";
    $html.= "<td>{$service['owner_id_name']}</td>\n";
    $html.= "<td>".date ("Y-m-d H:i:s",$service['reg_date'])."/{$service['update_date']}</td>\n";
    //$html.= "<td>".date ("Y-m-d H:i:s",$service['reg_date'])."-".date ("Y-m-d H:i:s",$service['reg_date'])."/{$service['update_date']}</td>\n";
    //$html.= "<td>{$service['model_id']}</td>\n";
    //$html.= "<td>{$service['version']}</td>\n";

    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function createSerialTable($nLimit)
{
  if ($nLimit==0)
      $sql = "select * from isat.series_number";
  else $sql = "select * from isat.series_number limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //$arr['date'] = date ("Y-m-d H:i:s", $arr['reg_date']). "/" . date ("Y-m-d H:i:s",$arr['update_date']);
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from isat.series_number";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>series_number</b> Total: {$arr['total']}";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>UID</td><td>password</td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['mac']} / {$service['uid']}</td>\n";
    $html.= "<td>{$service['activated_code']} / {$service['password']}</td>\n";
    //$html.= "<td>{$service['bind_mac']}</td>\n";
    //$html.= "<td>{$service['date']}</td>\n";
    $html.= "</tr>\n"; 
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function createLicenseTable($nLimit)
{
  if ($nLimit==0)
    $sql = "select * from qlync.license";
  else  $sql = "select * from qlync.license limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //$arr['date'] = date ("Y-m-d H:i:s", $arr['reg_date']). "/" . date ("Y-m-d H:i:s",$arr['update_date']);
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from qlync.license";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>qlync.license</b> Total: {$arr['total']}";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>MAC /code</td><td>PID / CID</td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['ID']}</td>\n";
    $html.= "<td>{$service['Mac']} / {$service['Code']}</td>\n";
    $html.= "<td>{$service['PID']} / {$service['CID']}</td>\n";
    $html.= "</tr>\n"; 
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}
function createPositionTable($nLimit)
{
  if ($nLimit==0)
    $sql = "select * from isat.position";
  else $sql = "select * from isat.position limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from isat.position";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>position</b> Total: {$arr['total']}";

  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>device_id</td><td>owner id (visitor id)</td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['device_id']}</td>\n";
    $html.= "<td>{$service['owner_id']}</td>\n";
    $html.= "</td>\n";         
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function ListPositionTable($owner_id)
{
  $html = "OWNER_ID>{$owner_id}<br>";
  $sql="select c1.id, c1.owner_id, device_id, c2.uid  from isat.position as c1 left join isat.device as c2 on c1.device_id=c2.id where c1.owner_id >{$owner_id}";
  sql($sql,$result,$num,0);
  $html .= "Found {$num}<br>\n<table id='tbl4' class=table_main><tr class=topic_main><td>position id</td><td>owner id</td><td>device_id</td><td>uid</td><td></td></tr>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$arr['id']}</td>\n";
    $html.= "<td>{$arr['owner_id']}</td>\n";
    $html.= "<td>{$arr['device_id']}</td>\n";
    $html.= "<td>{$arr['uid']}</td>\n";
    if (isset($_REQUEST['debugadmin'])){
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btnDeletePosByID' value=\"Remove\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='id' value=\"{$arr['id']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>\n";
      $html.= "</form></td></tr>\n";
    }else
      $html.= "<td></td></tr>\n";
  }
  $html.= "</table>\n";
  $sql="select id,uid,owner_id from isat.device_share where owner_id>{$owner_id}";
  sql($sql,$result,$num,0);
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>device_share id</td><td>owner id</td><td>uid</td><td></td></tr>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$arr['id']}</td>\n";
    $html.= "<td>{$arr['owner_id']}</td>\n";
    $html.= "<td>{$arr['uid']}</td>\n";
    $html.= "<td></td></tr>\n";
  }
  $html.= "</table>\n";
  return $html;
}

//$sql="select  from isat.position as c1 left join isat.device_share as c2 on c1.owner_id=c2.owner_id where c1.owner_id=85 group by c2.uid;";
function findPositionList($owner_id)
{
  $html = "OWNER_ID={$owner_id}<br>";
  $sql="select c1.id, device_id, c2.uid  from isat.position as c1 left join isat.device as c2 on c1.device_id=c2.id where c1.owner_id={$owner_id}";
  sql($sql,$result,$num,0);
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>position id</td><td>device_id</td><td>uid</td><td></td></tr>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$arr['id']}</td>\n";
    $html.= "<td>{$arr['device_id']}</td>\n";
    $html.= "<td>{$arr['uid']}</td>\n";
    if (isset($_REQUEST['debugadmin'])){
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btnDeletePosByID' value=\"Remove\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='id' value=\"{$arr['id']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>\n";
      $html.= "</form></td></tr>\n";
    }else
      $html.= "<td></td></tr>\n";
  }
  $html.= "</table>\n";
  $sql="select * from isat.device_share where owner_id={$owner_id}";
  sql($sql,$result,$num,0);
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>device_share id</td><td>uid</td><td></td></tr>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$arr['id']} (owner={$arr['owner_id']} / visitor={$arr['visitor_id']})</td>\n";
    $html.= "<td>{$arr['uid']}</td>\n";
    if (isset($_REQUEST['debugadmin'])){ //btnDeletePosByID
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btnDeleteShareByID' value=\"Remove\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='id' value=\"{$arr['id']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>\n";
      $html.= "</form></td></tr>\n";
    }else
    $html.= "<td></td></tr>\n";
  }
  $html.= "</table>\n";
  return $html;
}

function createPositionStatisticTable($nLimit)
{
  $sql = "select owner_id, count(device_id) as num from isat.position group by owner_id order by num desc";
  if ($nLimit >0)
    $sql .= " limit {$nLimit}"; 
  sql($sql,$result,$num,0);
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>owner id</td><td>Share Camera #</td><td></td></tr>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$arr['owner_id']}</td>\n";
    $html.= "<td>{$arr['num']}</td>\n";
    $html.= "<td></td></tr>\n";
  }
  $html.= "</table>\n";
  echo $html;
}

function createShareStatisticTable($nLimit)
{
   $sql2 = "select id, name from isat.user";
   sql($sql2,$result2,$num2,0);
   $userArr = array();
    for($i=0;$i<$num2;$i++){
      fetch($arr,$result2,$i,0);
      $userArr[$arr['id']] = $arr['name'];
    }
    ////////share count table
    $sql = "select visitor_id, count(*) as share_count from isat.device_share group by visitor_id order by share_count desc";
    if ($nLimit >0)
      $sql .= " limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['visitor_id_name'] = $arr['visitor_id'] . " : " . $userArr[$arr['visitor_id']];
      $services[$index] = $arr;
    	$index++;
    }//for
  $html = "<b>Share Count of visitor Account ({$index})</b>";
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>visiter id/name</td><td>Share Camera #</td><td></td></tr>"; 
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['visitor_id_name']}</td>\n";
    $html.= "<td>{$service['share_count']}</td>\n";
    if (isset($_REQUEST["debugadmin"])){
    $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
    $html.= "<input type=submit name='btnCameraUnshareFromVisitor' value=\"Remove\" class=\"btn_2\">\n";
    $html.= "<input type=hidden name='step' value=\"delsharevisitor\" >\n";
    $html.= "<input type=hidden name='visitor_id_name' value=\"{$service['visitor_id_name']}\" >\n";
    $html.= "<input type=hidden name=debugadmin value='1'>\n";
    $html.= "</form></td>\n";
    }else $html.= "<td></td>\n";
  }
  $html.= "</tr></table>\n";
  ////////camera share count table
  unset($services);
    $sql = "select owner_id, uid, count(*) as num from isat.device_share group by uid order by num desc";
    if ($nLimit >0)
      $sql .= " limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id']. " : " .$userArr[$arr['owner_id']];
      $services[$index] = $arr;
    	$index++;
    }//for
  $html .= "<b>Camera Share Count ({$index})</b>";
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main><td>uid</td><td>Share times</td><td></td></tr>"; 
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['owner_id_name']} / {$service['uid']}</td>\n";
    $html.= "<td>{$service['num']}</td>\n";
    if (isset($_REQUEST["debugadmin"])){
    $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
    $html.= "<input type=submit name='btnCameraUnshareByMAC' value=\"Remove\" class=\"btn_2\">\n";
    $html.= "<input type=hidden name='step' value=\"delsharecamera\" >\n";
    $html.= "<input type=hidden name='uid' value=\"{$service['uid']}\" >\n";
    $html.= "<input type=hidden name=debugadmin value='1'>\n";
    $html.= "</form></td>\n";
    }else $html.= "<td></td>\n";
  }
  $html.= "</tr></table>\n";
  echo $html;
}

function createShareTable($nLimit)
{
   $sql2 = "select id, name from isat.user";
   sql($sql2,$result2,$num2,0);
   $userArr = array();
    for($i=0;$i<$num2;$i++){
      fetch($arr,$result2,$i,0);
      $userArr[$arr['id']] = $arr['name'];
    }
  ////////share database
  if ($nLimit ==0)
    $sql = "select c1.id as id, c1.uid, c1.owner_id, c2.name as owner_name, c1.visitor_id from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id";
  else $sql = "select c1.id as id, c1.uid, c1.owner_id, c2.name as owner_name, c1.visitor_id from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
      $arr['visitor_id_name'] = $arr['visitor_id'] . " : " . $userArr[$arr['visitor_id']];
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from isat.device_share";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>device_share</b> Total: {$arr['total']}";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>UID</td><td>owner id/name</td><td>visiter id/name</td><td></td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['uid']}</td>\n";
    $html.= "<td>{$service['owner_id_name']}</td>\n";
    $html.= "<td>{$service['visitor_id_name']}</td>\n";
    if (isset($_REQUEST["debugadmin"])){
    $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
    $html.= "<input type=submit name='btnAction' value=\"Remove\" class=\"btn_2\">\n";
    $html.= "<input type=hidden name='step' value=\"delshare\" >\n";
    $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
    $html.= "<input type=hidden name='uid' value=\"{$service['uid']}\" >\n";
    $html.= "<input type=hidden name='visitor_id' value=\"{$service['visitor_id']}\" >\n";
    $html.= "<input type=hidden name=debugadmin value='1'>\n";
    $html.= "</form></td>\n";
    }else $html.= "<td></td>\n";         
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function selectDeviceUid($tagName)
{
    $sql = "select DISTINCT c1.uid,c1.owner_id,c2.name as owner_name from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id order by c1.uid";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
      $html.= "\n<option value='{$arr['uid']}_{$arr['owner_id']}'>{$arr['uid']} ({$arr['owner_id_name']})</option>";
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}
function selectUserList($tagName)
{
   $sql = "select id, name from isat.user order by id";
   sql($sql,$result,$num,0);

  $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['id_name'] = $arr['id']. " : " .$arr['name'];
      $html.= "\n<option value='{$arr['id']}'>{$arr['id_name']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}
?>
<!--html>
<head>
</head>
<body-->
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<div align=center><b><font size=5>Mgmt Devices</font></b></div>
<a href="online_list_sort.php" target=onlineList>Online Sort List</a>
<div id="container">
<?php
if (isset($msg_err))
  echo $msg_err."<hr>";
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php
     selectDeviceUid("uid_owner_id");
?>
<input type=submit name=btnCameraShare value="Share to" class=btn_2>
<?php
     selectUserList("visitor_id");
if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<?php
if (isset($_REQUEST["debugadmin"])){ //manually add/delete share 
?>
<form name=manualshare method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=text name=uid_owner_id placeholder='M04CC-MAC(18)_ownerID'>
<input type=submit name=btnCameraShare value="Share to" class=btn_2>
<input type=text name=visitor_id placeholder='visitorID'>
<input type=hidden name=debugadmin value='1'>
</form>

<form name=manualdelshare method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=submit name=btnAction1 value="Delete Share MAC" class=btn_1>
<input type=text name=uid placeholder='M04CC-MAC(18)'>
<input type=hidden name=step value='delsharecamera'>
<input type=hidden name=debugadmin value='1'>
</form>
<?php } ?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter_stable" id="uid_filter_stable" onchange="optionValue(this.form.uid_filter_stable, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_stable'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(MORE20)" <?php if($_REQUEST['uid_filter_stable'] =="(MORE20)" ) echo "selected";?>>(MORE20)</option>
<!--option value="(ALL)" <?php if($_REQUEST['uid_filter_stable'] =="(ALL)" ) echo "selected";?>>(ALL)</option-->
</select>
</form>
<?php
if($_REQUEST['uid_filter_stable'] =="(ALL)" ){
     createShareStatisticTable(0);
     createPositionStatisticTable(0);
}else if($_REQUEST['uid_filter_stable'] =="(MORE)" ){
     createShareStatisticTable(10);
     createPositionStatisticTable(10);
}else if($_REQUEST['uid_filter_stable'] =="(MORE20)" ){
     createShareStatisticTable(20);
     createPositionStatisticTable(20);
}else
  echo "isat.sTable and isat.pTable";
?>
<form name=findpos method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=text name=owner_id value='<?php if( isset($_REQUEST['owner_id'])) echo $_REQUEST['owner_id'];?>' placeholder='owner_id'>
<input type=submit name=btnCameraPos value="Find Shared Cam Pos" class=btn_1>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<form name=findpos2 method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=submit name=btnPosOwnerID value="List owner_id >" class=btn_1>
<input type=text name=owner_id value='<?php if( isset($_REQUEST['owner_id'])) echo $_REQUEST['owner_id'];?>' placeholder='owner_id'>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<form name=findpos2 method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=submit name=btnPosNULL value="Cleanup NULL owner_id Pos" class=btn_1>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter_share" id="uid_filter_share" onchange="optionValue(this.form.uid_filter_share, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_share'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_share'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
</form>
<?php
if($_REQUEST['uid_filter_share'] =="(ALL)" )
     createShareTable(0);
else if($_REQUEST['uid_filter_share'] =="(MORE)" )
     createShareTable(20);
else
  echo "isat.device_share";
?>

<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter_pos" id="uid_filter_pos" onchange="optionValue(this.form.uid_filter_pos, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_pos'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_pos'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<?php
if($_REQUEST['uid_filter_pos'] =="(ALL)" )
    createPositionTable(0);
else if($_REQUEST['uid_filter_pos'] =="(MORE)" )
    createPositionTable(20);
else
  echo "isat.position";

if (isset($_REQUEST["debugadmin"])){ 
?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<br>
<?php
     createDeviceRegTable(); 
?>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
</form>
<?php
if($_REQUEST['uid_filter'] =="(ALL)" )
     createDeviceTable(0);
else if($_REQUEST['uid_filter'] =="(MORE)" )
     createDeviceTable(20);
else
  echo "isat.device";
?>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=text  size=20 name=mac value='<?php if ($_REQUEST["mac"]!="") echo $_REQUEST["mac"]; else echo "Z01CC-000000000xxx";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnDelDeviceMAC value="Delete Device like"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter_serial" id="uid_filter_serial" onchange="optionValue(this.form.uid_filter_serial, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_serial'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_serial'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
</form>
<?php
if($_REQUEST['uid_filter_serial'] =="(ALL)" )
  createSerialTable(0);
else if($_REQUEST['uid_filter_serial'] =="(MORE)" )
  createSerialTable(20);
else
  echo "isat.series_number";
?>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=text  size=20 name=mac value='<?php if ($_REQUEST["mac"]!="") echo $_REQUEST["mac"]; else echo "Z01CC-000000000xxx";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnDelSerialMAC value="Delete Serial like"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name="uid_filter_lic" id="uid_filter_lic" onchange="optionValue(this.form.uid_filter_lic, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_lic'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_lic'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
</form>
<?php
if($_REQUEST['uid_filter_lic'] =="(ALL)" )
  createLicenseTable(0);
else if($_REQUEST['uid_filter_lic'] =="(MORE)" )
  createLicenseTable(20);
else
  echo "qlync.license";
?>
<br>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=text  size=20 name=mac value='<?php if ($_REQUEST["mac"]!="") echo $_REQUEST["mac"]; else echo "Z01CC-000000000xxx";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnLicenseMAC value="Delete License like"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>

	</div>
</body>
</html>