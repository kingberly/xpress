<?php
/*
include("header.php");
include("menu.php");
//require_once 'dbutil.php';
echo "<html><head></head><body>";
*/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
require_once 'dbutil.php';
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
#####new authentication, session check#####
if ( !isset($_SESSION["ID_qlync"]) )
    exit();
?>