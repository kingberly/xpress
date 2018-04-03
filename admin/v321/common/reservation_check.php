<?PHP
require_once ("/var/www/qlync_admin/doc/config.php");
require_once "{$home_path}/header.php";
include("{$home_path}/html/common/cid.php");

##################################################################
# Do unshare first while the time out or there is new share needed
# if unshare happened, then the vitual cam has to enable and move the file
###################################################################
$mac_list=array();
$s2=0;
$sym["lunch"]='a';
$sym['dinner']='b';
#1. check if there is share and need to be share
$sql="select * from qlync.reservation where S1=0 and Date_start='".date("Y-m-d")."' and Time_start='".date("H")."' order by Date_start desc";
sql($sql,$result_list,$num_list,0);
for($i=0;$i<$num_list;$i++)
{
        fetch($db_list,$result_list,$i,0);
        $sql="select * from qlync.account where id='{$db_list["FID"]}' limit 0,1";
        sql($sql,$result_restaurant,$num_restaurant,0);
        fetch($db_r,$result_restaurant,0,0);
        $sql="select * from isat.user where id='{$db_list["Room_id"]}' limit 0,1";
        sql($sql,$result_room,$num_room,0);
        fetch($db_room,$result_room,0,0);
        $tmp=explode("_",strtolower($db_room["name"]));
        //check there is some one need to be new share but the camera is still under share with others.
        $sql="select * from isat.device_share where owner_id='{$db_room["id"]}' ";
        sql($sql,$result_mac,$num_mac,0);
	if($num_mac>0)
	{
		$sql="select * from qlync.reservation where S1=1 and S2=0 and Room_id='{$db_list["Room_id"]}' and FID='{$db_list["FID"]}' limit 0,1";
		sql($sql,$result_list2,$num_list2,0);
		fetch($db_list2,$result_list2,0,0);

		// add virtual mac first
                for($n=1;$n<4;$n++)
                {
                        $sql="select cid,pid,mac,activated_code from isat.series_number,isat.license where left(mac,1)='V' and sid is null and license_id=license.id limit 0,1";
                        sql($sql,$result_mac2,$num_mac2,0);
                        fetch($db_mac2,$result_mac2,0,0);

                        $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
			$import_data_array=array();
//                        $import_data_array[] = array('mac' => "{$db_mac2["mac"]}", 'ac' => "{$db_mac2["activated_code"]}",'user' =>  "R".$db_r["ID"]."{$tmp["1"]}_".str_replace("-","",substr($db_list2["Date_start"],-5)).$db_list2["Time_start"],);
			$import_data_array[] = array('mac' => "{$db_mac2["mac"]}", 'ac' => "{$db_mac2["activated_code"]}",'user' =>  str_replace("-","",substr($db_list2["Date_start"],-5)).$db_r["ID"].$sym[$db_list2["Number"]].$tmp["1"],);



                        $data = array('action'=>'bind_device_order','data'=>json_encode($import_data_array),'cid' => "{$db_mac2["cid"]}",'pid' => "{$db_mac2["pid"]}");
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$import_target_url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));

                        $result=curl_exec($ch);



                            $content=array();
                            $content=json_decode($result,true);
                            curl_close($ch);
				echo "bind virtual mac";
				echo "<BR>";
				print_r($data);
				print_r($content);

                        if($content["status"]=='success')
                        {
				$mac_list[]=$db_mac2["cid"]."CC-".$db_mac2["mac"];
                        // cahnge the camera name
                                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
                                $path='/manage_device.php';
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                if(trim($db_mac2["mac"]) <> "")
                                {
                                        $mobile_tmp=explode("-",$_REQUEST["mobile"]);

                                        $params = array(
                                                'command'       =>'rename_device',
                                                'new_name'      =>"歷史回顧".str_pad($n,2,"00",STR_PAD_LEFT),
                                                'mac_addr'      =>$db_mac2["mac"] );


                                        $url = $web_address . $path . '?' . http_build_query($params);
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        $result = curl_exec($ch);
                                        $content=json_decode($result,true);
					echo "<BR>";
					echo "change name";
					print_r($content);
                                }
                                curl_close($ch);

				$web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
                                $path = 'manage_streamserver.php';
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $params = array(
                                        'command'       => 'update_camera',
                                        'mac'           => $db_mac2["mac"],
                                        'days'          => '7',
                                        'dataplan'      => 'AR'
                	         );
                                $url = $web_address . $path . '?' . http_build_query($params);
                                curl_setopt($ch, CURLOPT_URL, $url);
                                $result = curl_exec($ch);
                                $content_r=json_decode($result,true);
                //              print_r($content_r);
                                curl_close();
				sleep(2);

		// 		clear all the recording file old 2016/11/08

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $params = array(
                                        'command'       => 'remove_recording',
                                        'uid'           => $db_mac2["cid"]."CC-".$db_mac2["mac"],
                                 );
                                $url = $web_address . $path . '?' . http_build_query($params);
                                curl_setopt($ch, CURLOPT_URL, $url);
                                $result = curl_exec($ch);
                                $content_r=json_decode($result,true);
                //              print_r($content_r);
                                curl_close();
                                sleep(2);


			}
		}



		// remove the mac share
		for($m=0;$m<$num_mac;$m++)
	        {
	        	fetch($db_mac,$result_mac,$m,0);
// move recording file first
$web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
$path = 'manage_streamserver.php';
$ch = curl_init();
set_time_limit(0);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 40000);
$d_start=strtotime($db_list2["Date_start"]." ".$db_list2["Time_start"].":00:00");
$d_end=strtotime("now");
echo $d_start;
$d_start-=($db_list2["Time_zone"]*3600);
$d_end-=($db_list2["Time_zone"]*3600);

