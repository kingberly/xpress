<?php
/****************
 *Validated on Mar-8,2018,
 * reference from installGIS.php, camgisbind.php-> auto login
 * Required qlync mysql connection config.php
 * demo: https://engeye.chimei.com.tw:8080/plugin/rpic/updateGIS.php?MAC=001BFE0723F2 
 * Validate TCNAME by head string matched 
 *Writer: JinHo, Chang
*****************/
define("DEBUG_FLAG","OFF"); //OFF ON
define("HEAD_CHAR_MATCH",4);
include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
header("Content-Type:text/html; charset=utf-8");
if (DEBUG_FLAG == "ON") $oem = "C13";
require_once ("share.inc");
require_once ("rpic.inc");
define("EXPIRE_LIMIT",1800);//300s=5min, 30min
if ($oem == "C13")  define("DEFAULT_BIND_ACCOUNT","chimei00");
else if ($oem == "X02")  define("DEFAULT_BIND_ACCOUNT","ivedatest");
define("DEFAULT_IS_INSTALLER","1"); //always site, not vendor info

if ($_REQUEST['isclean']!="false"){
  unset($_REQUEST['ACNO']);
}
    
if (DEBUG_FLAG == "ON") var_dump($_REQUEST);
//if (DEBUG_FLAG == "ON") var_dump($GISAPPMODE[$oem]);

if (!is_null($RPICAPP_USER_PWD[$oem][1])){
  define("APP_USER_PWD",$RPICAPP_USER_PWD[$oem][1]);
  define("APP_URL",$RPICAPP_USER_PWD[$oem][0]);
}else die("OEM/Pwd Info Not Found!!");


$tcservice = null; //default value


if(isset($_REQUEST['MAC'])){
  session_start();
  setcookie('MAC', $_REQUEST['MAC'] , time()+EXPIRE_LIMIT);
}else if (isset($_COOKIE['MAC'])){
 $_REQUEST['MAC'] = $_COOKIE['MAC'];
}
if (!isset($_REQUEST['MAC'])) die("<div align=center><h2>請掃描攝影機上的QR碼進行開工!!</h2></div>\n");
/*
if(isset($_REQUEST['MAC'])){
  session_start();
  setcookie('MAC', $_REQUEST['MAC'] , time()+EXPIRE_LIMIT);
  if (isset($_COOKIE['ACNO'])){
    $installsite = strtoupper($_COOKIE['ACNO']);
    //$service['is_public'] = 1;
    $service['ACNO'] = $installsite; //existed?
    $service['id'] = getValueByType("ACNO",$service['ACNO'],"id");
    if ( $service['id'] < 0 ) //new submitted 
    //$myTitle = "{$installsite} 已登記攝影{$_REQUEST['MAC']}";
      $myTitle = "設定{$_REQUEST['MAC']}";
    else  $myTitle = "登記{$_REQUEST['MAC']}";
//    addShareDeviceAPI($_REQUEST['MAC'],$service['ACNO']);
//    updateDBfield($service['id'],$service);
  }
  //else $myTitle= "請輸入工地證號!!\n<br>";
}else if(isset($_REQUEST['ACNO'])){
  //bypass if share is done?????
  session_start();
  setcookie('ACNO', $_REQUEST['ACNO'] , time()+EXPIRE_LIMIT);
  $installsite = strtoupper($_REQUEST['ACNO']);
  if (isset($_COOKIE['MAC'])){
    //$service['is_public'] = 1;
    $service['ACNO'] = $installsite;
    $service['id'] = getValueByType("ACNO",$service['ACNO'],"id"); 
    addShareDeviceAPI($_COOKIE['MAC'],$service['ACNO']);
    updateDBfield($service['id'],$service);
    //$myTitle= "{$installsite} 已登記攝影{$_COOKIE['MAC']}";
    $myTitle= "已登記{$_COOKIE['MAC']}";
  }else   die("請掃描攝影機上的QR進行開工!!<br>\n");
}else{}
*/


