<?
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
//include("../common/ip.php");
include("/var/www/qlync_admin/html/common/geoip.inc");
include("/var/www/qlync_admin/html/common/geoipcity.inc");
include("/var/www/qlync_admin/html/common/geoipregionvars.php");
include("/var/www/qlync_admin/html/common/country.php");
#Authentication Section
if( !isset($_SESSION["Contact"]) )   exit();
############  Authentication Section End
$_REQUEST["account"] = $_SESSION["Contact"];
$_REQUEST["step"] = "search"; 


#Get Data File
/*
*/
//new section from DB
$where = "";
if($_REQUEST["step"]=="search")
{
	if($_REQUEST["account"] <> "")
	{                $where=" and query_info.user_name like '%".mysql_real_escape_string($_REQUEST["account"])."%' or query_info.reg_email like '%".mysql_real_escape_string($_REQUEST["account"])."%' ";

	}
	if($_REQUEST["uid"] <> "")
	{
                $where=" and mac_addr like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";

	}
	if($_REQUEST["uid"] <> "" and $_REQUEST["account"] <> "")
	{
		$where=" and ( query_info.user_name like '%".mysql_real_escape_string($_REQUEST["account"])."%' or query_info.reg_email like '%".mysql_real_escape_string($_REQUEST["account"])."%')  and mac_addr like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";
	}
}
if((int)$_SESSION["SCID"]<>0)
{
//        if($where=="")
//        {
//                $where="where left(group_id,".(strlen((int)$_SESSION["SCID"])+1).")='".(int)($_SESSION["SCID"].$_SESSION["AID"])."' ";
//        }
//        else
        {
                $where=$where." and  left(group_id,".(strlen((int)$_SESSION["SCID"])).")='".(int)($_SESSION["SCID"].$_SESSION["AID"])."' and length(group_id) >1";
        }
}

/*
if($_REQUEST["step"]=="refresh")
{
        exec("php5 {$home_path}/html/common/daily_update_device.php");
}
*/

##################################
$pagesize=30;
//$sql="select Uid,Name,Email,count(Email) as Ec from qlync.account_device {$where} group by Name order by Name asc";
$sql="select count(distinct(query_info.reg_email)) as Ec,user.group_id from isat.query_info,isat.user where 1=1 and user.id=query_info.owner_id {$where} ";
sql($sql,$result_count_email,$num_count_email,0);
fetch($db_ec,$result_count_email,0,0);
$total_dev=$db_ec["Ec"];
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
if($stop >$total_dev)
{
        $sql_stop= $total_dev-$start;
}
else
{
        $sql_stop= $pagesize;
}

$account=array();
//$sql="select * from qlync.account_device where Email in ({$t2}) order by Name asc";
//$sql="select user.group_id,query_info.user_name from isat.query_info,isat.user where 1=1 and user.id=query_info.owner_id {$where} group by query_info.user_name,uid order by query_info.user_name asc limit {$start},{$sql_stop}";
$sql="select distinct(query_info.user_name),user.group_id from isat.user,isat.query_info where 1=1 and query_info.user_name=user.name {$where} order by query_info.user_name asc limit {$start},{$sql_stop}";
//echo $sql;
sql($sql,$result_list_tmp,$num_list_tmp,0);
$t="";
for($i=0;$i<$sql_stop;$i++)
{
	fetch($db_tmp,$result_list_tmp,$i,0);
	$user_gid[$db_tmp["user_name"]]	= str_pad($db_tmp["group_id"],10,"0000000000",STR_PAD_LEFT);
	$t[]=$db_tmp["user_name"];
}

$t2=implode("','",$t);
$sql="select query_info.user_name,query_info.reg_email,mac_addr,query_info.name,uid,ip_addr,model,device_models_version from isat.query_info,isat.user where query_info.user_name in ('{$t2}')  {$where} group by query_info.mac_addr,uid order by query_info.user_name asc ";
//echo $sql;
sql($sql,$result_list,$num_list,0);
$total_device_count=0;
for($c=0;$c<$num_list;$c++)
{
	fetch($db_list,$result_list,$c,0);
$gi = geoip_open("{$home_path}/html/common/GeoIP.dat", GEOIP_STANDARD);
$gi2 = geoip_open("{$home_path}/html/common/GeoLiteCity.dat",GEOIP_STANDARD);

        $account[$db_list['user_name']][email]     = $db_list["reg_email"];
        $account[$db_list['user_name']][mac][]     = $db_list["mac_addr"];
	$account[$db_list['user_name']][name][]	   = $db_list["name"];
        $account[$db_list['user_name']][uid]       = $db_list["uid"];
        $account[$db_list['user_name']][ip][]      = $db_list["ip_addr"];
        $account[$db_list['user_name']][model][]   = $db_list["model"];
        $account[$db_list['user_name']][m_ver][]   = $db_list["device_models_version"];

        //add for new
                                $country=geoip_country_code_by_addr($gi, $db_list["ip_addr"]);
                                $city   =geoip_record_by_addr($gi2, $db_list["ip_addr"]);


//	$account[$db_list['user_name']][country][] = $db_list["Country"];
//	$account[$db_list['user_name']][city][]    = $db_list["City"];
	$account[$db_list['user_name']][country][] = $country;
	$account[$db_list['user_name']][city][]    = $city->city;



	$account[$db_list['user_name']][oem_id][]  = substr($db_list["uid"],0,3);	
	$account[$db_list['user_name']][count]++;
	$total_device_count++;

geoip_close($gi);
geoip_close($gi2);


	

}
###########Temp section
###########


echo "<table  class=table_main>";
	echo "<tr class=tr_2>";
		echo "<td colspan=8>";
			echo "<h3>"._("Total")." "._("Account").": ".$total_dev;
			 echo " | "._("Total")." "._("Device")." : ".$total_device_count." ";

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
//			 echo "  |  <input type=submit  value='"._("Refresh")."' class=btn_2 onclick=\"javascript:location.href='account_list.php?step=refresh';\">\n";

		echo "</td>";
	echo "</tr>";
	echo "</table>";
	############## Search Section

#################### list
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

 				//if($_SESSION["CID"] =="N99" or $_SESSION["CID"] == $oem or $oid == $_SESSION["CID"] )
        if( isset($_SESSION["CID"]))
				{		
			$row++;
			for($li=0;$li<$value["count"];$li++)
			{	
			echo "<tr class=tr_".($row%2+1).">";	
				if($li==0)
				{
		       		echo "<td rowspan={$value["count"]} nowrap>";	
					echo $key." (".sizeof($account[$key]['mac']).")";
					echo "<BR>\n";
	                                echo $scid[substr($user_gid[$key],0,3)][name];
	                                echo " | ";
	                                echo $gid[substr($user_gid[$key],3,1)][0][name];
	                                echo " | ";
	                                echo $gid[substr($user_gid[$key],3,1)][(int)substr($user_gid[$key],4,2)][0];

					
				echo "</td>";
				}
				echo "<td nowrap>";
						echo "<a href='playback_list.php?mac=".$value[mac][$li]."' target=_blank>".$value[mac][$li]."</a>"; //echo $value[mac][$li];
						if($value[mac][$li] <> $value[name][$li])
						{
							echo " [ {$value[name][$li]} ]";
						}
						echo "<BR>";
				echo "</td>\n";
                                if($li==0)
                                {
                                echo "<td rowspan={$value["count"]}>";
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

?>
