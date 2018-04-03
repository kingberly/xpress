<?php

require_once ("_db_add_plugin_menu.inc");
//$mysql_ip="192.168.2.138";
//$mysql_id="isatRoot";
//$mysql_pwd="isatPassword";

$deletePosArr=array(
"Stream Server License Upload",
"File Server");
$disablePosArr=array(
"Dealer Account Mgr.",
"Region Mgr.",
"Plug-in Mgr.",
"Upload",
"Billing",
"Store");

$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");

for ($i=0;$i<sizeof($disablePosArr);$i++)
{
    $mID=checkMenuID($link,$disablePosArr[$i]);
    if (is_null($mID)){
      printf("No Such Menu Name: %s\n", $disablePosArr[$i]);
      continue;
    }else{
        if (is_null(cleanRightTreeID($mID,$link) ) ){
          printf("No Such Menu Right_tree: %s\n", $disablePosArr[$i]);
          continue;
        }else printf("Disable Menu: (%s) %s\n", $mID,$disablePosArr[$i]); 
    }
      
}

if ( sizeof($deletePosArr) >0)
{
  for ($i=0;$i<sizeof($deletePosArr);$i++)
  {
    if (cleanMenuByName($deletePosArr[$i],$link))
      printf ("Delete Menu:%s => Successs\n",$deletePosArr[$i]);
    else
      printf ("Delete Menu:%s => Fail\n",$deletePosArr[$i]);
  }
}else{
  cleanupDebug($link);
}
mysql_close($link);
?>