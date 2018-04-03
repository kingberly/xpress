<?php
/****************
 *Validated on Nov-22,2016,
 * validated taipei site
 * config file is under same folder _iveda.inc
 * Translated to Chinese page
 * add debugadmin feature to hide remove button
 * add debugadmin for create/share feature
 * fix share/unshare API bug     
 *Writer: JinHo, Chang
 * /etc/php5/apache2/php.ini set  ;default_charset = "UTF-8" 
*****************/
include("../../header.php");
include("../../menu.php");
header("Content-Type:text/html; charset=utf-8");
if (!isset($_SESSION["Email"]) ) exit();
###################
////jinho user prefix config.
require_once '_iveda.inc';
//_iveda.inc $RPICAPP_USER_PWD
if ($RPICAPP_USER_PWD[$oem]==NULL){
	define("APP_USER_PWD",$RPICAPP_USER_PWD['RPIC'][0]);
	define("AUTOLOGIN_PAGE",$RPICAPP_USER_PWD['RPIC'][1]);
  $ACCOUNT_FORMAT=$RPICAPP_USER_PWD['RPIC'][2];
}else{
	define("APP_USER_PWD",$RPICAPP_USER_PWD[$oem][0]);
	define("AUTOLOGIN_PAGE",$RPICAPP_USER_PWD[$oem][1]);
  $ACCOUNT_FORMAT=$RPICAPP_USER_PWD[$oem][2];
}

//created by web API cannot be deleted on Admin
if($_REQUEST["step"]=="Add User"){
    if ( checkSiteAccountRule($_REQUEST["name"]) ) 
    {//no log
      $result = insertUser($_REQUEST["name"],$_REQUEST["name"].USER_EMAIL_POSTFIX,APP_USER_PWD);
      if ($result){
          $msg_err = "<font color=blue>新增證號".$_REQUEST["name"]. " 成功!</font><br>\n";
      }else $msg_err = "<font color=red>新增證號".$_REQUEST["name"]. " 失敗!</font><br>\n";
    }else $msg_err = "<font color=blue>".$_REQUEST["name"] ." 不符合證號規則! </font><br>\n"; 

}//if add admin
else if($_REQUEST["step"]=="deluser")
{
    //$result = deleteUserByEmail($_REQUEST["email"]);
    $result = deleteUserAPI($_REQUEST["name"]);
    if ($result){
        $msg_err = "<font color=blue>刪除證號".$_REQUEST["name"]. " 成功!</font><br>\n";
    }else $msg_err = "<font color=red>刪除證號".$_REQUEST["name"]. " 失敗!</font><br>\n";    
}else if($_REQUEST["step"]=="device_share")
{
    if( isset($_REQUEST['btn_device_share']) ){
        $result = addShareDeviceAPI($_REQUEST["share_mac"],$_REQUEST["share_visitor_name"]);
        if ($result){
          $msg_err = "<font color=blue>新增分享".$_REQUEST["share_mac"]. "至".$_REQUEST["share_visitor_name"]." 成功!</font><br>\n";
        }else $msg_err = "<font color=red>新增分享".$_REQUEST["share_mac"]. "至".$_REQUEST["share_visitor_name"]." 失敗!</font><br>\n";
    }else if( isset($_REQUEST['btn_device_unshare']) ){
        //$result = deleteShareDevice($_REQUEST["id"]);
        $result = deleteShareDeviceAPI($_REQUEST["share_mac"],$_REQUEST["share_visitor_name"]);
        if ($result){
          $msg_err = "<font color=blue>從".$_REQUEST["share_visitor_name"]."刪除分享".$_REQUEST["share_mac"]. " 成功!</font><br>\n";
        }else $msg_err = "<font color=red>從".$_REQUEST["share_visitor_name"]."刪除分享".$_REQUEST["share_mac"]. " 失敗!</font><br>\n";
    }
}

function getPassword ($username)
{
  return APP_USER_PWD;//return strrev($username);
}


function getUserName($id) {
  // Get user
  $data_db = new DataDBFunction();
  $table = 'user';
  $condition = "id=:id";
  $params = array(':id'=>$id);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'name', $params);
  
  return $user_id["name"];
}


