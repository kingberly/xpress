<?php
/****************
 *Validated on Apr-25,2017,
 * reference from showService.php
 * Required qlync mysql connection config.php
 * Limit by god admin permission       
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
define("EMAIL_POSTFIX","@safecity.com.tw");
//require_once 'dataSourcePDO.php'; use Qlync sql and fetch instead
if (isset($_SESSION)){
  if ($_SESSION['ID_admin_qlync']!="1") exit();
}else exit();
//var_dump($_REQUEST);
$COMMON_GIS = [
84,86,88,89,90,91,93,94,95,96,
97,99,101,102,103,104,106,109,116,119,
124,126,129,130,145,171,176,199,200,202,
204,207,211,213,215,227,228,229,230,401,
403,405,406,407,412,415,417,420,422,425,
430,434,435,438,446,459,479,482,483,491,
492,494,521,525,528,538,544,660
];

//empty search value
if ($_REQUEST['cloudid']!="") $_REQUEST['uid_filter'] = "";

if(isset($_REQUEST['form-type'])){
  $type = htmlspecialchars($_REQUEST['form-type'], ENT_QUOTES);
  if($type=='search-service'){
     $searchCloudID = htmlspecialchars($_REQUEST['cloudid'], ENT_QUOTES);
  }else if($type=='add-service'){
    $services = null;
    $service['account'] = trim(htmlspecialchars($_REQUEST['add-account'], ENT_QUOTES));
    $service['name'] = trim(htmlspecialchars($_REQUEST['add-name'], ENT_QUOTES));
    $cloudid = $service['cloudid'];
    $b = createCloudService($service);

    if($b){
      echo "<div id=\"info\" class=\"success\">新增 {$service['account']} - 成功.</div>";
      $searchCloudID = $service['account']; 
    }else{
      echo "<div id=\"info\" class=\"error\">新增 {$service['account']} 失敗.</div>";
    }
  }else if($type=='delete-service'){
    $deleteCloudID = htmlspecialchars($_REQUEST['delete-cloudid'], ENT_QUOTES);
    $old_account = getAccountByID($deleteCloudID);
    checkAdminUser($old_account);
    checkWebUser($old_account);
    if (isExistUserByID($deleteCloudID))//created Admin/Web user Existed
    		$b=false;
    else $b = removeByCloudID($deleteCloudID);
    if($b){
      echo "<div id=\"info\" class=\"success\">刪除 id:{$deleteCloudID} 成功.</div>";
    }else{
    	$searchCloudID = $old_account;
      echo "<div id=\"info\" class=\"error\">刪除 id:{$deleteCloudID} 失敗.</div>";
    }
  }else if($type=='update-service'){
    $services = null;
    $service['id'] = htmlspecialchars($_REQUEST['update-cloudid'], ENT_QUOTES);
    $service['account'] =  trim(htmlspecialchars($_REQUEST['update-account'], ENT_QUOTES));
    $searchCloudID = $service['account'];
    $old_account = getAccountByID($service['id']);
    $service['name'] = trim(htmlspecialchars($_REQUEST['update-name'], ENT_QUOTES));
    if ($old_account!=$service['account']){
    	checkAdminUser($old_account);
    	checkWebUser($old_account);
    	if (isExistUserByID($service['id']))//created Admin/Web user Existed
    		$b=false;
    	else $b = updateCloudService($service); 
    }else $b = updateCloudService($service);
    if($b){
    	checkAdminUser($service['account']);
    	checkWebUser($service['account']);
      echo "<div id=\"info\" class=\"success\">更新 {$service['id']}:{$service['account']} 成功.</div>";
    }else{
    	$searchCloudID = $old_account;
    	if ($old_account!=$service['account'])
      echo "<div id=\"info\" class=\"error\">更新 {$service['id']}:{$old_account} 到 {$service['id']}:{$service['account']} 失敗.</div>";
    }
  }else if($type=='check-service'){
    $checkAccount =  trim(htmlspecialchars($_REQUEST['check-account'], ENT_QUOTES));
    $b = checkAdminUser($checkAccount);
    $b = checkWebUser($checkAccount);
    $searchCloudID = $checkAccount;
    if($b){
      echo "<div id=\"info\" class=\"success\">檢查存在帳號 {$checkAccount} 成功.</div>";
    }else{
      echo "<div id=\"info\" class=\"error\">檢查存在帳號 {$checkAccount} 失敗.</div>";
    }
  }else if($type=='account-service'){
    $checkAccount =  trim(htmlspecialchars($_REQUEST['mgmt-account'], ENT_QUOTES));
    $searchCloudID = $checkAccount;
    if (isset($_REQUEST["add_admin"])){
      if(createAdminUser($checkAccount)){
        echo "<div id=\"info\" class=\"success\">新增 Admin {$checkAccount} 成功.</div>";
      }else{
        echo "<div id=\"info\" class=\"error\">新增 Admin {$checkAccount} 失敗.</div>";
      }
    }else if (isset($_REQUEST["del_admin"])){
      if(removeAdminUser($checkAccount)){
        echo "<div id=\"info\" class=\"success\">刪除 Admin {$checkAccount} 成功.</div>";
      }else{
        echo "<div id=\"info\" class=\"error\">刪除 Admin {$checkAccount} 失敗.</div>";
      } 
    }else if (isset($_REQUEST["add_web"])){
      if(insertEndUserAPI($checkAccount)){
      	checkWebUser($checkAccount);
        echo "<div id=\"info\" class=\"success\">新增 Web {$checkAccount} 成功.</div>";
      }else{
        echo "<div id=\"info\" class=\"error\">新增 Web {$checkAccount} 失敗.</div>";
      } 
    }else if (isset($_REQUEST["del_web"])){
      if(deleteEndUserAPI($checkAccount)){
      	checkWebUser($checkAccount);
        echo "<div id=\"info\" class=\"success\">刪除 Web {$checkAccount} 成功.</div>";
      }else{
        echo "<div id=\"info\" class=\"error\">刪除 Web {$checkAccount} 失敗.</div>";
      } 
    }
  }//account-service
}


/* //require_once ("/var/www/qlync_admin/doc/config.php");
define("DB_NAME",'customerservice');
define("DB_HOST",$mysql_ip);
define("DB_USER",$mysql_id);
define("DB_PASSWORD",$mysql_pwd);
function getPDO(){
  $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.'', DB_USER, DB_PASSWORD,
      array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  return $pdo;
}
*/
function getArrayToString($arr,$prefix)
{
	$str = "";
	for ($i=0;$i<sizeof($arr);$i++){
		$str.= "'{$prefix}{$arr[$i]}', ";
	}
	$str = rtrim($str,", ");	
	return $str;
}
function createAdminUser($email){
  global $oem;
  $sql="INSERT INTO qlync.account (Email,Contact, Password, id_admin_oem, CID, Status, Company_english,ICID, id_fae) VALUES ('{$email}','{$email}',ENCODE('{$email}','".substr($email,0,5)."'),'1','{$oem}','1','PipeGis','0','0')";
  sql($sql,$result,$num,0);
  if ($result) checkAdminUser($email);
  return $result;
}
function removeAdminUser($email){
  $sql = "DELETE FROM qlync.account WHERE Email='{$email}'";
  sql($sql,$result,$num,0);
  if ($result) checkAdminUser($email);
  return $result;
}
function getAccountByID($id)
{
	$sql="select * from customerservice.pipeunit where id={$id}";
	sql($sql,$result,$num,0);
	if ($num==1){
		fetch($arr,$result,0,0);
		return $arr['account'];
	}
	return "";
}

