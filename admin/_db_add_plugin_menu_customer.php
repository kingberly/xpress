<?php
//configuration of menu insertion
require_once ("_db_add_plugin_menu.inc");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
$right_tree_flag=1;  //1 means it will set right for each admin
$menuGroup = "customerservice";
$pluginPath = "/var/www/qlync_admin/plugin/{$menuGroup}/";
$menuParent = checkMenuID($link,"Support"); //55 support, 77 webmaster, 6 License ,12 user,
$menupos=790;
$menupos = getMAXOID($menuParent,$menupos,$link);
 
$menuArray = [
  "Customer Support" => array("/plugin/customerservice/showService.php",$menuParent,"ID_fae")
];
//$admin_level = "ID_fae"; // ID_admin, ID_admin_oem , ID_fae, ID_none, ID_webmaster
include_once("_db_add_plugin_menu_nest.php");
?>

