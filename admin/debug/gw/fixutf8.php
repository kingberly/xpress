<?php
require_once ("/var/www/qlync_admin/doc/config.php");
$link=mysql_connect($mysql_ip,$mysql_id,$mysql_pwd)or die("Mysql Connecting Failed..!!");
mysql_query("SET NAMES 'utf-8'");
if (!isset($_REQUEST['db'])) {
  echo "No DB, use qlync!<br>";
  $_REQUEST['db']="qlync";
}
mysql_select_db($_REQUEST['db']);


include("/var/www/qlync_admin/doc/sql.php");
if (!isset($_REQUEST['table'])) {
  echo "No table";
  exit;
}else{
  $sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '".$_REQUEST['table']."' and table_schema='".$_REQUEST['db']."'";
  sql($sql,$result2,$num2,0);
  if ($num2<=0){
  echo "No such db.table > ".$_REQUEST['db'].".".$_REQUEST['table'];
  exit;
  }
}
if ($_REQUEST['step']=="update") {
  
  $sql="select * from ".$_REQUEST['table']." where id=".$_REQUEST['id'];
  sql($sql,$result,$num,0);
  fetch($db,$result,0,0);
  $oldvalue=mysql_real_escape_string($db[$_REQUEST['key']]);
  //echo $oldvalue."<br>";
  mysql_query("SET NAMES 'UTF8'");
  $sql="update ".$_REQUEST['db'].".".$_REQUEST['table']." set ".$_REQUEST['key']."='{$oldvalue}' where id=".$_REQUEST['id'];
  echo $sql;
  sql($sql,$result,$num,0);
  echo "<br><hr>";
  mysql_close($link);
  $link=mysql_connect($mysql_ip,$mysql_id,$mysql_pwd)or die("Mysql Connecting Failed..!!");
  mysql_select_db($_REQUEST['db']);
}
$total=0;
mysql_query("SET NAMES 'utf-8'");
$sql="select * from ".$_REQUEST['db'].".".$_REQUEST['table'];
sql($sql,$result,$num,0);
for($i=0;$i<$num;$i++)
{
 fetch($db,$result,$i,0);
 while ($string = current($db)) {
    if (strlen($string) != mb_strlen($string, 'utf-8')) 
    {
      if (!is_int(key($db)) ){
        if ( (strtolower(key($db)) != "password")) {
          $dbid=$db[id];
          if ($dbid=="") $dbid=$db[ID]; 
          echo $dbid.">".key($db)."={$string}<br>";
          echo "<form method=post action=".$_SERVER['PHP_SELF'].">";
          echo "<input type=hidden name='step' value=update>";
          echo "<input type=hidden name='id' value={$dbid}>";
          echo "<input type=hidden name='key' value=".key($db).">";
          echo "<input type=hidden name='db' value={$_REQUEST['db']}>";
          echo "<input type=hidden name='table' value={$_REQUEST['table']}>";
          echo "<input type=submit name='btnAction' value='Update {$dbid}'></form>";
          $total++;
        }
      }
    }
    next($db);
 } 
}
echo "Total utf8 db error:".$total; 
?>
</body>
</html>