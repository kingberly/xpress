<?php
/****************
 *Validated on Apr-11,2016
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");
define("LOGDB","qlync.push_log");
define("INBOX","isat.inbox_message");

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

if($_REQUEST["step"]=="sendmsg"){
  if(isset($_REQUEST["btnActionSendShare"])){
      $msg_err = sendGroupMsg($_REQUEST['mac'],$_REQUEST['msg'],"to_group");
   }else if(isset($_REQUEST["btnActionSendUser"])){
      $msg_err = sendGroupMsg($_REQUEST['mac'],$_REQUEST['msg'],"to_user");
   }else if(isset($_REQUEST["btnActionSendUID"])){
      $msg_err = sendGroupMsg($_REQUEST['mac'],$_REQUEST['msg'],"to_uid");
  }else if(isset($_REQUEST["btnActionDelMsg"])){
      $delList ="";
      foreach ($_REQUEST['inbox'] as $selectedOption){      
        $result =deleteInboxMsg($selectedOption);
        $delList.=$selectedOption.",";
      }
      //$result =deleteInboxMsg($_REQUEST['inbox']);
      if ($result) $msg_err = "<font color=blue>刪除訊息 ".$delList." 成功!</font>";
      else $msg_err = "<font color=red>刪除訊息 ".$delList." 失敗!</font>";
  }
}

function sendGroupMsg($mac, $msg, $type)
{
  global $oem, $api_id, $api_pwd, $api_path;
define("MCID","M04CC");
define("MMAC_PREFIX","184E");
define("ZCID","Z01CC");
define("ZMAC_PREFIX","001B");
define("IMCID",$oem."MC");
define("IMMAC_PREFIX","M".$oem);
  $MsgList = "";

	$t=explode("/",$api_path);
  if (!ereg("[A-Za-z0-9]{12}",str_replace("-","",str_replace(":","",$mac))))
    return $Msg="<font color=red>Error MAC</font>";

   switch ($type)
   {
      case ("to_uid"): //jinho add debug API
        if ( preg_match("/^".MMAC_PREFIX."/",strtoupper($mac)) )
          $uid = MCID. "-".strtoupper($mac);
        else if ( preg_match("/^".ZMAC_PREFIX."/",strtoupper($mac)) )
          $uid = ZCID. "-".strtoupper($mac);
        else if ( preg_match("/^".IMMAC_PREFIX."/",strtoupper($mac)) )
          $uid = IMCID. "-".strtoupper($mac); 
        $import_target_url="https://{$t[0]}/push_notify/push.php?cmd=SMSG&deviceuid={$uid}&msg=".urlencode($msg)."";
        $MsgList=curlSendReq($import_target_url,$uid);
      case ("to_user"):
        $sql_tmp="select count(ID) as c,Name from qlync.account_device where Mac='".strtoupper(str_replace("-","",str_replace(":","",$mac)))."' limit 0,1"; 
        sql($sql_tmp,$result_tmp,$num_tmp,0);
        if ($result_tmp){
          fetch($arr,$result_tmp,0,0);
          $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$arr["Name"]}&msg=".urlencode($msg)."";
          $MsgList=curlSendReq($import_target_url,$arr["Name"]);
        }else $MsgList="<font color=red>Query DB Fail!</font>";
        break;
      case ("to_camera"):
        break;
      case("to_all"):
        break;
      case("to_group"):
        //to share camera user 
        $sql_tmp="select device_share.uid, device_share.visitor_id,user.id,user.name from isat.device_share,isat.user where device_share.uid like '%".str_replace("-","",str_replace(":","",$mac))."' and device_share.visitor_id=user.id";
        sql($sql_tmp,$result_tmp,$num_tmp,0);
        for($k=0;$k<$num_tmp;$k++)
        {
                fetch($db_tmp,$result_tmp,$k,0);
                $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db_tmp["name"]}&msg=".urlencode($msg)."";
                $MsgList.=curlSendReq($import_target_url,$db_tmp["name"]);
        }
        //to camera owner
        //$import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db["Name"]}&msg=".urlencode($_REQUEST["msg"])."";
        //curlSendReq($import_target_url,$db["Name"]);
        break;
   }  
//$_SESSION["CID"]=="N99"
    $sql="insert into qlync.push_log (Oem_id, Cmd, Msg, Time_s1,Email) values ('X02','{$type}','".urlencode($msg)."','".date("Ymd H:i:s")."','jinho.chang@tdi-megasys.com')";
    sql($sql,$logresult,$num,0);

  return "<small>".$MsgList."</small>";
}

function curlSendReq($url,$tag)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);

    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

    $result=curl_exec($ch);
    curl_close($ch);
        // JSON Decoded Array to $content
    $content=array();
    $content=json_decode($result,true);
  //print_r($content);
    if($content["status"]=="success")
    {
            return " <font color=blue>Successfully Send to {$tag}!</font><br>";
    }

}
function deleteInboxMsg($id)
{
    $sql = "delete from isat.inbox_message where id={$id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function createList($tagName, $dbtable)
{
    $sql = "select * from {$dbtable} order by id desc limit 100";
    
    if ($dbtable == LOGDB){
    sql($sql,$result,$num,0);
    $html = "<select multiple name='{$tagName}' style='width: 300px;height: 300px;'>";
    }else if ($dbtable == INBOX){  //for pass multiple value
      $arrs =myPDOQuery($sql);
      $num=sizeof($arrs);
      $html = "<select multiple name='{$tagName}[]' style='width: 300px;height: 500px;'>";
    }

    for($i=0;$i<$num;$i++){
      
      if ($dbtable == LOGDB){
        fetch($arr,$result,$i,0);
        $msg = urldecode($arr['Msg']);
        //$html.= "\n<option value='{$arr['ID']}'>{$arr['Time_s1']}:{$msg}</option>";
        $html.= "\n<option value='{$msg}' ondblclick=\"this.form.msg.value=this.form.".$tagName.".value;\">{$arr['Time_s1']}({$arr['Cmd']}):{$msg}</option>";
      }else if ($dbtable == INBOX){
        $arr = $arrs[$i];
        //remove text after: @this.text???
        //$html.= "\n<option value='{$arr['id']}' ondblclick=\"this.form.msg.value=this.form.".$tagName.".value;\">{$arr['create_ts']}:{$arr['message']}</option>";
        $html.= "\n<option value='{$arr['id']}' ondblclick=\"this.form.msg.value=this.text;\">{$arr['create_ts']}({$arr['owner_name']}):{$arr['message']}</option>";
      }
    }
    $html .= "</select>\n";   //add table end
    echo $html;
}
?>

<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>App訊息管理</font></b></div>
<div id="container">
<?php
if (isset($msg_err))
  echo $msg_err."<hr>";
?>
<table id='tbl1' class=table_main>
<tr class=topic_main>
<td width=400>送訊息</td><td>信件匣管理</td>
</tr>
<tr class=tr_2><td>
<form id="group1" method="post"	action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=hidden name=step value='sendmsg'>
MAC: <input type=text name=mac value='<?php if (isset($_REQUEST["mac"])) echo $_REQUEST["mac"];?>'>
<input type=button value='選攝影機' onclick="window.location='godwatchq_share.php';" class='btn_2'>
<br><br>只支援最多30中文字<br>
<textarea rows=2 cols=50 name=msg>
<?php if (isset($_REQUEST["msg"])) echo $_REQUEST["msg"]; ?>
</textarea>
<input type=submit name=btnActionSendShare value="送訊給分享信眾" class="btn_1">
<br>
<?php   if (isset($_REQUEST["debugadmin"])){?>
<input type=submit name=btnActionSendUID value="送訊給AppViewer使用者" class="btn_1">
<input type=hidden name=debugadmin value='1'>
<?php   }?>
<br>
<hr>
<h3>送訊紀錄100筆</h3>
<?php
createList("log",LOGDB); 
?>
</td>
<td>
<?php   if (isset($_REQUEST["debugadmin"])){?>
<input type=submit name=btnActionDelMsg value="刪除" class="btn_2">
<?php   }?>
<br></p>
<?php
createList("inbox",INBOX); 
?>
</form>
</td>
</tr>
</table> 
</div>
</body>
</html>