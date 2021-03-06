<?php
/****************
 *Validated on Nov-10,2016
 *check user exist before update camera type
 *obosolete after isat DB fixed.  
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");

//if( !isset($_SESSION["Contact"]) )   exit();


  if(isset($_REQUEST["btnActionEdit"])){
    $uresult =updateDeviceNameByUID($_REQUEST['uid'],"name",mysql_real_escape_string($_REQUEST['name']));
    if ($uresult) $msg_err .= "<br><font color=blue>更新攝影機".$_REQUEST['deviceid'].$_REQUEST['uid']."名稱 ".$_REQUEST["name"]. " 成功!</font>";
    else $msg_err .= "<br><font color=red>更新攝影機".$_REQUEST['deviceid'].$_REQUEST['uid']."名稱 ".$_REQUEST["name"]. " 失敗!</font>";
  }
if($_REQUEST["step"]=="editdevice"){
    ////load value
  if (isset($_REQUEST["deviceid"]) ){
      $deviceid= $_REQUEST["deviceid"];
      $sql = "select * from isat.device where id={$deviceid}";
      sql($sql,$result,$num,0);
      fetch($arr,$result,0,0);
      $name = $arr['name'];
      $uid = $arr['uid'];
  }

}




function updateDeviceByField($id,$type,$value)
{
  if ($type == "group_id" )
    $sql = "update godwatch.gw_device set {$type}={$value} where id={$id}";
  else
    $sql = "update godwatch.gw_device set {$type}='{$value}' where id={$id}";
  sql($sql,$result,$num,0);
  if ($result) return true;
  return false;
}

function updateDeviceNameByUID($uid,$type,$value)
{
  /*if ($type =="name"){
    $sql = "select name from isat.device where uid='{$uid}'";
    sql($sql,$result,$num,0);
    fetch($arr,$result,0,0);
    if ($arr['name']== $value) return true;
  } */

  $sql = "update isat.device set {$type}='{$value}' where uid='{$uid}'";
  sql($sql,$result,$num,0);
  if ($result) return true;
  return false;
}

