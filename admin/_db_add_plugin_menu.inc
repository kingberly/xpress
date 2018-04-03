<?php
require_once ("/var/www/qlync_admin/doc/config.php");
define("ROOT_FOLDER","/var/www/qlync_admin");
$roleLevel=[
"ID_webmaster",
"ID_admin",
"ID_admin_oem",
"ID_fae",
"ID_01",
"ID_02", //used for T06 admin
"ID_09"
];
function cleanMenuByName($Name,$link)
{
  $sql_string="delete from qlync.menu where Name='{$Name}'";
  $result=mysql_query($sql_string,$link);
  if ($result) return true;
  return false; 
}

function cleanMenuDB($filePath,$link)
{
  //$response="";
  $sql = "select Name, OID, FID from qlync.menu where Link='{$filePath}'";
  $result=mysql_query($sql,$link);
  if ($result){
    $num=mysql_num_rows($result);
    if ($num>0){ 
      $deletePosOID = mysql_result($result, 0,'OID');
      $deletePosFID = mysql_result($result, 0,'FID');
      $menuName = mysql_result($result, 0,'Name');
    }else{
      printf("No such menu item: %s\n", $filePath);
      return false;
    }    
  }
  if (deleteMenuRight($link,$deletePosFID,$deletePosOID)){
  	printMessage("DELETE MenuRight SUCCESS", array("Name"=>$menuName,"Link"=>$filePath,"FID"=>$deletePosFID,"OID"=>$deletePosOID));
  	if (deleteMenu($link,$deletePosFID,$deletePosOID))
  		printMessage("DELETE MENU SUCCESS", array("Name"=>$menuName,"Link"=>$filePath,"FID"=>$deletePosFID,"OID"=>$deletePosOID));
  	else printMessage("DELETE MENU FAIL", array("Link"=>$filePath));
  }else printMessage("DELETE MenuRight FAIL", array("Link"=>$filePath));

  /*
  $sql_string="select id from menu where OID = '{$deletePosOID}' and FID =  '{$deletePosFID}'";
  $result=mysql_query($sql_string,$link);
  if ($result){
    $num=mysql_num_rows($result);
    if ($num>0) $mID = mysql_result($result, 0,'id');
    else return;
  }
     
  $sql_string_rt="delete from right_tree where Cfun = '{$mID}'";
  $result=mysql_query($sql_string_rt,$link);
  if ($result) $response .= "Successs on cleanup plugin right_tree {$mID}\n";
  else $response .= "Fail on cleanup plugin right_tree {$mID}.\n";   

  $sql_string="delete from menu where OID='{$deletePosOID}' and FID='{$deletePosFID}'";
  $result=mysql_query($sql_string,$link);
  if ($result) $response .= "Successs on cleanup plugin menu {$filePath}\n";
  else $response .= "Fail on cleanup plugin menu {$filePath}.\n({$sql_string})\n";
  printf($response);
	*/   
}

function getMAXOID($FID,$position, $link)
{
  $sql ="select max(CONVERT(OID,UNSIGNED INTEGER)) as MAX from qlync.menu where FID='{$FID}'";
  $result=mysql_query($sql,$link);
  if ($result){
    $maxpos = intval(mysql_result($result,0,'MAX'));
    if ($maxpos > $position) $position =  $maxpos+1;
  }
  return $position;
}

function cleanRightTreeID($menuid,$link)
{
  $sql="update qlync.right_tree set right_tree.Right='0' where Cfun = '{$menuid}'";
  //echo $sql;
  $result=mysql_query($sql,$link);
  if ($result){
    return 1;
  }
  
  return null;
}

function checkRightTreeID($link, $menuid,$Fright="")
{
	if ($Fright!="")
		  $sql="select ID from qlync.right_tree where Cfun = '{$menuid}' and Fright='{$Fright}'";
	else  $sql="select ID from qlync.right_tree where Cfun = '{$menuid}'";
  $result=mysql_query($sql,$link);
  if ($result){
    $num=mysql_num_rows($result);
    if ($num>0) return mysql_result($result, 0,'ID');
    else return null;
  }
  return null;
}

function checkMenuID($link,$mName,$filepath="")
{//filepath bypass url type
	if (($filepath!="") and (strpos($filepath,"http") === false))
		$sql="select ID from qlync.menu where Name = '{$mName}' and Link= '{$filepath}'";
  else $sql="select ID from qlync.menu where Name = '{$mName}'";
  $result=mysql_query($sql,$link);
  if ($result){
    $num=mysql_num_rows($result);
    if ($num>0) return mysql_result($result, 0,'ID');
    else return null;
  }
  return null;
}

function connect_db($host,$user,$password,$dbname)
{
 //this function will overwrite database utf issue, use mysql_connect.php
  $link=mysql_connect($host,$user,$password);
  if(!$link)
  {
    die("Can't connect to db ".$host);
  }

  $db_selected=mysql_select_db($dbname,$link);
  if(!$db_selected)
  {
    die("Can't open database: <br>".mysql_error($link));
  }
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");
  if ($ref!="")
    mysql_query("SET NAMES 'UTF8'");
  return $link;
}

function cleanupDebug($link)
{ //clean up all plugin menu
  $sql_string="delete from menu where Link like '%/plugin%'";
  $result=mysql_query($sql_string,$link);
  if ($result) $response = "Successs on cleanup plugin menu\n";
  else $response = "Fail on cleanup plugin menu.\n"; 
  printf ("command: %s => %s" ,$sql_string,$response);
  $sql_string_rt="delete c2 from right_tree as c2 left join menu as c1 on c1.id=c2.Cfun where c1.id is NULL";
  $result=mysql_query($sql_string_rt,$link);
  if ($result) $response = "Successs on cleanup plugin right_tree\n";
  else $response = "Fail on cleanup plugin right_tree.\n"; 
  printf ("command: %s => %s" ,$sql_string_rt,$response);
}

