<?php
/****************
 *Validated on Jan-11,2016,
 * user Info list
 * Add access link
 * add debugadmin feature to hide remove button
 * add cleanup button    
 *Writer: JinHo, Chang
*****************/
include("../../header.php");
include("../../menu.php");
require_once '_auth_.inc';


if($_REQUEST["step"]=="deluser")
{
    $result = deleteUserByEmail($_REQUEST["email"]);
    if ($result){
        $msg_err = "<font color=blue>Delete User ".$_REQUEST["name"]. " Success!</font><br>\n";
    }else $msg_err = "<font color=red>Delete User ".$_REQUEST["name"]. " Fail!</font><br>\n";   
}else if($_REQUEST["step"]=="user_display")
{
    if ($_POST['btnAction2'] =='display')
      $msg_err = listNoActivityUser($_REQUEST["day"],$_REQUEST["accountrule"]);
    else if ($_POST['btnAction2'] =='cleanup')
      $msg_err = deleteNoActivityUser($_REQUEST["day"],$_REQUEST["accountrule"]);
}else if($_REQUEST["step"]=="username_length")
{
    if (isset($_REQUEST["namelen"]))
        $msg_err = listUserByLength($_REQUEST["namelen"]);
}else if($_REQUEST["step"]=="update_oem_id")
{
    if (isset($_REQUEST["id"]) and isset($_REQUEST["oem_id"]))
      if (update_user_oem($_REQUEST["id"],$_REQUEST["oem_id"]))
          $msg_err ="<font color=blue>Success Update User ".$_REQUEST["id"]." to oem_id=".$_REQUEST["oem_id"]."</font>";
      else $msg_err ="<font color=red>Fail Update User ".$_REQUEST["id"]." to oem_id=".$_REQUEST["oem_id"]."</font>";
}else if (isset($_REQUEST['btnUserID'])){
    if (isset($_REQUEST["VAL"]))
        $msg_err = listUserByTYPE($_REQUEST["TYPE"],$_REQUEST["VAL"]);
}

function listUserByTYPE($TYPE,$value)
{
  if ($TYPE=='id')
    $sql = "select * from isat.user where {$TYPE}={$value}";
  else if (strpos($TYPE,"like")!== FALSE)
     $sql = "select * from isat.user where {$TYPE} '%{$value}%'";
  else $sql = "select * from isat.user where {$TYPE}='{$value}'";
  sql($sql,$result,$num,0);
  $html .= "Found {$num}<br>\n<table id='tbl4' class=table_main><tr class=topic_main>";
  $html .= "<td>ID</td><td>oem id</td><td>group id</td><td>name</td><td>Email</td><td>Date (Login / Valid Reg.)</td><td>Count</td><td colspan=4></td>";
  for($i=0;$i<$num;$i++){
    fetch($service,$result,$i,0);
    $reg_date = date ("Y-m-d H:i:s",$service['reg_date']);
    $login_date = date ("Y-m-d H:i:s",$service['login_date']);
    $expire_date = date ("Y-m-d H:i:s",$service['expire_date']);
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td style='width:50px'><b>{$service['oem_id']}</b>";
    $html.=  "</td>\n";
    $html.= "<td>{$service['group_id']}</td>\n";
    $html.= "<td>{$service['name']}</td>\n";  //$service['pwd_hash']
    $html.= "<td>{$service['reg_email']}</td>\n";
    if ($service['login_count']=='0')
        $html.= "<td>--/ {$reg_date} - {$expire_date}</td>\n";
    else    $html.= "<td>{$login_date} /<br>{$reg_date} - {$expire_date}</td>\n";
    $html.= "<td>{$service['login_count']}</td>\n";
    $html.= "<td colspan=4></td>\n";    
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";
  return $html;
}

function update_user_oem($id,$oem_id)
{
    $sql="update isat.user set oem_id='{$oem_id}' where id={$id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function listNoActivityUser($beforeDays, $accountrule)
{//mysql where xxx REGEXP '^-?[0-9]+$'
  $before = time() - 86400* (int)$beforeDays;
  $sql = "select id,name from isat.user where login_date < ".$before." and name regexp '^".$accountrule."$'"; //[A-Z0-9]{7,15}
  //select id,name from isat.user where name regexp '^[A-Z0-9]{7,15}$'
  sql($sql,$result,$num,0);
  $services = array();
  $index = 0;
  $html = $sql ."<br>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$index] = $arr;
  	$index++;
  }//for
  $html .= "total: {$index}<br>";
  foreach($services as $service)
  {
      $html.= "{$service['id']}:{$service['name']}, ";
  }
  return $html;
}


function listUserByLength($namelen)
{

  $sql = "SELECT name FROM isat.user WHERE LENGTH(name) = ".$namelen; 
  sql($sql,$result,$num,0);
  $services = array();
  $index = 0;
  $html = $sql ."<br>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$index] = $arr;
  	$index++;
  }//for
  $html .= "total: {$index}<br>";
  foreach($services as $service)
  {
      $html.= "{$service['id']}:{$service['name']}, ";
  }
  return $html;
}
function deleteNoActivityUser($beforeDays, $accountrule)
{
  $before = time() - 86400* (int)$beforeDays;
  $sql = "select id,name from isat.user where login_date < ".$before." and name regexp '^".$accountrule."$'";
  sql($sql,$result,$num,0);
  $services = array();
  $index = 0;
  $html = $sql ."<br>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$index] = $arr;
  	$index++;
  }//for
  $html .= "total: {$index}<br>";
  foreach($services as $service)
  {
      $res = deleteUserByEmail(strtolower($service['name'])."@safecity.com.tw");
      if ($res)  $html.= "delete {$service['name']} success.<br>";
      else $html.= "delete {$service['name']} fail.<br>";
  }
  return $html;
}

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

