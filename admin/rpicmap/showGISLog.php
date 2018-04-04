<?php
/*************
 *Validated on Jul-18,2017,  
 * Log list OK
 * Query mac, ACNO, result OK  
 * check database customerservice.gis_log
 * move errMsg to _iveda.inc 
 *Writer : JinHo Chang  
 *************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
#Authentication Section same as Tech support right
//if ( $_SESSION["Email"]=="" )   exit();
if ( $_SESSION["Email"]=="" )   header("Location:".$home_url);
############  Authentication Section End
//$sql="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'customerservice'";
$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'customerservice' and TABLE_NAME='gis_log'";
sql($sql,$result,$num,0);
if ($num == 0){ 
	echo "<font color=red>gis_log TABLE Not Exist</font>";
	exit;
}

define("PAGE_LIMIT",50);
define("NON_LIMIT",200);

$mac = '';$vname='';$action='';$result='';
if(isset($_REQUEST['mac']))	$mac = $_REQUEST['mac'];
if(isset($_REQUEST['vname']))	$vname = $_REQUEST['vname'];
if(isset($_REQUEST['action']))	$action = $_REQUEST['action'];

if(isset($_REQUEST['result']))	$result = $_REQUEST['result'];


//$services = query($name);
//(mac,owner_id,owner_name,visitor_id,visitor_name,action,result,ip_addr) 
function createServiceTable(){
  global $mac,$vname , $action, $result;

	$sql = "select * from customerservice.gis_log";
	if($mac!='')
		$sql.=" where mac like '%$mac%'";
	if($vname!='')
    if ($mac!='')
      $sql.=" and ACNO like '%$vname%'";
		else $sql.=" where ACNO like '%$vname%'";
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
	$html .="<th>ID({$num})</th><th>MAC</th><th>Owner</th><th>ACNO</th>";
	$html .="<th>DIGADD (LAT;LNG;)</th>";
  $html .="<th>Action / Result</th><th>IP address, UserAgent</th><th>Timestamp</th></tr></thead><tbody>";
  		
  foreach($services as $service)
  {
		$html.= "\n<tr>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['mac']}</td>\n";
    $html.= "<td>{$service['bind_account']}</td>\n";
    $html.= "<td>{$service['ACNO']}</td>\n";
    if ($service['DIGADD']!="")
    	$html.= "<td>{$service['DIGADD']} (<a href='https://www.google.com.tw/maps/search/{$service['LAT']}+{$service['LNG']}' target=_blank>{$service['LAT']};{$service['LNG']}</a>)</td>\n";
  	else $html.="<td></td>\n";
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
</style>
	<div id="container"><font color=black>
		<form id="searchForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			MAC&nbsp;&nbsp;<input type="text"	size="10" name="mac" id="mac" value="<?php echo $mac?>">&nbsp;&nbsp;
      Action&nbsp;&nbsp;
      <select name="action" id="action">
      <option value="">(EMPTY)</option>
      <option value="ADD">ADD</option>
      <option value="UPDATE">UPDATE</option>
      <option value="DELETE">DELETE</option>
      <option value="VALIDATE">VALIDATE</option>
      <option value="ENABLE">ENABLE</option>
      <option value="DISABLE">DISABLE</option>
      </select>&nbsp;&nbsp;
			Result&nbsp;&nbsp;
      <select name="result" id="result">
      <option value="">(EMPTY)</option>
      <option value="FAIL">FAIL</option>
      <option value="SUCCESS">SUCCESS</option>
      </select>&nbsp;&nbsp;
			ACNO&nbsp;&nbsp;<input type="text"	size="10" name="vname" id="vname" value="<?php echo $vname?>">&nbsp;&nbsp;
			<input type="submit" value="Search">
NonLimited <input type="checkbox" name="NonLimited" value="Yes" <?php if ($_REQUEST["NonLimited"] == "Yes" ) echo "checked";?>> 
		</form>
<?php
echo "Latest Records (SEARCH {$mac}/{$vname}/{$action}/{$result}) :\n";
createServiceTable();
?>
	</font></div>
</body>
</html>