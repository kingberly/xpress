<?
include("../../header.php");
include("../../menu.php");
ini_set('memory_limit', '256M');
//include("../common/ip.php");
include("../common/country.php");
include("../common/geoip.inc");
include("../common/geoipcity.inc");
include("../common/geoipregionvars.php");

#Authentication Section
$sql="select * from qlync.menu where Name = 'Online Device'";
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


#Get Data File
$where="";
if($_REQUEST["step"]=="search")
{
//      $where =" where Email like '%".mysql_real_escape_string($_REQUEST["account"])."%' or Account like '%".mysql_real_escape_string($_REQUEST["account"])."%' ";
	if($_REQUEST["account"]<> "")
		$where=$where." and user_name like '%".mysql_real_escape_string($_REQUEST["account"])."%' ";

	if($_REQUEST["uid"] <> "")//jinho fix uid ambigious issue query_info.
	        $where =$where." and query_info.uid like '%".mysql_real_escape_string($_REQUEST["uid"])."%' ";
}

if((int)$_SESSION["SCID"]<>0)
{
	                $where=$where."and left(group_id,".(strlen((int)$_SESSION["SCID"])).")='".(int)($_SESSION["SCID"].$_SESSION["AID"])."' and length(group_id)>1";
}

$sql="select query_info.id,user.group_id from isat.query_info,isat.user where is_signal_online ='true' and owner_id=user.id {$where}  group by mac_addr ";

sql($sql,$result_total,$num_total,0);
###########Temp section
//jinho add total online
if ($where !=""){
$sqlc="select count(*) as total from isat.signal_server_online_client_list";
sql($sqlc,$resultc,$numc,0);
fetch($dbc, $resultc,0,0);
$totalc=$dbc['total'];
}
//end of total
$pagesize=30;
//$total_dev=sizeof($total)/$item;
$total_dev=$num_total;
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

//$sql="select * from isat.query_info,isat.user where is_signal_online='true' and user.id=query_info.owner_id {$where} group by mac_addr order by user_name asc limit {$start},{$sql_stop}";
//jinho add online time
$sql="select * from isat.query_info,isat.user,isat.signal_server_online_client_list  where is_signal_online='true' and user.id=query_info.owner_id and query_info.uid=signal_server_online_client_list.uid {$where} group by mac_addr order by user_name asc limit {$start},{$sql_stop}"; 
//echo $sql;
sql($sql,$result_list,$num_list,0);
for($i=0;$i<$num_list;$i++)
{
	fetch($db_list,$result_list,$i,0);
        $account[$i]	= $db_list['user_name'];
        $uid[$i]    	= $db_list['uid'];
//        $uid_cid[$i]= substr($uid[$i],0,3);
        $model[$i]  	= $db_list['model'];
        $fw[$i]     	= $db_list['device_models_version'];
        //$online_time[$i]= $total[$i*$item+5];
        $online_time[$i]= $db_list['login_date'];
        $ip[$i]         = $db_list['ip_addr'];
	$user_gid[$i]	= str_pad($db_list['group_id'],10,"0000000000",STR_PAD_LEFT);

}
###########

$gi = geoip_open("{$home_path}/html/common/GeoIP.dat", GEOIP_STANDARD);
$gi2 = geoip_open("{$home_path}/html/common/GeoLiteCity.dat",GEOIP_STANDARD);


echo "<div class=bg_mid>
\n";
echo "<div class=content>
\n";

############## List Section
echo "<table  class=table_main>";
	echo "<tr class=tr_2>";
		echo "<td colspan=8>";
		echo "<H3>";
		/*if($oem_id=="N99" or $oem_id="I02")
		{
                        if($_REQUEST["step"] <> "search")
			{
				echo ""._("User Account List")." #: ".(sizeof($account_group));
			}
		}*/
      //if($_REQUEST["step"] <> "search")
			{
			 	//echo " | "._("Online Device")." #: ";
        //jinho changed to show total count
			 	echo _("Total")." / "._("Online Device")." #: ";
        if ($where!="")
          echo $totalc ." / ". $total_dev;
        else
          echo $total_dev; 
                
			}
        
// seperate the page section
                        echo "  |  "._("Pages").": <a href=?page=1&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}> "._("First")."</a> ";
                        for( $i=1 ; $i<=$total_page ; $i++ ) {
                                 if ( $page-5 < $i && $i < $page+5){
                                  echo "<a href=?page=".$i."&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}>".$i."</a> ";
                                 }
                        } //分頁頁碼
                        echo "  <a href=?page=".$total_page."&step={$_REQUEST["step"]}&account={$_REQUEST["account"]}&uid={$_REQUEST["uid"]}> "._("Last")."</a>  |  ";
                        echo ''._("Page").' <font color=#FF0033>'.$page.'</font> / '.$total_page.'';

				
		echo "</td>";
	echo "</tr>";
	echo "</table>";
	############## Search Section