function isExistUserByID($id)
{
  $sql="select admin_created,web_created from customerservice.pipeunit where id={$id}";
  sql($sql,$result,$num,0);
  if ($num==1){
  	fetch($arr,$result,0,0);
		if ($arr['admin_created']==1) return true;
		if ($arr['web_created']==1) return true;
	}
	return false; //not exist
}

function checkAdminUser($email){
  $sql="select * from qlync.account where Email='{$email}'";
  sql($sql,$result,$num,0);
  if ($num==1) //update created_admin
  {//set 1
      $sql = "UPDATE customerservice.pipeunit SET admin_created=1 WHERE account='{$email}'";
      sql($sql,$result,$num,0);
  }else{ //set0
      $sql = "UPDATE customerservice.pipeunit SET admin_created=0 WHERE account='{$email}'";
      sql($sql,$result,$num,0);
  }
  return true;
}


function checkWebUser($name){
  $sql="select * from isat.user where name='{$name}'";
  sql($sql,$result,$num,0);
  if ($num==1) //update created_admin
  {//set 1
      $sql = "UPDATE customerservice.pipeunit SET web_created=1 WHERE account='{$name}'";
      sql($sql,$result,$num,0);
  }else{ //set0
      $sql = "UPDATE customerservice.pipeunit SET web_created=0 WHERE account='{$name}'";
      sql($sql,$result,$num,0);
  }
  return true;
}

