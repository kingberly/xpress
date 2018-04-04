<?php
/****************
 *Validated on Jul-3 ,2017,
 *share list multiple select table
 *parameter: owner, visitor, owner_mac, visitor_mac
 *session check   
 *Writer: JinHo, Chang 
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
header("Content-Type:text/html; charset=utf-8");
require_once ("share.inc"); //required sql api
#Authentication Section
//if( !isset($_SESSION["Contact"]) )   exit();
############  Authentication Section End
//created by web API cannot be deleted on Admin
//var_dump($_REQUEST);
if($_REQUEST["step"]=="device_share")
{
    if( isset($_REQUEST['btn_device_share']) ){
				foreach ($_REQUEST['owner_mac'] as $bmac){
	        $result = addShareDeviceAPI($bmac,$_REQUEST["visitor"]);
	        if ($result){
	          $msg_err .= "<font color=blue>新增分享{$bmac}至".$_REQUEST["visitor"]." 成功!</font><br>\n";
	        }else $msg_err .= "<font color=red>新增分享{$bmac}至".$_REQUEST["visitor"]." 失敗!</font><br>\n";
        }
    }else if( isset($_REQUEST['btn_device_unshare']) ){
    		$visitorid = getUserID($_REQUEST["visitor"]);
    		$tmp="";
    		foreach ($_REQUEST['visitor_mac'] as $vmac){
	        $result = deleteShareDeviceAPI($vmac,$visitorid);
	        if ($result){
	          $msg_err .= "<font color=blue>從".$_REQUEST["visitor"]."刪除分享{$vmac}成功!</font><br>\n";
	        }else $msg_err .= "<font color=red>從".$_REQUEST["visitor"]."刪除分享{$vmac}失敗!</font><br>\n";
        }
		 }else if( isset($_REQUEST['btn_device_status']) ){
		 		foreach ($_REQUEST['owner_mac'] as $bmac){
		 			$msg_err .= queryCamera($bmac);
		 		}
		 		foreach ($_REQUEST['visitor_mac'] as $vmac){
		 			$msg_err .= queryCamera($vmac);
		 		}
     }
}
function queryCamera($mac)
{
$resolutionNAME=[
"RVHI"=>"HD高畫質",
"RVME"=>"VGA一般畫質",
"RVLO"=>"QVGA手機畫質"
];
$dataplanNAME=[
	"AR"=>" 錄影 ",
	"LV"=>" 直播 ",
	"D"=>"停止服務",
	"EV"=>"事件錄影",
	"SR"=>"按時錄影"
];
	$sql = "select * from isat.stream_server_assignment where device_uid like '%{$mac}'";
	$html ="";
	sql($sql,$result,$num,0);
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $html .= "{$mac}: ".$resolutionNAME[$arr['purpose']]." ".$dataplanNAME[$arr['dataplan']]." {$arr['recycle']}天<br>";
  }
  return $html;
}
function getDeviceList($tagName, $account)
{
    $sql = "select mac_addr,name from isat.query_info where user_name = '{$account}' group by mac_addr";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}[]' multiple size='{$num}'  readonly='readonly'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($arr['name']!=$arr['mac_addr'])
      	$html.= "\n<option value='{$arr['mac_addr']}'>{$arr['mac_addr']} ({$arr['name']}) </option>";
      else $html.= "\n<option value='{$arr['mac_addr']}'>{$arr['mac_addr']}</option>";
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}

function getShareDeviceList($tagName, $owner, $visitor)
{
		$visitorid=getUserID($visitor);
		if ($visitorid < 0) return;
    $sql = "select mac_addr,name from isat.query_share where user_name = '{$owner}' and visitor_id={$visitorid} group by mac_addr";
    sql($sql,$result,$num,0);
   	$html = "<select name='{$tagName}[]' multiple  size='{$num}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $html.= "\n<option value='{$arr['mac_addr']}'>{$arr['mac_addr']}</option>";
    }//for

  $html .= "</select>\n";   //add table end
	echo $html;
}

?>
<html>
<head>
<title>Share</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script type="text/javascript">
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
</head>
<body>
<div id="container">
<?php
echo "<small>{$msg_err}</small>";
?>
<hr>
<form name=shareform method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=hidden name=step value='device_share'>
<table>
<tr><td>
租賃帳號<?php echo $_REQUEST['owner'];?>所屬攝影機:
</td><td>
</td><td>
公開帳號<?php echo $_REQUEST['visitor'];?>分享攝影機:
</td></tr>
<tr>
<td rowspan=3>
<?php
	getDeviceList("owner_mac",$_REQUEST["owner"]);
	echo "<input type=hidden name=owner value='".$_REQUEST["owner"]."'>";
?>
</td>
<td>
<input type=submit name=btn_device_share value="攝影機分享">
</td>
<td rowspan=3>
<?php
     getShareDeviceList("visitor_mac",$_REQUEST["owner"],$_REQUEST["visitor"]);
     echo "<input type=hidden name=visitor value='".$_REQUEST["visitor"]."'>";
?>
</td></tr>
<tr><td>
<input type=submit name=btn_device_unshare value="攝影機刪除分享">
</td></tr>
<tr><td>
<input type=submit name=btn_device_status value="攝影機狀態">
</td></tr>
</table>
</form>
</div>
</body>
</html>