echo $d_start;
$params = array(
//'command' => 'copy_recording',
'command'	=> 'transfer_recording',
'from_uid' => $db_mac["uid"],
'to_uid' => $mac_list[$m],
'start' => date("YmdHis",$d_start),
'end' => date("YmdHis",$d_end),
);
echo "<BR>";
echo "move Recording<BR>";
print_r($params);
$url = $web_address . $path . '?' . http_build_query($params);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$content = json_decode($result,true);
echo "<BR>";
print_r($content);

if ($content['status'] != 'success') {
print $content['error_msg'];
$s2=1;
return;
} else {
print "count: " . $content['count'] . "\n";
}

curl_close();
sleep(3);
//		if($S2==1)
		{

	                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
	                $path = 'manage_device.php';
	                $ch = curl_init();
	                curl_setopt($ch, CURLOPT_HEADER, false);
	                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        	        $params = array(
	                        'command'               => 'remove_share',
	                        'mac_addr'              => substr($db_mac["uid"],-12),
	                        'visitor_id'            => $db_mac["visitor_id"],
	                );
	                $url = $web_address . $path . '?' . http_build_query($params);
	                curl_setopt($ch, CURLOPT_URL, $url);
	                $result = curl_exec($ch);
	                $content_r=json_decode($result,true);
	 //             print_r($content_r);
	                curl_close();
			sleep(3);
		}


		}
//		if($s2==1)
		{
	                $sql="update qlync.reservation set S2=1 ,Time_s2='".date("Ymd His")."' where ID='{$db_list2["ID"]}'";
	                sql($sql,$result,$num,0);
		}