function createCloudService($service){
  $sql = "INSERT INTO customerservice.pipeunit SET account='{$service['account']}', name='{$service['name']}'";
  sql($sql,$result,$num,0);
  return $result;
}
function updateCloudService($service){
  $sql = "UPDATE customerservice.pipeunit SET account='{$service['account']}', name='{$service['name']}' WHERE id={$service['id']}";
  sql($sql,$result,$num,0);
  return $result; 
}
function removeByCloudID($cloudid){
  $sql = "DELETE FROM customerservice.pipeunit WHERE id={$cloudid}";
  sql($sql,$result,$num,0);
  return $result;    
}

function insertEndUserAPI ($name) //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
    $email=$name.EMAIL_POSTFIX;
    $password=$name;
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
      if (!preg_match("/^[0-9a-zA-Z-]{4,32}/",$name)) return false;
      else if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$password)) return false;
      $email = strtolower($email);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $params = array(
                'command'=>'add',
                'name'=>"{$name}",
                'pwd'=>"{$password}",
                'reg_email'=>"{$email}",
                'oem_id'=>"{$oem}" );
       
        $url = "http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?" . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        $content=json_decode($result,true);
        if($content["status"]=="success")
        {
            $sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','s','".date("Y-m-d H:i")."','{$oem}')";
            sql($sql,$result_tmp,$num_tmp,0);
            return true;
        }
      //print $content['error_msg'];
      return false;
}
function deleteEndUserAPI ($name)
{
  global $oem, $api_id, $api_pwd, $api_path;
  $email=$name.EMAIL_POSTFIX;
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
      $sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($email)."' and Oem_id='{$oem}'";
      sql($sql,$result_tmp,$num_tmp,0);
      return true;
    }
    return false;
}

function createServiceTable($sqlParam){
  //sqlParam=  limit 0
  if ($sqlParam!="")
    $sql = "select * from customerservice.pipeunit {$sqlParam}";
  else 
    $sql = "select * from customerservice.pipeunit";
  sql($sql,$result,$num,0);
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
  }
  //$html ="";
  //<input type='hidden' name='add-serviceid' id='add-serviceid' value='' size='10px'>
  $html ="<tr><td>(Total:{$num})</td><td><input type='text' name='account' id='account' value=''></td><td><input type='text' name='name' id='name' value='' size='25px'></td><td colspan=2><input type='button' value='新增管線帳號' onclick='addService();' style='width:180px;background-color:DodgerBlue;'></td></tr>";
 
  foreach($services as $service){
    $html.="\n<tr>";
    $cloudid = $service['id'];

    $cloudid_js = '\''.$cloudid.'\'';
    $html.="\n<td>{$cloudid}</td>";
    if ( ($service['admin_created']) or ($service['web_created']))
    	$html.="\n<td><input type='text' value='{$service['account']}' id='{$cloudid}-account' disabled></td>";
    else
    	$html.="\n<td><input type='text' value='{$service['account']}' id='{$cloudid}-account'></td>";
    $cloudid_js_a = '\''.$service['account'].'\'';
    $html.="\n<td><input type='text' value='{$service['name']}' id='{$cloudid}-name' size='25px'></td>";
    
    $html.="\n<td class='custom'><form id=\"account-service-{$cloudid}\" method=\"post\" action='".$_SERVER['PHP_SELF']."'><input type=\"hidden\" name=\"form-type\" value=\"account-service\"><input type=\"hidden\" name=\"mgmt-account\" id=\"mgmt-account\" value=\"{$service['account']}\">";
    $html.="預設密碼同帳號<br>";
    if ($service['admin_created']) $html.="<input type=submit name=del_admin value='刪除Admin帳號' style='background-color:red;' onclick=\"return confirmDelete('Admin','{$service['account']}');\">";
    else $html.= "<input type=submit name=add_admin value='建立Admin帳號' style='background-color:green;'>";  
    if ($service['web_created']) {
			$html.="<input type=submit name=del_web value='刪除Web帳號' style='background-color:red;' onclick=\"return confirmDelete('Web','{$service['account']}');\">";
			$html.= "<input type=\"button\" value=\"清除分享\" onclick=\"window.open('rpic_cleanshare.php?user_name={$service['account']}','',config='height=250,width=200')\">";
    }else $html.= "<input type=submit name=add_web value='建立Web帳號' style='background-color:green;'>"; 
    $html.="</form></td>";
    $html.="\n<td><input type=\"button\" value=\"檢查帳號狀態\" onclick=\"checkService({$cloudid_js_a})\"><br><input type=\"button\" value=\"更新資料\" onclick=\"updateService({$cloudid_js})\"  title='更新管線帳號名需處理Admin/Web帳號' style='background-color:LightSkyBlue;'><br><input type=\"button\" value=\"刪除\" onclick=\"removeService({$cloudid_js})\" style='background-color:LightPink;'></td>";
    $html.="</tr>";
  }
  echo $html;
}