function addShareDevice ($uid, $owner_id,$visitor_id)
{

    if ($owner_id == $visitor_id) return false;
    $sql = "insert into isat.device_share (uid,owner_id,visitor_id) values ('{$uid}',{$owner_id},{$visitor_id})";
    sql($sql,$result,$num,0);
    //$sql = "insert into isat.position (owner_id,device_id) values ({$owner_id},'xxxx')";
    //sql($sql,$result,$num,0);
    if ($result) return true;
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


//delete user formally from web API
function deleteUserByEmail ($email)
{
   global $oem, $api_id, $api_pwd, $api_path;
   	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$oem}";
      	$ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL,$import_target_url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$result=curl_exec($ch);
      	curl_close($ch);
      	$content=array();
      	$content=json_decode($result,true);
  	if($content["status"]=="success")
  	{
  		$sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($email)."'";
  		sql($sql,$result_tmp,$num_tmp,0);
      return true;
  	}
    return false;
}

function deleteUserAPI ($username)
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
if (isset($_REQUEST["debugadmin"])) 
  echo $import_target_url.";cont=".print_r($content,true);
  	if($content["status"]=="success")
  	{
  		$sql="delete from qlync.end_user_list where Email='{$username}@safecity.com.tw'";
  		sql($sql,$result_tmp,$num_tmp,0);
      return true;
  	}
    return false;
}

function deleteUserByName ($name)
{
    global $oem;
  	$sql="delete from qlync.end_user_list where Account='".mysql_real_escape_string($name)."'";
    sql($sql,$result,$num,0);
    $sql="delete from isat.user where name='".mysql_real_escape_string($name)."'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function insertUserAPI ($username) //can return true/false
{
   global $oem, $api_id, $api_pwd, $api_path;
   	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=add_account&user_name={$username}";
      	$ch = curl_init();
      	curl_setopt($ch, CURLOPT_URL,$import_target_url);
      	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	$result=curl_exec($ch);
      	curl_close($ch);
      	$content=array();
      	$content=json_decode($result,true);
if (isset($_REQUEST["debugadmin"])) var_dump($content);
  	if($content["status"]=="success")
  	{
  		$sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','s','".date("Y-m-d H:i")."','{$oem}')";
  		sql($sql,$result_tmp,$num_tmp,0);
      return true;
  	}
    return false;
}

function insertUser ($name,$email,$pwd) //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
    //User Name/password is 4 - 32 alphabet or numeric characters
    //if (isset($_REQUEST["debugadmin"])) echo "validate email OK\n";
    if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$name)) return false;
    //if (isset($_REQUEST["debugadmin"])) echo "validate name OK\n";
    if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$pwd)) return false;
    //if (isset($_REQUEST["debugadmin"])) echo "validate pwd OK\n";
    $email = strtolower($email);
  	$ch = curl_init();
  	curl_setopt($ch, CURLOPT_HEADER, false);
  	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  	$params = array(
  	        'command'=>'add',
  	        'name'=>"{$name}",
  	        'pwd'=>"{$pwd}",
  	        'reg_email'=>"{$email}",
  	        'oem_id'=>"{$oem}" );
  	
  	$url = "http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?" . http_build_query($params);
  	curl_setopt($ch, CURLOPT_URL, $url);
  	$result = curl_exec($ch);
  	$content=json_decode($result,true);
    if (isset($_REQUEST["debugadmin"])) var_dump($content);
  	curl_close();
  	if($content["status"]=="success")
  	{
  		$sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','s','".date("Y-m-d H:i")."','{$oem}')";
  		sql($sql,$result_tmp,$num_tmp,0);
      return true;
  	}
    return false;
}

