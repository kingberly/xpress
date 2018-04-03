<?php
//configuration of menu insertion
$right_tree_flag=1;  //1 means it will set right for each admin
$menuGroup = "debug";
$pluginPath = "/var/www/qlync_admin/plugin/{$menuGroup}/";
$menupos=820;
///new added for menu position

require_once ("_db_add_plugin_menu.inc");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
//$menuParent = checkMenuID($link, "Webmaster");//77
$menupos = getMAXOID("77",$menupos,$link);
//$menuParent = "12"; //55 support, 77 webmaster, 6 License ,12 user, 
$menuArray = [
  //"mgmt" =>  array("/plugin/aaa/aaa.php",$menuParent,$admin_level),
  //"conn log chart Const" => array("/plugin/debug/_connchartConst.php",77,"ID_admin"),
  "Tunnel Usage Report" => array("/plugin/debug/tunnel_connlog_Excel_Const.php",77,"ID_admin")
];
// $right_tree_flag=1 will check $admin_level
//$admin_level = "ID_fae"; // ID_admin, ID_admin_oem , ID_fae, ID_none
include_once("_db_add_plugin_menu_nest.php");
?>