<?php
/****************
 *Validated on Jul-28,2016
 * manage new godwatch feature from Qlync 
 *Writer: JinHo, Chang
 * /etc/php5/apache2/php.ini set  ;default_charset = "UTF-8" 
*****************/

require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");
//include("/var/www/qlync_admin/doc/mysql_connect.php"); 
//include("/var/www/qlync_admin/doc/sql.php");
include_once("/var/www/qlync_admin/html/common/gid.php");
include_once("/var/www/qlync_admin/html/common/cid.php");
include_once("/var/www/qlync_admin/html/common/scid.php");
#Authentication Section
if( !isset($_SESSION["Contact"]) )   exit();
//jinho added for db utf8 fix
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
//end of jinho
############  Authentication Section End
define("MSG_SUCCEED"," 成功!");
define("MSG_FAIL"," 失敗!");
//$groupArray=array(); 
if($_REQUEST["step"]=="deluser")
{
    $result = deleteGWUserAPI($_REQUEST["email"],$_REQUEST["oem"]);
    $titlemsg="刪除帳號 ";
    if ($result){
        $msg_err = "<font color=blue>".$titlemsg.$_REQUEST["email"].MSG_SUCCEED. "</font><br>\n";
    }else $msg_err = "<font color=red>".$titlemsg.$_REQUEST["email"]. MSG_FAIL."</font><br>\n";
    
}else if($_REQUEST["step"]=="changeoem")
{
    $result = setUserOEMID($_REQUEST["name"],$_REQUEST["new_oem"]);
    $titlemsg="變更OEM ID ".$_REQUEST["new_oem"]." ";
    
    if ($result){
        $msg_err = "<font color=blue>".$titlemsg.$_REQUEST["name"].MSG_SUCCEED. "</font><br>\n";
    }else $msg_err = "<font color=red>".$titlemsg.$_REQUEST["name"]. MSG_FAIL."</font><br>\n";
    
}else if($_REQUEST["step"]=="delimei")
{
    $result = deleteUserMeta($_REQUEST["imeiid"]);
    $titlemsg="解除綁定 ";
    if ($result){
        $msg_err = "<font color=blue>".$titlemsg.$_REQUEST["imeiid"]. MSG_SUCCEED ."</font><br>\n";
    }else $msg_err = "<font color=red>".$titlemsg.$_REQUEST["imeiid"]. MSG_FAIL." </font><br>\n";
}

function setUserOEMID($name,$new_oem)
{
  if (($name == "" ) or (strlen($new_oem) != 3)) return false; //wrong oem id
  $sql="update isat.user set oem_id='{$new_oem}' where name='{$name}'";
//echo $sql;
  sql($sql,$result,$num,0);
  if ($result)  return true;  
  return false;
}
function deleteGWUserAPI($email,$oemid)
{
    global $internal_api_id,$internal_api_pwd;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$dest = "https://".$_SERVER['HTTP_HOST'];
    if ($oemid=="G09")
		  $url = $dest.'/html/api/mgmt_enduser.php?id='.$internal_api_id.'&pwd='.$internal_api_pwd.'&command=deletegw&email='.$email;
    else
      $url = $dest."/html/api/mgmt_enduser.php?id=".$internal_api_id."&pwd=".$internal_api_pwd."&command=deletesp&oemid={$oemid}&email={$email}";
//echo $url;
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $result = str_replace("\n", '', $result); // remove new lines
    curl_close($ch);
    if ($result =="Success") return true;
    return false;

}

function deleteUserMeta($id)
{
  if ($id =="" )return false; 
  $sql = "delete from isat.user_metadata where id={$id}";
  sql($sql,$result,$num,0);
  if ($result)  return true;
  return false;
}

