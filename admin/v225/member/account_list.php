<?
include("../../header.php");
include("../../menu.php");
ini_set('memory_limit', '256M');
//include("../common/ip.php");
include("../common/country.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Account Device'";
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

$a_type[s]="SAT";
$a_type[g]="Google";


#Get Data File
/*
*/
//new section from DB
$where = "";
$whereA = "";//jinho fix QXP-247
if($_REQUEST["step"]=="search")
{
	if($_REQUEST["account"] <> "")
	{                $where=" where Name like '%".mysql_real_escape_string($_REQUEST["account"])."%' or Email like '%".mysql_real_escape_string($_REQUEST["account"])."%' ";

	}
	if($_REQUEST["uid"] <> "")
	{
                $where=" where Mac like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";
                $whereA=" and Mac like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";//jinho fix QXP-247

	}
	if($_REQUEST["uid"] <> "" and $_REQUEST["account"] <> "")
	{
		$where=" where (Name like '%".mysql_real_escape_string($_REQUEST["account"])."%' or Email like '%".mysql_real_escape_string($_REQUEST["account"])."%')  and Mac like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";
	}
}
if($_REQUEST["step"]=="refresh")
{
        exec("php5 {$home_path}/html/common/daily_update_device.php");
}

##################################
$pagesize=30;
$sql="select Uid,Name,Email,count(Email) as Ec from qlync.account_device {$where} group by Name order by Name asc";
sql($sql,$result_count_email,$num_count_email,0);

$total_dev=$num_count_email;
$total_page=ceil($total_dev/$pagesize);
if ( !isset($_GET["page"]) ) {
 $page=1; //設定起始頁
 } else {
 $page = intval($_GET["page"]); //確認頁數只能夠是數值資料
 $page = ($page > 0) ? $page : 1; //確認頁數大於零
 $page = ($total_page > $page) ? $page : $total_page; //確認使用者沒有輸入太神奇的數字
 }
$start = ($page-1)*$pagesize; //每頁起始資料序號
$stop  =  $page*$pagesize;
$t=array();
        $t2="'".implode("','",$t)."'";


for($c=$start;$c<$stop;$c++)
{
        fetch($db_count_email,$result_count_email,$c,0);
//        $email_list['email'][$c]=$db_count_email["Email"];
        $t[]=$db_count_email["Email"];
        $email_list_num["{$db_count_email["Email"]}"]   =$db_count_email["Ec"];
}
        $t2="'".implode("','",$t)."'";



$account=array();
//jinho fix QXP-247 if else
if($_REQUEST["uid"] <> "")
  $sql="select * from qlync.account_device where Email in ({$t2}) {$whereA} order by Name asc";
else
//jinho fix end 
$sql="select * from qlync.account_device where Email in ({$t2}) order by Name asc";
sql($sql,$result_list,$num_list,0);
for($c=0;$c<$num_list;$c++)
{
	fetch($db_list,$result_list,$c,0);
        $account[$db_list['Name']][email]     = $db_list["Email"];
        $account[$db_list['Name']][mac][]     = $db_list["Mac"];
        $account[$db_list['Name']][uid]       = $db_list["Uid"];
        $account[$db_list['Name']][ip][]      = $db_list["Ip"];
        $account[$db_list['Name']][model][]   = $db_list["Model"];
        $account[$db_list['Name']][m_ver][]   = $db_list["Fw"];
	$account[$db_list['Name']][country][] = $db_list["Country"];
	$account[$db_list['Name']][city][]    = $db_list["City"];
	$account[$db_list['Name']][oem_id][]  = substr($db_list["Uid"],0,3);	


	

}
###########Temp section
###########
fclose($handle);


for($j=0;$j<sizeof($uid_filter);$j++)
{
        echo $uid_filter[$j];
}

ksort($account);
//echo "<div class=bg_mid>\n";
//echo "<div class=content>\n";

############## List Sectio
$camera_num=0;
$key_num=0;
        foreach($account as $key=>$value){
                 foreach($value[oem_id] as $key_oid=>$value_oid){
//			if($value_oid == $_SESSION["CID"] or $_SESSION["CID"]=="N99" or $_SESSION["CID"]==$oem)
				$camera_num++;
			
		}
	}

echo "<table  class=table_main>";
	echo "<tr class=tr_2>";
		echo "<td colspan=8>";
			echo "<h3>"._("Total")." "._("Account").": ".sizeof($account);
			 echo " | "._("Total")." "._("Device")." : {$camera_num} ";

			echo " | ";
// seperate the page section
			echo ""._("Pages").": <a href=?page=1&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}> "._("First")."</a> ";
			for( $i=1 ; $i<=$total_page ; $i++ ) {
				 if ( $page-5 < $i && $i < $page+5){
				  echo "<a href=?page=".$i."&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}>".$i."</a> ";
				 }
			} //分頁頁碼
			echo "  <a href=?page=".$total_page."&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}> "._("Last")."</a>";
			echo ' | '._("Page").' <font color=#FF0033>'.$page.'</font> / '.$total_page.'';
			 echo "  |  <input type=submit  value='"._("Refresh")."' class=btn_2 onclick=\"javascript:location.href='account_list.php?step=refresh';\">\n";

		echo "</td>";
	echo "</tr>";
	echo "</table>";
	############## Search Section
echo "<form action=account_list.php method=post>";
	echo ""._("Account")."/"._("Email")." "._("Keyword").": <input type=text name=account value='".mysql_real_escape_string($_REQUEST["account"])."'>\n";
	echo "MAC "._("Keyword").": <input type=text name=uid  value='".mysql_real_escape_string($_REQUEST["uid"])."'>\n";
	echo "<input type=hidden name=step value=search>\n";
	echo "<input type=submit class=btn_2 value='"._("Search")."'>\n";
	
echo "</form>";
#################### list
//god super admin or tech support input any value
//if (($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) or ($_REQUEST["account"]!="" or $_REQUEST["uid"]!="")) { 
$row=1;
	echo "<table class=table_main>";
		echo "<tr class=topic_main>\n";
			echo "<td>"._("Account")."</td>";
			echo "<td>#</td>\n";
			echo "<td>"._("Email")."</td>\n";
			echo "<td>"._("Model")."</td>\n";
                        echo "<td>"._("Version")."</td>\n";
                        echo "<td>"._("Country")."</td>\n";
			echo "<td>"._("City")."</td>\n";
		echo "</tr>";
	foreach($account as $key=>$value)
	{
		{
 			{
				{
                                        foreach($value[oem_id] as $key_oid=>$value_oid){
                                                $oid= $value_oid;
                                        }

 				if($_SESSION["CID"] =="N99" or $_SESSION["CID"] == $oem or $oid == $_SESSION["CID"] )
				{		
			$row++;
			for($li=0;$li<$email_list_num[$value["email"]];$li++)
			{	
			echo "<tr class=tr_".($row%2+1).">";	
				if($li==0)
				{
		       		echo "<td rowspan={$email_list_num[$value["email"]]}>";	
					echo $key." (".sizeof($account[$key]['mac']).")";
					
				echo "</td>";
				}
				echo "<td>";
        if ($_SESSION["ID_admin_qlync"]!='1')
						echo $value[mac][$li]."<BR>";
        else //jinho added List
            echo "<a target='player' href='/plugin/debug/playback_list.php?debugadmin&user={$key}&mac=".$value[mac][$li]."' onclick=\"javascript:window.open(this.href,'player','height=650,width=600');return false;\">".$value[mac][$li]."</a>";
				echo "</td>\n";
                                if($li==0)
                                {
                                echo "<td rowspan={$email_list_num[$value["email"]]}>";
                                                echo $value[email];
				echo "</td>";
				}
				echo "<td nowrap>\n";
                                                echo $value[model][$li]."<BR>";

                                echo "</td>";
                                echo "<td>";
                                                echo $value[m_ver][$li]."<BR>";

                                echo "</td>";
                                echo "<td nowrap>";
						echo country_code_to_country($value[country][$li])."<BR>\n";

                                echo "</td>";
                                echo "<td nowrap>";
                                                echo "{$value[city][$li]}<BR>";

                                echo "</td>";




			echo "</tr>";
			}
				}  //oem_id check section end
				}
				}
			}// end with search loop
			}
echo "</table>";
//}//godadmin if
?>
