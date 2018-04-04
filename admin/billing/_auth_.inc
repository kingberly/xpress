<?php
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
require_once 'dbutil.php';
#Authentication Section same as Tech support right
if  ($_REQUEST['key']!=$RPICAPP_USER_PWD[$oem][0])
	if (!isset($_SESSION["Email"]) ) exit(); 
############  Authentication Section End

?>