function createUserMeta($id)
{
  $sql = "select * from isat.user_metadata where user_id={$id}";
  sql($sql,$result,$num,0);
  if ($result){
    $tmp="";
    for($i=0;$i<$num;$i++){ 
      fetch($arr,$result,$i,0);
      $tmp.=$arr['field']."/".$arr['value']."<br>";
      if (isset($_REQUEST["debugadmin"])){
        if ($arr['field'] == "imei"){
          $tmp.="<form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
          $tmp.= "<input type=submit name='btnAction' value=\"Unbind IMEI\">\n";
          $tmp.= "<input type=hidden name='step' value=\"delimei\" >\n";
          $tmp.= "<input type=hidden name='imeiid' value=\"{$arr['id']}\" >\n";
          $tmp.= "<input type=hidden name=debugadmin value='1'>";
          $tmp.= "</form><br>\n";
          }
      }
    }
    return $tmp;
  }
  return "";
}
function createUserTable($limit, $whereParam) //$whereParam = "where group_id={$gid}"
{
  global $gid,$scid,$oem;//qlync define
  //global $groupArray;
  $sql = "select count(*) as count from isat.user where group_id<>1 ".$whereParam;
  sql($sql,$result,$num,0);
  if ($result){
    fetch($arr,$result,0,0);
    $total = $arr['count'];
  }else return;

    if ($limit!=0)
      $sql = "select *,LPAD(group_id,'10','0000000000')as group_str from isat.user where group_id<>1 {$whereParam} order by id desc limit {$limit}";
    else
      $sql = "select *,LPAD(group_id,'10','0000000000')as group_str from isat.user where group_id<>1 {$whereParam} order by id desc";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for

  $html = "共{$total}位使用者<table id='tbl2' class=table_main><tr class=topic_main><td>ID</td><td>帳號</td><td>OEM 類別</td><td>註冊Email</td><td>其他</td><td colspan=2></td></tr>";
  foreach($services as $key=>$service)
  {
    if (intval($key) % 2 == 0)
      $html.= "\n<tr class=tr_2>\n";
    else
		  $html.= "\n<tr class=tr_2 style='background-color: #f2f2f2;!important'>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['name']}</td>\n";
    //$tmppad=str_pad($service['group_id'],10,"0000000000",STR_PAD_LEFT);
    // $scid_list[substr($user_gid[$i],0,3)];
    // $gid[substr($user_gid[$i],3,1)][0];
    // $gid[substr($user_gid[$i],3,1)][(int)substr($user_gid[$i],4,2)][0];
    //$tmp= $groupArray[substr($service['group_str'],0,3)];
    $tmp= $scid[substr($service['group_str'],0,3)]['name'];
    //$tmp.="|". $gid[substr($tmppad,3,1)][0]; 
    $tmp.="|". $gid[substr($service['group_str'],3,1)][(int)substr($service['group_str'],4,2)][0];
    $html.= "<td>{$service['oem_id']}:{$tmp}</td>\n";
    $html.= "<td>{$service['reg_email']}</td>\n";
    $html.= "<td>".createUserMeta($service['id'])."</td>\n";
    if (isset($_REQUEST["debugadmin"])){
      if (substr($service['group_str'],3,3)=="311") {
        $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
        $html.= "<input type=submit name='btnAction' value=\"移除\">\n";
        $html.= "<input type=hidden name='step' value=\"deluser\" >\n";
        $html.= "<input type=hidden name='email' value=\"{$service['reg_email']}\" >\n";
        $html.= "<input type=hidden name='oem' value=\"{$service['oem_id']}\" >\n";
        $html.= "<input type=hidden name=debugadmin value='1'>";
        $html.= "</form></td>\n";
      }
      if ($service['oem_id']==$oem){//change oemid
        $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
        $html.= "<input type=submit name='btnAction' value=\"變更G09\">\n";
        $html.= "<input type=hidden name='step' value=\"changeoem\" >\n";
        $html.= "<input type=hidden name='name' value=\"{$service['name']}\" >\n";
        $html.= "<input type=hidden name='new_oem' value=\"G09\" >\n";
        $html.= "<input type=hidden name=debugadmin value='1'>";
        $html.= "</form></td>\n";
      }else{
        $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
        $html.= "<input type=submit name='btnAction' value=\"變更{$oem}\">\n";
        $html.= "<input type=hidden name='step' value=\"changeoem\" >\n";
        $html.= "<input type=hidden name='name' value=\"{$service['name']}\" >\n";
        $html.= "<input type=hidden name='new_oem' value=\"{$oem}\" >\n";
        $html.= "<input type=hidden name=debugadmin value='1'>";
        $html.= "</form></td>\n";
      }
    }else $html.= "<td></td>\n";
    $html.= "</tr>\n";
	}
  $html.= "</table>\n";
  if ( ($limit>0) and (intval($total) > $limit) )
    $html.= "<small>以下略</small><br>\n";
	echo $html;
}

