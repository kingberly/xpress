<?
	if($_REQUEST["step"]=="aa")
	{
	        header("Refresh: 1;");
	}
	else
	{
                header("Refresh: 15;");

	}

include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Web Server'";
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
$path = '/manage/manage_webserver.php';



##########  update the server port

$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_webservers';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$webservers = $content['webservers'];
$stats = $content['stats'];
require('xmlrpc_client.php');

foreach ($webservers as $s) {
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	$resource[$s[uid]]=$rpc->call();
//   	 print_r($rpc->call());
    }
    catch (Exception $e) {
      $resource[$s[uid]] =NULL;//jinho add check 
    }
}


curl_close($ch);
/////////////////////////////////////////////////////




///////////////////////////////////////////////////////////
// List the tunnel server license

############  Add menu section end
echo "<BR>";
echo "<HR>";
####################prepare data

#################adding menu item section
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
		echo "<td> "._("ID")." </td>\n";
		echo "<td> UID </td>\n";
		echo "<td> "._("LAN")." IP </td>\n";
		echo "<td> "._("Name")."</td>\n";
		echo "<td> "._("Status")."</td>\n";
	echo "</tr>\n";
	foreach($webservers as $key=>$value){
		echo "<tr class=tr_".($key%2+1).">\n";
		echo "<td>{$value["id"]}</td>\n";
		echo "<td>{$value["uid"]}<BR>\n";
if (!is_null($resource[$value[uid]] )){//jinho added 
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$params = '?command=get_version&webserver='.$value["uid"];
			curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
			$result = curl_exec($ch);
			$ver=json_decode($result,true);
			echo "<font color=#0000CC>Version: {$ver[version][iSVW]}</font><BR>\n";
			echo "<font color=#0000CC>PHP. : {$ver[version][php]}</font><BR>\n";
			echo "<font color=#0000CC>Lighttp. : {$ver[version][lighttpd]}</font><BR>\n";
}else{//jinho added
    echo "<font color=red>Version : NA<br><br>\n</font><br>\n";
}
                        echo "<HR>\n";
                        echo "<font color=#0000CC>Region : {$region_name[$value[region]]}</font>\n";




		echo "</td>\n";
		echo "<td>\n";
			echo "{$value["internal_address"]}\n";
//			echo "port:{$value["stats"]["port"]}\n";
		echo "</td>\n";

		//get camera under the server
		echo "<td>{$value["hostname"]}</td>\n";

		echo "<td nowrap>\n";
if (!is_null($resource[$value[uid]] )){//jinho added
                        echo "CPU : ".round($resource[$value[uid]][0][cpu],2)." %<BR> MEM :".(round(1-$resource[$value[uid]][0][memfree]/$resource[$value[uid]][0][memtotal],4)*100)."% (".round($resource[$value[uid]][0][memtotal]/1000000,2)."GB)<BR>DISK:\n";
}else{//jinho added
echo "<font color=red>CPU : NA<br>\nMem : NA<br>DISK:\n</font><br>\n"; 
}

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


		echo "</tr>\n";
		
	}
		
echo "</table>\n";
?>


