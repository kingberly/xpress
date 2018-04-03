<?php
//configuration of menu insertion
require_once ("_db_add_plugin_menu.inc");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
$right_tree_flag=1;  //1 means it will set right for each admin
$menuGroup = "debug";
$pluginPath = "/var/www/qlync_admin/plugin/{$menuGroup}/";
///new added for menu position
$menuParent = checkMenuID($link,"Webmaster"); //77 webmaster
$menupos=1001;
$menupos = getMAXOID($menuParent,$menupos,$link);
$menuArray = [
  "mgmt server" => array("/plugin/debug/delete_server_new.php",$menuParent,"ID_admin"),
  "mgmt assign lic" => array("/plugin/debug/mgmt_camlic.php",$menuParent,"ID_admin"),
  "Conn Status" => array("/plugin/debug/_connchartConst.php",$menuParent,"ID_admin"),
//"Stream Status" => array("/plugin/debug/_statuschart.php",$menuParent,"ID_admin"),
  "mgmt user" => array("/plugin/debug/mgmt_user.php",$menuParent,"ID_admin"),
  "mgmt device" => array("/plugin/debug/mgmt_device.php",$menuParent,"ID_admin"),
  "mgmt fw" => array("/plugin/debug/mgmt_fw.php",$menuParent,"ID_admin"),
  "App Download" => array("/plugin/debug/appdownload.X02.php",checkMenuID($link,"Support"),"ID_none") 
];
//$admin_level = "ID_admin"; // ID_admin, ID_admin_oem , ID_fae, ID_none, ID_webmaster
include_once("_db_add_plugin_menu_nest.php");
?>