if(isset($_REQUEST['form-type'])){
  $service = null;
  $type = htmlspecialchars($_REQUEST['form-type'], ENT_QUOTES);
  if ($type=='login'){
    if (($bind_account=getUserByMAC($_REQUEST['MAC']))=="") die("<div align=center><h2>不合法攝影機!!請洽系統管理員</h2></div>\n");
    else{
      if ($bind_account!=DEFAULT_BIND_ACCOUNT){
        if (DEBUG_FLAG == "ON")
          $msgerr .= "<div id=\"info\" class=\"info\">非攝影機預設綁定帳號.</div>";
        define("VALID_BIND_ACCOUNT",$bind_account);
      }
    }

    if (isset($_REQUEST['REG_ACNO'])) define("CUR_ACNO",$_REQUEST['REG_ACNO']);
    $myTitle= CUR_ACNO." 工地資訊";    
  }else if ($type=='public-service'){
    $id = trim(htmlspecialchars($_REQUEST['public-cloudid'], ENT_QUOTES));
    $ACNO = trim(htmlspecialchars($_REQUEST['public-ACNO'], ENT_QUOTES));
    $type = trim(htmlspecialchars($_REQUEST['public-type'], ENT_QUOTES));
    define("CUR_ACNO",$ACNO); 
    if ($type == "enable"){
      $service['is_public'] = 1;
      $myTitle="{$ACNO}開工";
    }else if ($type == "disable"){
      $service['is_public'] = 0;
      $myTitle="{$ACNO}收工";
    }
    updateDBfield($id,$service);
   $msgerr .= "<div id=\"info\" class=\"success\">{$myTitle}成功.</div>";
   $service['ACNO'] = $ACNO; //update here,updateDBfield only update is_public
   $service['action'] .= strtoupper($type)." GIS {$ACNO}";
   if ($service['result'] == "") $service['result'] = "SUCCESS";
   insertGISLOG($service);
  }else if($type=='share-service'){
    $id = trim(htmlspecialchars($_REQUEST['share-cloudid'], ENT_QUOTES));
    $MAC = trim(htmlspecialchars($_REQUEST['share-MAC'], ENT_QUOTES));
    $command = trim(htmlspecialchars($_REQUEST['share-command'], ENT_QUOTES));
    $service['ACNO'] = getValueByID($id,"ACNO");
    define("CUR_ACNO",$service['ACNO']);
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
    
    $service['action'] .= "{$command}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);  
  }else if($type=='add-service'){
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['add-OEM_ID'], ENT_QUOTES));
    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['add-ACNO'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['add-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['add-APNAME'], ENT_QUOTES));
    $service['APPMODE'] = trim(htmlspecialchars($_REQUEST['add-APPMODE'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['add-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['add-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['add-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['add-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['add-LNG'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['add-bind_account'], ENT_QUOTES));
    $service['end_date'] = date("Y-m-d"); //latest update
    $service['note'] = trim(htmlspecialchars($_REQUEST['add-note'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['add-is_public'], ENT_QUOTES));
    $service['is_installer'] = trim(htmlspecialchars($_REQUEST['add-is_installer'], ENT_QUOTES));
    $tcservice = $service; //init default value
    define("CUR_ACNO",$service['ACNO']);
    
    if (is_null(getBindAccountFromVendor($service['TCNAME']))){//( getBindAccountFromVendor($service['TCNAME']) != $service['bind_account']){
      $msgerr .="<div id=\"info\" class=\"error\">".TXT_TCNAME."無法驗證</div>";
      //$msgerr.="<script>setNotice(\"TCNAME\");</script>";
    //else if ( $check ==0) $msgerr .="<div id=\"info\" class=\"error\">".TXT_TCNAME."不存在</div>";
    }else{//valid vendor
    if (! isACNOExist($service['ACNO'])){ //new
        $service['start_date'] = date("Y-m-d");
        $service['action'] = "ADD {$service['ACNO']}";
        if (getUserID($service['ACNO']) < 0){
          $service['action'] .= ";ACNO";
          //create ANCO account
          if (insertEndUserAPI($service['ACNO'],APP_USER_PWD)){ 
              $service['user_name']=$service['ACNO'];
              $msgerr .="<div id=\"info\" class=\"success\">分享帳號 {$service['ACNO']} 建立成功.</div>";
              if (createCloudService($service)){
                //if bind_account exist, share all mac to ANCO
                $userid =getUserID($service['bind_account']);
                if ($userid>0){
                    $service['action'] .= ";share_camera";
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
             $service['user_name'] ="";
             $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 建立失敗.</div>";
             $service['result'] = "FAIL";
          }

        }else{ //workeyegis and isat.user not sync
          $msgerr .="<div id=\"info\" class=\"error\">帳號 {$service['ACNO']} 不存在. 圖資資料不同步. </div>";
          $service['action'] .= ";ACNO_INSYNC";
          $service['result'] = "FAIL";
        } 
    }else{//update
      $service['action'] .= "UPDATE {$service['ACNO']}";
      $service['id'] = getValueByType("ACNO",$service['ACNO'],"id");
      if (getUserID($service['ACNO']) > 0){
        //$msgerr .="<div id=\"info\" class=\"info\">分享帳號 {$service['ACNO']} 已存在. </div>";
        if (updateCloudService($service)){
          $msgerr .="<div id=\"info\" class=\"success\">更新 {$service['ACNO']}成功.</div>";
          $gservice=array();$gservice['is_public'] = 1;
          updateDBfield($service['id'],$gservice);
          if (strpos(getValueByID($service['id'],"note"),$service['note'])===false){//not existed MAC?
          $service['action'] .= ";share_camera";
          if (addShareDeviceAPI($service['note'],$service['ACNO'])){
            $msgerr .="<div id=\"info\" class=\"success\">分享攝影機{$service['note']}至 {$service['ACNO']}成功.</div>";
            updateMACList($service['id'],"share_camera",$service['note']);
          }else{
            $service['result'] = "FAIL";
            $msgerr .="<div id=\"info\" class=\"error\">分享攝影機{$service['note']}至 {$service['ACNO']}失敗.</div>";
          }
          }else $service['action'] .= ";share_camera exist";
        }else $msgerr .="<div id=\"info\" class=\"error\">更新 {$service['ACNO']}失敗.</div>";        
      }else{

        $msgerr .="<div id=\"info\" class=\"error\">帳號 {$service['ACNO']} 不存在. 圖資資料不同步. </div>";
        $service['action'] .= ";ACNO_INSYNC";
        $service['result'] = "FAIL";
      }
    }//add or update
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
    }//validateVendor OK
  }
}//form-type


if ( (defined('CUR_ACNO')) and is_null($tcservice)) //login
  getDefaultInfo(CUR_ACNO,$tcservice);
if (!isset($tcservice['MAC']))  $tcservice['MAC']=$_REQUEST['MAC'];
if (!isset($tcservice['ACNO']))  $tcservice['ACNO']=CUR_ACNO;

//if (DEBUG_FLAG == "ON") var_dump($tcservice);

function getUserByMAC($mac){
  $sql = "select user_name from isat.query_info where mac_addr='{$mac}'";
  sql($sql,$result,$num,0);
  if ($num==0) return "";
  fetch($arr,$result,0,0);
  return  $arr['user_name']; 
}

//validate
function getBindAccountFromVendor($TCNAME){
  if (strlen($TCNAME) < HEAD_CHAR_MATCH) return null;
  //$sql = "select * from customerservice.gis_vendor_info where TCNAME = '{$TCNAME}'";
  $sql = "select * from customerservice.gis_vendor_info where TCNAME like '{$TCNAME}%'";
  sql($sql,$result,$num,0);
  if ($num >0){ //if ($num == 1){
    fetch($arr,$result,0,0);
    return $arr['bind_account'];
  }
  return null;//multiple account or no account
  
  //return $num;
}
function isACNOExist($ACNO){
  $sql = "select * FROM customerservice.workeyegis WHERE ACNO='{$ACNO}'";
  sql($sql,$result,$num,0);
  if ($num ==1) return true;
  return false; 
}
function insertDBfield($service){
  $sql = "INSERT INTO customerservice.workeyegis SET ";
  foreach ($service as $key=>$value){
    if (($key == "is_installer") or ($key == "is_public")) $sql .="{$key}={$value}, ";
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
    if (($key == "is_installer") or ($key == "is_public")) $sql .="{$key}={$value}, ";
    else if ($key == "id") continue;
    else $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
  $sql .= " WHERE id={$id}";
//  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
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
  URL='".APP_URL."',
  start_date='{$service['start_date']}',
  note='{$service['note']}'";
//  end_date='',
  if (DEBUG_FLAG=="ON") echo $sql;
  sql($sql,$result,$num,0);
  return $result;
}

function updateCloudService($service){
  if ($service['id'] == "") return false;
  $sql = "UPDATE customerservice.workeyegis SET 
  PURP='{$service['PURP']}',
  APNAME='{$service['APNAME']}',
  DIGADD='{$service['DIGADD']}',
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  end_date='{$service['end_date']}',
  APPMODE='{$service['APPMODE']}'
   WHERE id={$service['id']}";
//  OEM_ID='{$service['OEM_ID']}',
//  is_public={$service['is_public']}',
//  start_date='{$service['start_date']}',
//  ACNO='{$service['ACNO']}', //should not change
//  APPMODE='',
//  bind_account='{$service['bind_account']}',
//  note='{$service['note']}'
//  user_name='{$service['user_name']}',
//  user_pwd='".APP_USER_PWD."', //no need to update
  sql($sql,$result,$num,0);
  return $result; 
}


//new function for installer
function getDefaultInfo($ACNO,&$myArr)
{
  $ACNO = strtoupper($ACNO);
  $sql = "select * from customerservice.workeyegis where ACNO='{$ACNO}'";
  sql($sql,$result,$num,0);
  if ($num>0) fetch($myArr,$result,0,0);
  return $result;
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
  if (strpos($maclist,$MAC)!==FALSE) return; //match duplicate MAC
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
<title><?php echo TXT_GIS_ENABLE;?>通報</title>
<!-- To support ios sizes -->
<link rel="apple-touch-icon" href="image/install180.png">
<!-- To support android sizes -->
<link rel="icon" href="image/install192.png">
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
a, a:hover #maplink{
text-decoration: none;
}
</style>
<script type="text/javascript">
$(document).ready(function()
{
<?php
  if (!defined('CUR_ACNO'))  
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
function validateForm(myform){ //sync with rpic_cleanshare.php
  if (/^[0-9]{3,}-[0-9]{2,}$/.test(myform.REG_ACNO.value)=== false){
    alert("<?php echo TXT_ACNO;?>格式錯誤");
    myform.REG_ACNO.focus();
    return false;
  } 
  myform.submit();
  return true;
}
function submitResetValue(formobj)
{
  formobj.isclean.value = "true";
  formobj.submit();
}
</script>
</head>
<body>
<div align=center><h2><?php echo $myTitle;?></h2>
<div align=right><font size=1> 
<?php 
if (defined('CUR_ACNO')) {
  if ($oem != "C13") //C13 disable
    echo "<a href=\"/plugin/rpic/appdownload.php\" target=_blank>應用下載</a>&nbsp;&nbsp;&nbsp;";
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
  <form method = "post" action = "<?php echo $_SERVER['PHP_SELF'];?>">
  <p><?php echo TXT_CAMERA."{$_REQUEST['MAC']}".TXT_GIS_ENABLE;?></p>
   <p><input type = "text" id = "REG_ACNO" name = "REG_ACNO"  placeholder = "填入<?php echo TXT_ACNO;?>"></p>
   <input type = "hidden" name="MAC" value = "<?php echo $_REQUEST['MAC'];?>">
   <input type = "hidden" name="form-type" value = "login">
   <input type = "button" id = "dologin" value = "登記" onclick="validateForm(this.form)">
  </form>
 </div>
</center>
<?php
if (! defined('CUR_ACNO')) die("</body></html>");
?>

<table class="mytable">
<tr><td class=table_header><?php echo TXT_ACNO;?></td><td><input type='text' name='ACNO' id='ACNO' placeholder='<?php echo TXT_ACNO;?>' ></td></tr><tr>
 <td class=table_header><?php echo TXT_PURP;?></td><td><input type='text' name='PURP' id='PURP' placeholder='<?php echo TXT_PURP;?>'  ></td></tr>
<tr><td class=table_header><?php echo TXT_TCNAME;?></td><td><input type='text' name='TCNAME' id='TCNAME' placeholder='<?php echo TXT_TCNAME;?>'></td></tr><tr>
 <td class=table_header><?php if (defined('TXT_TC_TEL2')) echo TXT_TC_TEL2; else echo TXT_TC_TEL;?></td><td><input type='text' name='TC_TEL' id='TC_TEL' placeholder='<?php if (defined('TXT_TC_TEL2')) echo TXT_TC_TEL2; else echo TXT_TC_TEL;?>' ></td></tr>
<tr><td class=table_header><?php echo TXT_APNAME;?></td><td><input type='text' name='APNAME' id='APNAME' placeholder='<?php echo TXT_APNAME;?>'  ></td></tr>
<tr><td class=table_header><?php echo TXT_DIGADD;?></td><td><input type='text' name='DIGADD' id='DIGADD' placeholder='<?php echo TXT_DIGADD;?>'>
<br><input type='text' name='LAT' id='LAT' placeholder='LAT' size='5px' readonly class='inputReadOnly'>
<input type='text' name='LNG' id='LNG' placeholder='LNG' size='5px' readonly class='inputReadOnly'><br>
<input type=button name='GISbtn' id='GISbtn' value="找地理坐標" onclick="window.open('getGIS.php?addr='+getAddr('DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');">
</td></tr>
<?php if (defined('TXT_APPMODE')){  ?>
<tr><td class=table_header><?php echo TXT_APPMODE;?></td><td><select name='APPMODE' id='APPMODE' onchange="javascript:setBG('APPMODE');"><?php echo printAPPMODEList();?></select></td></tr>
<?php }else{ echo "<input name='APPMODE' id='APPMODE' type=\"hidden\" value=\"\">";} ?>
<tr><td class=table_header><?php echo TXT_CAMERA;?></td><td align="center">
<input type='text'  name='note' id='note' disabled>
<?php
//validate if camera is bind or under bind_account 
if (defined('CUR_ACNO')){
$cloudid = getValueByType("ACNO",CUR_ACNO,"id"); 
$cloudid_js = '\''. $cloudid.'\'';
$fullcamlist= getValueByType("ACNO",CUR_ACNO,"note");
$macNum = count(explode(';' , $fullcamlist));
$macArr = explode(';',$fullcamlist);
for ($i=0;$i<$macNum;$i++){
  if ($macArr[$i]== "") continue;
  else if ($macArr[$i]== $_REQUEST['MAC']) continue;
  //echo "<br><input type=button value='刪除{$macArr[$i]}' onclick=\"shareService('unshare_camera','{$macArr[$i]}',{$cloudid_js})\"><br>";
  if ( ($tmpName=getCamNameByMac($macArr[$i]))!="")  echo "<br>{$macArr[$i]} ({$tmpName})<br>";
  else echo "<br>{$macArr[$i]}<br>";
}//for
}//ACNO
?>
</td></tr>
<tr><td colspan=2>
<div style="height:1px;vertical-align: bottom">
<form method = "post" action = "<?php echo $_SERVER['PHP_SELF'];?>">
<input id="isclean" name="isclean" type="hidden" value="false">
<input type = "hidden" name="MAC" value = "<?php echo $_REQUEST['MAC'];?>">
<input id="btnclean" name="btnclean" type="button" value="<?php echo TXT_CANCEL.TXT_GIS_ENABLE;?>" onclick='submitResetValue(this.form);' style='font-weight:bold;'>
</form>
</div>
<?php
if (defined('CUR_ACNO')){
$cloudid_js = '\''. CUR_ACNO.'\'';
if ( getValueByType("ACNO",CUR_ACNO,"is_public")=="1"){ //check is_public , new=> null
    echo "<input type=\"button\" value=\"".TXT_GIS_DISABLE."\" onclick=\"vendorService('disable',{$cloudid_js})\" class='buttonDisable'><input type=\"button\" value=\"".TXT_GIS_ENABLE."\" class='buttonEnable' disabled>\n";
}else{
    echo "<input type=\"button\" value=\"".TXT_GIS_DISABLE."\" class='buttonDisable' disabled><input type=\"button\" value=\"".TXT_GIS_ENABLE."\" onclick=\"vendorService('enable',{$cloudid_js})\" class='buttonEnable'>\n";
}
}//ACNO

?>
</td></tr>
<input type=hidden  name='is_installer' id='is_installer' value='<?php echo DEFAULT_IS_INSTALLER;?>'>
<input type=hidden  name='bind_account' id='bind_account' value='<?php if (defined('VALID_BIND_ACCOUNT'))  echo VALID_BIND_ACCOUNT;  else  echo DEFAULT_BIND_ACCOUNT;?>'>
<input type=hidden  name='is_public' id='is_public' value='1'>
<input type=hidden  name='OEM_ID' id='OEM_ID' value='<?php echo $oem;?>'>
</table>
  <form id="add-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="add-service"> 
    <input type="hidden" name="add-OEM_ID" id="add-OEM_ID">
    <input type="hidden" name="add-ACNO" id="add-ACNO">
    <input type="hidden" name="add-PURP" id="add-PURP">
    <input type="hidden" name="add-APPMODE" id="add-APPMODE">
    <input type="hidden" name="add-APNAME" id="add-APNAME">
    <input type="hidden" name="add-DIGADD" id="add-DIGADD">
    <input type="hidden" name="add-TCNAME" id="add-TCNAME">
    <input type="hidden" name="add-TC_TEL" id="add-TC_TEL">
    <input type="hidden" name="add-LAT" id="add-LAT">
    <input type="hidden" name="add-LNG" id="add-LNG">
    <input type="hidden" name="add-bind_account" id="add-bind_account">
    <input type="hidden" name="add-is_public" id="add-is_public">
    <input type="hidden" name="add-note" id="add-note">
    <input type="hidden" name="add-is_installer" id="add-is_installer">
  </form>
  <form id="public-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="public-service">
    <input type="hidden" name="public-cloudid" id="public-cloudid">
    <input type="hidden" name="public-type" id="public-type">
    <input type="hidden" name="public-ACNO" id="public-ACNO">
  </form>
  <form id="share-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="share-service">
    <input type="hidden" name="share-cloudid" id="share-cloudid">
    <input type="hidden" name="share-command" id="share-command">
    <input type="hidden" name="share-MAC" id="share-MAC">
  </form>
<script type="text/javascript">
<?php 
if (isset($msgerr))
  echo "$('#myMessage').show();";
?>
setGISDefault();


function setGISDefault()
{
<?php //for insert entry fail
  if (defined('CUR_ACNO')){
    if (!is_null($tcservice)){//get data from original
      if ($tcservice['ACNO']!="") echo "$('#ACNO').val('".$tcservice['ACNO']."');\n";
      if ($tcservice['PURP']!="") echo "$('#PURP').val('".$tcservice['PURP']."');\n";
      if ($tcservice['TCNAME']!="") echo "$('#TCNAME').val('".$tcservice['TCNAME']."');\n";
      if ($tcservice['TC_TEL']!="") echo "$('#TC_TEL').val('".$tcservice['TC_TEL']."');\n";
      if ($tcservice['APNAME']!="") echo "$('#APNAME').val('".$tcservice['APNAME']."');\n";
      if ($tcservice['DIGADD']!="") echo "$('#DIGADD').val('".$tcservice['DIGADD']."');\n";
      if ($tcservice['LAT']!="") echo "$('#LAT').val('".$tcservice['LAT']."');\n";
      if ($tcservice['LNG']!="") echo "$('#LNG').val('".$tcservice['LNG']."');\n";
      if ($tcservice['OEM_ID']!="") echo "$('#OEM_ID').val('".$tcservice['OEM_ID']."');\n";
      if ($tcservice['is_public']!="")   echo "$('#is_public').val('".$tcservice['is_public']."');\n";
      if ($tcservice['is_installer']!="")   echo "$('#is_installer').val('".$tcservice['is_installer']."');\n";
      //APPMODE
      if (defined('TXT_APPMODE'))
        if ($tcservice['APPMODE']!=""){
          echo "$(\"select#APPMODE\").val('".$tcservice['APPMODE']."');\n";
          echo "setBG(\"APPMODE\");\n";
        }
      else  if ($tcservice['APPMODE']!="") echo "$('#APPMODE').val('".$tcservice['APPMODE']."');\n";
      //echo "$('#start_date').val('".$tcservice['start_date']."');\n";
      if ($tcservice['MAC']!="") echo "$('#note').val('".$tcservice['MAC']."');\n";
    }
    echo "setReadOnly('ACNO');\n";
    if ( getValueByType("ACNO",CUR_ACNO,"is_public")=="1"){//use for disable only
    echo "setReadOnly('TCNAME');\n";
    echo "setReadOnly('TC_TEL');\n";
    echo "setReadOnly('APNAME');\n";
    echo "setReadOnly('PURP');\n";
    echo "setReadOnly('APPMODE');\n";
    echo "setReadOnly('DIGADD');\n";
    echo "setReadOnly('GISbtn');\n";
    }
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
  if (tag=="APPMODE"){
      var txtColor="";
      for (var i = 0; i < APPMODE_Array.length; i++) {//match to find color
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
function vendorService(type,ACNO){
  if (type == "enable"){
    addService();//add /update after submit
  }else if (type == "disable"){
  <?php  echo "setServiceMAP(type,'{$cloudid}');";?>
  }
  
}
function setServiceMAP(type,cloudid){
  var ACNO = $('#ACNO').val();
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
  var LAT = $('#LAT').val();
  var LNG = $('#LNG').val();
  var APPMODE = $('#APPMODE').val();
  var bind_account = $('#bind_account').val();
  var is_public = "1";//var is_public = $('#is_public').val();
  var is_installer ="1";//var is_installer = $('#is_installer').val();
  var note = $('#note').val();
//alert(is_public);
  if ( (isEmpty(OEM_ID)) || (isEmpty(ACNO))  
    || (isEmpty(DIGADD)) || (isEmpty(TCNAME)) || (isEmpty(TC_TEL)) 
    || (isEmpty(LAT)) || (isEmpty(LNG))  )
  {

    if (isEmpty(ACNO))  setNotice("ACNO");
    //if (isEmpty(PURP))  setNotice("PURP");
    if (isEmpty(TCNAME))  setNotice("TCNAME");
    else if (TCNAME.length < <?php echo HEAD_CHAR_MATCH;?>)  setNotice("TCNAME");
    if (isEmpty(TC_TEL))  setNotice("TC_TEL");
    //if (isEmpty(APNAME))  setNotice("APNAME");
    if (isEmpty(DIGADD))  setNotice("DIGADD");
    if (isEmpty(LAT)) setNotice("GISbtn");
    //if (isEmpty(OEM_ID))  setNotice("OEM_ID");
    //if (isEmpty(bind_account))  setNotice("bind_account");
    //if (isEmpty(note))  setNotice("note");
    showCustomMessage('error','必要欄位不可空白.');
    return;
  }
    
  $('#add-OEM_ID').val(OEM_ID);
  $('#add-ACNO').val(ACNO); 
  $('#add-PURP').val(PURP);
  $('#add-APNAME').val(APNAME);
  $('#add-APPMODE').val(APPMODE);
  $('#add-DIGADD').val(DIGADD);
  $('#add-TCNAME').val(TCNAME);
  $('#add-TC_TEL').val(TC_TEL);
  $('#add-LAT').val(LAT);
  $('#add-LNG').val(LNG);
  $('#add-bind_account').val(bind_account);
  $('#add-is_public').val(is_public);
  $('#add-is_installer').val(is_installer);
  $('#add-note').val(note);
  $('#add-service').submit();
}

function getAddr(idname){
  var idvalue=$('#'+idname).val();
  //alert(idvalue); 
  return encode_utf8(idvalue);
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

var gisDBfieldName = [
"ACNO",
"PURP",
"TCNAME",
"TC_TEL",
"APNAME",
"DIGADD",
"OEM_ID",
"bind_account"
//"user_name",
//"user_pwd",
//"APPMODE",
//"start_date",
//"end_date"
];
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
  $("#"+prefix+"GISbtn").prop('disabled', false);
  $("#"+prefix+"APPMODE").prop('disabled', false);
  
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