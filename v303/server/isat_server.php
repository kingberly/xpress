<?php
	if($_REQUEST["step"]=="aa")
	{
	        header("Refresh: 10;"); //jinho extend from 1 to 10
	}
	else
	{
          header("Refresh: 30;"); //jinho extend from 15 to 30

	}

include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Tunnel Server'";
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

################################################
#API A-1
$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_tunnelserver.php';
#########################################################AA section Strart
if($_REQUEST["step"]=="aa")
{
	if($_REQUEST["from_uid"]<> "" and $_REQUEST["to_uid"]<> "")
	{
//        	exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_tunnelserver.php?command=migrate&from={$_REQUEST["from_uid"]}&to={$_REQUEST["to_uid"]}'");
		exec( "wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_tunnelserver.php?command=migrate&from={$_REQUEST["from_uid"]}&to={$_REQUEST["to_uid"]}&force=true&migrate_port=false&migrate_license=true'");
		$sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','tunnel_manual_switch','From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} | Reson: Manual Switch Detection')";
		sql($sql,$result_tmp,$num_tmp,0);
/*
                $h=fopen("{$home_path}/html/log/".date("Ymd").".php","a+");
                fwrite($h,"<?\$sys_log[\"tunnel_manual_switch\"][\"".date("Ymd-His")."\"]=\"From: {$_REQUEST["from_uid"]} | To: {$_REQUEST["to_uid"]} | Reson: Manual Switch Detection\"?>\n");
//                fclose($h);
*/
	}


}




###########################################################AA section Stop
##########  update the server port
if($_SESSION["CID"]=="N99" and $_REQUEST["step"]=="update_port")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$params = "?command=set_port&port={$_REQUEST["port"]}&tunnelserver=".$_REQUEST["uid"];
	curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
	$result = curl_exec($ch);
	$content_port_set=json_decode($result,true);
	curl_close($ch);

}

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_tunnelservers';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$tunnelservers = $content['tunnelservers'];
$stats = $content['stats'];
$uidstats = Array();//jinho add check
require('xmlrpc_client.php');

foreach ($tunnelservers as $s) {
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	$resource[$s[uid]]=$rpc->call();
//   	 print_r($rpc->call());
  $uidstats[$s[uid]] = "ON";//jinho 
    }
    catch (Exception $e) {
      $resource[$s[uid]] =NULL;//jinho add check
      $uidstats[$s[uid]] = "OFF";
    }
}


curl_close($ch);
/////////////////////////////////////////////////////
##### unbind license
#API A-5
if($_REQUEST["step"] == "unbind")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$params = '?command=unbind_license&tunnelserver='.$_REQUEST["uid"];
	curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
	$result = curl_exec($ch);
	$content=json_decode($result,true);
	//var_dump($content);
	curl_close($ch);	
}

######Bind license
#API A-4
if($_REQUEST["step"] == "bind")
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$params = '?command=bind_license&license_id='.$_REQUEST["lic_id"].'&tunnelserver=' . $_REQUEST["uid"];

        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);


}

///////////////////////////////////////////////////////////
// List the tunnel server license
#API A-2
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_licenses';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$license=$content["licenses"];
curl_close($ch);
############  Add menu section end
echo "<BR>";
echo "<HR>";
####################prepare data
flush($lic);
foreach($license as $key=>$value){
		if($value[tunnel_server_id]<> "")
		{
                $lic[$value[tunnel_server_id]][id]=$value[id];
                $lic[$value[tunnel_server_id]][version]=$value[version];
                $lic[$value[tunnel_server_id]][begin]=gmdate("Y-m-d",$value[begin]);
                $lic[$value[tunnel_server_id]][expire]=gmdate("Y-m-d",$value[expire]);
                $lic[$value[tunnel_server_id]][license_key]=$value[license_key];
		}

}
################################AA Section
echo "<H1>"._("Redundancy Policy")."</H1>\n";
echo "<form action=isat_server.php method=post>\n";
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

		foreach($tunnelservers as $key=>$value)
		{
        if ($uidstats[$value["uid"]] =="OFF"){//jinho skip
          continue;
        }
                        if($lic[$value["id"]][id] <> "")
			{
				echo "<option value=\"$value[uid]\" {$chk}>{$value[id]} (Region : {$region_name[$value[region]]})</option>\n";
				$chk="";
			}
		}
		echo "</select>\n";
		echo "</td>\n";
                echo "<td>\n";
                //temporary to list without license
                echo "<select name=to_uid>\n";
		$chk="selected";
                foreach($tunnelservers as $key=>$value)
                {
                    if ($uidstats[$value["uid"]] =="OFF"){//jinho skip
                      continue;
                    }
                        if($lic[$value["id"]][id] == "" and is_numeric($resource[$value[uid]][cpu]))
                        {
                                echo "<option value=\"$value[uid]\" {$chk}>{$value[id]} (Region : {$region_name[$value[region]]})</option>\n";
				$chk="";
                        }
                }
                echo "</select>\n";
                echo "</td>\n";
		echo "<td>\n";
			echo "<input type=submit class=btn_4 value='"._("switch")."'>\n";
		echo "</td>\n";	

	echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";
echo "<HR>\n";

