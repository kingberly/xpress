<?php
/*************
 *Validated on Mar-16,2017,  
 * Log list OK
 * Query mac, visitor_name, result OK  
 * check database customerservice.share_log
 * Add Fail query options, fixed inquiry defects  
 * update jquery library to local js
 * move errMsg to _iveda.inc 
 *Writer : JinHo Chang  
 *************/
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
#Authentication
if (!isset($_SESSION["ID_admin_qlync"]) and !isset($_SESSION["ID_admin_oem"])) die("No Permission");
############  Authentication Section End
//$sql="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'customerservice'";
$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'customerservice' and TABLE_NAME='share_log'";
sql($sql,$result,$num,0);
if ($num == 0){ 
	echo "<font color=red>share_log TABLE Not Exist</font>";
	exit;
}
require_once '_iveda.inc';
define("PAGE_LIMIT",50);
define("NON_LIMIT",200);

$mac = '';
$vname='';
$result='';
if(isset($_REQUEST['mac'])){
	$mac = $_REQUEST['mac'];
}
if(isset($_REQUEST['vname'])){
	$vname = $_REQUEST['vname'];
}
if(isset($_REQUEST['action'])){
	$action = $_REQUEST['action'];
}
if(isset($_REQUEST['result'])){
	$result = $_REQUEST['result'];
}

//$services = query($name);
//(mac,owner_id,owner_name,visitor_id,visitor_name,action,result,ip_addr) 
function createServiceTable(){
  global $mac,$vname , $action, $result;

	$sql = "select * from customerservice.share_log";
	if($mac!='')
		$sql.=" where mac like '%$mac%'";
	if($vname!='')
    if ($mac!='')
      $sql.=" and visitor_name like '%$vname%'";
		else $sql.=" where visitor_name like '%$vname%'";
	if($action!='')
    if ( ($mac!='') or ($vname!=''))
      $sql.=" and action like '%$action%'";
		else $sql.=" where action like '%$action%'";
	if($result!='')
    if ( ($mac!='') or ($vname!='')or($action!=''))
        $sql.=" and result like '$result%'";
		else $sql.=" where result like '$result%'";
  if ($_REQUEST["NonLimited"] == "Yes" )
      $sql .=" order by ts DESC limit ".NON_LIMIT;
  else  $sql .=" order by ts DESC limit ".PAGE_LIMIT;
	//$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);

	$services = array();

	for($i=0;$i<mysql_num_rows($result);$i++){
    fetch($arr,$result,$i,0);
		$services[$i] = $arr;
	}
//  $html = "<font color=black>Total ".mysql_num_rows($result) ."<br>";
	$html .= '<table id="tbl" style="margin-top: 5px"><thead><tr>';
	$html .="<th>ID ({$num})</th><th>MAC</th><th>所屬帳號</th><th>分享/工地帳號</th>";
  $html .="<th>指令 / 結果</th><th><div class=\"tooltip\">IP address<span class=\"tooltiptext\">192.168.1.100/101/102為負載平衡IP</span></div>, UserAgent</th><th>時間</th></tr></thead><tbody>";
  		
  foreach($services as $service)
  {
		$html.= "\n<tr>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['mac']}</td>\n";
    $html.= "<td>({$service['owner_id']}) {$service['owner_name']}</td>\n";
    $html.= "<td>({$service['visitor_id']}) {$service['visitor_name']}</td>\n";
    if ( ($service['result'] == "SUCCESS") or (getErrorMsg($service['result'])=="") )
    	$html.= "<td>{$service['action']} / {$service['result']}</td>\n";
    else {
			$html.= "<td>{$service['action']} / <div class=\"tooltip\">{$service['result']}<span class=\"tooltiptext\">".getErrorMsg($service['result'])."</span></div></td>\n";
		}
    $html.= "<td>{$service['ip_addr']}";
    if (isset($service['user_agent']) )
    	$html.= ", {$service['user_agent']}</td>\n";
    else $html.= "</td>\n";
    $html.= "<td>{$service['ts']}</td>\n";
    $html.= "</tr>\n";
	}
  $html .="</tbody></table></font>";
	echo $html;
}
function getErrorMsg($tag)
{
	  global $errorMSG;
	  foreach ($errorMSG as $key => $value){
	  	if ($key==$tag)
	  		return $value;
		}
		return "";
}
function printErrorCode()
{
  global $errorMSG;
  $html = '<select>';
  foreach ($errorMSG as $key => $value)
  {
      $html .= "<option>{$key}={$value}</option>";
  }
  $html .= '</select>';
   echo $html;
}
?>

<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
<style>
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}

/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 200px;
    background-color: black;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;
 
    /* Position the tooltip text - see examples below! */
    position: absolute;
    z-index: 1;
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
}
</style>
	<div id="container"><font color=black>
		<form id="searchForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			MAC&nbsp;<input type="text"	size="10" name="mac" id="mac" value="<?php echo $mac?>">&nbsp;&nbsp;
      指令
      <select name="action" id="action">
      <option value=""></option>
      <option value="ADD">新增分享</option>
      <option value="DELETE">刪除分享</option>
      <option value="ADD S_ACCT">新增工地帳號</option>
      <option value="share_list">分享帳號清單API</option>
      <option value="owner_list">所屬帳號清單API</option>
      <!--option value="enable_map">公開圖資</option-->
      <!--option value="disable_map">不公開圖資</option-->
      </select>&nbsp;&nbsp;
			結果
      <select name="result" id="result">
      <option value=""></option>
      <option value="FAIL">失敗</option>
      <option value="SUCCESS">成功</option>
  <?php 
  foreach ($errorMSG as $key => $value)  {
      echo "<option value='{$key}'>{$key}</option>";
  }
  ?>
      </select>&nbsp;&nbsp;
			分享/工地帳號
      <input type="text"	size="10" name="vname" id="vname" value="<?php echo $vname?>">&nbsp;&nbsp;
      搜尋不限<?php echo PAGE_LIMIT;?>筆<input type="checkbox" name="NonLimited" value="Yes" <?php if ($_REQUEST["NonLimited"] == "Yes" ) echo "checked";?>>
&nbsp;&nbsp;
			<input type="submit" value="搜尋"><br>
      錯誤碼列表:<?php printErrorCode();?> 
		</form>
<?php
echo "<h3>紀錄 (搜尋 {$mac}/{$vname}/{$action}/{$result}) :</h3>\n";
createServiceTable();
?>
	</font></div>
</body>
</html>