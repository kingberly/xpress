<?php
/****************
 *Validated on Mar-8,2018,
 * reference from showGIS.php
 * Required qlync mysql connection config.php
 * Update uid_filter bug, change list mode to head string match % 
 *Writer: JinHo, Chang
*****************/
define("DEBUG_FLAG","OFF"); //OFF ON

include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php"); 
header("Content-Type:text/html; charset=utf-8");

if (DEBUG_FLAG == "ON") $oem = "C13"; //test

require_once ("rpic.inc");
//require_once ("share.inc");//insertGISLOG()
//define("DEFAULT_ADMIN_PWD","1qaz2wsx"); //same as account
if (DEBUG_FLAG == "OFF") if ( $_SESSION["Email"]=="" )   exit();
if ($oem == "C13")  define("DEFAULT_BIND_ACCOUNT","chimei00");
else if ($oem == "X02")  define("DEFAULT_BIND_ACCOUNT","ivedatest");
else die("This site does not have required bind account.");

if (DEBUG_FLAG == "ON") var_dump($_REQUEST);
//filter user
if (($_SESSION["ID_admin_qlync"]==1) or ($_SESSION["ID_admin_oem_qlync"]==1))
  $installuser = "";
else $installuser = $_SESSION['Email'];

define("INSTALL_USER","{$installuser}"); 