//
	}

}
#2. check if the share camera has meet the time limitation and share backa
$mac_list=array();
$s2=0;
$sql="select * from qlync.reservation where S1=1 and S2=0 order by Date_start desc";
sql($sql,$result_list,$num_list,0);
echo "123";
for($i=0;$i<$num_list;$i++)
{
        fetch($db_list,$result_list,$i,0);
        $sql="select * from qlync.account where id='{$db_list["FID"]}' limit 0,1";
        sql($sql,$result_restaurant,$num_restaurant,0);
        fetch($db_r,$result_restaurant,0,0);
        $sql="select * from isat.user where id='{$db_list["Room_id"]}' limit 0,1";
        sql($sql,$result_room,$num_room,0);
        fetch($db_room,$result_room,0,0);
        $tmp=explode("_",strtolower($db_room["name"]));

	$d_now=substr(date("Ymd"),3)*24+date("H");
	$d_start=substr(str_replace("-","",$db_list["Date_start"]),3)*24+$db_list["Time_start"]+5;
	if($d_now >=$d_start)
	{
		// add the virtua mac first
                // add virtual mac first
		echo "234";
               for($n=1;$n<4;$n++)
                {
                        $sql="select cid,pid,mac,activated_code from isat.series_number,isat.license where left(mac,1)='V' and sid is null and license_id=license.id limit 0,1";
                        sql($sql,$result_mac2,$num_mac2,0);
			
                        fetch($db_mac2,$result_mac2,0,0);

                        $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
                        //    $import_data_array = array();
				$import_data_array=array();
//                                $import_data_array[] = array('mac' => "{$db_mac2["mac"]}", 'ac' => "{$db_mac2["activated_code"]}",'user' =>  "R".$db_r["ID"]."{$tmp["1"]}_".str_replace("-","",substr($db_list["Date_start"],-5)).$db_list["Time_start"],);
                        $import_data_array[] = array('mac' => "{$db_mac2["mac"]}", 'ac' => "{$db_mac2["activated_code"]}",'user' =>  str_replace("-","",substr($db_list["Date_start"],-5)).$db_r["ID"].$sym[$db_list["Number"]].$tmp["1"],);




                        // $data_array[] = array('mac' => 'xxxxxx', 'ac' => 'yyyyyy');
                        // ...
                        $data = array('action'=>'bind_device_order','data'=>json_encode($import_data_array),'cid' => "{$db_mac2["cid"]}",'pid' => "{$db_mac2["pid"]}");
                        // CURL Post
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,$import_target_url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));

                        $result=curl_exec($ch);

                        //              echo curl_error($ch);


                            // JSON Decoded Array to $content
                            $content=array();
                            $content=json_decode($result,true);
                            curl_close($ch);

                        if($content["status"]=='success')
                        {
//                              echo "<H1>Binding success!!</H1><BR>";
                        // cahnge the camera name
                                $mac_list[]=$db_mac2["cid"]."CC-".$db_mac2["mac"];

                                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
                                $path='/manage_device.php';
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                if(trim($db_mac2["mac"]) <> "")
                                {
                                        $mobile_tmp=explode("-",$_REQUEST["mobile"]);

                                        $params = array(
                                                'command'       =>'rename_device',
                                                'new_name'      =>"歷史回顧".str_pad($n,2,"00",STR_PAD_LEFT),
                                                'mac_addr'      =>$db_mac2["mac"] );


                                        $url = $web_address . $path . '?' . http_build_query($params);
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        $result = curl_exec($ch);
                                        $content=json_decode($result,true);
                                }
                                curl_close($ch);
				sleep(3);
                                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
                                $path = 'manage_streamserver.php';
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $params = array(
                                        'command'       => 'update_camera',
                                        'mac'           => $db_mac2["mac"],
                                        'days'          => '7',
                                        'dataplan'      => 'AR'
                                );
                                $url = $web_address . $path . '?' . http_build_query($params);
                                curl_setopt($ch, CURLOPT_URL, $url);
                                $result = curl_exec($ch);
                                $content_r=json_decode($result,true);
                //              print_r($content_r);
                                curl_close();
				sleep(3);

                //              clear all the recording file old 2016/11/08

                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_HEADER, false);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                $params = array(
                                        'command'       => 'remove_recording',
                                        'uid'           => $db_mac2["cid"]."CC-".$db_mac2["mac"],
                                 );
                                $url = $web_address . $path . '?' . http_build_query($params);
                                curl_setopt($ch, CURLOPT_URL, $url);
                                $result = curl_exec($ch);
                                $content_r=json_decode($result,true);
                //              print_r($content_r);
                                curl_close();
                                sleep(2);




                        }

		}	
		// time out and need to be unshare
	        //check there is some one need to be new share but the camera is still under share with others.
			echo "334";
		       $sql="select * from isat.device_share where owner_id='{$db_room["id"]}' ";
		       sql($sql,$result_mac,$num_mac,0);
			echo $sql;
			echo $num_mac;
		        for($m=0;$m<$num_mac;$m++)
		        {
		                fetch($db_mac,$result_mac,$m,0);


$d_start=strtotime($db_list["Date_start"]." ".$db_list["Time_start"].":00:00");
$d_end=strtotime("now");
echo "<BR>";
echo $d_start;
$d_start-=($db_list["Time_zone"]*3600);
$d_end-=($db_list["Time_zone"]*3600);

echo $d_start;

$web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
$path = 'manage_streamserver.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 40000);
$params = array(
//'command' => 'copy_recording',
'command' 	=> 'transfer_recording',
'from_uid' => $db_mac["uid"],
'to_uid' => $mac_list[$m],
'start' => date("YmdHis",$d_start),
'end' => date("YmdHis",$d_end),
);
$url = $web_address . $path . '?' . http_build_query($params);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$content = json_decode($result,true);
if ($content['status'] != 'success') {
print $content['error_msg'];
$s2=1;
return;
} else {
print "count: " . $content['count'] . "\n";
}
curl_close();
print_r($params);
//			if($s2==1)
			{
	
		                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
		                $path = 'manage_device.php';
		                $ch = curl_init();
		                curl_setopt($ch, CURLOPT_HEADER, false);
		                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		                $params = array(
	                        'command'               => 'remove_share',
	                        'mac_addr'              => substr($db_mac["uid"],-12),
	                        'visitor_id'            => $db_mac["visitor_id"],
	                );
	                $url = $web_address . $path . '?' . http_build_query($params);
	                curl_setopt($ch, CURLOPT_URL, $url);
	                $result = curl_exec($ch);
	                $content_r=json_decode($result,true);
	 //             print_r($content_r);
	                curl_close();
			sleep(2);
			}
	
		        }

