<?php
/****************
 *Validated on Sep-15,2017
 * HTML5 API for admin access URL 
 * Include playback_list.php from /plugin/debug 
 *  Required to authenticate via: adminuser, adminpwd  
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");

require_once '_iveda.inc';  //under /plugin/rpic/
if (!isset($_REQUEST["url"]) and ( !isset($_REQUEST["datefolder"])) ){
  //page login
  if( ($_REQUEST["adminuser"]!="") and ($_REQUEST["adminpwd"]!="") ){
    if (pdoLogin($_REQUEST["adminuser"],$_REQUEST["adminpwd"])){
       $_SESSION['Email'] = $_REQUEST["adminuser"];
       $_REQUEST["debugadmin"] =""; //set debugadmin
       if (isDBFieldAvail("Contact","account","qlync")){
        $_SESSION['Contact'] = getContact($_SESSION['Email']);
        if ($_SESSION['Contact']!=""){
          if ($_SESSION['Contact']!=$_REQUEST["user"]){//for share
            addShareDeviceAPI($_REQUEST["mac"],$_SESSION['Contact']);
            $_SESSION['ContactPwd'] = $_REQUEST["adminpwd"];
          }
        }else unset($_SESSION['ContactPwd']);
       } 
    }else  httpError('401 Unauthorized', 'Fail to Login.');
  }else if (($_SESSION['Email']!="") and ($_REQUEST["adminuser"]==$_SESSION['Email'])) { //bypass adminpwd if logined
    $_REQUEST["debugadmin"]="";
  }else  die("Require Login!!");
}//has login info

//var_dump($_REQUEST);
include("../debug/playback_list.php");
?>