function getAdminCameraList($username,$scid, $type) //ID_02, ID_01, ID_09
{
    $sqlmac = "select mac_addr,name from isat.query_info where user_name='{$username}' group by mac_addr order by mac_addr";      
    //sql($sqlmac,$resultmac,$nummac,0);
    $textList="<td>";
    //for($i=0;$i<$nummac;$i++){
    //  fetch($arrmac,$resultmac,$i,0);
    $arrmacs=myPDOQuery($sqlmac);//jinho update
    foreach ($arrmacs as $arrmac){
      if ($type=="ID_02")
        $textList.= "<a href='/html/public/lesson_info.php?scid={$scid}&mac={$arrmac['mac_addr']}' target=popup onclick=\"window.open('/html/public/lesson_info.php?scid={$scid}&mac={$arrmac['mac_addr']}','',config='height=500,width=350')\">{$arrmac['mac_addr']}</a>({$arrmac['name']})<br>";
        //$textList.= "<a href='/html/public/lesson_info.php?scid={$scid}&mac={$arrmac['mac_addr']}' target=_blank>{$arrmac['mac_addr']}</a><br>";
      else if ($type=="ID_01")
        $textList.= "<a href='/html/public/festival_info.php?scid={$scid}&mac={$arrmac['mac_addr']}' target=popup onclick=\"window.open('/html/public/festival_info.php?scid={$scid}&mac={$arrmac['mac_addr']}','',config='height=500,width=350')\">{$arrmac['mac_addr']}</a>({$arrmac['name']})<br>\n"; 
        //$textList.= "<a href='/html/public/festival_info.php?scid={$scid}&mac={$arrmac['mac_addr']}' target=_blank>{$arrmac['mac_addr']}</a><br>\n";
      else if ($type=="ID_09") $textList.= "{$arrmac['mac_addr']} ({$arrmac['name']})";
    }//for
    $textList.="<br></td>\n";
    return $textList;
}

function createAdminTable($limit, $whereParam) //$whereParam = "where group_id={$gid}"
{
  global $gid; //qlync define
  global $groupArray;
  $sql = "select count(*) as count from qlync.account where AID='3'".$whereParam;
  sql($sql,$result,$num,0);
  if ($result){
    fetch($arr,$result,0,0);
    $total = $arr['count'];
  }else return;
    //substring(Email,1,5) as Akey
    if ($limit!=0)
      $sql = "select * from qlync.account where AID='3' {$whereParam} order by id desc limit {$limit}";
    else
      $sql = "select * from qlync.account where AID='3' {$whereParam} order by id desc";
    sql($sql,$result,$num,0);
    
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for

  $html = "<a href='/html/admin/partner_list.php' target=_blank>後台管理</a>共{$total}位使用者<table id='tbl2' class=table_main><tr class=topic_main><td>ID</td><td>帳號Email/Contact</td><td>類別SCID/ID2/ID1/ID9</td><td>攝影機</td><td>其他</td></tr>";
  $index=0;$tmpid="";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
   
    $html.= "<td>{$service['ID']}</td>\n";
    $html.= "<td>{$service['Email']} / {$service['Contact']}</td>\n";
    $tmp= $groupArray[$service['SCID']];
    if ($service['ID_02']=='1') $tmp2="老師ID_02";
    else if ($service['ID_01']=='1') $tmp2="法會ID_01";
    else if ($service['ID_09']=='1') $tmp2="管ID_09";
    //$html.= "<td>{$service['SCID']}:{$tmp}/老師:{$service['ID_02']}/法會:{$service['ID_01']}/管:{$service['ID_09']}</td>\n";
    $html.= "<td>{$service['SCID']}:{$tmp}/{$tmp2}</td>\n";
    if ($service['ID_02']=='1') $html.= getAdminCameraList($service['Contact'],$service['SCID'],"ID_02");
    else if ($service['ID_01']=='1') $html.= getAdminCameraList($service['Contact'],$service['SCID'],"ID_01");
    else if ($service['ID_09']=='1') $html.= "<td><small><a href=#  target=popup onclick=\"window.open('/html/scid/{$service['SCID']}/cover.jpg','',config='height=450,width=500')\">底圖</a></small></td>"; 
    //$html.= getAdminCameraList($service['Contact'],$service['SCID'],"ID_09");
    $html.= "<td>{$service['Company_english']}/{$service['Company_chinese']}/{$service['Mobile']}/{$service['Phone']}</td>\n";
    
    $html.= "</tr>\n";
	}
  $html.= "</table>\n";

  if ( ($limit>0) and (intval($total) > $limit) )
    $html.= "<small>以下略</small><br>\n";
	echo $html;
}

function selectSCIDGroupList ($gid)
{
  global $groupArray;
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
      $groupArray[$arr['id']] = $arr['Name']; 
    }//for
	echo $html;
}

?>
<!--
<html>
<head>
<title>GodWatch Easy Wizard</title>
<link href="/plugin/licservice/css/layout.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/plugin/licservice/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/plugin/licservice/css/menu.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/plugin/licservice/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />
</head>
<body>
-->
<script>
//check mobile device
function detectmobile() { 
 if( navigator.userAgent.match(/Android/i)
 || navigator.userAgent.match(/webOS/i)
 || navigator.userAgent.match(/iPhone/i)
 || navigator.userAgent.match(/iPad/i)
 || navigator.userAgent.match(/iPod/i)
 || navigator.userAgent.match(/BlackBerry/i)
 || navigator.userAgent.match(/Windows Phone/i)
 ){
    return true;
  }
 else {
    return false;
  }
}

function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<div class="container"> 

