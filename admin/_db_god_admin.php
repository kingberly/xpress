<?php
/****************
 * Validated on May-9,2017, 
 *  $argv[0] is script name
 *  $argv[1] DEFAULT_PWD param: any string, default qwedcxzas
 *Writer: JinHo, Chang
*****************/ 
//read pwd parameter god_pwd
require_once ("_db_add_plugin_menu.inc");
//$mysql_ip="192.168.2.138";
//$mysql_id="isatRoot";
//$mysql_pwd="isatPassword";
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");
if (isset($argv[1]))
	define ("GODADMIN_PWD", $argv[1]);
else define("GODADMIN_PWD","qwedcxzas"); 
define("DEFAULT_PWD","qwedcxzas");

if ((GODADMIN_PWD!="") and (GODADMIN_PWD!=DEFAULT_PWD) )
{
  echo "replace god admin password started.";
  $sql="select DECODE(Password,'admin') as pwd from account where Email='admin@localhost.com'";
  $result=mysql_query($sql,$link);
  $num=mysql_num_rows($result);
  if ($num>0){
    $show=mysql_result($result,0,'pwd'); 
    printf("current god admin password=%s\n",$show);
    $sql_update="update qlync.account set Password=ENCODE('".GODADMIN_PWD."', 'admin') where Email='admin@localhost.com'";
    mysql_query($sql_update,$link);
    $result_after=mysql_query($sql,$link);
    $show_after=mysql_result($result_after,0,'pwd');
    printf("update new god admin password=%s\n",$show_after);  
  }else
    printf("God admin account missing\n");
}
//set god admin permission everytime
$sql="select DECODE(Password,'admin') as pwd from account where Email='admin@localhost.com'";
$result=mysql_query($sql,$link);
$show=mysql_result($result,0,'pwd'); 
printf("god admin password=%s\n",$show);
$sql="update qlync.account set id_admin='1',ID_webmaster='1' where Email='admin@localhost.com'";
mysql_query($sql,$link);
echo "reset id_admin, id_webmaster right for god admin\n";

//add my account for super admin  //encode use first 5 email digit
$sql="INSERT INTO `account` (Email, Password, id_admin_oem, CID, Status, Company_english,ICID, id_fae) VALUES ('jinho.chang@tdi-megasys.com',ENCODE('123456','jinho'),'1','{$oem}','1','IVEDA','0','0')";
$result=mysql_query($sql,$link);
if ($result) printf("Added jinho super admin account succes\n");
mysql_close($link); //close isat
?>