<?php
/*************
 *Validated on May-22,2017,  
 * Log list OK
 * Query mac, visitor_name, result OK  
 * check database customerservice.api_log
 * Add Fail query options, fixed inquiry defects  
 * update jquery library to local js
 * Check DB table existence 
 *Writer : JinHo Chang  
 *************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
#Authentication Section same as Tech support right
$sql="select * from qlync.menu where Name = 'FAQ'";
#same as Super admin
#$sql="select * from qlync.menu where Name = 'Login Log'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
        if($_SESSION["{$db["Fright"]}_qlync"] ==1)
        {
                $right+=1;
                if($db["Oem"] == "0")
                {
                        $oem_id="N99";
                }
                if($db["Oem"] == "1" and $oem_id == "")
                {
                        $oem_id=$_SESSION["CID"];
                }
        }
}
if($right  == "0")
        exit();
############  Authentication Section End
$pagelimit = 50;
$mac = '';
$vname='';
$result='';
if(isset($_REQUEST['api'])){
	$api = $_REQUEST['api'];
}

if(isset($_REQUEST['action'])){
	$action = $_REQUEST['action'];
}
if(isset($_REQUEST['result'])){
	$result = $_REQUEST['result'];
}

//$sql="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'customerservice'";
$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'customerservice' and TABLE_NAME='api_log'";
sql($sql,$result,$num,0);
if ($num == 0){ 
	echo "<font color=red>api_log TABLE Not Exist</font>";
	exit;
}
//$services = query($name);
//(mac,owner_id,owner_name,visitor_id,visitor_name,action,result,ip_addr) 
function createServiceTable(){
  global $pagelimit,$api, $action, $result;

	$sql = "select * from customerservice.api_log";
	if($api!='')
		$sql.=" where api like '%$api%'";
	if($action!='')
    if ($api!='') 
      $sql.=" and action like '%$action%'";
		else $sql.=" where action like '%$action%'";
	if($result!='')
    if ( ($api!='') or($action!=''))
        $sql.=" and result like '$result%'";
		else $sql.=" where result like '$result%'";
  if ($_REQUEST["NonLimited"] == "Yes" )
      $sql .=" order by ts DESC";
  else
  $sql .=" order by ts DESC limit $pagelimit";
	//$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);
  if ($_REQUEST["NonLimited"] == "Yes" ) //query too much data
           if (mysql_num_rows($result) > 200){
              $sql .=" limit 200";
              //$result=mysql_query($sql,$link);
              sql($sql,$result,$num,0);
           }

	$services = array();
	$index = 0;
	for($i=0;$i<mysql_num_rows($result);$i++){
		$index = $i+1;
      fetch($arr,$result,$i,0);
			$index++;
		$services[$index] = $arr;
	}
//  $html = "<font color=black>Total ".mysql_num_rows($result) ."<br>";
  $html = "<font color=black>PAGE Limit {$pagelimit}<br>";
	$html .= '<table id="tbl" style="margin-top: 5px"><thead><tr>';
  $html .="<th>ID</th><th>API</th><th>Action / Result</th><th>IP address, UserAgent</th><th>Timestamp</th></tr></thead><tbody>";
  		
  foreach($services as $service)
  {
		$html.= "\n<tr>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['api']}</td>\n";
    $html.= "<td>{$service['action']} / {$service['result']}</td>\n";
    $html.= "<td>{$service['ip_addr']}";
    $html.= ", {$service['user_agent']}</td>\n";
    $html.= "<td>{$service['ts']}</td>\n";
    $html.= "</tr>\n";
	}
  $html .="</tbody></table></font>";
	echo $html;
}

?>

<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
	<div id="container">
		<form id="searchForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
      API&nbsp;&nbsp;
      <select name="api" id="api">
      <option value="">(EMPTY)</option>
      <option value="bind_camera.php">bind_camera.php</option>
      <option value="unbind_camera.php">unbind_camera.php</option>
      <option value="delete_camera.php">delete_camera.php</option>
      <option value="getinfo_camera.php">getinfo_camera.php</option>
      <option value="mgmt_enduser.php">mgmt_enduser.php</option>
      </select>&nbsp;&nbsp;
			Result&nbsp;&nbsp;
      <select name="result" id="result">
      <option value="">(EMPTY)</option>
      <option value="FAIL">FAIL</option>
      <option value="SUCCESS">SUCCESS</option>
      </select>&nbsp;&nbsp;
			<input type="submit" value="Search">
NonLimited <input type="checkbox" name="NonLimited" value="Yes" <?php if ($_REQUEST["NonLimited"] == "Yes" ) echo "checked";?>> 
		</form>
    <h3>Latest Records  
    <?php if($api!='') echo "(Found {$api}) ";if($action!='') echo "(Found {$action}) ";if($result!='') echo "(Found {$result}) ";?>  :</h3><br>
					<?php createServiceTable()?>
	</div>
</body>
</html>