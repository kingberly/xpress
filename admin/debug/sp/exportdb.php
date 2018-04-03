<?php
//require_once ("/var/www/qlync_admin/doc/config.php");
//include("/var/www/qlync_admin/doc/sql.php");
include("/var/www/qlync_admin/header.php");


if ($_REQUEST['step']=="dbquery") {
  $msg = DBquery($_REQUEST['dbtable'],$_REQUEST['field1'],$_REQUEST['field2']);
}else if ($_REQUEST['step']=="dbupdate") {
  $msg = DBupdate($_REQUEST['dbtable'],$_REQUEST['field'],$_REQUEST['oldvalue'],$_REQUEST['tovalue']);
}else if ($_REQUEST['step']=="qlyncaccount") {
  $scheme="<pre>
DROP TABLE IF EXISTS `account`;
CREATE TABLE IF NOT EXISTS `account` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Email` varchar(64) NOT NULL,
  `Password` tinyblob DEFAULT NULL,
  `Company_english` varchar(32) NOT NULL,
  `Company_chinese` varchar(32) NOT NULL,
  `Contact` varchar(32) NOT NULL,
  `Mobile` varchar(16) NOT NULL,
  `Phone` varchar(16) NOT NULL,
  `Address` varchar(64) NOT NULL,
  `CID` varchar(8) NOT NULL,
  `Level` varchar(8) NOT NULL DEFAULT '0' COMMENT '0-for normal partner  9-for superuser',
  `Status` varchar(8) NOT NULL DEFAULT '0' COMMENT '0-for just apply  1-for approved with CID add by PM ',
  `Company_nickname` varchar(16) NOT NULL,
  `ID_admin` varchar(1) NOT NULL DEFAULT '0',
  `ID_admin_oem` varchar(1) NOT NULL DEFAULT '0',
  `ID_webmaster` varchar(1) NOT NULL DEFAULT '0',
  `ID_qlync_pm` varchar(1) DEFAULT '0',
  `ID_qlync_rd` varchar(4) DEFAULT '0',
  `ID_pm_oem` varchar(4) DEFAULT '0',
  `ID_sales` varchar(4) DEFAULT '0',
  `ID_qlync_fae` varchar(1) DEFAULT '0',
  `ID_qlync_qa` varchar(4) DEFAULT '0',
  `ID_qlync_admin` varchar(4) DEFAULT '0',
  `ICID` varchar(8) DEFAULT NULL,
  `ID_fae` varchar(4) DEFAULT NULL,
  `ID_01` varchar(1) DEFAULT '0',
  `ID_02` varchar(1) DEFAULT '0',
  `ID_03` varchar(1) DEFAULT '0',
  `ID_11` varchar(1) DEFAULT '0',
  `SCID` varchar(3) DEFAULT '000',
  `ID_09` varchar(2) DEFAULT '00',
  `ID_04` varchar(1) DEFAULT '0',
  `ID_05` varchar(1) DEFAULT '0',
  `ID_06` varchar(1) DEFAULT '0',
  `AID` varchar(1) DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  </pre>";
  $msg=$scheme.writeData();
}

function writeData()
{
  $sql="SELECT count(*) as maxIndex FROM information_schema.`COLUMNS`  WHERE table_schema='qlync' and table_name='account'";
  sql($sql,$result,$num,0);
  fetch($db,$result,0,0);
  $maxIndex=$db['maxIndex'];
  //$sql="select *,SUBSTR(Email,1,5) as PKEY,DECODE(Password,SUBSTR(Email,1,5))as PPWD from qlync.account;";
  $sql="select *,SUBSTR(Email,1,5) as PKEY,DECODE(Password,SUBSTR(Email,1,5)) as PPWD, CONVERT(CAST(Address as BINARY)USING UTF8) as ADDR from qlync.account";
  sql($sql,$result,$num,0);
  $html ="INSERT INTO `account` VALUES ";
  $spcol=2;//ENCODE('{$db[PPWD]}','{$db[PKEY]}')
  $utf8col=8;
  for($i=0;$i<$num;$i++){
    fetch($db,$result,$i,0);
    //$html.="({$db[0]},'{$db[1]}',ENCODE('{$db[PPWD]}','{$db[PKEY]}'),'{$db[3]}','{$db[4]}','{$db[5]}','{$db[6]}','{$db[7]}','{$db[8]}','{$db[9]}','{$db[10]}','{$db[11]}','{$db[12]}','{$db[3]}','{$db[14]}','{$db[15]}','{$db[16]}','{$db[17]}','{$db[18]}','{$db[19]}','{$db[20]}','{$db[21]}','{$db[22]}','{$db[23]}','{$db[24]}','{$db[25]}','{$db[26]}','{$db[27]}','{$db[28]}','{$db[29]}','{$db[30]}','{$db[31]}','{$db[32]}','{$db[33]}','{$db[34]}'),";
    $html.="({$db[0]},'{$db[1]}',ENCODE('{$db[PPWD]}','{$db[PKEY]}'),";
    for($j=$spcol+1;$j<$maxIndex;$j++){
      if ($utf8col==$j) $html.="'{$db[ADDR]}',";
      else $html.="'{$db[$j]}',";
    }
    $html=rtrim($html,",");
    $html.="),";
  }
  $html=rtrim($html,",");
  $html.=";";
  return $html; 
}

function DBquery($dbtable, $field1, $field2)
{
  $dbarr=explode(".",$dbtable); 
  $sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '".$dbarr[1]."' and table_schema='".$dbarr[0]."'";
  sql($sql,$result,$num,0);
  if ($num <= 0) return "<font color=red>No such Table/Field!</font>";

  if ($field1=="") $field1="id";
  if ($field2=="") $field2="id";
  $html="<table><tr><th>{$field1}</th><th>{$field2}</th></tr>";
  $sql="select {$field1},{$field2} from {$dbtable}";
  sql($sql,$result,$num,0);
  if ($result){
    for($i=0;$i<$num;$i++)
    {
        fetch($db,$result,$i,0);
        $html.= "<tr><td>".$db[$field1]."</td><td>".$db[$field2]."</td></tr>";
    }
  }
  $html.="</table>";
  return $html;
}
function DBupdate($dbtable, $field, $oldvalue,$tovalue)
{
  if ($oldvalue=="") return "<font color=red>Empty Input Value</font>";
  if ($tovalue=="") return "<font color=red>Empty Update Value</font>";
  $dbarr=explode(".",$dbtable); 
  $sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '".$dbarr[1]."' and table_schema='".$dbarr[0]."'";
  sql($sql,$result,$num,0);
  if ($num <= 0) return "<font color=red>No such Table/Field!</font>";

  $sql="update {$dbtable} set {$field}='{$tovalue}' where {$field}='{$oldvalue}'";
  sql($sql,$result,$num,0);
  if ($result) return "Succeed Update {$field} to {$tovalue}!!";
}

?>
<div></p></div>
<?php
if ($msg!="" ){
  echo $msg;
}
?> 
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type=hidden name=step value='qlyncaccount'>
<input type=submit name=btnAction2 value="qlync.account export, SAVE as sql">
</form>
</body>
</html>