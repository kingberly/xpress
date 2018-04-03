<?PHP
        if($_REQUEST["step"]=="aa")
        {
                header("Refresh: 1;");
        }
        else
        {
                header("Refresh: 30;");

        }

ini_set('memory_limit', '256M');

include("../../header.php");
include("../../menu.php");
include("../common/country.php");
include("../common/geoip.inc");
include("../common/geoipcity.inc");
include("../common/geoipregionvars.php");

#Authentication Section
$sql="select * from qlync.menu where Name = 'Stream Server'";
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
##############temp for device info
$gi = geoip_open("{$home_path}/html/common/GeoIP.dat", GEOIP_STANDARD);
$gi2 = geoip_open("{$home_path}/html/common/GeoLiteCity.dat",GEOIP_STANDARD);

#################
/*
$path="{$api_temp}/account_device.csv";
$handle = @fopen($path, "r");
if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
	flush($tmp);
        $tmp=explode(",",$buffer);
	$check_list[substr($tmp[4],-12)][fw]=$tmp[9];
	$check_list[substr($tmp[4],-12)][ip]=$tmp[6];
    }
}
*/



############ Show array
$resolution["RVHI"]=" HD 30 fps";
$resolution["RVME"]=" VGA 5 fps";
$resolution["RVLO"]=" QVGA 5 fps";

$dataplan["LV"]	= "Live View";
$dataplan["SR"]	= "Schedule + Event";
$dataplan["AR"]	= "Always + Event";
$dataplan["D"]	= "Disable";
$dataplan["EV"] = "Event Only";

#######################


$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_streamserver.php';

#########################################################AA section Strart
if($_REQUEST["step"]=="aa")
{
        if($_REQUEST["from_uid"]<> "" and $_REQUEST["to_uid"]<> "" and $_REQUEST["from_uid"]<> $_REQUEST["to_uid"])
        {
              exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=enable&streamserver={$_REQUEST["to_uid"]}'");              
		exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=disable&streamserver={$_REQUEST["from_uid"]}'");



                $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_manual_switch','From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} | Reson: Manual Switch Detection')";
                sql($sql,$result_tmp,$num_tmp,0);

        }


}




##### unbind license
#API A-10
if($_REQUEST["step"] == "disable")
{
        $path = '/manage/manage_streamserver.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $params = '?command=disable&streamserver='.$_REQUEST["uid"];
        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);
                $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_manual_disable','From: {$_REQUEST["uid"]} | Reson: Manual Disable Detection')";
                sql($sql,$result_tmp,$num_tmp,0);



}


######Bind license
#API A-9
if($_REQUEST["step"] == "enable")
{
	$path = '/manage/manage_streamserver.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $params = '?command=enable&streamserver=' . $_REQUEST["uid"];

        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);


}


######################

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_streamservers';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$streamservers = $content['streamservers'];
$stats = $content['stats'];
require_once('xmlrpc_client.php');
foreach ($streamservers as $s) {
//	if($s[license_id] <> "")
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	$resource[$s[uid]]=$rpc->call();
//   	 print_r($rpc->call());
    }
    catch (Exception $e) {}
}

        curl_close($ch);
///get server status for enable / disable
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$path = '/manage/manage_streamserver.php';

foreach($streamservers as $key=>$value)
{
	$params = '?command=is_enabled&streamserver='.$value["uid"];
	curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
	$result = curl_exec($ch);
	$r=json_decode($result,true);
	if($r["status"]=="success" and $r["enabled"])
	{
		$streamservers[$key][license_id]="enabled";
	}
	else
	{
		$streamservers[$key][license_id]="";
	}

}

//////////////////////////////////////////////////////////
// List the tunnel server license
#API A-7

# 1. Get stream server list
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$params = '?command=get_licenses';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$license=$content["licenses"];
        curl_close($ch);

flush($lic);
foreach($license as $key=>$value){
	$lic[$value[id]][license_id]	=$value["LICENSE_ID"];
	$lic[$value[id]][issue_date]	=$value["ISSUE_DATE"];
	$lic[$value[id]][purpose]	=$value["PURPOSE"];
	$lic[$value[id]][company]	=$value["COMPANY"];
}

########### AA Section Start
echo "<H1>"._("Redundancy Policy")."</H1>\n";
echo "<form action=vlc_server.php method=post>\n";
        echo "<input type=hidden name=step value=aa>\n";