function createUserTable($limit,$where)
{
    $sql = "select * from isat.user {$where}";
    if ($limit!=0)
      $sql .= " order by id desc limit {$limit}";
    else
      $sql .= " order by id desc";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
//      $arr['id'] = mysql_result($result,$i,'id');
      $services[$index] = $arr;
    	$index++;
    }//for
	if ($where!=""){
		$html = "Found: {$num}";
	}else{
  $sql = "select count(*) as total from isat.user";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  $html = "Total: {$arr['total']}";
  }
  $html .= "<table id='tbl2' class=table_main><tr class=topic_main>\n";
  $html .= "<td>ID</td><td>帳號</td><td>註冊Email</td><td>登入 (註冊)</td><td colspan=4></td>\n";
  $html .= "</tr>\n";

  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['name']}</td>\n";
    //$html.= "<td>{$service['pwd']}</td>\n";
    $html.= "<td>{$service['reg_email']}</td>\n";
    $login_date = date ("Y-m-d H:i:s",$service['login_date']);
    $reg_date = date ("Y-m-d H:i:s",$service['reg_date']);
    //$expire_date = date ("Y-m-d H:i:s",$service['expire_date']);
    $html.= "<td>{$login_date} ({$reg_date})</td>\n";
    if ( checkSiteAccountRule($service['name']) )  
    {
        $loginpage = getWebURL("https://");//http:xxxx
        $apipage = $loginpage."/".AUTOLOGIN_PAGE;//?mode=1&user_name={$service['name']}&user_pwd=".
        $html.= "<td><form action='{$apipage}' target=web><input type=hidden name=user_name value='{$service['name']}'><input type=hidden name=user_pwd value='".APP_USER_PWD."'><input type='submit' value='登入'></form></td>\n";
        $html.= "<td><form action='{$loginpage}/logout.php' target=web><input type='submit' value='登出'></form></td>\n";
        //$html.= "<a href='https://{$loginpage[0]}/logout.php' target=web>登出</a>\n";
        $html.= "<td><form action='{$apipage}' target=web><input type=hidden name=mode value=1><input type=hidden name=user_name value='{$service['name']}'><input type=hidden name=user_pwd value='".APP_USER_PWD."'><input type='submit' value='登入大畫面'></form></td>\n";
        if (isset($_REQUEST["debugadmin"])){
            $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
            $html.= "<input type=submit name='btnAction' value=\"移除\">\n";
            $html.= "<input type=hidden name='step' value=\"deluser\" >\n";
            $html.= "<input type=hidden name='email' value=\"{$service['reg_email']}\" >\n";
            $html.= "<input type=hidden name='name' value=\"{$service['name']}\" >\n";
            $html.= "<input type=hidden name=debugadmin value='1'>";
            $html.= "</form>\n";
            $html.= "<form action='{$loginpage}/backstage_liveview.php' target=web>\n";
            $html.= "<input type=submit value=\"VLC\">\n";
            $html.= "<input type=hidden name=user_name value='{$service['name']}'><input type=hidden name=user_pwd value='".APP_USER_PWD."'></form>\n";
            $html.= "</td>\n";
        }else $html.= "<td></td>\n";
    }else $html.= "<td colspan=4></td>\n";
    $html.= "</tr>\n";
	}
  $html .= "</table>";
	echo $html;
}

