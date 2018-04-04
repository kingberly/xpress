<?php
/****************
 *Validated on Jun-16,2017,
 *	Input parameter: key=KZo3i6UJbKd0bb6B5Suv, oemid  
 *  debug parameter: debugadmin         
 *Writer: JinHo, Chang
*****************/
require_once("rpic.inc");
header("Content-Type:application/json; charset=utf-8");
if (file_exists("/var/www/qlync_admin/doc/config.php")){ //admin oemid sql
	require_once ("/var/www/qlync_admin/doc/config.php");
	include("/var/www/qlync_admin/doc/mysql_connect.php"); 
	include("/var/www/qlync_admin/doc/sql.php");
}else exit(1);
	
if (isset($_REQUEST['oemid'])) define ("OEM_ID", strtoupper($_REQUEST['oemid']));
else if (isset($argv[1]))	define ("OEM_ID", strtoupper($argv[1]));
else define ("OEM_ID", $oem);
if (!isset($argv[1])) //from http access must check key
	if ( ($_REQUEST['key']!="KZo3i6UJbKd0bb6B5Suv")) exit(1);

if (isset($_REQUEST['debugadmin'])) var_dump($_REQUEST);
 
$mapData = array();
parseDB2MapArray(OEM_ID, $mapData); 

function getCameraCount($type="SHARE",$username)
{
//"select count(distinct mac_addr) as num  from isat.query_share where user_name='{$username}'";//share to others count
	if ($type=="SHARE")
	$sql = "select count(distinct mac_addr) as num from isat.query_share as c1 left join isat.user as c2 on c1.visitor_id=c2.id where c2.name='{$username}'"; 
	else	$sql = "select count(distinct mac_addr) as num from isat.query_info where user_name='{$username}'";
	sql($sql,$result,$num,0);
	if ($result) return $num;
	return 0;
}
function parseDB2MapArray($oemid,&$mapData)
{
	global $RPICAPP_USER_PWD;
	if ($oemid =="DEMO")
		$sql = "select * from customerservice.workeyegis where OEM_ID NOT IN ('X02','T06') and (user_name!='') and is_public=1";
	else	$sql = "select * from customerservice.workeyegis where OEM_ID='{$oemid}' and (user_name!='') and is_public=1";
  sql($sql,$result,$num,0);
  if ($num > 0)
	for($i=0;$i<$num;$i++){
		fetch($arr,$result,$i,0);
		if ($oemid =="DEMO"){
			$myGIS= array(
					"OEM_ID" =>  $arr['OEM_ID'],
		      "ACNO" => $arr['ACNO'],
		      "PURP" => $arr['PURP'],
		      "APNAME" => $arr['APNAME'],
		      "DIGADD" => $arr['DIGADD'],
		      "TCNAME" => $arr['TCNAME'],
		      "TC_TEL" => $arr['TC_TEL'],
		      "LAT" => $arr['LAT'],
		      "LNG" => $arr['LNG'],
		      "VIDEONO" => 1,
		      "APPMODE" => $arr['APPMODE'],
					"URL" => $arr['URL'],
		      "user_name" => $arr['user_name'],
		      "user_pwd" => $arr['user_pwd'],
		      ); 
		} else { 
			$myGIS= array(
					//"OEM_ID" =>  $oemid,
		      "ACNO" => $arr['ACNO'],
		      "PURP" => $arr['PURP'],
		      "APNAME" => $arr['APNAME'],
		      "DIGADD" => $arr['DIGADD'],
		      "TCNAME" => $arr['TCNAME'],
		      "TC_TEL" => $arr['TC_TEL'],
		      "LAT" => $arr['LAT'],
		      "LNG" => $arr['LNG'],
		      "VIDEONO" => getCameraCount("SHARE",$arr['user_name']),
		      "APPMODE" => $arr['APPMODE'],
					"URL" => $RPICAPP_USER_PWD[$oemid][0],
		      "user_name" => $arr['user_name'],
		      "user_pwd" => $arr['user_pwd'],
		      );
		}
		array_push($mapData,$myGIS);
	}//for
}
echo json_encode($mapData); 
?>