echo "<table class=table_main>\n";
        echo "<tr class=topic_main>\n";
                echo "<td> "._("Target From List")."</td>\n";
                echo "<td> "._("Target To List")." </td>\n";
                echo "<td> "._("Function")." </td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
                echo "<td>\n";
                //temporary from list with license
                echo "<select name=from_uid>\n";
                $chk="selected";
                foreach($streamservers as $key=>$value)
                {
                        if($value[license_id] <> "")
                        {
                                echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}</option>\n";
                                $chk="";
                        }
                }
                echo "</select>\n";
                echo "</td>\n";
                echo "<td>\n";
                //temporary to list without license
                echo "<select name=to_uid>\n";
                $chk="selected";
                foreach($streamservers as $key=>$value)
                {
                        if($value[license_id] == "" and is_numeric($resource[$value[uid]][0][cpu]))
                        {
                                echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}</option>\n";
                                $chk="";
                        }
                }
                echo "</select>\n";
                echo "</td>\n";
                echo "<td>\n";
                if($_SESSION["ID_admin_qlync"])
                        echo "<input type=submit class=btn_4 value='"._("switch")."'>\n";
                echo "</td>\n";

        echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";



########### AA Section End


############  Add menu section end
echo "<BR>";
echo "<HR>";
#################adding menu item section
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
		echo "<td> "._("ID")." </td>\n";
    echo "<td> "._("UID")." </td>\n"; //jinho fix, previous is Name
		echo "<td> "._("LAN")." IP </td>\n";
		echo "<td>Ch. # "._("Used")."</td>\n";