echo "<form action='".htmlentities($_SERVER['PHP_SELF'])."' method=post>";
	echo ""._("Account")." "._("Keyword").": <input type=text name=account value='".mysql_real_escape_string($_REQUEST["account"])."'>\n";
	echo ""._("MAC")." "._("Keyword").": <input type=text name=uid value='".mysql_real_escape_string($_REQUEST["uid"])."'>\n";
	echo "<input type=hidden name=step value=search>\n";
	echo "<input type=submit class=btn_2 value="._("Search").">\n";
	
echo "</form>";
#################### list
//array_multisort($account,SORT_ASC,$uid,SORT_ASC,$company,$model,SORT_ASC,$fw,SORT_ASC,$online_time,SORT_ASC, $ip,SORT_ASC,$library,SORT_ASC);
$row=1;
	echo "<table class=table_main>";
		echo "<tr class=topic_main>\n";
			echo "<td>"._("Account")."</td>";
			echo "<td>UID</td>\n";
//			echo "<td>Company</td>\n";
			echo "<td>"._("Model")."</td>\n";
			echo "<td>"._("FW Ver").".</td>\n";
			echo "<td>"._("Online")."</td>\n";
			echo "<td>IP</td>\n";
//			echo "<td>Ver.</td>\n";
		echo "</tr>";
	for($i=0;$i<sizeof($uid);$i++)
	{
		
		if($account[$i] <> "")
		{
			$pos=strripos($account[$i],$_REQUEST["account"]);

			if($pos ===false && $_REQUEST["step"]=="search" && $_REQUEST["account"] <> "") // check account key word search if 
			{
			}
			else
 			{
				$posuid=strripos(substr($uid[$i],0,18),$_REQUEST["uid"]);
                                if($posuid ===false && $_REQUEST["step"]=="search" && $_REQUEST["uid"] <> "") // check uid key word search if
                                {
                                }
				else
				{
 					if($oem_id == "N99" or $oem_id == substr($uid[$i],0,3) or $oem_id="I02" )
				{		
		
			echo "<tr class=tr_".($row+$i%2).">";	
		       		echo "<td >";	//nowrap
					echo $account[$i];
echo "<BR>\n";
if (substr($user_gid[$i],0,3) != "000"){ //jinho add
echo $scid[substr($user_gid[$i],0,3)][name];
echo " | ";
echo $gid[substr($user_gid[$i],3,1)][0][name];
echo " | ";
echo $gid[substr($user_gid[$i],3,1)][(int)substr($user_gid[$i],4,2)][0];
} //jinho add					
				echo "</td>";
				echo "<td nowrap>";
        if ($_SESSION["ID_admin_qlync"]!='1')
					echo substr($uid[$i],0,18)."<BR>";
        else //jinho replaced with liveview
          echo "<a target='player' href='/plugin/debug/online_player.php?user={$account[$i]}&uid=".substr($uid[$i],0,18)."' onclick=\"window.open(this.href,'player','height=450,width=500');return false;\">".substr($uid[$i],0,18)."</a>";

				echo "</td>";		
//				echo "<td>";
//                                                echo $company[$i];
//				echo "</td>";
                                echo "<td>";
                                                echo $model[$i];
                                echo "</td>";
                                echo "<td>";
                                                echo $fw[$i];
                                echo "</td>";
                                echo "<td nowrap>";
			//			$server_tz = date_default_timezone_get();
			//			echo $server_tz;
			//			date_default_timezone_set($server_tz);
                                                echo $online_time[$i]." /	 ";
						$start_date=new DateTime($online_time[$i]);
						$since_start=$start_date->diff(new DateTime(date("Y-m-d H:i:s")));
						echo "<font color=#0000FF>".$since_start->days."</font> d <font color=#0000FF>".($since_start->h)."</font> h <font color=#0000FF>".$since_start->i."</font> m <font color=#0000FF>".$since_start->s."</font> s ";
				                                       

                                echo "</td>";
                                echo "<td nowrap>";
		                               $country=geoip_country_code_by_addr($gi, $ip[$i]);
		                               $city   =geoip_record_by_addr($gi2, $ip[$i]);

		//				echo country_code_to_country($ip_to_country[$ip[$i]]);
//                                                echo $country_list[$ip_to_country[$ip[$i]]]."<BR>";
						echo country_code_to_country($country);
						echo "<br>\n";
//						echo $ip_to_city[$ip[$i]];
						echo $city->city;
						
						echo "<BR>{$ip[$i]}";

                                echo "</td>";

			echo "</tr>";
				}  //oem_id check section end
				}
				}
			}// end with search loop
			}
echo "</table>";

?>
