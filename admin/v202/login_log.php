<?
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Login Log'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
        if($_SESSION["{$db["Fright"]}_qlync"] ==1)
        {
                $right+=1;
                if($db["Oem"] == "0")
                {
                        $oem_id="N99";
                }
                if($db["Oem"] == "1" and $oem_id == "")
                {
                        $oem_id=$_SESSION["CID"];
                }
        }
}
if($right  == "0")
        exit();
############  Authentication Section End

######################

echo "<div class=bg_mid>
\n";
echo "<div class=content>
\n";

if($_SESSION["ID_admin_qlync"]==1)
{//jinho modifyed for latest 30 days
        $date = new DateTime(date("Y-m-d"));
        $date->modify('-30 day');
        $sql="SELECT * FROM qlync.login_log where Date BETWEEN '".$date->format('Y-m-d')."' and NOW() order by ID desc"; 
	//$sql="select * from qlync.login_log where Date like '".date("Y-m")."%' order by Date asc";
}
else
{
        $sql="select Email, ID_admin from qlync.account where ID_admin <> 1";
        sql($sql,$result_filter,$num_filter,0);
        for($i=0;$i<$num_filter;$i++)
        {
                fetch($db_filter,$result_filter,$i,0);
                $li[]=$db_filter["Email"];
        }
        $email_array = '"' . implode('","', $li) . '"';
        //jinho modifyed for latest 30 days
        $date = new DateTime(date("Y-m-d"));
        $date->modify('-30 day');
        $sql="SELECT * FROM qlync.login_log where Date BETWEEN '".$date->format('Y-m-d')."' and NOW() and Account IN ({$email_array}) order by ID desc";            
        //$sql="SELECT * FROM qlync.login_log where  Date like '".date("Y-m")."%' and Account IN ({$email_array}) order by ID desc";


}
sql($sql,$result_log,$num_log,0);
	echo "<table class=table_main>";
		echo "<tr class=topic_main>\n";
			echo "<td>"._("ID")."</td>\n";
			echo "<td>"._("Account")."</td>";
			echo "<td>"._("Time")."</td>\n";
		echo "</tr>";
for($i=0;$i<$num_log;$i++)
{
	fetch($db_log[],$result_log,$i,0);
	$login_list[]=$db_log[$i][Account];
}
$count	= array_count_values($login_list);
foreach($count as $key_count => $value_count){

		echo "<tr bgcolor=#DDDDDD>";
			echo "<td></td>";
			echo "<td>{$key_count}</td>";
			echo "<td>{$value_count}</td>";
		echo "</tr>";
}
foreach($db_log as $key=>$value){

				echo "<tr class=tr_".(1+$key%2).">";
					echo "<td>".($key+1)."</td>";
					echo "<td>{$value["Account"]}</td>";
					echo "<td>{$value["Date"]}</td>";
				echo "</tr>";
				
	
}
	echo "</table>";
?>