//		echo "<td>Recording type</td>\n";
		echo "<td>"._("Recycle days")."</td>\n";
		echo "<td>"._("Resouces")."</td>\n";
		echo "<td>"._("Info")." </td>\n";
		echo "<td>"._("Status")."</td>\n";
		echo "<td>"._("Function")."</td>\n";

	echo "</tr>\n";
	foreach($streamservers as $key=>$value){
		echo "<tr class=tr_".($key%2+1).">\n";
		echo "<td>{$value["id"]}</td>\n";
		echo "<td>{$value["uid"]}<BR>\n";
		//get the version list
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$path = '/manage/manage_streamserver.php';
		$params = '?command=get_version&streamserver='.$value["uid"];
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$ver=json_decode($result,true);
		
			//echo "<font color=#0000CC>Version:{$ver[version][evostream]}</font>\n";
      echo "<font color=#0000CC>Version:{$ver[version][vlc]}</font>\n";//jinho fix
		echo "</td>\n";

		echo "<td>{$value["hostname"]}<br>{$value["internal_address"]}</td>\n";

		//get camera under the server

		$params = '?command=get_cameras_by_streamserver&streamserver=' . $streamservers[$key]['uid'];
    $stream_count=0;
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    if($value[license_id] <> "")
    {
			$result = curl_exec($ch);
			$cn_camera[$key]=json_decode($result,true);
		}
		// API A-4
		$params = '?command=get_recording_status&streamserver='.$streamservers[$key]['uid'];
    curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
    if($value[license_id] <> "")
    {
      $result = curl_exec($ch);
      $content=json_decode($result,true);
  		foreach($content[recording][record] as $key_rec=>$value_rec){
  			$rec[$value_rec[uid]][status]=$value_rec[status];
  			$rec[$value_rec[uid]][t]=gmdate("Y-m-d H:i:s",$value_rec[t]);
  //			$rec[status][$value_rec[uid]]=$value_rec[status];
        if($value_rec[status]=='Streaming') $stream_count++;
  		}
		}


		echo "<td>Total : ".sizeof($cn_camera[$key][cameras])."<br>( <font color=#0000FF>Streaming: {$stream_count}</font> )</td>\n";
		echo "<td>3</td>\n";


		echo "<td>\n";
			if(($resource[$value[uid]][0][memtotal]+0)==0)
				$resource[$value[uid]][0][memtotal]++;  //avoid divide by 0

			echo "CPU : ".round($resource[$value[uid]][0][cpu],2)."%.<BR>\n";
			echo "Mem : ".round((1-$resource[$value[uid]][0][memfree]/$resource[$value[uid]][0][memtotal])*100,2)."% ( ".round($resource[$value[uid]][0][memtotal]/1000000,0)." GB )";
			echo "<BR>\n";
//			echo gmdate("Y-m-d H:i:s",$cn_cpu[stats][$key]["t"]);
		echo "</td>\n";
		echo "<td>\n";
			if($_REQUEST["step"] <> "show_camera" or $_REQUEST["server_id"]<> $value[id])
			{
				if($value["license_id"] <> "")
				{
					echo "<a href=vlc_server.php?server_id={$value[id]}&step=show_camera>Show Camera</a>\n";
				}
			}
			else
			{
				echo "<a href=vlc_server.php>Close Camera</a>\n";
			}
		echo "</td>\n";
                echo "<td>\n";
			
                        if($value["license_id"] <> "")
                                echo ""._("Enabled")."";
                        else
                                echo ""._("Disable")."";
                echo "</td>\n";
                echo "<td>\n";
                // show unbind button with binding statusa
		if($_SESSION["ID_admin_qlync"]=="1")
                        if($value["license_id"] <> "")
                        {
                                echo "<form action=vlc_server.php method=post>\n";
                                        echo "<input type=hidden name=step value=disable>\n";
                                        echo "<input type=hidden name=uid value={$value["uid"]}>\n";
                                        echo "<input type=submit class=btn_4 value='"._("Disable")."'>\n";
                                echo "</form>\n";
                        }
                        else
                        {
                                echo "<form action=vlc_server.php method=post>\n";
                                        echo "<input type=hidden name=step value=enable>\n";
                                        echo "<input type=hidden name=uid value={$value["uid"]}>\n";
                                        echo "</select>\n";
                                        echo "<input type=submit class=btn_4 value='"._("Enable")."'>\n";
                                echo "</form>\n";

                        }


                echo "</td>\n";



		echo "</tr>\n";
// if click to show camera
		$t=array_count_values($rec[$value[uid]][total]);
		if($_REQUEST["step"] == "show_camera" && $_REQUEST["server_id"]==$value[id])
		{
// get onlin list to see if there is wait for streamm is binding but not online
	$path=$api_temp."/online_list.csv";

	$file = fopen($path,"r");

	while(! feof($file))
	  {
			flush($tmp);
		  $tmp=explode(",",fgets($file));
			$c_list[]=$tmp[1];
	  }
	
	fclose($file);
// get the account list from the csv
foreach($cn_camera[$key][cameras] as $key_cam =>$value_cam)
{
	$t1[]=substr($value_cam[device_uid],-12);
}
$tmp=implode("','",$t1);
$sql="select * from qlync.account_device where Mac in ('{$tmp}')";
sql($sql,$result_mac_list,$num_mac_list,0);
for($j=0;$j<$num_mac_list;$j++)
{
	fetch($db_mac_list,$result_mac_list,$j,0);
	$uid_list[]=$db_mac_list["Uid"];
        $check_list[$db_mac_list["Mac"]][fw]=$db_mac_list["Fw"];
        $check_list[$db_mac_list["Mac"]][ip]=$db_mac_list["Ip"];

}
//$path="{$api_temp}/account_device.csv";
//$handle=fopen($path,"r");
//
$i=0;
//while (($line = fgetcsv($handle)) !== FALSE) {
  //$line is an array of the csv elements
//	$uid_list[]=$line[4];
//}
//	fclose($handle);




			echo "<tr class=topic_main>\n";
				echo "<td> UID </td>\n";
				echo "<td> MAC </td>\n";
				echo "<td> Resolution </td>\n";
				echo "<td> Package </td>\n";
				echo "<td> Recycle Days </td>\n";
				echo "<td colspan=4> Recording Status</td>\n";
				echo "<td> Firmware </td>\n";
				echo "<td> IP </td>\n";
			echo "</tr>\n";
				sort($cn_camera[$key][cameras]);
				$row=0;
				foreach($cn_camera[$key][cameras] as $key_cam =>$value_cam){
	                        echo "<tr class=tr_".($row++%2+1).">\n";

					echo "<td>{$value_cam[id]}</td>\n";
					echo "<td>".substr($value_cam[device_uid],-12)."</td>\n";
					echo "<td>".$resolution[$value_cam[purpose]]."</td>\n";
					echo "<td nowrap>{$dataplan[$value_cam[dataplan]]}</td>\n";
					echo "<td>{$value_cam[recycle]}</td>\n";
					if(!in_array($value_cam[device_uid],$uid_list)) // check register or not
					{
						echo "<td colspan=4><font color=#000000>Unregistered</td>\n";
					}
					elseif(!in_array($value_cam[device_uid],$c_list)) //check online or not
					{
                                                $chk="<font color=#AAAA33>( Off-line )</font>";
                                                echo "<td colspan=4><font color=#FF3333></font> {$chk}</td>\n";

					}
					elseif($value_cam[dataplan]=="LV" or $value_cam[dataplan]=="D" or $value_cam[dataplan]=="EV" )
					{
                                                echo "<td colspan=4><font color=#33AAAA>Package Not Service</font> </td>\n";
					}
					elseif($rec[$value_cam[device_uid]][status]=="Streaming")
					{
                                                echo "<td colspan=4><font color=#0033FF><B>Streaming</B></font></td>\n";
					}
					else
					{
                                                echo "<td colspan=4><font color=#FF3333><B>Not Streaming</B></font></td>\n";
					}
					
					

///
						echo "<td>{$check_list[substr($value_cam[device_uid],-12)][fw]}</td>\n";
						echo "<td>\n";
			                                $country=geoip_country_code_by_addr($gi, $check_list[substr($value_cam[device_uid],-12)][ip]);
                        			        $city   =geoip_record_by_addr($gi2, $check_list[substr($value_cam[device_uid],-12)][ip]);

	                                                //echo $ip_to_city[$check_list[substr($value_cam[device_uid],-12)][ip]];
							echo $country."<BR>\n";
							echo $city->city."<BR>\n";
	                                                echo "{$check_list[substr($value_cam[device_uid],-12)][ip]}";
						echo "</td>\n";

        	                echo "</tr>\n";

				}
				
		}
		
	}
		
echo "</table>\n";
geoip_close($gi);
geoip_close($gi2);


?>