function selectDeviceUid($tagName)
{
    $sql = "select DISTINCT c1.uid,c1.owner_id,c2.name as owner_name from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id order by c1.uid";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    $html.= "\n<option value=''>--NA--</option>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
        $arr['owner_id_name'] = $arr['owner_id']. " : " .$arr['owner_name'];
        $html.= "\n<option value='{$arr['uid']}'>{$arr['uid']} ({$arr['owner_id_name']})</option>";
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectCOMMDeviceUid($tagName)
{
  global $GWMACArray;
  $usedCOMM = array();
    $sql = "select uid from godwatch.gw_device where device_type='".TYPE_COMM."'";
    sql($sql,$result,$num,0);
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $usedCOMM[] = $arr['uid'];
    }

    $html = TYPE_COMM_TEXT." <select name='{$tagName}'>";
    $html.= "\n<option value=''>--NA--</option>";
    foreach($GWMACArray as $mac => $username) {
        if (!in_array($mac,$usedCOMM))
          $html.= "\n<option value='{$mac}'>{$mac}</option>";
        else
          $html.= "\n<option value='{$mac}' disabled>{$mac}</option>";
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}


function insertGWDevice($uid,$gid,$dtype)
{
    $sql = "select name from isat.device where uid='{$uid}'";
    sql($sql,$result,$num,0);
    if ($num >0){
      fetch($arr,$result,0,0);
      $name = $arr['name'];
    }else $name="佈告欄";                                                            
    if ($gid == "") $gid ="0";
    $sql = "insert into godwatch.gw_device (name,uid,group_id,device_type) values ('{$name}','{$uid}',{$gid},'{$dtype}')";

    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function isDeviceExist($uid)
{
    //check uid before call this function??
    $sql = "select * from godwatch.gw_device where uid='{$uid}'";
    sql($sql,$result,$num,0);
    if ($num > 0) {
        return true;
    } 
    return false;
}

function isCOMMDeviceSet($gid)
{
    if ($gid=="") return true; //check gid before call this function??
    $sql = "select uid from godwatch.gw_group where id={$gid}";
    sql($sql,$result,$num,0);
    if ($num > 0){
      fetch($arr,$result,0,0);
      if ($arr['uid']!="") return true;
    }
    return false;
}
function updateGWDeviceByField($id, $type, $value)
{
    if ($type == "group_id")
      $sql = "update godwatch.gw_device set {$type}={$value} where id={$id}";
    else
      $sql = "update godwatch.gw_device set {$type}='{$value}' where id={$id}";
    sql($sql,$result,$num,0);

    if ($result) return true;
    return false;
}

function updateGWDevice($id, $name, $gid)
{
  //does not replace device
    $sql = "update godwatch.gw_device set name='{$name}' , group_id={$gid} where id={$id}";
    sql($sql,$result,$num,0);

    if ($result) return true;
    return false;
}
function deleteGWDevice($id)
{
  //cleanup common device from group
    $sql = "select group_id,uid from godwatch.gw_device where id={$id} and device_type='".TYPE_COMM."'";
    sql($sql,$result,$num,0);
    if ($num > 0){
      fetch($arr,$result,0,0);
      if ($arr['group_id'] !="0"){  
        $sql = "update godwatch.gw_group set uid='' where id={$arr['group_id']}";
        sql($sql,$result,$num,0);
      }
    }
   
    $sql = "delete from godwatch.gw_device where id={$id}";
    sql($sql,$result,$num,0);

    if ($result) return true;
    return false;
}

function selectGWTypeList($tagName, $type)
{
  global $GWTYPE;
  $html = "<select name='{$tagName}'>";
  if ($type ==""){
    $html.= "\n<option value='' selected>--NA--</option>";
    foreach ($GWTYPE as $key => $val)
      if ($key!=TYPE_COMM)//ignore common camera
          $html.= "\n<option value='".$key."' >".$val."</option>";
  }else{
    foreach ($GWTYPE as $key => $val)
      if ($type == $key)
          $html.= "\n<option value='".$key."' selected>".$val."</option>";
      else
          if ($key!=TYPE_COMM)//ignore common camera
          $html.= "\n<option value='".$key."' >".$val."</option>";
  }
  $html .= "</select>\n";   //add table end
	echo $html;
}


function setDatabaseStrByArray ($table, $key, $value)
{
global $GWMACArray;
  $param=" where (";
  foreach($GWMACArray as $mac => $username) {
    $param.= " device_uid='$mac' OR";
  }
  $param=rtrim($param," OR");
  $param.=" )";

   $sql="update isat.{$table} set {$key} = '{$value}' {$param}";
   sql($sql,$result,$num,0);
   if ($result) return true;
    return false;
}
////////////////////above from mgmt_camlic.php
function createDeviceTable($whereParam)
{
    //$sql = "select * from isat.device where mac_addr<>name {$whereParam} group by mac_addr";
    $sql = "select c1.id,  c1.uid, c1.mac_addr, c1.name as camera_name, c1.owner_id, c2.name as owner_name, c2.group_id,c2.oem_id from isat.device as c1 left join isat.user as c2 on c1.owner_id = c2.id where c1.mac_addr<>c1.name group by c1.mac_addr";
    //$sql = "select c1.*, c2.name as groupname from godwatch.gw_device as c1 left join godwatch.gw_group as c2 on c1.group_id = c2.id {$whereParam} order by group_id";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for

  $html = "<table id='tbl2' class=table_main><tr class=topic_main><td>ID</td><td>MAC</td><td>攝影機名稱</td><td>擁有者</td><td>宮廟</td><td colspan=3></td></tr>";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['mac_addr']}</td>\n";
    $html.= "<td>{$service['camera_name']}</td>\n";
    $html.= "<td>{$service['owner_name']}</td>\n";
    $html.= "<td>{$service['group_id']}/{$service['oem_id']}</td>\n";
    $html.= "<td><form name=editg action=\"godwatch_device.php\" method=POST><input type=hidden name=step value='editdevice'><input type=hidden name='deviceid' value=\"{$service['id']}\" ><input type='submit' value='編輯'></form></td>\n";
    $html.= "<td><input type=button value='送訊息' onclick=\"window.location='godwatchq_msg.php?mac={$service['mac_addr']}';\" ></td>\n";
    $html.= "<td></td>\n";
	}
  $html.= "</tr></table>\n";
	echo $html;
}
?>

<html>
<head>
<meta http-equiv="Content-Language" content="utf-8">
<meta http-equiv="Content-Type" content="text/javascript; charset=utf-8">
<meta http-equiv="Page-Exit" content="revealTrans(Duration=4.0,Transition=2)">
<meta http-equiv="Site-Enter" content="revealTrans(Duration=4.0,Transition=1)">
<link href="/css/layout.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/menu.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />
</head>
<body>
<div align=center><b><font size=5>攝影機資料 (DB中文顯示已修正)</font></b></div>
<div class="bg_top mgtop22"></div>
<div id="container">
<form id="group1" method="post"	action="<?php echo $_SERVER['PHP_SELF'];?>">
<table id='tbl1' class=table_main>
<tr class=topic_main>
            <td>ID</td>
						<td>攝影機名/MAC</td>
						<td></td>
					</tr>
<tr class=tr_2>
<td>
<?php
if (isset($deviceid))
  echo "<input type=hidden name=deviceid value='{$deviceid}'>{$deviceid}";
?>
</td>
<td>
<?php
if (isset($deviceid)){
  echo "<input type=\"text\" size=\"32\" name=\"name\" value=\"{$name}\" ><input type=hidden name=uid value='{$uid}'><br> {$uid}";
}
?>
</td>
<td>
<?php if (isset($deviceid)) {?>
<input type=submit name=btnActionEdit value="編輯攝影機" class="btn_2">
<br>
<?php }?>
</td>
</tr>
</table>
</form>
<HR>
<?php
echo $msg_err."<br><p>";
createDeviceTable("");
?>
</div> 
</body>
</html>
 