<div align=center><b><font size=5>神在看</font></b></div>
<table id='tbl1' class=table_main>
					<tr class=topic_main>
            <td>宮廟APP管理</td>
						<td>帳號管理</td>
					</tr>
<tr class=tr_2>
<td>
<a href='fixutf8.php?table=user_metadata&db=isat' target=_blank>修正宮廟註冊使用者資料庫中文</a><br>                          
<input type=button name=btnActionGroup value="設定宮廟SCID"  onclick="window.open('/html/webmaster/scid_edit.php');" class="btn_2">
<br>
<a href="https://docs.google.com/spreadsheets/d/1-eOPf6D46uD7_Iak4pLoeLBEU3CdBrzmwW-gy7-0rQI/edit?ts=577e361f#gid=1282661652" target=_blank>
上傳更新SCID及1024x1024, 1080x1920底圖</a>
<br>
通知Qlync宮廟SCID對應號,取得APP
<br>
<input type=button name=btnActionGroup value="APP下載"  onclick="window.open('/plugin/debug/apk.htm','',config='height=300,width=350');" class="btn_2" >
<br>
<input type=button name=btnActionGroup value="001神在看使用者清單"  onclick="window.open('godwatchqlist.php?scid=1');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="003翰林院使用者清單"  onclick="window.open('godwatchqlist.php?scid=3');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="004中華瑤池母使用者清單"  onclick="window.open('godwatchqlist.php?scid=4');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="005天池天鳳宮使用者清單"  onclick="window.open('godwatchqlist.php?scid=5');" class="btn_1" >
<br>
</td>

<td>
APP申請信眾帳號(G09)<br>
<input type=button name=btnActionGroup value="新增001神在看帳號"  onclick="window.open('/html/public/apply.php?scid=001','',config='height=500,width=350');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="新增003翰林院帳號"  onclick="window.open('/html/public/apply.php?scid=003','',config='height=500,width=350');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="新增004中華瑤池母帳號"  onclick="window.open('/html/public/apply.php?scid=004','',config='height=500,width=350');" class="btn_1" >
<br>
<input type=button name=btnActionGroup value="新增005天池天鳳宮帳號"  onclick="window.open('/html/public/apply.php?scid=005','',config='height=500,width=350');" class="btn_1" >
<br><br>
老師/法會<a href="/html/member/delete_user.php" target=_blank>申請帳號 及 攝影機預綁</a>
<br>
老師要登入APP請debugadmin修改oem id
<br><br>
請手動分享攝影機給使用者, 法會已經在申請時分享<br>
<input type=button name=btnActionGroup value="分享攝影機管理"  onclick="window.open('/plugin/debug/godwatchq_share.php');" class="btn_1">
<input type=button name=btnActionGroup value="改攝影機名稱"  onclick="window.open('/plugin/debug/godwatch_device.php');" class="btn_2">
<br><br>
老師/法會後台管理帳號申請,需與前台號碼一致<br>
<input type=button name=btnActionGroup value="新增後台帳號"  onclick="window.open('/html/member/login.php');" class="btn_2" >
&nbsp;&nbsp;
<input type=button name=btnActionGroup value="App訊息"  onclick="window.open('/plugin/debug/godwatchq_msg.php');" class="btn_2">
<br>

</td></tr>
</table>

<HR>
<?php
echo $msg_err;
?>
<br>
<form method=POST>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin value='1'>";
?>
<select name="group_id_filter" id="group_id_filter" onchange="optionValue(this.form.group_id_filter, this);this.form.submit();">
<option value="(FOLD)" <?php if($_REQUEST['group_id_filter'] =="(FOLD)" ) echo "selected";?>>(FOLD)</option>
<option value="(ADMIN)" <?php if($_REQUEST['group_id_filter'] =="(ADMIN)" ) echo "selected";?>>(ADMIN)</option>
<option value="(ALL)" <?php if($_REQUEST['group_id_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
<?php
     selectSCIDGroupList($_REQUEST['group_id_filter']);
?>
</select>
</form>
<?php
  if($_REQUEST['group_id_filter'] =="(FOLD)" ){
    createUserTable(3,"");
    createAdminTable(3,"");
  }else if($_REQUEST['group_id_filter'] =="(ALL)" ){
    createUserTable(0,"");
    createAdminTable(0,"");
  }else if($_REQUEST['group_id_filter'] =="(ADMIN)" ){
    createUserTable(0," and group_id like '%30%'");
    createAdminTable(0,"");
  }else if(isset($_REQUEST['group_id_filter'])) {
    createUserTable(0," and group_id like '".$_REQUEST['group_id_filter']."%'");
    createAdminTable(0, " and SCID like '%".$_REQUEST['group_id_filter']."%'");
  }else{
    createUserTable(3,"");
    createAdminTable(3,"");
  }  
?>
  <br>
	</div>
</body>
</html>
