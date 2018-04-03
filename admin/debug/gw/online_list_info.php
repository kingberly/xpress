<?php
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
ini_set('memory_limit', '64M');
//include("./var/www/qlync_admin/html/common/ip.php");
include("/var/www/qlync_admin/html/common/country.php");
include("/var/www/qlync_admin/html/common/geoip.inc");
include("/var/www/qlync_admin/html/common/geoipcity.inc");
include("/var/www/qlync_admin/html/common/geoipregionvars.php");

#Authentication Section
if( !isset($_SESSION["Contact"]) )   exit();
############  Authentication Section End

//$_REQUEST["account"] = $_SESSION["Contact"];
#Get Data File
$path="{$api_temp}/online_list.csv";
$fileLastModifyTime=new DateTime();
$fileLastModifyTime->setTimestamp(filemtime($path));
$now = new DateTime();
$diff=$now->diff($fileLastModifyTime);
if ($diff->i > 3){    //jinho added, get when file is older than 3 minutes
#Get Data File
$url="http://{$api_id}:{$api_pwd}@{$api_path}/fetch_online_clients.php";
exec("wget ".$url." -O ".$path);
chmod($path,0777);
}

$handle=fopen($path,"r");
$content=fread($handle,filesize($path));
$content=str_replace("\n",",",$content);
$total=explode(",",$content);
flush($account_group);
$item=8;

for($i=0;$i<sizeof($total);$i++)
{
	$account[$i]= $total[$i*$item];
	$uid[$i]    = $total[$i*$item+1];
	$uid_cid[$i]= substr($uid[$i],0,3);
	$company[$i]= $total[$i*$item+2];
	$model[$i]  = $total[$i*$item+3];
	$fw[$i]     = $total[$i*$item+4];
        $online_time[$i]= $total[$i*$item+5];
	$ip[$i]		= $total[$i*$item+6];
	$library[$i]	= $total[$i*$item+7];
	// CID group from Device UID
//	if(array_search($account[$i],$account)==$i)
//		$account_group[]=$account[$i];

	if(array_search($uid_cid[$i],$uid_cid)==$i)
		$uid_cid_group[]=$uid_cid[$i];
	if($account[$i] <> "")
		$account_group[$account[$i]]="1";
}

fclose($handle);

$company_group=array_unique($company);
$uid_cid_count=array_count_values($uid_cid);
$gi = geoip_open("{$home_path}/html/common/GeoIP.dat", GEOIP_STANDARD);
$gi2 = geoip_open("{$home_path}/html/common/GeoLiteCity.dat",GEOIP_STANDARD);


echo "<div class=bg_mid>
\n";
echo "<div class=content>
\n";


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
      if ($account[$i] == $_SESSION["Contact"])
		  {
			echo "<tr class=tr_".($row+$i%2).">";	
		       		echo "<td>";	
        if (preg_match("/^[0-9-]{10,13}/",$account[$i])) 
        { 
          $web_url = explode (":",$_SERVER['HTTP_HOST']);
          $url2 = $web_url[0]."/backstage_login_gw.php";
          if (strpos($account[$i], "-") === 0)//no -
            $user_pwd= substr($account[$i], -4);
          else
            $user_pwd= substr($account[$i], -4+strpos($account[$i], "-")-strlen($account[$i]),4);
            
          echo "<a href='https://{$url2}?user_name={$account[$i]}&user_pwd={$user_pwd}&oem_id=G09' target=_blank>{$account[$i]}</a>";
        }else
					echo $account[$i];
				echo "</td>";
				echo "<td nowrap>";
        //echo "<a href='online_player.php?uid=".substr($uid[$i],0,18)."' target=_blank>".substr($uid[$i],0,18)."</a><BR>";
        echo "<a href='#' target=popup onclick=\"window.open('online_player.php?uid=".substr($uid[$i],0,18)."','',config='height=450,width=500')\">".substr($uid[$i],0,18)."</a><BR>";
        //echo substr($uid[$i],0,18)."<BR>";

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

				
			}	
			}// end with search loop
			}
echo "</table>";

?>
