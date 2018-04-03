<?php
header("Content-Type:text/html; charset=utf-8");
require_once ("_db_add_plugin_menu.inc");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
$right_tree_flag=1;  //1 means it will set right for each admin
if ($oem=="X02") define("LIC_MENU","Xpress License Mgmt.");
else {echo "{$oem} Not Support License Mgmt Functions!!";exit(1); }
define("LIC_OID","98"); //main menu
$licID=checkMenuID($link,LIC_MENU); //old 55 Support
if (is_null($licID)){
addMainMenu($link, LIC_MENU,LIC_OID);
$licID=checkMenuID($link,LIC_MENU);
}else updateMainMenu($link,$licID);
addSubMenuRight($link,"ID_admin",$licID,"1");

$menuGroup = "licservice";
$pluginPath = "/var/www/qlync_admin/plugin/{$menuGroup}/";
$menupos=900;
//$menuParent = "55"; //55 support, 77 webmaster, 6 License ,12 user,
$menupos = getMAXOID($licID,$menupos,$link);

$menuArray = [
  "Camera Lic. List Mgmt." => array("/plugin/licservice/listLicensePage.php",$licID,"ID_admin_oem"),
  "Tunnel Lic. Mgmt." => array("/plugin/licservice/addLicense_tun.php",$licID,"ID_admin"),
//  "Site Tunnel Lic. Mgmt." => array("/plugin/licservice/tunLicense_upload.php",$licID,"ID_admin"),  
//  "EvoStream Lic. Mgmt." => array("/plugin/licservice/addLicense_evo.php",$licID,"ID_EMPTY"),
  "Other Lic. Mgmt." => array("/plugin/licservice/addLicense.php",$licID,"ID_admin"),
  "QR & URLShorten" => array("/plugin/licservice/genQR.php",$licID,"ID_admin"),
  "Camera Lic. Input/Update" => array("/plugin/licservice/addLicense_cam.php",$licID,"ID_admin"),
  "Camera Lic. Update" => array("/plugin/licservice/updateLicense_cam.php",$licID,"ID_admin")
//  "Camera Lic. Input" => array("/plugin/licservice/inputLicense_cam.php",$licID,"ID_admin")
];
//$admin_level = "ID_admin_oem"; // ID_admin, ID_admin_oem , ID_fae, ID_none, ID_webmaster
include_once("_db_add_plugin_menu_nest.php");
?>