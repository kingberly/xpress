<?php
/****************
 *Validated on Nov-11,2016,
 * mount unmount web nas folder
 * install under /plugin/taipei or /plugin/debug
 * MUST upgrade web mountcheck.sh
 * MUST use admin/check/rpic.py  
*****************/
include("../../header.php");
include("../../menu.php");
ini_set('memory_limit', '64M');
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","cmdweb_access.log");
define("WEBCMD_PATH","/home/ivedasuper/admin/check/rpic.py");
define("WEBCMD_UMOUNT","unmountvideo");
define("WEBCMD_UMOUNT_FORCE","umountforce");
if ($oem=="T04")
  define("WEBCMD_MOUNT","mountvideo");
else//testing
  define("WEBCMD_MOUNT","mountvideo".strtolower($oem));
define("WEBCMD_STATUS","mountstatus");
define("WEBCMD_ULIST","umountfuser");
define("WEBCMD_ULIST_L","umountlsof");
define("WEB_MOUNT_POINT","/media/videos");
define("MSG_SUCCEED"," 成功!");
define("MSG_FAIL"," 失敗!");

header("Content-Type:text/html; charset=utf-8");
?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>道管網頁NAS進階管理</font></b></div>
<div id="container">

<?php
if ( ( $_SESSION["Email"] != "jinho.chang@tdi-megasys.com")
  and ( $_SESSION["Email"] != "admin@localhost.com")  
  and ( $_SESSION["Email"] != "11526")
  and ( $_SESSION["Email"] != "40184") ) 
  exit;

