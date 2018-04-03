<?php
include("../../header.php");
include("../../menu.php");
//include(date("Ymd").".php");
#Authentication Section
if ($_SESSION["ID_admin_qlync"]!='1') die("No Permission");
define("PAGE_LIMIT_NUM", 100);
define("PAGE_LIMIT", "(".PAGE_LIMIT_NUM.")");
define("PAGE_LIMIT_NUM2", 200);
define("PAGE_LIMIT2", "(".PAGE_LIMIT_NUM2.")");
define("PAGE_NOLIMIT", "(ALL)");
############  Authentication Section End
$i=0;
$dt = date("Y-m-d");
$dp = date( "Ymd", strtotime( "$dt -30 day" ) ); // PHP:  2009-03-04
//echo date( "Y-m-d", strtotime( "2009-01-31 +2 month" ) ); // PHP:  2009-03-31
$sql="delete from qlync.sys_log where left(Time_s1,8) < '{$dp}'";
sql($sql,$result_log,$num_log,0);
if ($_REQUEST['id_filter']==PAGE_NOLIMIT)
$sql="select * from qlync.sys_log where left(Time_s1,8) > '{$dp}' order by ID desc";
else if ($_REQUEST['id_filter']==PAGE_LIMIT2)
$sql="select * from qlync.sys_log where left(Time_s1,8) > '{$dp}' order by ID desc LIMIT ".PAGE_LIMIT_NUM2;
else  $sql="select * from qlync.sys_log where left(Time_s1,8) > '{$dp}' order by ID desc LIMIT ".PAGE_LIMIT_NUM;
sql($sql,$result_log,$num_log,0);
for($i=0;$i<$num_log;$i++)
{
  fetch($db_log,$result_log,$i,0);
  $sys_log[$db_log["Time_s1"]][$db_log["Cat1"]]=$db_log["Content"];
}
//below is drop down menu for filter
?>
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<select name="id_filter" id="id_filter" onchange="optionValue(this.form.id_filter, this);this.form.submit();">
<option value="<?php echo PAGE_LIMIT;?>"><?php echo PAGE_LIMIT;?></option>
<option value="<?php echo PAGE_LIMIT2;?>" <?php if($_REQUEST['id_filter'] ==PAGE_LIMIT2 ) echo "selected";?>><?php echo PAGE_LIMIT2;?></option>
<option value="<?php echo PAGE_NOLIMIT;?>" <?php if($_REQUEST['id_filter'] ==PAGE_NOLIMIT ) echo "selected";?>><?php echo PAGE_NOLIMIT;?></option>
</select>
</form> 
<?php
echo "<table class=table_main>\n";
echo "<tr class=topic_main>\n";
echo "<td > "._("Date")."</td><td> "._("Event")."</td><td>"._("Note")."</td>\n";
echo "</tr>\n";
foreach($sys_log as $key1=>$value1){
  foreach($value1 as $key2 =>$value2){
    echo "<tr class=tr_".($i++%2+1).">\n";
    echo "<td nowrap>".($key1)."</td>\n";
    echo "<td>{$key2}</td>\n";
    echo "<td>{$value2}</td>\n";
    echo "</tr>\n";
  }
}
echo "</table>\n";

echo "</body></html>";