function createUserTable($nLimit)
{
  global $oem;
  if ($nLimit==0)
    $sql = "select * from isat.user order by id desc";
  else $sql = "select * from isat.user order by id desc limit {$nLimit}";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for
  $sql = "select count(*) as total from isat.user";
  sql($sql,$result_total,$num_total,0);
   fetch($arr,$result_total,0,0);
  $html = "<b>user</b> Total: {$arr['total']}";
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main>";
  $html .= "<td>ID</td><td>oem id</td><td>group id</td><td>name</td><td>Email</td><td>Date (Login / Valid Reg.)</td><td>Count</td><td colspan=4></td>"; //add table header 3
  $html .= "</tr>";
  foreach($services as $service)
  {
    $reg_date = date ("Y-m-d H:i:s",$service['reg_date']);
    $login_date = date ("Y-m-d H:i:s",$service['login_date']);
    $expire_date = date ("Y-m-d H:i:s",$service['expire_date']);
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    //$html.= "<td style='width:50px'><b>{$service['oem_id']}</b>";
    $html.= "<td style='width:50px'>";
    if ($oem=="X02"){
        $setArr = array("G09","S08","S09",$oem);
        $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
        $html.= "<select name='oem_id' onchange='optionValue(this.form.oem_id, this);this.form.submit();'><option value='' selected>{$service['oem_id']}</option>";
        for ($i=0;$i<sizeof($setArr);$i++){
          if ($service['oem_id'] != $setArr[$i])
            $html.= "<option value='{$setArr[$i]}'>{$setArr[$i]}</option>";
        }
        $html.= "</select>\n";
        $html.= "<input type=hidden name='step' value=\"update_oem_id\" >\n";
        $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
        $html.= "</form>&nbsp;\n";
    }else   $html.="<b>{$service['oem_id']}</b>";
    
    $html.=  "</td>\n";
    $html.= "<td>{$service['group_id']}</td>\n";
    $html.= "<td>{$service['name']}</td>\n";  //$service['pwd_hash']
    $html.= "<td>{$service['reg_email']}</td>\n";
    if ($service['login_count']=='0')
        $html.= "<td>--/ {$reg_date} - {$expire_date}</td>\n";
    else    $html.= "<td>{$login_date} /<br>{$reg_date} - {$expire_date}</td>\n";
    $html.= "<td>{$service['login_count']}</td>\n";  
    //matrix_display_mode
    if (isset($_REQUEST["debugadmin"])){
        //$wwwURL = explode (":",$_SERVER['HTTP_HOST']);
        //no password input to login
        //$html.= "<td><form action='http://".$wwwURL[0]."/backstage_login.php' target=web><input type=hidden name=user_name value='{$service['name']}'><input type=hidden name=user_pwd value='pwd'><input type='submit' value='BackStage Login'></form></td>\n";
        //$html.= "<td><form action='http://".$wwwURL[0]."/logout.php' target=web><input type='submit' value='Logout'></form></td>\n";
        //$html.= "<td><form action='http://".$wwwURL[0]."/iveda/index.php?mode=personal&view_location=iveda%2Fshared_matrix.php' target=web><input type='submit' value='Enlarge Windows'></form></td>\n";
        $html.= "<td colspan=2></td>";
        $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
        $html.= "<input type=submit name='btnAction' value=\"Remove\">\n";
        $html.= "<input type=hidden name='step' value=\"deluser\" >\n";
        $html.= "<input type=hidden name='email' value=\"{$service['reg_email']}\" >\n";
        $html.= "<input type=hidden name='name' value=\"{$service['name']}\" >\n";
        $html.= "<input type=hidden name=debugadmin value='1'>\n";
        $html.= "</form></td>\n";
    }else $html.= "<td colspan=4></td>\n";
    
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function createUserRegTable()
{
    $sql = "select * from isat.user_reg";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for
  $html = "<b><a href=\"javascript: window.open('http://".explode(":",$_SERVER['HTTP_HOST'])[0]."/user_register.php','',config='height=350,width=600');\">user_reg</a></b> Total: {$num}";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID</td><td>name</td><td>email</td><td>auth code</td><td>reg date</td><td></td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['name']}</td>\n";
    $html.= "<td>{$service['reg_email']}</td>\n";
    $html.= "<td>{$service['authentication_code']}</td>\n";
    $reg_date = date ("Y-m-d H:i:s",$service['reg_date']);
    $html.= "<td>{$reg_date}</td>\n";
    
    $html.= "<td><a href = \"javascript: window.open('http://".explode (":",$_SERVER['HTTP_HOST'])[0]."/user_register_authentication.php?auth_code=".$service['authentication_code']."&name=".$service['name']."','',config='height=350,width=600');\">Activate Link</a><br></td>\n";
    $html.= "</tr>\n"; 
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

function createPwdRecoveryTable()
{
    $sql = "select c1.recovery_code as recovery_code, c1.user_id as user_id, c2.name as name, c1.request_date as request_date from isat.password_recovery as c1 left join isat.user as c2 on c1.user_id = c2.id";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for
  //input https://xpress.megasys.com.tw/password_recovery.php
  $html = "<b><a href=\"javascript: window.open('http://".explode(":",$_SERVER['HTTP_HOST'])[0]."/password_recovery.php','',config='height=350,width=600');\">recovery_code</a></b> Total: {$num}";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>recovery_code</td><td>user_id / name</td><td>request_date</td><td></td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['recovery_code']}</td>\n";
    $html.= "<td>{$service['user_id']} / {$service['name']}</td>\n";
    $reg_date = date ("Y-m-d H:i:s",$service['request_date']);
    $html.= "<td>{$reg_date}</td>\n";
    $html.= "<td><a href = \"javascript: window.open('http://".explode (":",$_SERVER['HTTP_HOST'])[0]."/password_recovery_authentication.php?recovery_code=".$service['recovery_code']."&name=".$service['name']."','',config='height=350,width=600');\">ForgetPwd Link</a><br></td>\n";
    $html.= "</tr>\n"; 
	}
  $html .= "</table>\n";   //add table end
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

<div align=center><b><font size=5>Mgmt Users</font></b></div>
<div id="container">
<?php
if (isset($msg_err))  echo $msg_err."<hr>";
if (isset($_REQUEST["debugadmin"])){ 
?>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
Display user name length =<input type=text  size=1 name=namelen value="<?php if (isset($_REQUEST['namelen'])) echo $_REQUEST['namelen']; else echo "12";?>">
<input type=hidden name=step value='username_length'>
<input type=hidden name=debugadmin value='1'>
<input type=submit name=btnAction2 value="List">
</form>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
Account <input type=text  size=3 name=accountrule value="<?php if (isset($_REQUEST['accountrule'])) echo $_REQUEST['accountrule']; else echo "[A-Z0-9]{7,15}";?>"> No activity over<input type=text  size=1 name=day value="<?php if (isset($_REQUEST['day'])) echo $_REQUEST['day']; else echo "90";?>"> days
<input type=hidden name=step value='user_display'>
<input type=submit name=btnAction2 value="display">
<?php if (isset($_REQUEST['day'])) { ?>
  <input type=submit name=btnAction2 value="cleanup">
<?php }?>
<br><small>[A-Z0-9]{7,15}   &nbsp;&nbsp;&nbsp;-?[0-9]+</small> 
<input type=hidden name=debugadmin value='1'>
</form>
<br>
<hr>
<?php } ?>
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
     createUserTable(0);
else if($_REQUEST['uid_filter'] =="(MORE)" )
     createUserTable(20);
else
  echo "user table:<br><p>";
     
?>
<form name=finduser method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<select name=TYPE>
<option value="name">name</option>
<option value="name like ">name like</option>
<option value="reg_email like">reg_email like</option>
<option value="id">id</option>
</select>
<input type=text name=VAL value='<?php if( isset($_REQUEST['VAL'])) echo $_REQUEST['VAL'];?>' placeholder='value'>
<input type=submit name=btnUserID value="Find User" class=btn_1>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
</form>
<br>
<?php
  createUserRegTable();
?>
<br>
<?php
  createPwdRecoveryTable();
?>
<br>
	</div>
</body>
</html>