function addMainMenu($link, $mName, $mOID)
{
  /*$sql="INSERT INTO qlync.menu ( `Name`, `Level`, `FID`, `OID`, `Link`, `Auth`) VALUES ('{$mName}', '0', '0', '{$mOID}', '','1')";
  $result=mysql_query($sql,$link);
  return $result;
  */
  return addMenu($link,$mName, 0, $mOID, "", 0);
}

function updateMainMenu($link, $ID)
{
  return updateMenu($link, 0, $ID, 0);
}

function addMenu($link, $mName, $mFID, $mOID, $filepath, $level=1)
{//FID=0/Level0/Link empty => main
  $sql="INSERT INTO qlync.menu ( `Name`, `Level`, `FID`, `OID`, `Link`, `Auth`) VALUES ('{$mName}', '{$level}', '{$mFID}', '{$mOID}', '{$filepath}','1')";
  $result=mysql_query($sql,$link);
  return $result;
}

function updateMenu($link, $mFID, $ID,$level=1)
{
  $sql="UPDATE qlync.menu SET FID='{$mFID}', Level='{$level}' WHERE ID={$ID}";
  $result=mysql_query($sql,$link);
  return $result;
}
function deleteMenu($link,$deletePosFID,$deletePosOID)
{
  $sql_string="delete from qlync.menu where OID='{$deletePosOID}' and FID='{$deletePosFID}'";
  $result=mysql_query($sql_string,$link);
	return $result;
}

function addSubMenuRight($link, $Fright, $Cfun, $Right=1, $AID=0)
{//Cfun= menuID, Fright=Right name
  $sql="INSERT INTO qlync.right_tree ( `Fright`, `Cfun`, `Right`) VALUES ('{$Fright}', '{$Cfun}', '{$Right}')";
  $result=mysql_query($sql,$link);
  
  if ((intval($AID)!=0) and (strpos($Fright,"ID_0") !== false))
	{//for ID_0x/AID
  	updateMenuRight($link, $Fright, $Cfun, $Right,$AID);
  }
  //ID_0N , ID_admin, no need
  if ($Fright == "ID_admin_oem")  //for menu right < ID_admin, auto add
  	addSubMenuRight($link, "ID_admin", $Cfun, $Right,$AID);
  else if ($Fright == "ID_fae")
  	addSubMenuRight($link, "ID_admin_oem", $Cfun, $Right,$AID);
  else if ($Fright == "ID_none")
  	addSubMenuRight($link, "ID_fae", $Cfun, $Right,$AID);

  return $result;
}

function updateMenuRight($link, $Fright, $Cfun, $Right=1,$AID=0)
{//`Right` is reserved keywork, use `
  $sql="UPDATE qlync.right_tree SET Oem='1', `Right`='1', AID ='{$AID}' WHERE Cfun='{$Cfun}' and Fright='{$Fright}'";
  $result=mysql_query($sql,$link);
  
  if ($Fright == "ID_admin_oem")  //for menu right < ID_admin, auto add
  	if (is_null(checkRightTreeID($link,$Cfun,"ID_admin")))
  		addSubMenuRight($link, "ID_admin", $Cfun, $Right,$AID);
  	else  updateMenuRight($link, "ID_admin", $Cfun, $Right,$AID);

  else if ($Fright == "ID_fae")
  	if (is_null(checkRightTreeID($link,$Cfun,"ID_admin_oem")))
  		addSubMenuRight($link, "ID_admin_oem", $Cfun, $Right,$AID);
  	else  updateMenuRight($link, "ID_admin_oem", $Cfun, $Right,$AID);

  else if ($Fright == "ID_none")
  	if (is_null(checkRightTreeID($link,$Cfun,"ID_fae")))
  		addSubMenuRight($link, "ID_fae", $Cfun, $Right,$AID);
  	else  updateMenuRight($link, "ID_fae", $Cfun, $Right,$AID);

  return $result;
}

function deleteMenuRight($link, $deletePosFID, $deletePosOID)
{
  $sql_string="select id from qlync.menu where OID = '{$deletePosOID}' and FID =  '{$deletePosFID}'";
  $result=mysql_query($sql_string,$link);
  if ($result){
    $num=mysql_num_rows($result);
    if ($num>0) $mID = mysql_result($result, 0,'id');
    else return false;
  }
  $sql_string_rt="delete from qlync.right_tree where Cfun = '{$mID}'";
  $result=mysql_query($sql_string_rt,$link);
  return $result;
}

function printMessage($msg, $keyArr)
{
	$output =  "";
	foreach ($keyArr as $key => $value){
		$output.= " {$key}={$value},";
	}
	$output= $msg . $output ."\n";
	printf ($output);
}

function isMaster()
{
	$hastat = exec ("ps -ax | grep [h]eartbeat");
	if ($hastat != ""){ //use LB
    //stdout = os.popen("ifconfig | grep eth0:0 | wc -l")
    //isMaster = stdout.read().replace("\n", "")
    $stat = exec ("ifconfig | grep eth0:0 | wc -l");
    $stat = trim ( $stat , " \t\n\r\0\x0B");
    if (intval($stat) == 1)
    	return true;
	}else return true;  //single Admin
	return false;
}
function isAID($link)
{
	$sql ="SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'qlync' AND TABLE_NAME = 'account' AND COLUMN_NAME = 'AID'";
	$result=mysql_query($sql,$link);
	$num=mysql_num_rows($result);
  if ($num>0) return true;
  return false;
}
?>