//$bInputData = false; //keep input data flag
//default pwd is align with SITE
/*
if (!is_null($RPICAPP_USER_PWD[$oem])){ //rpic.inc
  define("APP_USER_PWD",$RPICAPP_USER_PWD[$oem][1]);
}else die("Required pwd parameter missing!!");
*/
if ($_REQUEST['uid_filter'] =="(PAGE)" ){
  $total = getSizeByTable("customerservice.gis_vendor_info");
  $total_page=ceil($total/PAGE_LIMIT);
  if (isset($_REQUEST['page'])){
    $page = intval($_REQUEST["page"]); //確認頁數只能夠是數值資料
    $page = ($page > 0) ? $page : 1; //確認頁數大於零
  }else{
    $page = 1;
  }
  $pageSTART = ($page-1)*PAGE_LIMIT;
  $pageEND =$pageSTART + PAGE_LIMIT;
  $pageBANNER = "頁 ";
  for ($i=1;$i<=$total_page;$i++){
    if ($i!=$page)
      $pageBANNER .="<a href='?uid_filter=(PAGE)&page={$i}'>{$i}</a> | ";
    else $pageBANNER .= "{$i} | "; 
  }
  $pageBANNER = rtrim($pageBANNER,"| ");
}
/***********below is common user ***************************************/
if(isset($_REQUEST['form-type'])){
  $type = htmlspecialchars($_REQUEST['form-type'], ENT_QUOTES);
  $service = null;
  if($type=='search-service'){
    if ($_REQUEST['isclean']=="false")
      $searchCloudID = htmlspecialchars($_REQUEST['cloudid'], ENT_QUOTES);
      $searchCloudID=trim($searchCloudID," \t");
  }else if($type=='default-account'){
      $searchCloudID = trim(htmlspecialchars($_REQUEST['default-cloudid'], ENT_QUOTES));
      //$bind_account = trim(htmlspecialchars($_REQUEST['default-bind_account'], ENT_QUOTES));
      $admin_account = trim(htmlspecialchars($_REQUEST['default-admin_account'], ENT_QUOTES));
      $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['default-TCNAME'], ENT_QUOTES));
      $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['default-TC_TEL'], ENT_QUOTES));
      if (getAdminUserID($admin_account) < 0)
        if (createAdminUser($admin_account)){
          $msgerr .= "<div id=\"info\" class=\"success\">建立Admin帳號{$admin_account} - 成功.</div>";
          updateAdminUserInfo($bind_account,$service);
        }else  $msgerr .="<div id=\"info\" class=\"error\">建立Admin帳號{$admin_account} - 失敗.</div>";
      else
        if (setDefaultAdminUser($admin_account)){
          $msgerr .= "<div id=\"info\" class=\"success\">恢復Admin帳號{$admin_account}預設值 - 成功.</div>";
          updateAdminUserInfo($admin_account,$service);
        }else  $msgerr .="<div id=\"info\" class=\"error\">恢復Admin帳號{$admin_account}預設值 - 失敗.</div>";
  }else if($type=='add-service'){
    $service = null;
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['add-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['add-TC_TEL'], ENT_QUOTES));
    $service['admin_account'] = trim(htmlspecialchars($_REQUEST['add-admin_account'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['add-bind_account'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['add-note'], ENT_QUOTES));
    if (getAdminUserID($service['admin_account']) > 0){
      //create account
      $msgerr .="<div id=\"info\" class=\"error\">".TXT_ADMIN_ACCOUNT." {$service['admin_account']} 已存在.</div>";
      $service['result'] = "FAIL";
      //$bInputData = true; 
    }else{
      if (createVendor($service)){ //duplicate TCNAME
        createAdminUser($service['admin_account']);
        updateAdminUserInfo($service['admin_account'],$service);
      }else{
        $msgerr .="<div id=\"info\" class=\"error\">".TXT_TCNAME." {$service['TCNAME']} 已存在于".TXT_ADMIN_ACCOUNT.getDBfieldByKey("TCNAME",$service['TCNAME'],"admin_account")."</div>";
        $service['result'] = "FAIL";
      }
    }
    //$searchCloudID = $service['admin_account'];
    $service['action'] = "ADD {$service['admin_account']}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    //insertGISLOG($service);
  }else if($type=='delete-service'){
    $deleteCloudID = htmlspecialchars($_REQUEST['delete-cloudid'], ENT_QUOTES);
    $email=getDBfieldByID($deleteCloudID,"admin_account");
    $b = removeVendorByID($deleteCloudID);
    if($b){
      removeAdminUser($email);
      $msgerr .="<div id=\"info\" class=\"success\">刪除 {$email} 成功.</div>";
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">刪除 {$email} 失敗.</div>";
      $service['result'] ="FAIL";
    }
    $service['action'] = "DELETE {$email}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    //insertGISLOG($service);
  }else if($type=='update-service'){
    $service = null;
    $service['id'] = trim(htmlspecialchars($_REQUEST['update-cloudid'], ENT_QUOTES));

    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['update-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['update-TC_TEL'], ENT_QUOTES));
    //$service['bind_account'] = trim(htmlspecialchars($_REQUEST['update-bind_account'], ENT_QUOTES));
    //$service['admin_account'] = trim(htmlspecialchars($_REQUEST['update-admin_account'], ENT_QUOTES));
    $service['admin_account']=getDBfieldByID($service['id'],"admin_account");;
    $service['note'] = trim(htmlspecialchars($_REQUEST['update-note'], ENT_QUOTES));

    $searchCloudID = $service['admin_account'];
  //if (DEBUG_FLAG == "ON") var_dump($service);
    $b = updateVendor($service);
    if($b){
      $msgerr .="<div id=\"info\" class=\"success\">更新 {$service['admin_account']} 成功.</div>";
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">更新 {$service['admin_account']} 失敗.".TXT_TCNAME." {$service['TCNAME']} 已存在于".TXT_ADMIN_ACCOUNT.getDBfieldByKey("TCNAME",$service['TCNAME'],"admin_account")."</div>";
      $service['result'] = "FAIL";
    }
    $service['action'] = "UPDATE Admin{$service['admin_account']}";
    if ($service['result'] == "") $service['result'] = "SUCCESS";
    //insertGISLOG($service);
  }
}//end of form submit

function getUniqueAdminAccount()
{
  global $oem;
  $max = 1000;
  $base = 10;
  $log = log($max, $base);
  if ($oem=="X02") $prefix = "MEGA";
  else if ($oem=="C13") $prefix = "CHIMEI";
  else $prefix = "IVD";
  //$shareAccount= $prefix.date("YmdHis");
  $shareAccount= $prefix.date("Ymd")."-";
  for ($i=1;$i<$max;$i++){ //3 digits
    $sn = sprintf("%0{$log}d", $i);
    if (getAdminUserID($shareAccount.$sn)<0)
      return $shareAccount.$sn;
  }
  return $shareAccount; //1-999 all fail

/*  //get random number
  $errCount = 1;
  $sn= sprintf("%{$log}d", rand(1, $max));
  while (getAdminUserID($shareAccount.$sn)>0){
    $sn= sprintf("%0{$log}d", rand(1, $max));
    if (getAdminUserID($shareAccount.$sn)<0) break;
    $errCount++;
    if ($errCount > $base){//if error more than 10 times, get one more digit
      $max = $max*$base;
      $log = log($max, $base);
    }
  }
  return $shareAccount.$sn;*/
}
 
function updateAdminUserInfo($email,$service){
  if (getAdminUserID($email) < 0) return false;
  $sql="UPDATE qlync.account SET
  Company_english='{$service['TCNAME']}', 
  Company_chinese='{$service['TCNAME']}',
  Mobile='{$service['TC_TEL']}'
  WHERE Email='{$email}'";
  //if (DEBUG_FLAG == "ON") echo $sql;
  sql($sql,$result,$num,0);
  //if ($result) checkAdminUser($email);
  return $result;
}

function updateVendor($service){
//  bind_account='{$service['bind_account']}',
//  note='{$service['note']}' WHERE admin_account='{$service['admin_account']}'";
  if (getAdminUserID($service['admin_account']) < 0) return false;
  $sql = "UPDATE customerservice.gis_vendor_info SET 
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  note='{$service['note']}' WHERE id={$service['id']}";
  if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result;
}

function createVendor($service){

  $sql = "INSERT INTO customerservice.gis_vendor_info SET 
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  admin_account='{$service['admin_account']}',
  bind_account='{$service['bind_account']}',
  note='{$service['note']}'";
  sql($sql,$result,$num,0);
  return $result;
}


function insertDBfield($service){
  $sql = "INSERT INTO customerservice.gis_vendor_info SET ";
  foreach ($service as $key=>$value){
    $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
  if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}

function updateDBfield($id,$service){
  $sql = "UPDATE customerservice.gis_vendor_info SET ";
  foreach ($service as $key=>$value){
    $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
  $sql .= " WHERE id={$id}";
  if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}

function getDBfieldByID($id,$field){
  $sql = "SELECT * from customerservice.gis_vendor_info ";
  $sql .= " WHERE id={$id}";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  return $arr[$field]; 
}

function getDBfieldByKey($key,$data,$getfield){
  $sql = "SELECT {$getfield} from customerservice.gis_vendor_info ";
  if ($key == "id")
    $sql .= " WHERE {$key}={$data}";
  else
    $sql .= " WHERE {$key}='{$data}'";
  if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  return $arr[$getfield]; 
}

function removeVendorByID($cloudid){
  $sql = "DELETE FROM customerservice.gis_vendor_info WHERE id={$cloudid}";
  sql($sql,$result,$num,0);
  return $result;    
}


function createServiceTable($sqlParam=""){
  global $oem;
  /*if ($_REQUEST['uid_filter'] =="(PAGE)"){
    $limitParam = $sqlParam;
    $sqlParam = "";
  }else if ($_REQUEST['uid_filter'] =="(MORE)"){*/
  if ($_REQUEST['uid_filter'] !=""){
    if (strpos($sqlParam,"LIMIT")!==false){
    $limitParam = $sqlParam;
    $sqlParam = "";
    }    
  }else $limitParam = " LIMIT ".PAGE_LIMIT;   

  $sql = "select * from customerservice.gis_vendor_info {$sqlParam} order by id desc {$limitParam}";
  //echo $sql;
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
    //$html .="<td class=list>({$service['id']})\t{$service['admin_account']}</td>";
    $html .="<td class=list>{$service['admin_account']}</td>";
    $html .="<td class=list>{$service['TCNAME']}</td>";
    $html .="<td class=list>{$service['TC_TEL']}</td>";
    $html .="<td class=list>";
    //installGIS.php link
    if ($oem=="X02")
    $html.="<a target='installerAPP' href='installGIS.php?installuser={$service['admin_account']}' onclick=\"window.open(this.href,'installerAPP','width=400,height=600,resizable=1,scrollbars=yes');\">".TXT_GISLIST."</a>";
    else  if ($oem=="C13")
    $html.="<a target='installerAPP' href='installGIS.php?TCNAME={$service['TCNAME']}%' onclick=\"window.open(this.href,'installerAPP','width=400,height=600,resizable=1,scrollbars=yes');\">".TXT_GISLIST."</a>";

    if (($_SESSION["ID_admin_qlync"]==1) or ($_SESSION["ID_admin_oem_qlync"]==1)){
      //$html.="<input type=\"button\" value=\"".TXT_DELETE."\" onclick=\"removeService({$cloudid_js})\"  class='buttonEnable'>\n";
      $html.="&nbsp;&nbsp;&nbsp;&nbsp;<a onclick=\"removeService({$cloudid_js},'{$service['admin_account']}');return false;\" >".TXT_DELETE."</a>\n";
    }
      $html.="&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript: switchPopup('tr-gis{$cloudid}');switchLink('edit-gis-link{$cloudid}');\" id=\"edit-gis-link{$cloudid}\" >".TXT_UPDATE."</a>\n";
    $html.="</td></tr>";

      $html.="<tr id=\"tr-gis{$cloudid}\" style=\"width=100%;display:none\"><td colspan=4>";

     $html.="<table class='mytable'>";
     //ACNO row
     $html .="\n<tr><td class=table_header>".TXT_ADMIN_ACCOUNT."</td><td>";

      if (getAdminUserID($service['admin_account']) > 0) //updateACNO                                                                                                                       
          $html .="<input type='text' id='{$cloudid}-admin_account' value='{$service['admin_account']}' size='20px' readonly class='inputReadOnly'>";
      else $html .="<input type='text' id='{$cloudid}-admin_account' value='{$service['admin_account']}' size='20px'><font color=red>不存在!</font><input type=button value='".TXT_UPDATE."' onClick=\"updateAccount({$cloudid_js},'validate-admin_account')\">";
      $html .="</td>";
      $html .="</tr>";
      //TCNAME row
      $html .="\n<tr><td class=table_header>".TXT_TCNAME."</td>";
      if (($_SESSION["ID_admin_qlync"]==1) or ($_SESSION["ID_admin_oem_qlync"]==1))
      $html .="<td><input type='text' id='{$cloudid}-TCNAME' value='{$service['TCNAME']}' size='20px'></td>";
      else  $html .="<td><input type='text' id='{$cloudid}-TCNAME' value='{$service['TCNAME']}' size='20px' readonly style='background-color:grey'></td>";
      $html .="<td class=table_header>".TXT_TC_TEL."</td><td colspan=2><input type='text' id='{$cloudid}-TC_TEL' value='{$service['TC_TEL']}' size='20px'></td></tr>";

  //note row
  $html .="\n<tr><td class=table_header>".TXT_NOTE."</td><td colspan=2 align='center'><input type='text' id='{$cloudid}-note' value='{$service['note']}' size='60px'></td>";
     
    $html .= "<td><input type=button value='".TXT_UPDATE."' onclick=\"updateService({$cloudid_js})\"  class='buttonAdd'>";
    //list button
    //$html.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button  value='".TXT_GISLIST."' onclick=\"window.open('installGIS.php?installuser={$service['admin_account']}','installerAPP','width=400,height=600,resizable=1,scrollbars=yes');\"  class='buttonDisable'>";
    $html.="</td></tr></table>";
    $html.="</td></tr>";
  }
  echo $html;
}

function updateShareAccountList($id,$command,$username){
  //if key is mac
  $list = "";
  $sqlmac = "SELECT share_account from customerservice.workeyegis WHERE id ={$id}"; 
  sql($sqlmac,$resultmac,$nummac,0);
  if ($resultmac){
    fetch($arrmac,$resultmac,0,0);
    $list = $arrmac['share_account'];
  }else return $resultmac;
  //$macNum = count(explode(';' , $list));  
  if ($command == "add"){
      $list .= ";{$username}"; //add after
  }else if ($command == "delete"){
  //extract $MAC from $list
      $list = str_replace("{$username}","",$list);
      $list = str_replace(";;",";",$list); //remove middle cam
  }
  $list = ltrim($list,";"); //only one camera case  
  $list = rtrim($list,";"); //only one camera case

  $sql = "UPDATE customerservice.workeyegis SET share_account = '{$list}' WHERE id={$id}";
if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}
?>
<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">
function setReadOnly(tag){
    $("#"+tag).prop("readonly", true);
    $("#"+tag).css({'background-color' : 'grey'});
}

var gisDBfieldName = [
"TCNAME",
"TC_TEL",
"admin_account",
"bind_account"
];

function defaultAccount(cloudid){
  var TCNAME = $('#'+cloudid+'-TCNAME').val();
  var TC_TEL = $('#'+cloudid+'-TC_TEL').val();

  $('#default-TCNAME').val(TCNAME);
  $('#default-TC_TEL').val(TC_TEL);

  $('#default-cloudid').val(cloudid);  
  $('#default-account-service').submit();
}
function updateAccount(cloudid,formtype){
  var admin_account = $('#'+cloudid+'-admin_account').val();

  $('#validate-type').val(formtype);
  $('#valid-admin_account').val(admin_account);

  $('#valid-cloudid').val(cloudid);  
  $('#update-account-service').submit();
}
function updateService(cloudid){
  hideMessage();
  resetCheckcss(cloudid);
  var TCNAME = $('#'+cloudid+'-TCNAME').val();
  TCNAME = TCNAME.trim();
  var TC_TEL = $('#'+cloudid+'-TC_TEL').val();
  TC_TEL = TC_TEL.trim();
  var admin_account = $('#'+cloudid+'-admin_account').val();
  var note = $('#'+cloudid+'-note').val();
  if (isEmpty(TCNAME))  setNotice(cloudid+"-TCNAME");
  if (isEmpty(TC_TEL))  setNotice(cloudid+"-TC_TEL");
  if (! TCNAME.match(/[\u4E00-\u9FFF\u3400-\u4DFF\uF900-\uFAFF]+/g)) //chiniese only
  {
    setNotice(cloudid+"-TCNAME");
    showCustomMessage('error','欄位只可填中文.');
    return;
  }
  
  $('#update-cloudid').val(cloudid);
  //$('#update-admin_account').val(admin_account);
  $('#update-TCNAME').val(TCNAME);
  $('#update-TC_TEL').val(TC_TEL);
  $('#update-note').val(note);  
  $('#update-service').submit();
}

function removeService(cloudid, param){
  if (window.confirm('確認要刪除 '+param+' ?') == true) {
    hideMessage();
    $('#delete-cloudid').val(cloudid);
    $('#delete-service').submit();
  }
}

function getAddr(idname){
  var idvalue=$('#'+idname).val();
  //alert(idvalue); 
  return encode_utf8(idvalue);
}

function proprService(command,username, cloudid){
  if (!isEmpty(username)) {
    $('#propr-username').val(username);
    $('#propr-command').val(command);
    $('#propr-cloudid').val(cloudid);
    $('#propr-service').submit();
  }else{
    setNotice(cloudid+"-share_account");
  }
}

function addService(){
  hideMessage();
  resetCheckcss("");
  var TCNAME = $('#TCNAME').val();
  TCNAME = TCNAME.trim();
  var TC_TEL = $('#TC_TEL').val();
  TC_TEL = TC_TEL.trim();
  var admin_account = $('#admin_account').val();
  var note = $('#note').val();
  if (! TCNAME.match(/[\u4E00-\u9FFF\u3400-\u4DFF\uF900-\uFAFF]+/g)) //chiniese only
  {
    setNotice("TCNAME");
    showCustomMessage('error','欄位只可填中文.');
    return;
  }
  if( (isEmpty(TCNAME)) || (isEmpty(TC_TEL)) || (isEmpty(admin_account)) )
  {
    if (isEmpty(TCNAME))  setNotice("TCNAME");
    //if (isEmpty(TC_TEL))  setNotice("TC_TEL");
    if (isEmpty(admin_account)) setNotice("admin_account");
    showCustomMessage('error','必要欄位不可空白.');
    return;
  }
    
  $('#add-TCNAME').val(TCNAME);
  $('#add-TC_TEL').val(TC_TEL);
  $('#add-admin_account').val(admin_account);
  $('#add-note').val(note);
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
  //$("#"+prefix+"GISbtn" ).css({'background-color' : 'white'}); //LAT check
}
function hideMessage(){
  $('#myMessage').hide();
  $('#customMessage').hide();
}

function optionValue(thisformobj, selectobj){
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
  //empty search value if click select
  //document.getElementById('cloudid').value="";
  selectobj.form.cloudid.value="";
}
function confirmDelete(type, name) {//jinho add confirmDelete
    return confirm('確認刪除'+type+'帳號'+name+'?');
}

function updateUserValue(value){    // this gets called from the popup window and updates
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

function switchLink(tagname){
  var substring = "";
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
function switchPopup(id){//table-row, table-cell //block will break colspan
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
function submitResetValue(formobj)
{
  formobj.isclean.value = "true";
  formobj.submit();
}
</script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
<style type="text/css">
.table_header{height:25px; background-color:#0069C9; font:bold 14px arial; color:#FFF; text-align:center; border:1px solid #fff}
.table_headerImage{height:55px; background-color:#0069C9; font:bold 14px arial; color:#FFF; text-align:center; border:1px solid #fff}
table.mytable {
   border: 1px solid #CCC;
}
td.list {
font: bold 14px arial !important; 
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
    /* Position the tooltip text - see examples below! */
    top: -25px;
    left: 10%; 
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
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
  /*font-weight:bold;*/
}
.buttonDisable{
  background-color: #99FF99;
  float: right;
  /*font-weight:bold;*/
}

.inputReadOnly{
  background-color : grey;
}

a, a:hover #maplink{
text-decoration: none;
}
</style>

<div align=center><b><font size=5><?php echo TXT_TCNAME;?>資料管理</font></b></div>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="hidden" name="form-type" value="search-service">
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="" ></option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(多筆)</option>
<option value="(PAGE)" <?php if($_REQUEST['uid_filter'] =="(PAGE)" ) echo "selected";?>>(分頁)</option>
</select>&nbsp;&nbsp;
<input type="text" size="30" name="cloudid" id="cloudid" value="<?php echo $searchCloudID?>" placeholder='<?php echo TXT_ADMIN_ACCOUNT.",".TXT_TCNAME.",".TXT_TC_TEL;?>'>
&nbsp;&nbsp;
<input id="isclean" name="isclean" type="hidden" value="false">
<input id="btnclean" name="btnclean" type="button" value="重置" onclick='submitResetValue(this.form);'>
&nbsp;&nbsp;<input type="submit" value="搜尋">
<?php 
if ($_REQUEST['uid_filter'] =="(PAGE)")
  echo $pageBANNER;
?>
</form>
<div style="display: none" id="customMessage"></div>
<div style="display: none" id="myMessage">
<?php
if (isset($msgerr)) echo $msgerr;
?>
</div>
<table style="border-collapse">
          <tr class="mylist">
            <th><?php echo TXT_ADMIN_ACCOUNT;?></th>
            <th><?php echo TXT_TCNAME;?></th>
            <th><?php echo TXT_TC_TEL;?></th>
            <th></th>
          </tr>
<tr><td colspan=4 align=right style="text-align: right;vertical-align: top;">
<?php if ($installuser==""){ ?>
<a href="javascript: switchPopup('tr-add-gis');switchLink('add-gis-link');" id="add-gis-link" ><?php echo TXT_NEW.TXT_TCNAME;?></a>
<?php } ?>
</td></tr>
<tr id="tr-add-gis" style="width=100%;display:none">
<td colspan=4 align=right>
<table class="mytable">

<tr><td class=table_header><?php echo TXT_ADMIN_ACCOUNT;?></td>
<td colspan=3><input type='text' name='admin_account' id='admin_account' placeholder='' size='20px'></td>
</tr>
<tr><td class=table_header><?php echo TXT_TCNAME;?></td><td><input type='text' name='TCNAME' id='TCNAME' placeholder='名稱' size='20px'></td>
 <td class=table_header><?php echo TXT_TC_TEL;?></td><td><input type='text' name='TC_TEL' id='TC_TEL' placeholder='電話' size='20px'></td></tr>
 <tr><td class=table_header><?php echo TXT_NOTE;?></td><td><input type='text' name='note' id='note' placeholder='' size='40px'></td>
 <td colspan=2><input type=button value="<?php echo TXT_NEW;?>" onclick='addService();'  class='buttonAdd'></td></tr>
</table>
</td></tr>

<?php
            if ($searchCloudID!=""){
              if (INSTALL_USER!="")
                createServiceTable("WHERE ((TCNAME like '%{$searchCloudID}%') or (TC_TEL like '%{$searchCloudID}%') and (admin_account='".INSTALL_USER."') )");
              else  createServiceTable(" WHERE (TCNAME like '%{$searchCloudID}%') or (TC_TEL like '%{$searchCloudID}%') or (admin_account like '%{$searchCloudID}%')");
            }else if ($_REQUEST['uid_filter'] =="(MORE)" ){
              createServiceTable(" LIMIT ".(PAGE_LIMIT*2));
            }else if ($_REQUEST['uid_filter'] =="(PAGE)" ){
              createServiceTable(" LIMIT {$pageSTART}, {$pageEND}");
            }else{
              if (INSTALL_USER!="")
                createServiceTable("WHERE admin_account='".INSTALL_USER."'");
              else createServiceTable();
            }
?>
</table>

  <form id="add-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="add-service"> 
    <input type="hidden" name="add-TCNAME" id="add-TCNAME">
    <input type="hidden" name="add-TC_TEL" id="add-TC_TEL">
    <input type="hidden" name="add-admin_account" id="add-admin_account">
    <input type="hidden" name="add-bind_account" id="add-bind_account" value="<?php echo DEFAULT_BIND_ACCOUNT;?>">
    <input type="hidden" name="add-note" id="add-note">
  </form>

  <form id="delete-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="delete-service">
    <input type="hidden" name="delete-cloudid" id="delete-cloudid">
  </form>
  <form id="update-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="update-service">
    <input type="hidden" name="update-cloudid" id="update-cloudid">
    <!--input type="hidden" name="update-admin_account" id="update-admin_account"-->
    <input type="hidden" name="update-TCNAME" id="update-TCNAME">
    <input type="hidden" name="update-TC_TEL" id="update-TC_TEL">
    <input type="hidden" name="update-note" id="update-note">
  </form>
  <form id="default-account-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="default-account">
    <input type="hidden" name="default-cloudid" id="default-cloudid">
    <input type="hidden" name="default-TCNAME" id="default-TCNAME">
    <input type="hidden" name="default-TC_TEL" id="default-TC_TEL">
  </form>
  <form id="update-account-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="validate-account">
    <input type="hidden" name="valid-admin_account" id="valid-admin_account">
    <input type="hidden" name="valid-cloudid" id="valid-cloudid">
    <input type="hidden" name="validate-type" id="validate-type">
  </form>
<script type="text/javascript">
setDefault();
function setDefault(){
  var admin_account="<?php echo getUniqueAdminAccount();?>";
  if (admin_account!="")
    $('#admin_account').val(admin_account);
  <?php
    if (isset($msgerr)){
      if ($type=="add-service"){
        echo "$('#TCNAME').val('".$service['TCNAME']."');";
        echo "$('#TC_TEL').val('".$service['TC_TEL']."');";
        echo "$('#note').val('".$service['note']."');";
        echo "switchPopup('tr-add-gis');switchLink('add-gis-link');";
      }else if ($type=="update-service"){
        echo "switchPopup('tr-gis{$service['id']}');switchLink('edit-gis-link{$service['id']}');";
      }
      
      echo "$('#myMessage').show();";
    }
  ?>
}
</script>
</body>
</html>