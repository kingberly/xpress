<?php
//require_once ("/var/www/qlync_admin/doc/config.php");
//include("/var/www/qlync_admin/doc/sql.php");
include("/var/www/qlync_admin/header.php");


if ($_REQUEST['step']=="dbquery") {
  $msg = DBquery($_REQUEST['dbtable'],$_REQUEST['field1'],$_REQUEST['field2']);
}else if ($_REQUEST['step']=="dbupdate") {
  $msg = DBupdate($_REQUEST['dbtable'],$_REQUEST['field'],$_REQUEST['oldvalue'],$_REQUEST['tovalue']);
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


<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
Display table<input type=text  size=15 name=dbtable value="<?php if (isset($_REQUEST['dbtable'])) echo $_REQUEST['dbtable']; else echo "isat.stream_server";?>">
Field:<input type=text  size=10 name=field1 value="<?php if (isset($_REQUEST['field1'])) echo $_REQUEST['field1']; else echo "internal_address";?>">
,<input type=text  size=10 name=field2 value="<?php if (isset($_REQUEST['field2'])) echo $_REQUEST['field2']; else echo "external_address";?>">
<br>
<input type=hidden name=step value='dbquery'>
<input type=submit name=btnAction2 value="List">
</form>
<div></p></div> 
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
Display table<input type=text  size=15 name=dbtable value="<?php if (isset($_REQUEST['dbtable'])) echo $_REQUEST['dbtable']; else echo "isat.stream_server";?>">
Field:<input type=text  size=10 name=field value="<?php if (isset($_REQUEST['field'])) echo $_REQUEST['field']; else echo "external_address";?>">
<br>
Original Value:<input type=text  size=10 name=oldvalue value="<?php if (isset($_REQUEST['oldvalue'])) echo $_REQUEST['oldvalue']; else echo "111.235.240.49";?>">
&nbsp;&nbsp;&nbsp;
Set to:<input type=text  size=10 name=tovalue value="<?php if (isset($_REQUEST['tovalue'])) echo $_REQUEST['tovalue']; else echo "111.235.240.69";?>">
<br>
<input type=hidden name=step value='dbupdate'>
<input type=submit name=btnAction2 value="Update">
</form>
<div></p></div>
<?php
if ($msg!="" ){
  echo $msg;
}
?> 

</body>
</html>