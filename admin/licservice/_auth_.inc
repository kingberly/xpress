<?php
/*
include("header.php");
include("menu.php");
require_once 'dbutil.php';
echo "<html><head></head><body>";
*/
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
require_once 'dbutil.php';
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
if (isset($_SESSION["Contact"])){
  //login
}else if (chkSourceFromMegasys($submiter)){ //company ip
  //echo "{$submiter}:<br>";
}else  exit();
?>