?>
<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="hidden" name="form-type" value="search-service">
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="" >(FOLD/SEARCH)</option>
<option value="(COMMON)"  <?php if($_REQUEST['uid_filter'] =="(COMMON)" ) echo "selected";?>>(COMMON)</option>
<option value="(WEB_EXIST)" <?php if($_REQUEST['uid_filter'] =="(WEB_EXIST)" ) echo "selected";?>>(WEB_EXIST)</option>
<option value="(ADMIN_EXIST)" <?php if($_REQUEST['uid_filter'] =="(ADMIN_EXIST)" ) echo "selected";?>>(ADMIN_EXIST)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>&nbsp;&nbsp;
<input type="text" size="20" name="cloudid" id="cloudid" value="<?php echo $searchCloudID?>">&nbsp;&nbsp;<input type="submit" value="搜尋">
</form>

  <div style="display: none" id="customMessage"></div>
  <div id="container">
      <table style="margin-top: 15px" id="tbl">
        <thead>
          <tr>
            <th>ID</th>
            <th>管線帳號</th>
            <th>管線單位名稱</th>
            <th>Admin | Web</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
            if ($searchCloudID!="")
              //only support MyISAM " WHERE MATCH (`account`,`name`) against ('{$searchCloudID}')"
              createServiceTable(" WHERE account like '%{$searchCloudID}%' OR name like '%{$searchCloudID}%'");
						else if ($_REQUEST['uid_filter'] =="(COMMON)" )
							createServiceTable(" WHERE account in (".getArrayToString($COMMON_GIS,"pipegis").")");

						else if ($_REQUEST['uid_filter'] =="(WEB_EXIST)" )
							createServiceTable(" where web_created=1 ");
						else if ($_REQUEST['uid_filter'] =="(ADMIN_EXIST)" )
              createServiceTable(" where admin_created=1 ");
            else if ($_REQUEST['uid_filter'] =="(MORE)" )
              createServiceTable(" LIMIT 50 ");
            else if($_REQUEST['uid_filter'] =="(ALL)" )
              createServiceTable("");
            else  createServiceTable(" LIMIT 20 ");
          ?>
        </tbody>
      </table>
  </div>

  <form id="add-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="add-service"> 
    <input type="hidden" name="add-account" id="add-account">
    <input type="hidden" name="add-name" id="add-name">
  </form>

  <form id="delete-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="delete-service">
    <input type="hidden" name="delete-cloudid" id="delete-cloudid">
  </form>
  <form id="update-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="update-service">
    <input type="hidden" name="update-cloudid" id="update-cloudid">
    <input type="hidden" name="update-account" id="update-account">
    <input type="hidden" name="update-name" id="update-name">
  </form>
  <form id="check-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="check-service">
    <input type="hidden" name="check-account" id="check-account">
  </form>
  <script>


function updateService(cloudid){
  hideMessage();
  var account = $('#'+cloudid+"-account").val();
  var name = $('#'+cloudid+"-name").val();
  $('#update-cloudid').val(cloudid);
  $('#update-account').val(account);
  $('#update-name').val(name);
  $('#update-service').submit();
}

function checkService(account){
  hideMessage();
  $('#check-account').val(account);
  $('#check-service').submit();
}

function removeService(cloudid){
  if (window.confirm('確認要刪除 \''+cloudid+'\' ?') == true) {
    hideMessage();
    $('#delete-cloudid').val(cloudid);
    $('#delete-service').submit();
  }
}


function addService(){
  hideMessage();
  var account = $('#account').val();
  var name = $('#name').val();
  if(isEmpty(account)){
    showCustomMessage('error','帳號欄 - 不可空白.')
    return;
  }
  $('#add-account').val(account);
  $('#add-name').val(name); 
  $('#add-service').submit();
  
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

function hideMessage(){
  $('#info').hide();
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
</script>

</body>
</html>