<?PHP
        if($_REQUEST["step"]=="aa")
        {
                header("Refresh: 10;");
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
//jinho fix for recording_list issue
function updateHAserver ($fromUID, $toUID)
{
  if (($fromUID=="") or ($toUID=="")) return false;
      $sql="update isat.recording_list set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}'";

    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}
//jinho end
$web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
$path = 'manage_regions.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$params = array(
    'command' => 'get'
);
$url = $web_address . $path . '?' . http_build_query($params);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$content_r=json_decode($result,true);
$region_name=array();
$region_name[0]="<font color=#AA0000>None</font>";

foreach($content_r[regions] as $key=>$value)
{
	$region_name[$value[id]]=$value[name];
}
if ($content_r['status'] != 'success') {
    print $content_r['error_msg'];
    return;
}
else {
//    print_r($content['regions']);
}
// end of get list

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
$resolution["RVHI"]=" HD 30 fps"; //jinho added for Profile1
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
		//exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=disable&streamserver={$_REQUEST["from_uid"]}'");
    //jinho fix error case
        $path = '/manage/manage_streamserver.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
      	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        $params = '?command=disable&streamserver='.$_REQUEST["from_uid"];
        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);
        if ($content["status"]!="fail"){//jinho add check
            if (updateHAserver($_REQUEST["from_uid"],$_REQUEST["to_uid"]) )
             $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_manual_switch','From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} | Reson: Manual Switch Detection')";
            else $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_manual_switch','From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} | Reson: Manual Switch Detection | FAIL move recording_list')";
        }else{
          $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','FAIL:stream_manual_switch','From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} |  ".str_replace("'", "", $content["error_msg"])."')";
        }
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
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        $params = '?command=disable&streamserver='.$_REQUEST["uid"];
        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);
        if ($content["status"]!="fail")//jinho add check
             $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_manual_disable','From: {$_REQUEST["uid"]} | Reson: Manual Disable Detection')";
        else  $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','FAIL:stream_manual_disable','From: {$_REQUEST["uid"]} | ".str_replace("'", "", $content["error_msg"])."')";
                sql($sql,$result_tmp,$num_tmp,0);

}


######Bind license
#API A-9
if($_REQUEST["step"] == "set_port")
{
	$path = '/manage/manage_streamserver.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $params = '?command=set_port&port='.$_REQUEST["port"].'&streamserver=' . $_REQUEST["uid"];

        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);


}

if($_REQUEST["step"]=="enable")
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
$uidstats = Array();//jinho add check
require_once('xmlrpc_client.php');
foreach ($streamservers as $s) {
//	if($s[license_id] <> "")
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	 $resource[$s[uid]]=$rpc->call();
   	 //print_r($rpc->call());
     $uidstats[$s[uid]] = "ON";//jinho
    }
    catch (Exception $e) { 
    //echo $e;
    $resource[$s[uid]] =NULL;//jinho add check
    $uidstats[$s[uid]] = "OFF";
    }
}

        curl_close($ch);
///get server status for enable / disable
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$path = '/manage/manage_streamserver.php';

foreach($streamservers as $key=>$value)
{
  if ($uidstats[$value["uid"]] =="OFF"){//jinho skip
    $streamservers[$key][license_id]="";
    continue;
  }
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
                                //echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}</option>\n";
                                echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}  (Region : {$region_name[$value[region]]})</option>\n";

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
                                //echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}</option>\n";
                                echo "<option value=\"$value[uid]\" {$chk}>{$value[id]}  (Region : {$region_name[$value[region]]})</option>\n";

                                $chk="";
                        }
                }
                echo "</select>\n";
                echo "</td>\n";
                echo "<td>\n";
                if($_SESSION["ID_admin_qlync"]) //jinho add for god admin only 
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
    if ($uidstats[$value["uid"]] =="ON"){//jinho
		//get the version list
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$path = '/manage/manage_streamserver.php';
		$params = '?command=get_version&streamserver='.$value["uid"];
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$ver=json_decode($result,true);
		
			echo "<font color=#0000CC>Version:{$ver[version][vlc]}-{$ver[version][package]}</font>\n";
    }else{
      echo "<font color=red>Version: NA</font>";
    }
			echo "<HR>\n";
			echo "<font color=#0000CC>Region :{$region_name[$value[region]]}</font>\n";
		echo "</td>\n";

		echo "<td>{$value["hostname"]}<br>{$value["internal_address"]}\n";
                        echo "<form action=vlc_server.php method=post>\n";
                                echo "<input type=hidden name=step value=set_port>\n";
                                echo "<input type=hidden name=uid value={$value["uid"]}>\n";
                                echo "Port:\n";
				echo "<input type=text size=1 name=port value={$value["port"]}>\n";
                                echo "<input type=submit class=btn_4 value=\""._("submit")."\">\n";
                        echo "</form>\n";

		echo "</td>\n";
    $stream_count=0;
    if ($uidstats[$value["uid"]] =="ON"){//jinho
		//get camera under the server
		$params = '?command=get_cameras_by_streamserver&streamserver=' . $streamservers[$key]['uid'];
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
        if($value_rec[status]=='Streaming') $stream_count++;
        $rec[$value_rec[uid]][t]=gmdate("Y-m-d H:i:s",$value_rec[t]);
      }
		}
		echo "<td>Total: ".sizeof($cn_camera[$key][cameras])."<br>( <font color=#0000FF>Streaming: {$stream_count}</font> )\n</td>\n";
    }else{//jinho
      echo "<td>Total : <font color=red>NA</font><br></td>\n";
    }
		echo "<td></td>\n";

		echo "<td>\n";
    if (!is_null($resource[$value[uid]] )){//jinho added
			if(($resource[$value[uid]][0][memtotal]+0)==0)
				$resource[$value[uid]][0][memtotal]++;  //avoid divide by 0
      
			echo "CPU : ".round($resource[$value[uid]][0][cpu],2)."%.<BR>\n";
			echo "Mem : ".round((1-$resource[$value[uid]][0][memfree]/$resource[$value[uid]][0][memtotal])*100,2)."% ( ".round($resource[$value[uid]][0][memtotal]/1000000,0)." GB )";
			echo "<BR>\n";
//			echo gmdate("Y-m-d H:i:s",$cn_cpu[stats][$key]["t"]);
    }else{//jinho added
    echo "<font color=red>CPU : NA<br>\nMem : NA</font><br>\n"; 
    }

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
    if ( ($_SESSION["ID_admin_qlync"]=="1") and ($uidstats[$value["uid"]] =="ON") )//jinho
		//if($_SESSION["ID_admin_qlync"]=="1")
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
  //jinho auto update online_list if file is more than 3 minutes
    $path="{$api_temp}/online_list.csv";
      $fileLastModifyTime=new DateTime();
      $fileLastModifyTime->setTimestamp(filemtime($path));
      $now = new DateTime();
      $diff=$now->diff($fileLastModifyTime);
      if ($diff->i > 3){    //more than 3 minutes
        $url="http://{$api_id}:{$api_pwd}@{$api_path}/fetch_online_clients.php";
        exec("wget ".$url." -O ".$path);
      }else echo "online_list updated ".$diff->i." min ago.";
    //jinho auto update online_list end
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


