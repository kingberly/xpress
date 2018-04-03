<?
/****************
 *Validated on Sep-15,2015
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit', '64M');
include("../../header.php");
include("../../menu.php");

#Authentication Section
$sql="select * from qlync.menu where Name = 'Mobile Server'";
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

#######################


$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_rtmpd.php';

##### unbind license
if($_REQUEST["step"] == "set_port")
{

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        $params = '?command=set_port&port='.$_REQUEST["port"].'&rtmpd='.$_REQUEST["uid"];
        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);


}

#API A-10
if($_REQUEST["step"] == "unbind")
{

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

        $params = '?command=disable&rtmpd='.$_REQUEST["uid"];
        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        curl_close($ch);


}


######Bind license
#API A-9
if($_REQUEST["step"] == "bind")
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
        $params = '?command=enable&rtmpd=' . $_REQUEST["uid"];

        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
        //var_dump($content);
        curl_close($ch);


}


######################

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_servers';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$rtmpdservers = $content['rtmpd'];
$stats = $content['stats'];
/*
require_once('xmlrpc_client.php');
foreach ($rtmpdservers as $s) {
//	if($s[license_id] <> "")
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	$resource[$s[uid]]=$rpc->call();
//   	 print_r($rpc->call());
    }
    catch (Exception $e) {}
}
*/
        curl_close($ch);

