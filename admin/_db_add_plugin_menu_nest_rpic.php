<?php
//configuration of menu insertion
header("Content-Type:text/html; charset=utf-8");
require_once ("_db_add_plugin_menu.inc");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
$right_tree_flag=1;  //1 means it will set right for each admin
if ($oem=="X02") define("RPIC_MENU","Camera Lease Mgmt.");
else define("RPIC_MENU","RPIC Mgmt.");
define("RPIC_OID","99"); //main menu

$rpicID=checkMenuID($link,RPIC_MENU); //super admin
if (is_null($rpicID)){
addMainMenu($link, RPIC_MENU,RPIC_OID);
$rpicID=checkMenuID($link,RPIC_MENU);
}else updateMainMenu($link,$rpicID);

addSubMenuRight($link,"ID_admin_oem",$rpicID,"1");

$myAID=2; //[ 2 ] Security Monitoring

$menuGroup = "rpic";
if (!file_exists("/var/www/qlync_admin/plugin/".$menuGroup))
  if (file_exists("/var/www/qlync_admin/plugin/taipei"))
    $menuGroup = "taipei";
  if (file_exists("/var/www/qlync_admin/plugin/ty"))
    $menuGroup = "ty";
$pluginPath = "/var/www/qlync_admin/plugin/{$menuGroup}/";
$menupos=820;
$menupos = getMAXOID($rpicID,$menupos,$link);
//55 support, 77 webmaster, 6 License ,12 user=checkMenuID($link,"Users"), 
$menuArray = [ //name must different as key, URL limit 48char
  "Godwatch Mgr." => array("/plugin/debug/godwatchq.php",$rpicID,"ID_admin"),
  "Workeye Map Mgr." => array("/plugin/{$menuGroup}/showGIS.php",$rpicID,"ID_admin",[array("ID_03",$myAID)] ),//set to SID2
  "Workeye Map Log" => array("/plugin/{$menuGroup}/showGISLog.php",$rpicID,"ID_admin"),
  "Workeye Map" => array("/plugin/{$menuGroup}/workeyemap.php",$rpicID,"ID_admin_oem",[array("ID_03",$myAID)]),//set to SID2
//////////customer ID_03 menu
  "Online Device" => array("/plugin/{$menuGroup}/online_list.php",$rpicID,"ID_02",[array("ID_03",$myAID)]),//set to SID2
  "Account Device" => array("/plugin/{$menuGroup}/account_list.php",$rpicID,"ID_02",[array("ID_03",$myAID)]), //set to SID2
//////////          
  "RPIC Account Mgr." => array("/plugin/{$menuGroup}/rpic_cht.php",$rpicID,"ID_oem_admin"),
  " RPIC Account Mgr." => array("/plugin/{$menuGroup}/taipeiproj_cht.php",$rpicID,"ID_fae"),
  "Shared Online Device" => array("/html/member/online_list_share.php",checkMenuID($link,"Users"),"ID_06",[array("ID_06",$myAID)]),
  "Shared Account Device" => array("/html/member/account_list_share.php",checkMenuID($link,"Users"),"ID_06",[array("ID_06",$myAID)]),
  "Pipe Unit Mgr." => array("/plugin/{$menuGroup}/showPipeUnit.php",$rpicID,"ID_admin"),
  "NAS MountPoint Mgr." => array("/plugin/{$menuGroup}/cmdweb.php",$rpicID,"ID_admin_oem"),
  "App Download" => array("/plugin/{$menuGroup}/appdownload.php",$rpicID,"ID_none",[array("ID_03",$myAID)]),  //set to SID2
  "Documentation" => array("/html/faq/turtorial_rpic.php",$rpicID,"ID_none",[array("ID_03",$myAID)]),  //set to SID2
  "Share Log" => array("/plugin/{$menuGroup}/showShareLog.php",$rpicID,"ID_admin"),
  "Tunnel Usage Report" => array("/plugin/{$menuGroup}/tunnel_connlog_Excel.php",$rpicID,"ID_admin"),
  "Usage Report" => array("/plugin/{$menuGroup}/tunnelconn_Excel.php",$rpicID,"ID_fae"),
  "RPIC Maintain Mgr." => array("/plugin/debug/maintain.php",$rpicID,"ID_admin_oem"),
  "Device Usage Report" => array("/plugin/{$menuGroup}/device_status.php",$rpicID,"ID_admin"),
  "Video TimeTable" => array("/plugin/{$menuGroup}/rec_timetable.php",$rpicID,"ID_admin",[array("ID_03",$myAID)]),
  "APP Download State" => array("/plugin/{$menuGroup}/rpicappstat.php",$rpicID,"ID_admin"),
  "Traccar" => array("/plugin/debug/traccar.php",$rpicID,"ID_admin")
];
//$admin_level = "ID_fae"; // ID_admin, ID_admin_oem , ID_fae, ID_none
include_once("_db_add_plugin_menu_nest.php");
?>