#################adding menu item section
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
		echo "<td> "._("ID")." </td>\n";
		echo "<td> UID </td>\n";
		echo "<td> "._("LAN")." IP </td>\n";
		echo "<td> "._("Name")."</td>\n";
		echo "<td> "._("Status")."</td>\n";
		echo "<td> # </td>\n";
		echo "<td> "._("License")." "._("ID")."</td>\n";
		echo "<td> "._("Start")." "._("Date")." </td>\n";
		echo "<td> "._("License")." "._("Key")." </td>\n";
		echo "<td> "._("Status")." </td>\n";
		echo "<td> "._("Function")." </td>\n";
	echo "</tr>\n";
	foreach($tunnelservers as $key=>$value){
		echo "<tr class=tr_".($key%2+1).">\n";
		echo "<td>{$value["id"]}</td>\n";
		echo "<td>{$value["uid"]}<BR>\n";
    if ($uidstats[$value["uid"]] =="ON"){//jinho
	                $ch = curl_init();
	                curl_setopt($ch, CURLOPT_HEADER, false);
        	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$params = '?command=get_version&tunnelserver='.$value["uid"];
			curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$ver=json_decode($result,true);
			echo "<font color=#0000CC>Version : {$ver[version][library]}</font>\n";
}else{//jinho added
  echo "<font color=red>Version : NA</font>\n";
}
			echo "<HR>\n";
                        echo "<font color=#0000CC>Region : {$region_name[$value[region]]}</font>\n";


		echo "</td>\n";
		echo "<td>\n";
			echo "{$value["internal_address"]}\n";
			if($_SESSION["CID"] <> "N99")
			{
				echo "<HR>Public Port:<BR>{$value["port"]}\n";
			}
			else
			{
				echo "<HR>Public Port:<BR>\n";
				echo "<form action=isat_server.php method=post>\n";
					echo "<input type=hidden name=step value=update_port>\n";
					echo "<input type=hidden name=uid value={$value["uid"]}>\n";
					echo "<input type=text name=port value=\"{$value["port"]}\" size=\"8\">\n";
					echo "<input type=submit class=btn_4 value='"._("Update")."'>\n";
				echo "</form>\n";
			}
		echo "</td>\n";

		//get camera under the server
		echo "<td>{$value["hostname"]}</td>\n";
    if ($uidstats[$value["uid"]] =="ON"){//jinho
		// get the server status under this server
		#API A-7
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$params = '?command=get_stats&tunnelserver='.$value["uid"];
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
		$result = curl_exec($ch);
		flush($cn_cpu);
		$cn_cpu=json_decode($result,true);
	        curl_close($ch);

		echo "<td nowrap>\n";
//			echo "CPU : ".round($cn_cpu[stats][cpu],2)." %<BR> MEM :".(round(1-$cn_cpu[stats][memfree]/$cn_cpu[stats][memtotal],4)*100)."% (".round($cn_cpu[stats][memtotal]/1000000,2)."GB)<BR>DISK:\n";
                        echo "CPU : ".round($resource[$value[uid]][cpu],2)." %<BR> ";
			echo "MEM :".(round(1-$resource[$value[uid]][memfree]/$resource[$value[uid]][memtotal],4)*100)."% (".round($resource[$value[uid]][memtotal]/1000000,2)."GB)<BR>DISK:\n";

		echo "</td>\n";
                // get the server status under this server
                #API A-8
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$params = '?command=get_channels&tunnelserver='.$value["uid"];

                curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                $result = curl_exec($ch);
                $cn_camera=json_decode($result,true);
                curl_close($ch);
		echo "<td>{$cn_camera[channels][devices]}/{$cn_camera[channels][viewers]}</td>\n";

}else{//jinho added
echo "<td nowrap><font color=red>CPU : NA<br>\nMEM : NA<br>DISK:</font></td><td></td>\n"; 
}
		echo "<td>{$value["license_id"]}</td>\n";
		echo "<td>{$lic[$value["id"]][begin]}\n";
		if($_SESSION["ID_admin_qlync"] ==1)
		{
			echo " ~ <BR> {$lic[$value["id"]][expire]}</td>\n";
		}
		else
		{
			echo "</td>\n";
		}
		echo "<td>{$lic[$value["id"]][license_key]}\n";
			if(substr($lic[$value["id"]][expire],0,4)==date("Y") and (substr($lic[$value["id"]][expire],5,2)-1)<=date("m"))
			{
				echo "<font color=#FF0000><B>\n";
					echo "Expried Soon..";
				echo "</B></font>\n";

			}
		echo "</td>\n";
		echo "<td>\n";
			if($lic[$value["id"]][id] <> "")
				echo ""._("Bind")."";
			else
				echo ""._("Unbind")."";
		echo "</td>\n";
		echo "<td>\n";
		// show unbind button with binding status
		if($_SESSION["ID_admin_qlync"])
                        if($lic[$value["id"]][id] <> "")
			{
if ($uidstats[$value["uid"]] =="ON"){//jinho
				echo "<form action=isat_server.php method=post>\n";
					echo "<input type=hidden name=step value=unbind>\n";
					echo "<input type=hidden name=uid value={$value["uid"]}>\n";
					echo "<input type=submit class=btn_4 value='"._("Unbind")."'>\n";
				echo "</form>\n";
}//jinho
			}
			else
			{
if ($uidstats[$value["uid"]] =="ON"){//jinho
				echo "<form action=isat_server.php method=post>\n";
          echo "<input type=hidden name=step value=bind>\n";
          echo "<input type=hidden name=uid value={$value["uid"]}>\n";
					echo "<select name=lic_id>\n";
					foreach($license as $key_lic=>$value_lic){
						if($value_lic[tunnel_server_id]=="")
						{
							echo "<option value={$value_lic[id]}>{$value_lic[license_key]}</option>\n";
						}
					}
					echo "</select>\n";
         echo "<input type=submit class=btn_4 value='"._("Bind")."'>\n";
        echo "</form>\n";
}//jinho
			}

		echo "</td>\n";


		echo "</tr>\n";
		
	}
		
echo "</table>\n";

?>