//////////////////////////////////////////////////////////
// List the tunnel server license
#API A-7
############  Add menu section end
#################adding menu item section
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
		echo "<td> ID </td>\n";
		echo "<td> Name </td>\n";
		echo "<td> LAN IP </td>\n";
		echo "<td>Ch. # Used</td>\n";
		echo "<td>Resouces</td>\n";
		echo "<td>Status </td>\n";
		echo "<td>Status</td>\n";
		echo "<td>Function</td>\n";

	echo "</tr>\n";
	foreach($rtmpdservers as $key=>$value){
		echo "<tr class=tr_".($key%2+1).">\n";
		echo "<td>{$value["id"]}</td>\n";
		echo "<td>{$value["uid"]}<BR>\n";

		//get the version list
    /*
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$params = '?command=get_version&rtmpd='.$value["uid"];
			curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$ver=json_decode($result,true);
			echo "<font color=#0000CC>Version : {$ver[version][package]}</font>\n";
      */
		echo "</td>\n";

		echo "<td>{$value["internal_address"]}\n";
			echo "<BR>";
			echo "<form type=post action=".$_SERVER['PHP_SELF'].">\n";
				echo "<input type=hidden name=step value=set_port>\n";
				echo "<input type=hidden name=uid value={$value["uid"]}>\n";
				echo "Port:\n";
				echo "<input type=txt name=port value='{$value["port"]}' size=1>\n";
				echo "<input type=submit value=submit>\n";
			echo "</form>\n";

		echo "</td>\n";

		//get camera under the server
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$params = '?command=get_cameras_by_server&rtmpd=' . $value["uid"];;
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$cn_camera[$key]=json_decode($result,true);


		echo "<td>".sizeof($cn_camera[$key][cameras])."</td>\n";

                        //  get the recording status for this serve
/*
		// API A-4
		$params = '?command=get_recording_status&streamserver='.$rtmpdservers[$key]['uid'];
	        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                if($value[license_id] <> "")
                {

	                $result = curl_exec($ch);
	                $content=json_decode($result,true);

		foreach($content[recording] as $key_rec=>$value_rec){
			$rec[$value_rec[uid]][status]=$value_rec[status];
			$rec[$value_rec[uid]][t]=gmdate("Y-m-d H:i:s",$value_rec[t]);
			$rec[status][$value_rec[uid]]=$value_rec[status];
		}
		}
*/
		echo "<td>\n";
/* //xmlrpc is not ready on v2.2.5
		#API A-7
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$params = '?command=get_stats&rtmpd='.$rtmpdservers[$key]['uid'];
		curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
		$result = curl_exec($ch);
		flush($cn_cpu);
		$cn_cpu=json_decode($result,true);
     curl_close($ch);

      echo "CPU : ".round($resource[$value[uid]][cpu],2)." %<BR> ";
			echo "MEM :".(round(1-$resource[$value[uid]][memfree]/$resource[$value[uid]][memtotal],4)*100)."% (".round($resource[$value[uid]][memtotal]/1000000,2)."GB)<BR>DISK:\n";
			echo "<BR>\n";
//			echo gmdate("Y-m-d H:i:s",$cn_cpu[stats][$key]["t"]);

                #API A-8          //xmlrpc is not ready
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$params = '?command=get_channels&rtmpd=' . $rtmpdservers[$key]['uid'];

                curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                $result = curl_exec($ch);
                $cn_camera=json_decode($result,true);
                curl_close($ch);
		echo "{$cn_camera[channels][devices]}/{$cn_camera[channels][viewers]}\n"; 
*/    
		echo "</td>\n";
		echo "<td>\n";
			if($_REQUEST["step"] <> "show_camera" or $_REQUEST["server_id"]<> $value[id])
			{
				if($value["license_id"] <> "")
				{
					echo "<a href=".$_SERVER['PHP_SELF']."?server_id={$value[id]}&step=show_camera>Show Camera</a>\n";
				}
			}
			else
			{
				echo "<a href=".$_SERVER['PHP_SELF'].">Close Camera</a>\n";
			}
//				$t=array_count_values($rec[$value[uid]][total]);
//				var_dump($rec[status]);
		echo "</td>\n";
                echo "<td>\n";
			$ch= curl_init();
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

	                $params = '?command=is_enabled&rtmpd=' . $rtmpdservers[$key]['uid'];
        	        curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                        $result = curl_exec($ch);
			$tmp	= json_decode($result,true);

                        if($tmp["enabled"])
                                echo "Enabled";
                        else
                                echo "Disabled";
                echo "</td>\n";
                echo "<td>\n";
                // show unbind button with binding statusa
		if($_SESSION["ID_admin_qlync"]=="1")
                        if($tmp["enabled"])
                        {
                                echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n";
                                        echo "<input type=hidden name=step value=unbind>\n";
                                        echo "<input type=hidden name=uid value={$value["uid"]}>\n";
                                        echo "<input type=submit value=Unbind>\n";
                                echo "</form>\n";
                        }
                        else
                        {
                                echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n";
                                        echo "<input type=hidden name=step value=bind>\n";
                                        echo "<input type=hidden name=uid value={$value["uid"]}>\n";
                                        echo "<input type=submit value=Bind>\n";
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

//$path="{$api_temp}/account_device.csv";
//$handle=fopen($path,"r");
//
$i=0;
//while (($line = fgetcsv($handle)) !== FALSE) {
  //$line is an array of the csv elements
//	$uid_list[]=$line[4];
//}
//	fclose($handle);
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





			echo "<tr class=topic_main>\n";
				echo "<td> UID </td>\n";
				echo "<td> MAC </td>\n";
				echo "<td> Firmware </td>\n";
        echo "<td> Status </td>\n";
			echo "</tr>\n";
				sort($cn_camera[$key][cameras]);
//				var_dump($cn_camera[$key][cameras]);
				$row=0;
				foreach($cn_camera[$key][cameras] as $key_cam =>$value_cam){
	                        echo "<tr class=tr_".($row++%2+1).">\n";

					echo "<td>{$value_cam[id]}</td>\n";
					echo "<td>".substr($value_cam[device_uid],-12)."</td>\n";
///
						echo "<td>{$check_list[substr($value_cam[device_uid],-12)][fw]}</td>\n";
//jinho added online_list.csv status
					if(!in_array($value_cam[device_uid],$uid_list)) // check register or not
					{
						echo "<td><font color=#000000>Unregistered</td>\n";
					}
					elseif(!in_array($value_cam[device_uid],$c_list)) //check online or not
					{
            $chk="<font color=#AAAA33>( Off-line )</font>";
            echo "<td><font color=#FF3333></font> {$chk}</td>\n";
					}
          elseif($value_cam[dataplan]=="LV" or $value_cam[dataplan]=="D" or $value_cam[dataplan]=="EV" )
					{
              //echo "<td ><font color=#33AAAA>Package Not Service</font> </td>\n";
              echo "<td ><font color=#33AAAA>Package Not Recording</font> </td>\n";
					}/* //no stream server id to proceed API-4
					elseif($rec[$value_cam[device_uid]][status]=="Streaming")
					{
              echo "<td ><font color=#0033FF><B>Streaming</B></font></td>\n";
					}*/
					else
					{
              //echo "<td ><font color=#FF3333><B>Not Streaming</B></font></td>\n";
              echo "<td ><font color=#0033FF><B>Online</B></font></td>\n";
					}
           
//jinho added onlin_list end
        	                echo "</tr>\n";

				}

		}

	}

echo "</table>\n";


?>
