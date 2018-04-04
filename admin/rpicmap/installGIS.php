<?php
/****************
 *Validated on Mar-8,2018,
 * reference from showGIS.php
 * Required qlync mysql connection config.php
 * https://xpress.megasys.com.tw:8080/plugin/rpic/installGIS.php
 * change to search first 4 character as list  
 *Writer: JinHo, Chang
*****************/
define("DEBUG_FLAG","OFF"); //OFF ON
define("HEAD_CHAR_MATCH",4);
include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
#################translation section
/*
$lan="zh_TW.UTF-8";
setlocale(LC_MESSAGES,  $lan);
setlocale(LC_NUMERIC, $lan);
setlocale(LC_TIME, $lan);
setlocale(LC_COLLATE, $lan);
setlocale(LC_MONETARY, $lan);
bindtextdomain("messages", "{$home_path}/locale");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");
$lan_tmp=explode(".",$lan);
putenv("LANG={$lan_tmp[0]}");
setlocale("LC_ALL","{$lan_tmp[0]}");
*/
#################translation section end
if (DEBUG_FLAG == "ON") $oem = "C13"; //test
require_once ("share.inc");
require_once ("rpic.inc");
header("Content-Type:text/html; charset=utf-8");
define("DEFAULT_APPMODE","租賃影像開工");
define("DEFAULT_IS_INSTALLER","1"); //always site, not vendor info

$DEFAULT_PWD = ["DEMO"=>"1qaz2wsx"];
//$siteOEMTag = array();
if (isset($RPICAPP_USER_PWD)){
  foreach ($RPICAPP_USER_PWD as $key=>$data){
    if (($key == "RPIC")or ($key == "N99")) continue;
    $DEFAULT_PWD[$key] = $data[1];
    //$siteOEMTag[$key] = array($data[5],"image/{$key}.png",$data[0]);
  }
}else  die("OEM/Pwd Info Not Found!!");
//var_dump($DEFAULT_PWD);


$bInputData = false; //keep input data flag
$tcservice = null; //default value
$installuser = "";
if ($oem=="X02"){
  if (isset($_REQUEST['installuser']))
    $installuser = $_REQUEST['installuser'];
  else if ($_SESSION["Email"]!=""){
    $installuser = $_SESSION['Email']; 
  }
  if ($installuser != "") getDefault($installuser,$tcservice);
}else if ($oem=="C13"){
  if (isset($_REQUEST['TCNAME']))
    getVendorDefault($_REQUEST['TCNAME'],$tcservice);
  $installuser = $_SESSION['Email'];
}

//default pwd is align with SITE
if (!is_null($DEFAULT_PWD[$oem])){
  define("APP_USER_PWD",$DEFAULT_PWD[$oem]);
}else die("OEM/Pwd Info Not Found!!");