//			if($s2==1)
			{
	        	        $sql="update qlync.reservation set S2=1 ,Time_s2='".date("Ymd His")."' where ID='{$db_list["ID"]}'";
		                sql($sql,$result,$num,0);
			}

	}

}



$sql="select * from qlync.reservation where S1=0 and S2=0 and Date_start='".date("Y-m-d")."' and Time_start='".date("H")."' order by Date_start desc";
//$sql="select * from qlync.reservation where S1=0 and ID=22 order by Date_start desc";
sql($sql,$result_list,$num_list,0);
for($i=0;$i<$num_list;$i++)
{
	fetch($db_list,$result_list,$i,0);
	//select FID for restaurant
	//seect Room ID will know the room admin number
        $sql="select * from qlync.account where id='{$db_list["FID"]}' limit 0,1";
        sql($sql,$result_restaurant,$num_restaurant,0);
        fetch($db_r,$result_restaurant,0,0);
        $sql="select * from isat.user where id='{$db_list["Room_id"]}' limit 0,1";
        sql($sql,$result_room,$num_room,0);
        fetch($db_room,$result_room,0,0);
	//select camera under room admin and share to the share account
	$sql="select * from isat.query_info where owner_id='{$db_room["id"]}' group by mac_addr";
	sql($sql,$result_mac,$num_mac,0);
	 $tmp=explode("_",strtolower($db_room["name"]));




	
		for($m=0;$m<$num_mac;$m++)
		{
			fetch($db_mac,$result_mac,$m,0);

	                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
	                $path = 'manage_device.php';
	                $ch = curl_init();
	                curl_setopt($ch, CURLOPT_HEADER, false);
	                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	                        $params = array(
	                                'command'               => 'add_share',
	                                'mac_addr'              => $db_mac["mac_addr"],
//	                                'user'                  => "R".$db_r["ID"]."{$tmp["1"]}_".date("mdH"),
					'user'			=> date("md").$db_r["ID"].$sym[$db_list["Number"]].$tmp["1"],

	                        );
//			print_r($params);
	                $url = $web_address . $path . '?' . http_build_query($params);
	                curl_setopt($ch, CURLOPT_URL, $url);
			sleep(1);
	                $result = curl_exec($ch);
	                $content_r=json_decode($result,true);
	                curl_close();
//			print_r($content_r);
			sleep(1);
		}

	sleep(1);
	// if finished, update s1=1
	$sql="update qlync.reservation set S1='1' , Time_s1='".date("Ymd His")."' where ID='{$db_list["ID"]}'";
	sql($sql,$result_s1,$num_s1,0);



	echo $db_list["Date_start"];
}
?>