function createShareTable($limit,$where="")
{
/*   $sql2 = "select id, name from isat.user";
   sql($sql2,$result2,$num2,0);
   $userArr = array();
    for($i=0;$i<$num2;$i++){
      fetch($arr,$result2,$i,0);
      $userArr[$arr['id']] = $arr['name'];
    }

    $sql = "select c1.id as id, c1.uid, c1.owner_id, c2.name as owner_name, c1.visitor_id from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id order by id desc";
    */
    $sql = "select c1.id as id, c1.uid, c1.owner_id, c2.name as owner_name, c1.visitor_id,c3.name as visitor_name from isat.device_share as c1 left join isat.user as c2 on c1.owner_id = c2.id left join isat.user as c3 on c1.visitor_id = c3.id {$where} order by id desc"; //where c3.name='ivedatest'
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
      $arr['visitor_id_name'] = $arr['visitor_id']. " : " .$arr['visitor_name'];//$arr['visitor_id'] . " : " . $userArr[$arr['visitor_id']];
      //$arr['visitor_name'] =  $userArr[$arr['visitor_id']];
      $services[$index] = $arr;
    	$index++;
    }//for
	if ($where!=""){
		$html = "Found: {$num}";
	}else{
  $sql = "select count(*) as total from isat.device_share";
  sql($sql,$result,$num,0);
  fetch($arr,$result,1,0);
  $html = "Total: {$arr['total']}";
  }

  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>攝影機MAC</td><td>所屬帳號</td><td>工地帳號</td><td></td></tr>"; //add table header
  $index = 0;
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['uid']}</td>\n";
    $html.= "<td>{$service['owner_id_name']}</td>\n";
    $html.= "<td>{$service['visitor_id_name']}</td>\n";
    //if($_SESSION["ID_admin_qlync"]) {//god admin
    if (isset($_REQUEST["debugadmin"])){ 
      $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btn_device_unshare' value=\"移除\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='step' value=\"device_share\" >\n";
      $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
      //$html.= "<input type=hidden name='uid' value=\"{$service['uid']}\" >\n";
      $mac=substr($service['uid'],strpos($service['uid'],"-")+1);
      $html.= "<input type=hidden name='share_mac' value=\"{$mac}\" >\n";
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
    $sql = "select DISTINCT c1.uid,c1.owner_id,c2.name as owner_name from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id order by c1.uid";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //if ( (strpos($arr['uid'],MCID)!== FALSE) or (strpos($arr['uid'],ZCID)!== FALSE) or (strpos($arr['uid'],IMCID)!== FALSE)) {
      if (checkUID($arr['uid'])) {
        $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
        //$html.= "\n<option value='{$arr['uid']}_{$arr['owner_id']}'>{$arr['uid']} ({$arr['owner_id_name']})</option>";
        $mac = substr($arr['uid'],strpos($arr['uid'],"-")+1);
        $html.= "\n<option value='{$mac}'>{$arr['uid']} ({$arr['owner_id_name']})</option>";
      }
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectUserList($tagName,$selectName)
{
   $sql = "select id, name from isat.user order by id desc";
   sql($sql,$result,$num,0);

  $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ( checkSiteAccountRule($arr["name"]) )
      {//urgent account XX#######, commone account #x9
        if (isset($selectName) and ($selectName == $arr['name']))
          //$html.= "\n<option value='{$arr['id']}_{$arr['name']}' selected>{$arr['id']}:{$arr['name']}</option>";
          $html.= "\n<option value='{$arr['name']}' selected>{$arr['id']}:{$arr['name']}</option>";
        else
          //$html.= "\n<option value='{$arr['id']}_{$arr['name']}'>{$arr['id']}:{$arr['name']}</option>";
          $html.= "\n<option value='{$arr['name']}'>{$arr['id']}:{$arr['name']}</option>";
      }else $html.= "\n<option value=''>{$arr['name']} 非工地證號</option>";
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
  //thisform.uid_filter.value = chosenoption.value;
  thisformobj.value = chosenoption.value;
}
</script>
<div align=center><b><font size=5>道管工地帳號管理</font></b></div>
<div id="container">
<?php
if ( $_SESSION["Email"] == "jinho.chang@tdi-megasys.com"){
//if ( $_SESSION["Email"] == "admin@rpic.taipei"){
  echo "<a href='/plugin/taipei/cmdweb.php'>Mount NAS</a><br>";
  echo "<a href='/plugin/taipei/showShareLog.php'>Share Log</a><br>";
}
?>

<form name=adduserform method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table id='tbl1' class=table_main>
					<tr class=topic_main>
						<td>帳號名稱</td>
						<td></td>
					</tr>
<tr class=tr_2>
<td>
<?php if($_SESSION["ID_admin_qlync"]){   ?>
<input type=text  size=20 name=name value=''>
<?php } ?>
<br>
<?php echo $ACCOUNT_FORMAT;?>
</td>
<td>
<input type=hidden name=step value="Add User">
<?php if($_SESSION["ID_admin_qlync"]){   ?>
<input type=submit value="新增工地證號" class="btn_1">
<?php }else{ 
  echo "<font size=2 style='color: white; background-color: #2E8DEC'>請至中心模組申請工地證號</font>";
} ?>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</td></tr>
</table>
</form>
<HR>
<?php
echo $msg_err;
?>
<form name=userform method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<input type=text name=found_account size=5 value='<?php if( isset($_REQUEST['found_account'])) echo $_REQUEST['found_account'];?>' placeholder='帳號'>
<input type=submit name=btnUserID value="找">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?> 
</form>
<?php
  if( isset($_REQUEST['found_account']) )
    createUserTable(0," where name like '%".$_REQUEST['found_account']."%'");
  else if($_REQUEST['uid_filter'] =="(ALL)" )
    createUserTable(0,"");
  else if($_REQUEST['uid_filter'] =="(MORE)" )
    createUserTable(50,"");
  else
    createUserTable(10,"");
?>
<hr>
<?php
if (isset($_REQUEST["debugadmin"])){ //manually add/delete share
    if (isLAN())
      $thisurl = "http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=";
    else $thisurl = "https://{$api_id}:{$api_pwd}@".ltrim(getWebURL("https://"),"https://")."/manage/manage_share.php?command=";  
   	echo $thisurl."share_camera&mac=M{$oem}12345678&user_name=IV1234567<br>";
    echo $thisurl."unshare_camera&mac=M{$oem}12345678&user_name=IV1234567<br>"; 
    echo $thisurl."delete_account&user_name=IV1234567<br>";
    echo getWebURL("https://")."/".AUTOLOGIN_PAGE."?user_name=IV1234567&user_pwd=".APP_USER_PWD."<br>";
?>
<form name=manualshare method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table>
<tr><tr>
<td rowspan=2>
<input type=text name=share_mac placeholder='MAC(12)' value='<?php if (isset($_REQUEST["share_mac"])) echo $_REQUEST["share_mac"];?>'>
</td>
<td>
<input type=submit name=btn_device_share value="Share MAC to ID" class=btn_1>
</td>
<td rowspan=2>
<input type=text name=share_visitor_name placeholder='Visitor Account' value='<?php if (isset($_REQUEST["share_visitor_name"])) echo $_REQUEST["share_visitor_name"];?>'>
</td></tr>
<tr><td>
<input type=submit name=btn_device_unshare value="Delete Share MAC" class=btn_1>
</td></tr>
<input type=hidden name=step value='device_share'>
<input type=hidden name=debugadmin value='1'>
</table>
</form>
<br>
<?php } ?>
<form name=shareform method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=hidden name=step value='device_share'>
<?php
     //selectDeviceUid("uid_owner_id");
     selectDeviceUid("share_mac");
if($_SESSION["ID_admin_qlync"]){
?>
<input type=submit name=btn_device_share value="攝影機分享至工地號" class=btn_1>
<?php
}else{
echo "<font size=2 style='color: white; background-color: #2E8DEC'>請至中心模組分享攝影機</font>";
} 
     selectUserList("share_visitor_name",$_REQUEST["visitor_name"]);
if (isset($_REQUEST["debugadmin"])){
  echo "<input type=hidden name=debugadmin value='1'>";
}
?>
</form>
<hr>
<h3>工地帳號攝影機列表</h3>
<form name=filter method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<select name="uid_filter_share" id="uid_filter" onchange="optionValue(this.form.uid_filter_share, this);this.form.submit();">
<option value="(FOLD)" <?php if($_REQUEST['uid_filter_share'] =="(FOLD)" ) echo "selected";?>>(FOLD)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter_share'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter_share'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<input type=text name=found_cam_account size=5 value='<?php if( $_REQUEST['found_cam_account']!="") echo $_REQUEST['found_cam_account'];?>' placeholder='工地帳號'>
<input type=text name=found_cam_mac size=5 value='<?php if( $_REQUEST['found_cam_mac']!="") echo $_REQUEST['found_cam_mac'];?>' placeholder='MAC'>
<input type=submit name=btnUserID value="找">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?> 
</form>
<?php
//var_dump($_REQUEST);
	if( $_REQUEST['found_cam_account']!="" )
		createShareTable(0," where c3.name like '%".$_REQUEST['found_cam_account']."%'");
	else if( $_REQUEST['found_cam_mac']!="" )
		createShareTable(0," where c1.uid like '%".$_REQUEST['found_cam_mac']."%'");
  else if($_REQUEST['uid_filter_share'] =="(ALL)" )
    createShareTable(0);
  else if($_REQUEST['uid_filter_share'] =="(MORE)" )
    createShareTable(50);
  else if($_REQUEST['uid_filter_share'] =="(FOLD)" )
    createShareTable(10);
  else
    createShareTable(10);
?>
  <br>
	</div>
</body>
</html>