if(isset($_REQUEST['form-type'])){
  $service = null;
  $type = htmlspecialchars($_REQUEST['form-type'], ENT_QUOTES);
  if($type=='public-service'){
    $id = trim(htmlspecialchars($_REQUEST['public-cloudid'], ENT_QUOTES));
    $ACNO = trim(htmlspecialchars($_REQUEST['public-ACNO'], ENT_QUOTES));
    $type = trim(htmlspecialchars($_REQUEST['public-type'], ENT_QUOTES));
    if ($type == "enable")
      $service['is_public'] = 1;
    else if ($type == "disable")
      $service['is_public'] = 0;
    updateDBfield($id,$service);
    if ($type == "enable")
      $msgerr .= "<div id=\"info\" class=\"success\">{$ACNO}".TXT_GIS_ENABLE.TXT_SUCCESS."</div>";
    else if ($type == "disable")
      $msgerr .= "<div id=\"info\" class=\"success\">{$ACNO}".TXT_GIS_DISABLE.TXT_SUCCESS."</div>";
   $service['ACNO'] = $ACNO; //update here,updateDBfield only update is_public
   $service['action'] .= strtoupper($type)." GIS {$ACNO}";
   if ($service['result'] == "") $service['result'] = "SUCCESS";
   insertGISLOG($service);
   //LOG
   /*$user_info['visitor_name'] = $ACNO;
    if ($type == "enable")  InsertShareLog($user_info,MAP_ON,$service['result']);
    else if ($type == "disable")  InsertShareLog($user_info,MAP_OFF,$service['result']);
*/
  }else if($type=='share-service'){
    $id = trim(htmlspecialchars($_REQUEST['share-cloudid'], ENT_QUOTES));
    $MAC = trim(htmlspecialchars($_REQUEST['share-MAC'], ENT_QUOTES));
    $command = trim(htmlspecialchars($_REQUEST['share-command'], ENT_QUOTES));
    $service['ACNO'] = getValueByID($id,"ACNO");
    if ($command == "share_camera"){
      if (addShareDeviceAPI($MAC,$service['ACNO'])){
        $msgerr .="<div id=\"info\" class=\"success\">分享攝影機{$MAC}至 {$service['ACNO']}成功.</div>";
        updateMACList($id,$command,$MAC);
      }else{
          $msgerr .="<div id=\"info\" class=\"error\">分享攝影機{$MAC}至 {$service['ACNO']}失敗.</div>";
          $service['result'] = "FAIL";
      }
    }else if ($command == "unshare_camera"){
      $vid = getUserID($service['ACNO']);
      if (deleteShareDeviceAPI($MAC,$vid)){
        $msgerr .="<div id=\"info\" class=\"success\">{$service['ACNO']}刪除分享攝影機{$MAC}成功.</div>";
        updateMACList($id,$command,$MAC);
      }else{
          $msgerr .="<div id=\"info\" class=\"error\">{$service['ACNO']}刪除分享攝影機{$MAC}失敗.</div>";
          $service['result'] = "FAIL";
      }

    }
    
    $service['action'] .= "{$command} MAC {$MAC}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);  

  }else if($type=='add-service'){
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['add-OEM_ID'], ENT_QUOTES));
    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['add-ACNO'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['add-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['add-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['add-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['add-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['add-TC_TEL'], ENT_QUOTES));
    $service['APPMODE'] = trim(htmlspecialchars($_REQUEST['add-APPMODE'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['add-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['add-LNG'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['add-is_public'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['add-bind_account'], ENT_QUOTES));
    $service['start_date'] = trim(htmlspecialchars($_REQUEST['add-start_date'], ENT_QUOTES));
    if ($service['start_date']=="") $service['start_date'] = date("Y-m-d");
    $service['end_date'] = trim(htmlspecialchars($_REQUEST['add-end_date'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['add-note'], ENT_QUOTES));
    $service['is_installer'] = trim(htmlspecialchars($_REQUEST['add-is_installer'], ENT_QUOTES));
    $service['action'] = "ADD {$service['ACNO']}";
    if (getUserID($service['ACNO']) < 0){
      $service['action'] .= ";ACNO";
    //create ANCO account
    if (insertEndUserAPI($service['ACNO'])){ 
        $service['user_name']=$service['ACNO'];
        $msgerr .="<div id=\"info\" class=\"success\">分享帳號 {$service['ACNO']} 建立成功.</div>";
    }else{
       $service['user_name'] ="";
       $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 建立失敗.</div>";
       $service['result'] = "FAIL";
    }
    $b = createCloudService($service);

    if($b){
        //$msgerr .="<div id=\"info\" class=\"success\">新增圖資成功.</div>";
        //if bind_account exist, share all mac to ANCO
        $userid =getUserID($service['bind_account']);
        if ($userid>0){
            $service['action'] .= ";SHARE";
            //shareDevice2User($service['bind_account'],$service['ACNO']);
            if (addShareDeviceAPI($service['note'],$service['ACNO']))
              $msgerr .="<div id=\"info\" class=\"success\">分享攝影機{$service['note']}至 {$service['ACNO']}成功.</div>";
            else{
                $msgerr .="<div id=\"info\" class=\"error\">分享攝影機{$service['note']}至 {$service['ACNO']}失敗.</div>";
                $service['result'] = "FAIL";
            }
            
        }else{
            $service['action'] .= ";NO_BIND_ACCT";
            $msgerr .="<div id=\"info\" class=\"error\">分享攝影機失敗. 帳號{$service['bind_account']}不存在</div>";
            $service['result'] = "FAIL";
        }
      } 
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 已存在. 新增圖資失敗.</div>";
      $service['action'] .= ";DUP ACNO";
      $service['result'] = "FAIL";
      $bInputData = true;
    }
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }else if($type=='delete-service'){
    $deleteCloudID = htmlspecialchars($_REQUEST['delete-cloudid'], ENT_QUOTES);
    $service['ACNO'] = htmlspecialchars($_REQUEST['delete-ACNO'], ENT_QUOTES);
    $service['action'] = "DELETE {$service['ACNO']}";
    $b = removeByCloudID($deleteCloudID);
    if($b){
      $msgerr .="<div id=\"info\" class=\"success\">刪除 id:{$deleteCloudID} 成功.</div>";
      deleteEndUserAPI($service['ACNO']);
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">刪除 id:{$deleteCloudID} 失敗.</div>";
      $service['result'] = "FAIL";
    }
    $service['bind_account'] =  htmlspecialchars($_REQUEST['installuser'], ENT_QUOTES);

    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);

  }else if($type=='update-service'){
    $service['id'] = trim(htmlspecialchars($_REQUEST['update-cloudid'], ENT_QUOTES));
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['update-OEM_ID'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['update-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['update-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['update-DIGADD'], ENT_QUOTES));
    $service['APPMODE'] = trim(htmlspecialchars($_REQUEST['update-APPMODE'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['update-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['update-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['update-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['update-LNG'], ENT_QUOTES));
    //$service['is_public'] = trim(htmlspecialchars($_REQUEST['update-is_public'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['update-bind_account'], ENT_QUOTES));
    //$service['start_date'] = trim(htmlspecialchars($_REQUEST['update-start_date'], ENT_QUOTES));
    //$service['end_date'] = trim(htmlspecialchars($_REQUEST['update-end_date'], ENT_QUOTES));
    $service['end_date'] = date("Y-m-d");
    //$service['note'] = trim(htmlspecialchars($_REQUEST['update-note'], ENT_QUOTES));
    $note = trim(htmlspecialchars($_REQUEST['update-note'], ENT_QUOTES));
    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['update-ACNO'], ENT_QUOTES));
    $searchCloudID = $service['id'];

    //$b = updateCloudService($service);
    $b = updateDBfield($service['id'],$service);
    if($b){
      $msgerr .="<div id=\"info\" class=\"success\">更新 {$service['ACNO']} 成功.</div>";
      //insert new MAC
      updateMACList($service['id'],"share_camera",$note);
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">更新  {$service['ACNO']} 失敗.</div>";
      $service['result'] = "FAIL";
    }
    $service['action'] = "UPDATE {$service['ACNO']}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }
}
function shareDevice2User($owner, $target)
{
    $sql = "select mac_addr from isat.query_info where user_name = '{$owner}' group by mac_addr";
    sql($sql,$result,$num,0);
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if (!addShareDeviceAPI($arr['mac_addr'],$target)) return false;
    }
    return true;    
}

function getUniqueACNO($oemid="")
{
  if ($oemid=="X02") $prefix = "MEGA";
  else $prefix = "IVD";
  //$shareAccount= $prefix.date("YmdHis");
  $shareAccount= $prefix.date("Ymd")."-";
  for ($i=1;$i<1000;$i++){ //3 digits
    $sn = sprintf("%03d", $i);
    if (getUserID($shareAccount.$sn)<0)
      return $shareAccount.$sn;
  }
  return $shareAccount; //1-999 all fail
}


function createCloudService($service){
  $sql = "INSERT INTO customerservice.workeyegis SET 
  OEM_ID='{$service['OEM_ID']}',
  ACNO='{$service['ACNO']}',
  PURP='{$service['PURP']}',
  APNAME='{$service['APNAME']}',
  DIGADD='{$service['DIGADD']}',
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  APPMODE='{$service['APPMODE']}',
  bind_account='{$service['bind_account']}',
  is_public={$service['is_public']},
  is_installer={$service['is_installer']},
  user_name='{$service['user_name']}',
  user_pwd='".APP_USER_PWD."',
  start_date='{$service['start_date']}',
  end_date='{$service['end_date']}',
  note='{$service['note']}'";

  sql($sql,$result,$num,0);
  return $result;
}

function insertDBfield($service){
  $sql = "INSERT INTO customerservice.workeyegis SET ";
  foreach ($service as $key=>$value){
    if ($key == "is_public") $sql .="{$key}={$value}, ";
    else if ($key == "is_installer") $sql .="{$key}={$value}, ";
    else $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
//  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}

function updateDBfield($id,$service){
    
  $sql = "UPDATE customerservice.workeyegis SET ";
  foreach ($service as $key=>$value){
    if (($key == "is_public") or ($key == "is_installer")) $sql .="{$key}={$value}, ";
    else if ($key == "id") continue;
    else $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
  $sql .= " WHERE id={$id}";
//  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}
function updateCloudService($service){
  if ($service['id'] == "") return false;
  $sql = "UPDATE customerservice.workeyegis SET 
  OEM_ID='{$service['OEM_ID']}',
  PURP='{$service['PURP']}',
  APNAME='{$service['APNAME']}',
  APPMODE='{$service['APPMODE']}',
  DIGADD='{$service['DIGADD']}',
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  bind_account='{$service['bind_account']}',
  note='{$service['note']}'
   WHERE id={$service['id']}";
//  is_public={$service['is_public']}',
//  start_date='{$service['start_date']}',
//  end_date='{$service['end_date']}',
//  ACNO='{$service['ACNO']}', //should not change
//  user_name='{$service['user_name']}',
//  user_pwd='".APP_USER_PWD."', //no need to update
  sql($sql,$result,$num,0);
  return $result; 
}
function removeByCloudID($cloudid){
  $sql = "DELETE FROM customerservice.workeyegis WHERE id={$cloudid}";
  sql($sql,$result,$num,0);
  return $result;    
}
//return default/target centered map
function getMAP_OEM_ID($oemid) //GIS_MAP index
{//if N99 type, use original oemid, 
  global $oem;
  if ( ($oemid == "T06") or ($oemid == "X02") or ($oemid == "DEMO") )
    return "T06";
  else return $oemid;

}

function createServiceTable($sqlParam="")
{
  global $oem;
  global $GISAPPMODE_COLOR;
  global $GIS_MAP;
  $limitParam="LIMIT ".PAGE_LIMIT;
  if ($oem== "X02")
    if (!isset($_REQUEST['DEMO']))
      if (($sqlParam == "")) $sqlParam = " WHERE OEM_ID IN ('T06','X02') ";
      else  $sqlParam .= " AND OEM_ID IN ('T06','X02') ";
  $sql = "select * from customerservice.workeyegis {$sqlParam} order by id desc {$limitParam}";
  sql($sql,$result,$num,0);
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
  }
  $html="";
  //$html ="<tr><td>(Total:{$num})</td><td></td></tr>";
  foreach($services as $service){ //two row, one for brief data, one for edit table
    $cloudid = $service['id'];
    $cloudid_js = '\''.$cloudid.'\'';

    $html .="<tr>";
    //$html .="<td class=list>({$cloudid})&nbsp;&nbsp;\t";
    $html .="<td class=list>";
    if ( $service['is_installer']!=DEFAULT_IS_INSTALLER){ //expire check vendor entry only
    $exp = strtotime($service['end_date']);
    if (time() > $exp )
          $html .="<div class=\"tooltip\"><bold><font color=gray>{$service['ACNO']}</font></bold><span class=\"tooltiptext\">租期{$service['end_date']}已過期</span></div><br>";
    else $html .="{$service['ACNO']}<br>";
    }else $html .="{$service['ACNO']}<br>"; 
    
    $html .="{$service['PURP']} / {$service['APNAME']}";

    if (isset($_REQUEST['debugadmin']))
      $html.="<input type=\"button\" value=\"刪除\" onclick=\"removeService({$cloudid_js})\" style='background-color:LightPink;'>\n";
    if ( $service['is_installer']=="1")
    $html.="&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript: switchPopup('tr-gis{$cloudid}');switchLink('edit-gis-link{$cloudid}');\" id=\"edit-gis-link{$cloudid}\" >更多資訊</a>\n";
  $html .= "</td>";

    if ( $service['is_installer']=="1")
    {//is installer info
      if ( $service['is_public']=="1"){
        $html .="<td class=list><a href='".$GIS_MAP[getMAP_OEM_ID($service['OEM_ID'])]."&mylat={$service['LAT']}&mylng={$service['LNG']}&conntype=direct&oemid=".$service['OEM_ID']."' class='maplink' target=_blank>已".TXT_GIS_ENABLE."</a><p>";
        $html.="<input type=\"button\" value=\"".TXT_GIS_DISABLE."\" onclick=\"setServiceMAP('disable',{$cloudid_js})\" class='buttonDisable'><input type=\"button\" value=\"".TXT_GIS_ENABLE."\" class='buttonEnable' disabled>\n";
      }else{
        $html .="<td class=list>已".TXT_GIS_DISABLE."<p>";
        //<button type=button disabled>收工</button>
        $html.="<input type=\"button\" value=\"".TXT_GIS_DISABLE."\" class='buttonDisable' disabled><input type=\"button\" value=\"".TXT_GIS_ENABLE."\" onclick=\"setServiceMAP('enable',{$cloudid_js})\" class='buttonEnable'>\n";
      }
      $html.="</td></tr>";
    }else{//not installer
      if ( $service['is_public']=="1"){
        $html .="<td class=list>預設公開<p>";
        $html.="<input type=\"button\" value=\"關閉\" onclick=\"setServiceMAP('disable',{$cloudid_js})\"  class='buttonDisable'><input type=\"button\" value=\"公開\" class='buttonEnable' disabled>\n";
        $html .="</td>";
      }else{
        $html .="<td class=list>預設非公開<p>";
        $html.="<input type=\"button\" value=\"關閉\" class='buttonDisable' disabled><input type=\"button\" value=\"公開\" onclick=\"setServiceMAP('enable',{$cloudid_js})\"  class='buttonEnable'>\n";
        $html .="</td>";
      }
      $html .="</tr>";
    }

      $html.="<tr id=\"tr-gis{$cloudid}\" style=\"width=100%;display:none\"><td colspan=2>";


    $html.="<table class='mytable'>";
    $html .="\n<tr><td class=table_header>".TXT_ACNO."</td><td >";
    if (isset($_REQUEST['DEMO']))
        $html .="<input type='text' id='{$cloudid}-ACNO' value='{$service['ACNO']}' >\n";
    $html .="<input type='text' id='{$cloudid}-ACNO' value='{$service['ACNO']}' readonly class='inputReadOnly'>";
    $html .="</td></tr><tr>";
    if ( $service['is_public']=="1")  $html .="<td class=table_header>".TXT_PURP."</td><td ><input type='text' id='{$cloudid}-PURP' value='{$service['PURP']}' readonly class='inputReadOnly'></td></tr>";
    else $html .="<td class=table_header>".TXT_PURP."</td><td ><input type='text' id='{$cloudid}-PURP' value='{$service['PURP']}' ></td></tr>";
    $html .="\n<tr><td class=table_header>".TXT_TCNAME."</td><td ><input type='text' id='{$cloudid}-TCNAME' value='{$service['TCNAME']}' readonly class='inputReadOnly'></td></tr><tr>";
    $html .="<td class=table_header>";
    if (defined('TXT_TC_TEL2')) $html .= TXT_TC_TEL2; else  $html .= TXT_TC_TEL;
    if ( $service['is_public']=="1"){
      $html .="</td><td ><input type='text' id='{$cloudid}-TC_TEL' value='{$service['TC_TEL']}' readonly class='inputReadOnly'></td></tr>";
      $html .="\n<tr><td class=table_header>".TXT_APNAME."</td><td ><input type='text' id='{$cloudid}-APNAME' value='{$service['APNAME']}'  readonly class='inputReadOnly'></td></tr><tr>";
      $html .="\n<td class=table_header>".TXT_DIGADD."</td><td ><input type='text' id='{$cloudid}-DIGADD' value='{$service['DIGADD']}'  readonly class='inputReadOnly'><br>\n<input type='text' id='{$cloudid}-LAT' value='{$service['LAT']}' size='5px' readonly class='inputReadOnly'><input type='text' id='{$cloudid}-LNG' value='{$service['LNG']}' size='5px' readonly class='inputReadOnly'><input type=button value='找地理坐標' onclick=\"window.open('getGIS.php?tag={$cloudid}&addr='+getAddr('{$cloudid}-DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');\" disabled class='btnReadOnly'></td></tr>";
    }else{  
    $html .="</td><td ><input type='text' id='{$cloudid}-TC_TEL' value='{$service['TC_TEL']}'></td></tr>";
    $html .="\n<tr><td class=table_header>".TXT_APNAME."</td><td ><input type='text' id='{$cloudid}-APNAME' value='{$service['APNAME']}' ></td></tr><tr>";
    $html .="\n<td class=table_header>".TXT_DIGADD."</td><td ><input type='text' id='{$cloudid}-DIGADD' value='{$service['DIGADD']}'><br>\n<input type='text' id='{$cloudid}-LAT' value='{$service['LAT']}' size='5px' readonly class='inputReadOnly'><input type='text' id='{$cloudid}-LNG' value='{$service['LNG']}' size='5px' readonly class='inputReadOnly'><input type=button value='找地理坐標' onclick=\"window.open('getGIS.php?tag={$cloudid}&addr='+getAddr('{$cloudid}-DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');\"></td></tr>";
    }
    if (defined('TXT_APPMODE')){
      $tmpcolor=$GISAPPMODE_COLOR[$oem.$service['APPMODE']];
      //$html.= "document.write('{[$service['APPMODE']}');";
      $html.="<tr><td class=table_header>".TXT_APPMODE."</td><td><select ";
      if ($tmpcolor !="") $html.="style='background-color: {$tmpcolor}'";
      if ( $service['is_public']=="1")  $html.=" name='{$cloudid}-APPMODE' id='{$cloudid}-APPMODE' onchange=\"javascript:setBG('{$cloudid}-APPMODE');\" disabled>".printAPPMODEList($service['APPMODE'])."</select></td></tr>";
      else $html.=" name='{$cloudid}-APPMODE' id='{$cloudid}-APPMODE' onchange=\"javascript:setBG('{$cloudid}-APPMODE');\">".printAPPMODEList($service['APPMODE'])."</select></td></tr>";
    }else $html.="<input name='{$cloudid}-APPMODE' id='{$cloudid}-APPMODE' type=\"hidden\" value=\"".DEFAULT_APPMODE."\">";      
    $html .="<input type=hidden  id='{$cloudid}-is_public' value='{$service['is_public']}'>";
    $html .="<input type=hidden  id='{$cloudid}-OEM_ID' value='{$service['OEM_ID']}'>";
    $html .="<input type=hidden  id='{$cloudid}-bind_account' value='{$service['bind_account']}'>";
      //{$cloudid}-note
    $html .="\n<tr><td class=table_header>".TXT_CAMERA."</td><td>";

    $macNum = count(explode(';' , $service['note']));
    $macArr = explode(';',$service['note']);
    for ($i=0;$i<$macNum;$i++){
      if ($macArr[$i]== "") continue;
      if ( $service['is_public']=="1")
        if ( ($tmpName=getCamNameByMac($macArr[$i]))!="")  $html .= "{$macArr[$i]} ({$tmpName})<br>";
        else $html .="{$macArr[$i]}<br>";
      else
        if ( ($tmpName=getCamNameByMac($macArr[$i]))!="") $html .="<input type=button value='".TXT_DELETE."{$macArr[$i]} ({$tmpName})' onclick=\"shareService('unshare_camera','{$macArr[$i]}',{$cloudid_js})\"><br>"; 
        else $html .="<input type=button value='".TXT_DELETE."{$macArr[$i]}' onclick=\"shareService('unshare_camera','{$macArr[$i]}',{$cloudid_js})\"><br>";
    }
    if ( $service['is_public']!="1"){
      $tmp = getCamList("{$cloudid}-note",$service['bind_account'],$service['note']);
      if ($tmp!=""){
        $html .= $tmp;
        $html .="<input type=button value='".TXT_NEW.TXT_CAMERA."' onclick=\"shareService('share_camera',$('#{$cloudid}'+'-note').val(),{$cloudid_js})\">";
      }
    }
    $html .="</td></tr>\n<tr>";
    if ( $service['is_public']=="1")  $html .= "<td colspan=2><input type=button class='buttonAdd' value='".TXT_UPDATE."' disabled style='color: grey!important'></td></tr>\n";
    else $html .= "<td colspan=2><input type=button class='buttonAdd' value='".TXT_UPDATE."' onclick=\"updateService({$cloudid_js})\"></td></tr>\n";

    $html.="</table>";
    $html.="</td></tr>";
  }
  echo $html;
}

//new function for installer
function getVendorDefault($name,&$myArr){
  if (strpos($name,"%")!==false)
    $sql = "select * from customerservice.gis_vendor_info where TCNAME like'{$name}'";
  else  $sql = "select * from customerservice.gis_vendor_info where TCNAME='{$name}'";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  $myArr['TCNAME']=$arr['TCNAME'];
  $myArr['TC_TEL']=$arr['TC_TEL'];
  return $result;
}
function getDefault($bind_account,&$myArr){
  $sql = "select * from customerservice.workeyegis where bind_account='{$bind_account}' and is_installer=0";
  sql($sql,$result,$num,0);
  fetch($myArr,$result,0,0);
  //add mac
  return $result;
}

function getTCACNO($prefix)
{
  $max = 10000;
  $base = 10;
  $log = log($max, $base);
  $errCount = 1;
//get random number
  $sn= sprintf("-%0{$log}d", rand(1, $max));
  while (getUserID($prefix.$sn)>0){
    $sn= sprintf("-%0{$log}d", rand(1, $max));
    if (getUserID($prefix.$sn)<0) break;
    $errCount++;
    if ($errCount > $base){//if error more than 10 times, get one more digit
      $max = $max*$base;
      $log = log($max, $base);
    }
  }
  /*
  for ($i=1;$i<10000;$i++){ //4 digits
  $sn = sprintf("-%04d", $i);
  if (getUserID($prefix.$sn)<0)
    return $prefix.$sn;
  }
  */
  return $prefix.$sn;
}
function printAPPMODEList($key=""){
  global $oem,$GISAPPMODE;
  $html="";

  foreach ($GISAPPMODE[$oem] as $index=>$data){
    if ($key == $data)  $html .= "<option value='{$data}' selected>{$data}</option>";
    else  $html .= "<option value='{$data}'>{$data}</option>";
  }
  return $html;
}
function getCamList($tag,$bind_account,$existMACList=""){
    $sql = "select name, mac_addr from isat.query_info where user_name = '{$bind_account}' group by mac_addr";
    sql($sql,$result,$num,0);
    if ( $num == count(explode(";",$existMACList))) return "";
    $html ="<select name=\"{$tag}\" id=\"{$tag}\"><option></option>\n";
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($existMACList!="") //exist list FOUND skip
        if (strpos($existMACList,$arr['mac_addr'])!==FALSE) continue;
      if ($arr['mac_addr'] != $arr['name'])
        $html .="<option value=\"{$arr['mac_addr']}\">{$arr['mac_addr']}({$arr['name']})</option>\n";
      else  $html .="<option value=\"{$arr['mac_addr']}\">{$arr['mac_addr']}</option>\n";
    }
  return $html;
}

function updateMACList($id,$command,$MAC){
  //if key is mac
  $maclist = "";
  $sqlmac = "SELECT note from customerservice.workeyegis WHERE id ={$id}"; 
  sql($sqlmac,$resultmac,$nummac,0);
  if ($resultmac){
    fetch($arrmac,$resultmac,0,0);
    $maclist = $arrmac['note'];
  }else return $resultmac;
  //$macNum = count(explode(';' , $maclist)); 
  if ($command == "share_camera"){
      $maclist .= ";{$MAC}"; //add after
  }else if ($command == "unshare_camera"){
  //extract $MAC from $maclist
      $maclist = str_replace("{$MAC}","",$maclist);
      $maclist = str_replace(";;",";",$maclist); //remove middle cam
  }
  $maclist = ltrim($maclist,";"); //only one camera case  
  $maclist = rtrim($maclist,";"); //only one camera case

  $sql = "UPDATE customerservice.workeyegis SET note = '{$maclist}' WHERE id={$id}";
//  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}

?>
<html>
<head>
<title>開工了</title>
<!-- To support ios sizes -->
<link rel="apple-touch-icon" href="image/install180.png">
<link rel="apple-touch-icon" sizes="57x57" href="image/install57.png">
<link rel="apple-touch-icon" sizes="72x72" href="image/install72.png">
<link rel="apple-touch-icon" sizes="114x114" href="image/install114.png">
<link rel="apple-touch-icon" sizes="144x144" href="image/install144.png">
<link rel="apple-touch-icon" sizes="60×60" href="image/install60.png">
<link rel="apple-touch-icon" sizes="76×76" href="image/install76.png">
<link rel="apple-touch-icon" sizes="120×120" href="image/install120.png">
<link rel="apple-touch-icon" sizes="152×152" href="image/install152.png">
<link rel="apple-touch-icon" sizes="180×180" href="image/install180.png">
<!-- To support android sizes -->
<link rel="icon" sizes="192×192" href="image/install192.png">
<link rel="icon" sizes="128×128" href="image/install128.png">
<?php
if (isMobile()){ //detect user agent
  printMobileMeta();
echo "<style type=\"text/css\">\n";
echo "html *\n{font-size: 1em !important;\n}\n";
echo "h1{\nfont-size: 2em !important;\n}\n";
echo "h2{\nfont-size: 1.5em !important;\n}\n";
echo ".tooltip .tooltiptext{\nfont-size: 0.8em !important;\n}\n";
//echo "html {font-size: 100%;}\n";
//echo "body\n{font-size: 1em !important;\n}\n";
//echo "tr.mylist{\nfont: 1.2em !important;\n}\n";
//echo "td.list{\nfont: 1em !important;\n}\n";
echo "</style>\n";
}
?>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
<style type="text/css">
.table_header{
width:25%; background-color:#0069C9; 
font:bold 14px arial;
color:#FFF; text-align:center; border:1px solid #fff
}
.table_headerImage{
height:55px; background-color:#0069C9; 
font:bold 14px  arial; 
color:#FFF; text-align:center; border:1px solid #fff
}
table.mytable {
   border: 1px solid #CCC;
}
td.list {
font: bold 14px arial; 
color:black !important;
}

tr.mylist{
 background-color:#0069C9;
 font:bold 14px arial;
 color:#FFF;
 text-align:center;
 border:1px;
}
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}
/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 150px;
    background-color: black;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;
    position: absolute;
    z-index: 1;
    /* Position the tooltip textsee examples below! */
    top: -25px;
    left: 10%; 
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
    /*display: inline-block;*/
}
#loginform
{
 width:300px;
 height:200px;
 margin-top:30px;
 background-color:#585858;
 border-radius:3px;
 box-shadow:0px 0px 10px 0px #424242;
 padding:10px;
 box-sizing:border-box;
 visibility:hidden;
 display:none;
 font-size:20px;
}

#loginform p
{
 margin-top:15px;
 font-size:22px;
 color:#E6E6E6;
}

#loginform #dologin{
 margin-left:5px;
 margin-top:10px;
 width:80px;
 height:40px;
 border:none;
 border-radius:3px;
 color:#E6E6E6;
 background-color:grey;
 font-size:20px;
}
.buttonAdd{
  background-color: #0066FF; /*Aquamarine*/
  color:white;
  width: 100px;
  text-align: center;
  vertical-align: middle;
  float: right;
  font-weight:bold;
}
.buttonEnable{
  background-color: LightPink;
  float: right;
  font-weight:bold;
}
.buttonDisable{
  background-color: #99FF99;
  float: right;
  font-weight:bold;
}
.inputReadOnly{
  background-color : grey;
}
.btnReadOnly{
  background-color : black;
}
a, a:hover #maplink{
text-decoration: none;
}
</style>
<script type="text/javascript">
$(document).ready(function()
{
<?php
  if ($installuser=="") 
    echo "showpopup();\n";
?>
 $("#show_login").click(function(){
  showpopup();
 });
 $("#close_login").click(function(){
  hidepopup();
 });
});

function showpopup()
{
 $("#loginform").fadeIn();
 $("#loginform").css({"visibility":"visible","display":"block"});
}
function hidepopup()
{
 $("#loginform").fadeOut();
 $("#loginform").css({"visibility":"hidden","display":"none"});
}
</script>
</head>
<body>
<div align=center><h2><?php 
if (isset($_REQUEST['TCNAME'])) echo "{$_REQUEST['TCNAME']}".TXT_TCNAME."管理"; 
else echo "{$installuser}<br>".TXT_ACNO."管理";?>
</h2>
<div align=right><font size=1> 
<?php 
if ($installuser!="") {
  if ($oem!="C13"){
  echo "<a href=\"/plugin/rpic/appdownload.php\" target=_blank>應用下載</a>&nbsp;&nbsp;&nbsp;";
  echo "<a href=\"maintainGIS.php\" target=_blank>變更資訊</a>&nbsp;&nbsp;&nbsp;";
  echo "<a href=\"javascript:var wobj=window.open('/html/member/logout.php?step=logout','print_popup','width=300,height=300');setTimeout(function(){location=location;},500);setTimeout(function() { wobj.close(); }, 500);\">登出</a>\n";
  }
} 
?>
</font></div>
</div>
<div style="display: none" id="customMessage"></div>
<div style="display: none" id="myMessage">
<?php
if (isset($msgerr))
echo $msgerr;
?>
</div>
<center>
 <div id = "loginform">
  <form method = "post" action = "/html/member/login.php" target="print_popup" onsubmit="var wobj=window.open('about:blank','print_popup','width=300,height=300');setTimeout(function(){window.location.reload();},500);setTimeout(function() { wobj.close(); }, 500);">
  <p><?php if ($_SESSION["login_err"] > 0) echo "<font color=red>登入驗證失敗 {$_SESSION["login_err"]}</font>"; else echo "開工請登入";?></p>
   <p><input type = "text" id = "email" name = "email"  placeholder = "帳號"></p>
   <input type = "password" id = "password" name = "password" placeholder = "密碼" AUTOCOMPLETE="OFF"><br>
   <input type = "hidden" name="step" value = "login">
   <input type = "submit" id = "dologin" value = "登入">
  </form>
 </div>
</center>
<?php
if ($installuser=="") exit();
?>
      <table style="border-collapse">
          <tr class="mylist">
            <th><?php echo TXT_ACNO."/".TXT_PURP."/".TXT_APNAME;?></th>
            <th><?php echo TXT_GIS_STATUS;?></th>
          </tr>
<tr><td colspan=2 align=right style="text-align: right;vertical-align: top;">
<a href="javascript: switchPopup('tr-add-gis');switchLink('add-gis-link');" id="add-gis-link" ><?php if (!isset($_REQUEST['TCNAME'])) echo TXT_NEW.TXT_ACNO;?></a>
</td></tr>
<tr id="tr-add-gis" style="width=100%;display:none"><td colspan=2 align=right>
<table class="mytable">

<tr><td class=table_header><?php echo TXT_ACNO;?></td><td><input type='text' name='ACNO' id='ACNO' placeholder='<?php echo TXT_ACNO;?>' ></td></tr><tr>
 <td class=table_header><?php echo TXT_PURP;?></td><td><input type='text' name='PURP' id='PURP' placeholder='<?php echo TXT_PURP;?>'  ></td></tr>
<tr><td class=table_header><?php echo TXT_TCNAME;?></td><td><input type='text' name='TCNAME' id='TCNAME' placeholder='<?php echo TXT_TCNAME;?>'></td></tr><tr>
 <td class=table_header><?php if (defined('TXT_TC_TEL2')) echo TXT_TC_TEL2; else echo TXT_TC_TEL;?></td><td><input type='text' name='TC_TEL' id='TC_TEL' placeholder='<?php if (defined('TXT_TC_TEL2')) echo TXT_TC_TEL2; else echo TXT_TC_TEL;?>' ></td></tr>
<tr><td class=table_header><?php echo TXT_APNAME;?></td><td><input type='text' name='APNAME' id='APNAME' placeholder='<?php echo TXT_APNAME;?>'  ></td></tr><tr>
 <td class=table_header><?php echo TXT_DIGADD;?></td><td><input type='text' name='DIGADD' id='DIGADD' placeholder='<?php echo TXT_DIGADD;?>'>
<br><input type='text' name='LAT' id='LAT' placeholder='LAT' size='5px' readonly class='inputReadOnly'>
<input type='text' name='LNG' id='LNG' placeholder='LNG' size='5px' readonly class='inputReadOnly'><br>
<input type=button name='GISbtn' id='GISbtn' value="找地理坐標" onclick="window.open('getGIS.php?addr='+getAddr('DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');">
</td></tr>
<?php if (defined('TXT_APPMODE')){  ?>
<tr><td class=table_header><?php echo TXT_APPMODE;?></td><td><select name='APPMODE' id='APPMODE' onchange="javascript:setBG('APPMODE');"><?php echo printAPPMODEList();?></select></td></tr>
<?php }else{ echo "<input name='APPMODE' id='APPMODE' type=\"hidden\" value=\"".DEFAULT_APPMODE."\">";} ?>
<!--default add site only----->
<input type=hidden  name='is_installer' id='is_installer' value='<?php echo DEFAULT_IS_INSTALLER;?>'>
<input type=hidden  name='is_public' id='is_public' value='1'>
<input type=hidden  name='OEM_ID' id='OEM_ID'>
<input type=hidden  name='start_date' id='start_date'>
<input type=hidden  name='end_date' id='end_date'>
<input type=hidden  name='bind_account' id='bind_account' value='<?php echo $installuser;?>'>
<input type=hidden  name='installuser' id='installuser' value='<?php echo $installuser;?>'>
 <tr><td class=table_header><?php echo TXT_CAMERA;?></td><td align="center"><?php echo getCamList("note",$installuser);?></td></tr><tr>
 <td colspan=2><input type=button class="buttonAdd" value="<?php echo TXT_NEW;?>" onclick='addService();'></td></tr>

</table>
</td></tr>

<?php
    if (isset($_REQUEST['TCNAME']))
      if (strpos($_REQUEST['TCNAME'],"%")!==false)  createServiceTable("WHERE TCNAME like '".substr($_REQUEST['TCNAME'],0,HEAD_CHAR_MATCH)."%'");
      else  createServiceTable("WHERE TCNAME='{$_REQUEST['TCNAME']}'");
    else  createServiceTable("WHERE bind_account='{$installuser}'");
?>

      </table>


  <form id="add-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="add-service"> 
    <input type="hidden" name="add-OEM_ID" id="add-OEM_ID">
    <input type="hidden" name="add-ACNO" id="add-ACNO">
    <input type="hidden" name="add-PURP" id="add-PURP">
    <input type="hidden" name="add-APNAME" id="add-APNAME">
    <input type="hidden" name="add-DIGADD" id="add-DIGADD">
    <input type="hidden" name="add-TCNAME" id="add-TCNAME">
    <input type="hidden" name="add-TC_TEL" id="add-TC_TEL">
    <input type="hidden" name="add-LAT" id="add-LAT">
    <input type="hidden" name="add-LNG" id="add-LNG">
    <input type="hidden" name="add-bind_account" id="add-bind_account">
    <input type="hidden" name="add-is_public" id="add-is_public">
    <input type="hidden" name="add-note" id="add-note">
    <input type="hidden" name="add-start_date" id="add-start_date">
    <input type="hidden" name="add-end_date" id="add-end_date">
    <input type="hidden" name="add-is_installer" id="add-is_installer">
    <?php
      if ($installuser!="")
        echo "<input type=\"hidden\" name=\"installuser\" id=\"installuser\" value=\"{$installuser}\">\n";
    ?>
  </form>

  <form id="delete-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="delete-service">
    <input type="hidden" name="delete-cloudid" id="delete-cloudid">
    <input type="hidden" name="delete-ACNO" id="delete-ACNO">
    <?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>";
      if ($installuser!="")
        echo "<input type=\"hidden\" name=\"installuser\" id=\"installuser\" value=\"{$installuser}\">\n";
    ?>  
  </form>
  <form id="update-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="update-service">
    <input type="hidden" name="update-cloudid" id="update-cloudid">
    <input type="hidden" name="update-OEM_ID" id="update-OEM_ID">
    <input type="hidden" name="update-ACNO" id="update-ACNO">
    <input type="hidden" name="update-PURP" id="update-PURP">
    <input type="hidden" name="update-APNAME" id="update-APNAME">
    <input type="hidden" name="update-APPMODE" id="update-APPMODE">
    <input type="hidden" name="update-DIGADD" id="update-DIGADD">
    <input type="hidden" name="update-TCNAME" id="update-TCNAME">
    <input type="hidden" name="update-TC_TEL" id="update-TC_TEL">
    <input type="hidden" name="update-LAT" id="update-LAT">
    <input type="hidden" name="update-LNG" id="update-LNG">
    <input type="hidden" name="update-bind_account" id="update-bind_account">
    <input type="hidden" name="update-note" id="update-note">
    <?php
      if (isset($_REQUEST['TCNAME']))
        echo "<input type=\"hidden\" name=\"TCNAME\" id=\"TCNAME\" value=\"{$_REQUEST['TCNAME']}\">\n";
      else if ($installuser!="")
        echo "<input type=\"hidden\" name=\"installuser\" id=\"installuser\" value=\"{$installuser}\">\n";
    ?>
  </form>
  <form id="public-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="public-service">
    <input type="hidden" name="public-cloudid" id="public-cloudid">
    <input type="hidden" name="public-type" id="public-type">
    <input type="hidden" name="public-ACNO" id="public-ACNO">
<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>";
      if (isset($_REQUEST['TCNAME']))
        echo "<input type=\"hidden\" name=\"TCNAME\" id=\"TCNAME\" value=\"{$_REQUEST['TCNAME']}\">\n";
      else if ($installuser!="")
        echo "<input type=\"hidden\" name=\"installuser\" id=\"installuser\" value=\"{$installuser}\">\n";
    ?>
  </form>
  <form id="share-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="share-service">
    <input type="hidden" name="share-cloudid" id="share-cloudid">
    <input type="hidden" name="share-command" id="share-command">
    <input type="hidden" name="share-MAC" id="share-MAC">
<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>";
      if (isset($_REQUEST['TCNAME']))
        echo "<input type=\"hidden\" name=\"TCNAME\" id=\"TCNAME\" value=\"{$_REQUEST['TCNAME']}\">\n";
      else if ($installuser!="")
        echo "<input type=\"hidden\" name=\"installuser\" id=\"installuser\" value=\"{$installuser}\">\n";
    ?>
  </form>
<script type="text/javascript">
<?php 
if ($searchCloudID!=""){
  if (is_numeric($searchCloudID)) //is_int cannot tell integer string 
    echo "switchPopup('tr-gis{$searchCloudID}');\nswitchLink('edit-gis-link{$searchCloudID}');";
}
if (isset($msgerr))
  echo "$('#myMessage').show();";
?>
setGISDefault();

var gisDBfieldName = [
"ACNO",
"PURP",
"TCNAME",
"TC_TEL",
"APNAME",
"DIGADD",
//"LAT", //=>GISbtn
//"LNG",
"OEM_ID",
"bind_account"
//"user_name",
//"user_pwd",
//"APPMODE",
//"start_date",
//"end_date"
];

function setGISDefault()
{
//var o = new Option('{$tcservice['MAC'][$i]}', '');
//$(o).html('{$tcservice['MAC'][$i]}');
//$("#note").append(o);
  <?php //for insert entry fail
  if ($installuser!=""){
    if (!is_null($tcservice)){//get data from original
      //echo "switchPopup('tr-add-gis');switchLink('add-gis-link');\n";
      if ($oem=="C13")
       echo "$('#ACNO').val('');\n";//self input
      else  echo "$('#ACNO').val('".getTCACNO($tcservice['ACNO'])."');\n"; 
      echo "$('#PURP').val('".$tcservice['PURP']."');\n";
      echo "$('#TCNAME').val('".$tcservice['TCNAME']."');\n";
      echo "$('#TC_TEL').val('".$tcservice['TC_TEL']."');\n";
      echo "$('#APNAME').val('".$tcservice['APNAME']."');\n";
      echo "$('#DIGADD').val('".$tcservice['DIGADD']."');\n";
      echo "$('#LAT').val('".$tcservice['LAT']."');\n";
      echo "$('#LNG').val('".$tcservice['LNG']."');\n";
      echo "$('#OEM_ID').val('".$tcservice['OEM_ID']."');\n";
      echo "$('#start_date').val('".$tcservice['start_date']."');\n";
      echo "$('#end_date').val('".$tcservice['end_date']."');\n";
      //echo "$('#note').val('".$tcservice['note']."');\n"; //note-> mac
    }else if ($bInputData){//user submit fail
      echo "switchPopup('tr-add-gis');switchLink('add-gis-link');\n";
      echo "$('#PURP').val('".$service['PURP']."');\n";
      echo "$('#TCNAME').val('".$service['TCNAME']."');\n";
      echo "$('#TC_TEL').val('".$service['TC_TEL']."');\n";
      echo "$('#APNAME').val('".$service['APNAME']."');\n";
      echo "$('#DIGADD').val('".$service['DIGADD']."');\n";
      echo "$('#LAT').val('".$service['LAT']."');\n";
      echo "$('#LNG').val('".$service['LNG']."');\n";
      echo "$('#OEM_ID').val('".$tcservice['OEM_ID']."');\n";
      echo "$('#note').val('".$service['note']."');\n";
      echo "$('#start_date').val('".$tcservice['start_date']."');\n";
      echo "$('#end_date').val('".$tcservice['end_date']."');\n";

    }
    if ($oem!="C13")  echo "setReadOnly('ACNO');";
    echo "setReadOnly('TCNAME');";
    if ($oem!="C13")  echo "setReadOnly('TC_TEL');";
  }
  ?>
}

function setBG(tag){
var APPMODE_Array=[
<?php
  $tmp = "\n";
  if (!is_null($GISAPPMODE_COLOR)){
    foreach($GISAPPMODE_COLOR as $mode=>$color){
      $tmp.= "\n[\"{$mode}\",\"{$color}\"], ";
    }
    $tmp = rtrim($tmp, ", ");
  }
  $tmp.= "\n";
  echo $tmp;
?>
];
  if (tag.indexOf("APPMODE")>=0){
      var txtColor="";
      for (var i = 0; i < APPMODE_Array.length; i++) {
        tmpString = APPMODE_Array[i][0].replace("<?php echo $oem;?>","");
        if ($('#'+tag).val()==tmpString) txtColor =APPMODE_Array[i][1];
      }
      if (txtColor!="") $("#"+tag).css({ 'background-color': txtColor  });
  }
}

function setReadOnly(tag){
    if (tag=="GISbtn"){
      $("#"+tag).prop('disabled', true);
      $("#"+tag).css({'background-color' : 'black'});
    }else if (tag=="APPMODE"){
      var txtColor="";
      $("#"+tag).prop('disabled', true);
      setBG(tag);
    }else{
      $("#"+tag).prop("readonly", true);
      $("#"+tag).css({'background-color' : 'grey'});
    }      
}

function defaultAccount(cloudid){
  var bind_account = $('#'+cloudid+'-bind_account').val();
  $('#default-bind_account').val(bind_account);
  $('#default-cloudid').val(cloudid);  
  $('#default-account-service').submit();
}
function updateAccount(cloudid,formtype){
  var ACNO = $('#'+cloudid+'-ACNO').val();
  var bind_account = $('#'+cloudid+'-bind_account').val();
  $('#validate-type').val(formtype);
  $('#valid-ACNO').val(ACNO);
  $('#valid-bind_account').val(bind_account);
  $('#valid-cloudid').val(cloudid);  
  $('#update-account-service').submit();
}

function setServiceMAP(type,cloudid){
  var ACNO = $('#'+cloudid+'-ACNO').val();
  //enable, disable
  $('#public-type').val(type);
  $('#public-ACNO').val(ACNO);
  $('#public-cloudid').val(cloudid);
  $('#public-service').submit();
}

function shareService(command,MAC, cloudid){
  //var ACNO = $('#'+cloudid+'-ACNO').val();
  //var newMAC = $('#'+cloudid+'-note').val();
  //if (MAC=="") $('#share-MAC').val(newMAC);
  //else 
  $('#share-MAC').val(MAC);
  $('#share-command').val(command);
  $('#share-cloudid').val(cloudid);
  $('#share-service').submit();

}

function updateService(cloudid){
  hideMessage();
  resetCheckcss(cloudid);
  var OEM_ID = $('#'+cloudid+'-OEM_ID').val();
  var ACNO = $('#'+cloudid+'-ACNO').val();
  var PURP = $('#'+cloudid+'-PURP').val();
  var APNAME = $('#'+cloudid+'-APNAME').val();
  var DIGADD = $('#'+cloudid+'-DIGADD').val();
  var TCNAME = $('#'+cloudid+'-TCNAME').val();
  var TC_TEL = $('#'+cloudid+'-TC_TEL').val();
  var APPMODE = $('#'+cloudid+'-APPMODE').val();
  var LAT = $('#'+cloudid+'-LAT').val();
  var LNG = $('#'+cloudid+'-LNG').val();
  var bind_account = $('#'+cloudid+'-bind_account').val();
  var note = $('#'+cloudid+'-note').val();  //new selected MAC
  if (isEmpty(PURP))  setNotice(cloudid+"-PURP");
  if (isEmpty(TCNAME))  setNotice(cloudid+"-TCNAME");
  if (isEmpty(TC_TEL))  setNotice(cloudid+"-TC_TEL");
  if (isEmpty(APNAME))  setNotice(cloudid+"-APNAME");
  if (isEmpty(DIGADD))  setNotice(cloudid+"-DIGADD");
  if (isEmpty(bind_account))  setNotice(cloudid+"-bind_account");
  
  $('#update-cloudid').val(cloudid);
  $('#update-OEM_ID').val(OEM_ID);
  $('#update-ACNO').val(ACNO); 
  $('#update-PURP').val(PURP);
  $('#update-APNAME').val(APNAME);
  $('#update-APPMODE').val(APPMODE);
  $('#update-DIGADD').val(DIGADD);
  $('#update-TCNAME').val(TCNAME);
  $('#update-TC_TEL').val(TC_TEL);
  $('#update-LAT').val(LAT);
  $('#update-LNG').val(LNG);
  $('#update-bind_account').val(bind_account);
  $('#update-note').val(note);  //new selected MAC  
  $('#update-service').submit();
}

function removeService(cloudid){
  if (window.confirm('確認要刪除 \''+cloudid+'\' ?') == true) {
    hideMessage();
    var ACNO = $('#'+cloudid+'-ACNO').val();
    $('#delete-ACNO').val(ACNO);
    $('#delete-cloudid').val(cloudid);
    $('#delete-service').submit();
  }
}

function getAddr(idname){
  var idvalue=$('#'+idname).val();
  //alert(idvalue); 
  return encode_utf8(idvalue);
}
function addService(){
  hideMessage();
  resetCheckcss("");
  var OEM_ID = $('#OEM_ID').val();
  var ACNO = $('#ACNO').val();
  var PURP = $('#PURP').val();
  var APNAME = $('#APNAME').val();
  var DIGADD = $('#DIGADD').val();
  var TCNAME = $('#TCNAME').val();
  var TC_TEL = $('#TC_TEL').val();
  var APPMODE = $('#APPMODE').val();
  var LAT = $('#LAT').val();
  var LNG = $('#LNG').val();
  var bind_account = $('#bind_account').val();
  var is_public = $('#is_public').val();
  var is_installer = $('#is_installer').val();
  var start_date = $('#start_date').val();  
  var end_date = $('#end_date').val();
  var note = $('#note').val();
  if ( (isEmpty(OEM_ID)) || (isEmpty(ACNO)) || (isEmpty(PURP)) 
    || (isEmpty(APNAME))|| (isEmpty(DIGADD)) || (isEmpty(TCNAME)) 
    || (isEmpty(TC_TEL)) || (isEmpty(LAT))
    || (isEmpty(LNG)) || (isEmpty(bind_account)) 
    || (isEmpty(note)) )
  {
    if (isEmpty(ACNO))  setNotice("ACNO");
    if (isEmpty(PURP))  setNotice("PURP");
    if (isEmpty(TCNAME))  setNotice("TCNAME");
    if (isEmpty(TC_TEL))  setNotice("TC_TEL");
    if (isEmpty(APNAME))  setNotice("APNAME");
    if (isEmpty(DIGADD))  setNotice("DIGADD");
    if (isEmpty(LAT)) setNotice("GISbtn");
    if (isEmpty(OEM_ID))  setNotice("OEM_ID");
    if (isEmpty(bind_account))  setNotice("bind_account");
    if (isEmpty(note))  setNotice("note");
    showCustomMessage('error','必要欄位不可空白.');
    return;
  }
    
  $('#add-OEM_ID').val(OEM_ID);
  $('#add-ACNO').val(ACNO); 
  $('#add-PURP').val(PURP);
  $('#add-APNAME').val(APNAME);
  $('#add-DIGADD').val(DIGADD);
  $('#add-TCNAME').val(TCNAME);
  $('#add-TC_TEL').val(TC_TEL);
  $('#add-LAT').val(LAT);
  $('#add-LNG').val(LNG);
  $('#add-bind_account').val(bind_account);
  $('#add-is_public').val(is_public);
  $('#add-is_installer').val(is_installer);
  $('#add-start_date').val(start_date);
  $('#add-end_date').val(end_date);
  $('#add-note').val(note);
  $('#add-APPMODE').val(APPMODE);
  $('#add-service').submit();
}

function setNotice(tag){
    $("#"+tag).focus();
    $("#"+tag).css({'background-color' : 'red'});
}

function isEmpty(inputStr) { 
  if ( null == inputStr || "" == inputStr ) {
    return true; 
  } return false; 
}

function showCustomMessage(className,msg){
  var obj = $('#customMessage');
  obj.attr("class", className);
  obj.html(msg);
  obj.show();
}

function resetCheckcss(cloudid){
  var prefix ="";
  //$('#inputId').attr('readonly', true); //<jquery1.9
  //$('#inputId').prop('readonly', true); //>jquery1.9
  if (cloudid !="") prefix = cloudid + "-";
  for (var i = 0; i < gisDBfieldName.length; i++) {
    if ( $("#"+prefix+gisDBfieldName[i]).is('[readonly]') ) continue;
    $("#"+prefix+gisDBfieldName[i] ).css({'background-color' : 'white'});
  }
  $("#"+prefix+"GISbtn" ).css({'background-color' : 'white'}); //LAT check
}
function hideMessage(){
  $('#myMessage').hide();
  $('#customMessage').hide();
}

function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
  //empty search value if click select
  //document.getElementById('cloudid').value="";
  selectobj.form.cloudid.value="";
}
function confirmDelete(type, name)  
{//jinho add confirmDelete
    return confirm('確認刪除'+type+'帳號'+name+'?');
}
function updateUserValue(value)
{    // this gets called from the popup window and updates
    //alert(value);
    //parse string tagname, value, value2 
    var arr = value.split(";");
    var t1="LAT";
    var t2="LNG"; 
    if (arr[0]!="")
    {
      t1= arr[0]+"-"+t1;
      t2= arr[0]+"-"+t2;
    }
    document.getElementById(t1).value = parseFloat(arr[1]).toFixed(8);
    document.getElementById(t2).value = parseFloat(arr[2]).toFixed(8);
}

function switchLink(tagname)
{
  var substring = "取消";
  var string = $('#'+tagname).text();
  //alert( string);
  if (string.indexOf(substring) !== -1){ //cancel found
    //alert(string.replace(substring, ""));
    $('#'+tagname).text(string.replace(substring, ""));
  }else{ //cancel not found
    //alert(substring + string);
    $('#'+tagname).text(substring + string);
  }
}
function switchPopup(id)
{//table-row, table-cell //block will break colspan
  var el = document.getElementById(id);
  //alert(id);
  if (el.style.display == "none"){
    if (id.indexOf("tr") !== -1)
      el.style.display = 'table-row';
    else if (id.indexOf("td") !== -1)
      el.style.display = 'table-cell';
    else    el.style.display = 'block';
  }else if (el.style.display == "block")
    el.style.display = 'none';
  else if (el.style.display == "table-row")
    el.style.display = 'none';
  else if (el.style.display == "table-cell")
    el.style.display = 'none';
}
function encode_utf8(s) {
  //return unescape(encodeURIComponent(s));
  return encodeURIComponent(s);
}

function decode_utf8(s) {
  //return decodeURIComponent(escape(s));
  return decodeURIComponent(s);
}

function setSelectImage(selid,imgid) {
    var oemid=$('#'+selid).val();
    //alert(oemid);
    if (oemid=="") $('#'+imgid).attr("src",""); 
    else $('#'+imgid).attr("src","image/"+oemid+".png");
    //var img = document.getElementById(imgid);
    //img.src = "image/"+oemid+".png";
}
function checkDate(date){
  //var pattern = new RegExp("^[0-9]{4}-[0-9]{2}-[0-9]{2}$");
  //if (pattern.test(date)){
  var d;
    if ((d = new Date(date))|0)
      return true; //return d.toISOString().slice(0,10) == date;
  //}
  return false;
}
</script>

</body>
</html>