<?php
/****************
 *Validated on Jul-21,2016,
 * config file is under same folder _iveda.inc
 * Translated to Chinese page
 * add debugadmin feature to hide remove button
 * add debugadmin for create/share feature    
 * Change Fold/Expand button to select/option dropdown list, 
 * add visitor Cam list button to the device_share dropdown list 
 *Writer: JinHo, Chang
 * /etc/php5/apache2/php.ini set  ;default_charset = "UTF-8" 
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");
include_once("/var/www/qlync_admin/html/common/scid.php");
#Authentication Section
if( !isset($_SESSION["Contact"]) )   exit();
function myPDOQuery($sql){
  global $mysql_ip, $mysql_id, $mysql_pwd;
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="")//pre v3.2.1 vesion
    $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd);
  else//correct utf8 
  $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
  $qResult =$pdo->query($sql);
  $arr= $qResult->fetchAll(PDO::FETCH_ASSOC);
  return $arr;
}
############  Authentication Section End
//created by web API cannot be deleted on Admin
if($_REQUEST["step"]=="device_share")
{
    if( isset($_REQUEST['btn_device_share']) ){
      if(( $_REQUEST['share_visitor_name']!="" ) and ( $_REQUEST['share_uid']!="" )){
        $req_var = explode("-", $_REQUEST['share_uid']);
        $req_id_name = explode(":", $_REQUEST['share_visitor_name']);
        $result = addShareDeviceAPI($req_var[1],$req_id_name[1]);
        //$result = myShareDeviceAPI($_REQUEST["share_mac"],$_REQUEST["share_visitor_name"]);
        if ($result){
          $msg_err = "<font color=blue>新增分享".$_REQUEST["share_uid"]. "至".$_REQUEST["share_visitor_name"]." 成功!</font><br>\n";
        }else $msg_err = "<font color=red>新增分享".$_REQUEST["share_uid"]. "至".$_REQUEST["share_visitor_name"]." 失敗!</font><br>\n";
      }
    }else if( isset($_REQUEST['btn_device_scidshare']) ){
      if(  ( $_REQUEST['share_uid']!="" ) ){
        $scidid=getDeviceSCID($_REQUEST['share_uid']);
        $result = addSCIDShareDeviceAPI( $_REQUEST['share_uid'],$scidid);
        if ($result){
            $msg_err = "<font color=blue>新增分享".$_REQUEST["share_uid"]. "至".$scidid." 成功!</font><br>\n";
          }else $msg_err = "<font color=red>新增分享".$_REQUEST["share_uid"]. "至".$scidid." 失敗!</font><br>\n";
        }
    }else if( isset($_REQUEST['btn_device_unshare']) ){
      if( ($_REQUEST['share_visitor_name']!="" )and ( $_REQUEST['share_uid']!="" )){
        $req_var = explode("-", $_REQUEST['share_uid']);
        $req_id_name = explode(":", $_REQUEST['share_visitor_name']);
        $result = ShareDeviceAPI('remove_share',$req_var[1],$req_id_name[0]);
        //$result = deleteShareDeviceAPI($req_var[1],$_REQUEST["share_visitor_name"]);
        if ($result){
          $msg_err = "<font color=blue>從".$_REQUEST["share_visitor_name"]."刪除分享".$_REQUEST["share_uid"]. " 成功!</font><br>\n";
        }else $msg_err = "<font color=red>從".$_REQUEST["share_visitor_name"]."刪除分享".$_REQUEST["share_uid"]. " 失敗!</font><br>\n";
      }
    }else if( isset($_REQUEST['btn_device_enableshare']) ){
        if( ($_REQUEST['share_visitor_name']!="" )and ( $_REQUEST['share_uid']!="" )){
          $req_var = explode("-", $_REQUEST['share_uid']);
           $req_id_name = explode(":", $_REQUEST['share_visitor_name']);
          $result = ShareDeviceAPI('enable_share',$req_var[1],$req_id_name[0]);
          if ($result){
            $msg_err = "<font color=blue>從".$_REQUEST["share_visitor_name"]."恢復分享".$_REQUEST["share_uid"]. " 成功!</font><br>\n";
          }else $msg_err = "<font color=red>從".$_REQUEST["share_visitor_name"]."恢復分享".$_REQUEST["share_uid"]. " 失敗!</font><br>\n";
        }
    }else if( isset($_REQUEST['btn_device_disableshare']) ){
        if( ($_REQUEST['share_visitor_name']!="" )and ( $_REQUEST['share_uid']!="" )){
          $req_var = explode("-", $_REQUEST['share_uid']);
          $req_id_name = explode(":", $_REQUEST['share_visitor_name']);
          $result = ShareDeviceAPI('disable_share',$req_var[1],$req_id_name[0]);
          if ($result){
            $msg_err = "<font color=blue>從".$_REQUEST["share_visitor_name"]."暫停分享".$_REQUEST["share_uid"]. " 成功!</font><br>\n";
          }else $msg_err = "<font color=red>從".$_REQUEST["share_visitor_name"]."暫停分享".$_REQUEST["share_uid"]. " 失敗!</font><br>\n";
        }
    }
}
function getDeviceSCID($uid)
{
   $sql="select user_name from isat.query_info where uid='{$uid}'";
   sql($sql,$result,$num,0);
   fetch($arr,$result,0,0);
   $sql="select LPAD(group_id,'10','0000000000')as group_str from isat.user where name='".$arr['user_name']."'";//find group
   sql($sql,$result2,$num2,0);
   fetch($arr2,$result2,0,0);
   if ($result2)
    return substr($arr2['group_str'],0,3);
  else return "";
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
function myShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
   	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share_gw.php?command=share_camera&mac={$mac}&user_name={$username}";
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
    return false;
}

//qlync API enabled after v3.x.x
function addShareDeviceAPI ($mac, $username)
{//add_share, disable_share, enable_share, remove_share
   global $oem, $api_id, $api_pwd, $api_path;
      $params = array(
              'command'               => 'add_share',
              'mac_addr'              => $mac,
              'user'            	=> $username,
      );
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_device.php?".http_build_query($params);
//echo $import_target_url;
      	$ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
      	curl_setopt($ch, CURLOPT_URL,$import_target_url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$result=curl_exec($ch);
      	curl_close($ch);
      	$content=array();
      	$content=json_decode($result,true);
//print_r($content);
  	if($content["status"]=="success")
      return true;
    return false;
}
//qlync API enabled after v3.x.x
function ShareDeviceAPI ($command, $mac, $vid)
{//disable_share, enable_share, remove_share
   global $oem, $api_id, $api_pwd, $api_path;
      $params = array(
              'command'               => $command,
              'mac_addr'              => $mac,
              'visitor_id'            	=> $vid,
      );
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_device.php?".http_build_query($params);
//  echo $import_target_url;
      	$ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
      	curl_setopt($ch, CURLOPT_URL,$import_target_url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$result=curl_exec($ch);
      	curl_close($ch);
      	$content=array();
      	$content=json_decode($result,true);
//print_r($content);
  	if($content["status"]=="success")
      return true;
    return false;
}


function deleteShareDevice ($id)
{//select c1.id as id, c1.uid, c1.owner_id, c2.name as owner_name, c1.visitor_id from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id
    $sqlinfo = "select * from isat.device_share where id = '{$id}'";
    sql($sqlinfo,$result,$num,0);
    if ($result)
      if ($num > 0)
        fetch($arr,$result,0,0);
    $sql = "delete from isat.device_share where id = '{$id}'";
    sql($sql,$result,$num,0);
    if ($result) {
      addShareLog ($arr['uid'],$arr['owner_id'],"",$arr['visitor_id'],"","DELETE");
      return true;
    }
    return false;
}
function deleteShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
   	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share_gw.php?command=unshare_camera&mac={$mac}&user_name={$username}";
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

function getCameraName($mac)
{
   $sql2 = "select name from isat.query_share where mac_addr='{$mac}' group by mac_addr";
   //sql($sql2,$result2,$num2,0);
   //fetch($arr,$result2,0,0);
    $arrs= myPDOQuery($sql2);
    if (sizeof($arrs)==1){
      $arr=$arrs[0];
  	//if($result2)
      return $arr['name'];
    }
    return "";
}

function createShareTable($limit, $whereParam)
{
  global $scid;
   $sql2 = "select id, name from isat.user where group_id<>1";
   sql($sql2,$result2,$num2,0);
   $userArr = array();
    for($i=0;$i<$num2;$i++){
      fetch($arr,$result2,$i,0);
      $userArr[$arr['id']] = $arr['name'];
    }

    $sql = "select c1.id as id, c1.uid, c1.owner_id, c1.enabled, c2.name as owner_name, c1.visitor_id,LPAD(c2.group_id,'10','0000000000')as group_str from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id where c2.group_id<>1  {$whereParam} order by owner_id, c1.uid";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
      $arr['visitor_id_name'] = $arr['visitor_id'] . " : " . $userArr[$arr['visitor_id']];
      $arr['visitor_name'] =  $userArr[$arr['visitor_id']];
      $services[$index] = $arr;
    	$index++;
    }//for
    
  $sql = "select count(*) as total from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id where c2.group_id<>1 {$whereParam}";
  sql($sql,$result,$num,0);
  fetch($arr,$result,1,0);
  $html = "Total: {$arr['total']}";

  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>攝影機MAC</td><td>所屬帳號</td><td>信眾帳號</td><td></td></tr>"; //add table header
  $index = 0;
  $tmpmac="";$tmpuser="";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $req_var = explode("-", $service['uid']);
    if ($tmpmac == $service['uid'] )
        $html.= "<td></td>\n";
    else{
        $html.= "<td><a href='godwatchq_msg.php?mac=".$req_var[1]."'>{$service['uid']}</a>{$service['name']}".getCameraName($req_var[1])."</td>\n";
        $tmpmac =$service['uid'];
    }
    if ($tmpuser == $service['owner_id_name'] )
        $html.= "<td></td>\n";
    else{
        $html.= "<td>{$service['owner_id_name']} (SCID:".substr($service['group_str'],0,3).$scid[substr($service['group_str'],0,3)]['name'].")</td>\n";
        $tmpuser =$service['owner_id_name'];
    }
    if ($service['enabled']<>1)
      $html.= "<td><font color=red><b>{$service['visitor_id_name']}</b></font></td>\n";
    else 
      $html.= "<td>{$service['visitor_id_name']}</td>\n";
    //if($_SESSION["ID_admin_qlync"]) {//god admin
    if (isset($_REQUEST["debugadmin"])){ 
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btn_device_unshare' value=\"移除分享\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='step' value=\"device_share\" >\n";
      $html.= "<input type=hidden name='share_uid' value=\"{$service['uid']}\" >\n";
      $html.= "<input type=hidden name='visitor_id' value=\"{$service['visitor_id']}\" >\n";
      $html.= "<input type=hidden name='share_visitor_name' value=\"{$service['visitor_name']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>";
      $html.= "</form></td>\n";
    }else $html.= "<td></td>\n";          
    $html.= "</tr>\n";
    $index++;
    if (($limit!=0) and ($index > $limit)) break;
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function selectDeviceUid($tagName)
{
  global $oem;
    $sql = "select DISTINCT c1.uid,c1.owner_id,c2.name as owner_name,LPAD(c2.group_id,'10','0000000000')as group_str from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id where c2.group_id<>1 order by c2.group_id";
    sql($sql,$result,$num,0);
    $tmp="";
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($tmp!=substr($arr['group_str'],0,3)){
          $tmp=substr($arr['group_str'],0,3);
          $html.= "\n<option value=''>====={$tmp}=====</option>";
      }
      //if ( (strpos($arr['uid'],MCID)!== FALSE) or (strpos($arr['uid'],ZCID)!== FALSE) or (strpos($arr['uid'],IMCID)!== FALSE)) {
      if (substr($arr['uid'],0,6) != "{$oem}CC-" ){
        $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
        $html.= "\n<option value='{$arr['uid']}'>{$arr['uid']} ({$arr['owner_id_name']})</option>";
      }
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectUserList($tagName,$selectName)
{
   //$sql = "select id, name from isat.user where group_id<>1";
   $sql = "select id, name,LPAD(group_id,'10','0000000000')as group_str from isat.user where group_id<>1 order by group_str, id desc";
   
   sql($sql,$result,$num,0);
  //$tmp=substr($arr['group_str'],0,3);
  $tmp="";
  $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //skip teacher and festival 
      if ((substr($arr['group_str'],3,3) =="301" ) or (substr($arr['group_str'],3,3) =="302" )) continue; 
      if ($tmp!=substr($arr['group_str'],0,3)){
          $tmp=substr($arr['group_str'],0,3);
          $html.= "\n<option value=''>====={$tmp}=====</option>";
      }
        if (isset($selectName) and ($selectName == $arr['id'].":".$arr['name']))
          //$html.= "\n<option value='{$arr['id']}_{$arr['name']}' selected>{$arr['id']}:{$arr['name']}</option>";
          $html.= "\n<option value='{$arr['id']}:{$arr['name']}' selected>{$arr['id']}:{$arr['name']}</option>";
        else
          //$html.= "\n<option value='{$arr['id']}_{$arr['name']}'>{$arr['id']}:{$arr['name']}</option>";
          $html.= "\n<option value='{$arr['id']}:{$arr['name']}'>{$arr['id']}:{$arr['name']}</option>";

    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectSCIDGroupList ($gid)
{
  //$scid[00x][name]
   $sql = "select SCID as id, Name from qlync.scid where name is not null;";
   sql($sql,$result,$num,0);

  $html = "";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $idnum=intval($arr['id']);
      if ( (is_numeric($gid)) and (intval($gid)==$idnum)){
        $html.= "\n<option value='{$idnum}' selected>{$arr['id']}:{$arr['Name']}</option>";
      }else{
        $html.= "\n<option value='{$idnum}'>{$arr['id']}:{$arr['Name']}</option>";     
      }
 
    }//for
	echo $html;
}
?>
<!--
<html>
<head>
<title>Share</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
-->
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  //thisform.uid_filter.value = chosenoption.value;
  thisformobj.value = chosenoption.value;
}
</script>
<div align=center><b><font size=5>神在看攝影機分享管理</font></b></div>
<div id="container">
<?php
echo $msg_err;
?>
<form name=shareform method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table>
<tr><tr>
<td rowspan=6>
<?php
     selectDeviceUid("share_uid");
?>
</td>
<td>
<input type=submit name=btn_device_share value="攝影機分享至信眾">
</td>
<td rowspan=6>
<?php
     selectUserList("share_visitor_name",$_REQUEST["share_visitor_name"]);
?>
</td></tr>
<tr><td>
<input type=submit name=btn_device_scidshare value="同SCID信眾分享">
</td></tr>
<tr><td>
<input type=submit name=btn_device_scidunshare value="同SCID信眾刪除分享" disabled>
</td></tr>
<tr><td>
<input type=submit name=btn_device_unshare value="攝影機刪除分享">
</td></tr>
<tr><td>
<input type=submit name=btn_device_disableshare value="攝影機暫停分享">
</td></tr>
<tr><td>
<input type=submit name=btn_device_enableshare value="攝影機恢復分享">
</td></tr>
<input type=hidden name=step value='device_share'>
</table>
</form>
<br> 
<hr>
<form>
<select name="uid_filter_share" id="uid_filter" onchange="optionValue(this.form.uid_filter_share, this);this.form.submit();">
<option value="(FOLD)" <?php if($_REQUEST['uid_filter_share'] =="(FOLD)" ) echo "selected";?>>(FOLD)</option>
<?php
     selectSCIDGroupList($_REQUEST['uid_filter_share']);
?>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_share'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
</form>
<?php
//var_dump($_REQUEST);
  if($_REQUEST['uid_filter_share'] =="(FOLD)" )
    createShareTable(10,"");
  else if($_REQUEST['uid_filter_share'] =="(ALL)" )
    createShareTable(0,"");
  else if( isset($_REQUEST['uid_filter_share'] ) )
    createShareTable(0,"  and c2.group_id like '".$_REQUEST['uid_filter_share']."%'");
  else
    createShareTable(10,"");    
?>
  <br>
	</div>
</body>
</html>