if (!file_exists(LOG_PATH.LOG_FILE)){
  //include_once(LOG_PATH.LOG_FILE);
    exec(" echo '' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);  
}
AccessTextLog( $_SESSION["Email"] ,"access");

if ( $_REQUEST["step"] == WEBCMD_UMOUNT){
  $title="NAS卸載";
  AccessDBLog( $_SESSION["Email"] ,"unmount");
  AccessTextLog( $_SESSION["Email"] ,$_REQUEST["step"]);
  echo "等待{$title}:<br>";
  $cmd = "python ".WEBCMD_PATH." ".WEBCMD_UMOUNT;        
  $output = shell_exec($cmd);
  if (preg_match('/lsof/',$output)){//ok
    $cmd2 = "python ".WEBCMD_PATH." ".WEBCMD_ULIST_L;
    $output2 = shell_exec($cmd2);
    if (preg_match('/PID/',$output2)){//ok
      $output2 = str_replace("\n","<br>",$output2);
      echo "<small>{$output2}</small><br>";
    }else{
      $cmd2 = "python ".WEBCMD_PATH." ".WEBCMD_ULIST;
      $output2 = shell_exec($cmd2);
      $output2 = str_replace("\n","<br>",$output2);
      echo "<small>{$output2}</small><br>";
    }
    $msg_err .="<font color=red>".$title.MSG_FAIL.",請洽系統技術人員</font>";
  }else $msg_err .= $title.MSG_SUCCEED;
}else if  ( $_REQUEST["step"] == WEBCMD_MOUNT){
  $title="NAS掛載";
  AccessDBLog( $_SESSION["Email"] ,"mount");
  AccessTextLog( $_SESSION["Email"] ,$_REQUEST["step"]);
  echo "等待{$title}:<br>";
  $cmd= "python ".WEBCMD_PATH." ".WEBCMD_MOUNT;
  $output = shell_exec($cmd);
  if (preg_match('/mount shared nas/',$output)){//ok
    $msg_err .="NAS已掛載.可以正常下載檔案";
  }else $msg_err .= "<font color=red>".$title.MSG_FAIL.",請洽系統技術人員</font>";
  
}else if  ( $_REQUEST["step"] == WEBCMD_UMOUNT_FORCE){
  $title="NAS強制卸載";
  AccessDBLog( $_SESSION["Email"] ,"unmountf");
  AccessTextLog( $_SESSION["Email"] ,$_REQUEST["step"]);
  echo "等待{$title}:<br>";
  $cmd = "python ".WEBCMD_PATH." ".WEBCMD_UMOUNT_FORCE;
  $output = shell_exec($cmd);
  $cmd = "python ".WEBCMD_PATH." ".WEBCMD_UMOUNT;        
  $output = shell_exec($cmd);
  if (preg_match('/lsof/',$output)){//ok
    $msg_err .="<font color=red>".$title.MSG_FAIL.",請洽系統技術人員</font>";
  }else $msg_err .= $title.MSG_SUCCEED;
}else if  ( $_REQUEST["step"] == WEBCMD_STATUS){
  $title="狀態回報";
//  AccessDBLog( $_SESSION["Email"] ,WEBCMD_STATUS);
  AccessTextLog( $_SESSION["Email"] ,$_REQUEST["step"]);
  echo "等待{$title}:<br>";
  $cmd = "python ".WEBCMD_PATH." ".WEBCMD_STATUS;
  $output = shell_exec($cmd);
  //if (strpos($outout,"videos")!==false){//fail
  if (preg_match('/videos/',$output)){//ok
    $msg_err .="<b>NAS已掛載.可以正常下載檔案</b>";
  }else $msg_err .= "NAS已卸載.無法下載檔案";
}

function AccessDBLog($account,$action)
{
  if (strlen( $account) > 18){
    if (strpos($account,"@")!==false){
      $logaccount=explode("@",$account);
      $DBaccount=$logaccount[0];
    }
  }else $DBaccount=substr($account, 0, 18);
  $sql="insert into qlync.login_log (Account, Date) values( '{$DBaccount}/{$action}','".date("Y-m-d H:i:s")."')";
	//sql($sql,$result,$num,0);
  $result=mysql_query($sql);
  if ($result) return true;
  return false;
}

function AccessTextLog($account,$action)
{
  $fplog=fopen(LOG_PATH.LOG_FILE,"a+");
  $fwrite = fwrite($fplog,date("Y-m-d H:i:s")."\t{$account}\t{$action}\n");
  fclose($fplog);
}
function readLog($nLimit)
{//read selected DB log and Text Access log
    $filepath = LOG_PATH.LOG_FILE; 

    if ($nLimit!="")
      $txthtml=shell_exec("tail -n {$nLimit} {$filepath}");
    else
      $txthtml = file_get_contents($filepath);
    $txthtml = str_replace("\n","<br>",$txthtml);
    $txthtml = "<pre>".$txthtml."</pre>";

    if ($nLimit!="")
      $sql = "select * from qlync.login_log where Account like '%/%' limit {$nLimit}";
    else $sql = "select * from qlync.login_log where Account like '%/%'";
    sql($sql,$result,$num,0);
   	for($i=0;$i<$num;$i++){
        fetch($arr,$result,$i,0);
        $dbhtml.=$arr['Account']."    ".$arr['Date']."<br>";
    }
    $dbhtml = "<pre>".$dbhtml."</pre>";
    $html = "<table border=1><tr><th>資料庫</th><th>記錄</th></tr><tr><td>";
    $html .=$dbhtml;
    $html .="</td><td>".$txthtml;
    $html .="</td></tr></table>";
    echo $html;
}  

if ($msg_err!="")
  echo "<font color=blue>{$msg_err}</font>";
  if (isset($_REQUEST["debugadmin"])){
    echo "<br><small>{$cmd}</small>";
    echo "<br><small>{$output}</small><hr>";
  }
?>
<?php
if ($cmd2!=""){
?>
<form name=web action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<input type=hidden name=step value="<?=WEBCMD_UMOUNT_FORCE?>">
<input type=submit value="強制 關閉 NAS下載">
</form>
<?php
 }
?>
<form name=web3 action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<input type=hidden name=step value="<?=WEBCMD_STATUS?>">
<input type=submit value="網頁NAS狀態" class=btn_1>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin>";
?>
</form>
<form name=web2 action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<input type=hidden name=step value="<?=WEBCMD_UMOUNT?>">
<input type=submit value="網頁 關閉 NAS下載" class=btn_1>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin>";
?>
</form>
<form name=web1 action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<input type=hidden name=step value="<?=WEBCMD_MOUNT?>">
<input type=submit value="網頁 啟動 NAS下載"  class=btn_1>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin>";
?>
</form>
<br></p>
<?php
if (isset($_REQUEST["debugadmin"])){
  if (isset($_REQUEST["nlimit"]))
       readLog("");
  else readLog("30");
}
?> 
	</div>
</body>
</html>