<?php
ini_set('memory_limit', '256M');

$p=dirname(__FILE__);
$p=explode("/html",$p);

include("{$p[0]}/doc/config.php");
include("{$p[0]}/doc/mysql_connect.php");
include("{$p[0]}/doc/sql.php");
if(file_exists("{$api_temp}/web_min.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/web_min.log");
        chmod("{$api_temp}/web_min.log",0777);
}
if(file_exists("{$api_temp}/web_hourly.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/web_hourly.log");
        chmod("{$api_temp}/web_hourly.log",0777);
}


include("{$api_temp}/web_min.log");
include("{$api_temp}/web_hourly.log");
#########################Linux AA check
/*
$t=scandir($disk_uuid_path);
$t2=implode("-",$t);
if($_REQUEST["active"]=="" )
{

//        exec("php5 ".dirname(__FILE__)." active");  //close the auto switch function
 exec("wget -q -O - '{$home_url}/html/server/web_log.php?active=on&uuid={$t2}' --no-check-certificate");  //close the auto switch function

//--no-check-certificate

        exit();
}

if($_REQUEST["uuid"] <> $t2 or $_REQUEST["uuid"] == "")
{
        exit();
}
*/

#########################

$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_webserver.php';
######################
//deal with each day
if(date("Hi") == "0100")
{
        $h=fopen("{$api_temp}/web_daily.log","a+");

        foreach($web_log_hour as $key=>$value)
        {
                $total=0;
                $count=0;;
                foreach($value["cpu"] as $key_2 =>$value_2)
                {
                        $total+=$value_2;
                        $count++;
                }
                if($count >0)
                        $hour=round($total/$count,2);
                else
                        $hour="0";
                fwrite($h,"<?\$web_log_day[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

        foreach($web_log_hour as $key=>$value)
        {
                $total=0;
                $count=0;;
                foreach($value["mem"] as $key_2 =>$value_2)
                {
                        $total+=$value_2;
                        $count++;
                }
                if($count >0)
                        $hour=round($total/$count,2);
                else
                        $hour="0";
                ///////////////jinho every day send email
                if ( ($hour=="100") and (bCheckLastMEMisZero($web_log_day, $key) ) ) sendEmailAlarm("Web {$key} Full Memory Usage Detected.");
                //////////////
                fwrite($h,"<?\$web_log_day[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

        fclose($h);
        $h=fopen("{$api_temp}/web_hourly.log","w+");
}
else
{
        $h=fopen("{$api_temp}/web_hourly.log","a+");

}
//deal with each hour
if(date("i") == "00")
{

	foreach($web_log as $key=>$value)
	{
		$total=0;
		$count=0;;
		foreach($value["cpu"] as $key_2 =>$value_2)
		{
			$total+=$value_2;
			$count++;
		}
		if($count >0)
			$hour=round($total/$count,2);
		else
			$hour="0";
		fwrite($h,"<?\$web_log_hour[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
	}

        foreach($web_log as $key=>$value)
        {
                $total=0;
                $count=0;;
                foreach($value["mem"] as $key_2 =>$value_2)
                {
                        $total+=$value_2;
                        $count++;
                }
                if($count >0)
                        $hour=round($total/$count,2);
                else
                        $hour="0";
    ///////////////jinho every hour log
    if ( ($hour=="100") and (bCheckLastMEMisZero($web_log_hour, $key) ) )
    {
			$sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','web_server_event','From: {$key} | Reson: Full Memory Usage Detection')";
      sql($sql,$result_tmp,$num_tmp,0);
		} 
    //////////////
                fwrite($h,"<?\$web_log_hour[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

	fclose($h);
	$h=fopen("{$api_temp}/web_min.log","w+");
}
else
{
	fclose($h);
	$h=fopen("{$api_temp}/web_min.log","a+");

}




#######################

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
require_once("xmlrpc_client.php");
foreach ($webservers as $s) {
    try {
   	 $rpc = new xmlrpc_client($s, $stats);
	$resource[$s["uid"]]=$rpc->call();
//   	 print_r($rpc->call());
    }
    catch (Exception $e) {}
}



foreach($webservers as $key=>$value){
		$d=date("YmdHi");
fwrite($h,"<?\$web_log[\"{$value["uid"]}\"][\"cpu\"][\"".$d."\"]=\"".round($resource[$value["uid"]][0]["cpu"]+0,2)."\"?>\n");
fwrite($h,"<?\$web_log[\"{$value["uid"]}\"][\"mem\"][\"".$d."\"]=\"".round((1-$resource[$value["uid"]][0]["memfree"]/$resource[$value["uid"]][0]["memtotal"])*100,2)."\"?>\n");
}
fclose($h);
chmod("{$api_temp}/web_min.log",0777);
curl_close($ch);

if(file_exists("{$api_temp}/memcached_min.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/memcached_min.log");
        chmod("{$api_temp}/memcached_min.log",0777);
}
$backup_list=array();
include("{$api_temp}/memcached_min.log");
//Examtarget/to list

                foreach($webservers as $key=>$value)  // get all the web server online now
                {
			if(is_numeric($resource[$value["uid"]][0]["memfree"]) and $resource[$value["uid"]][0]["memfree"] > 0)
			{
				$check_list[] =$value["internal_address"]; 
			} 
                }

    //jackey fixed on Jan-6,2016
     if((sizeof(array_diff($check_list,$backup_list))<>0 or sizeof(array_diff($backup_list,$check_list))<>0) and sizeof($check_list) > 0)  // array diffirent
		{
		        exec(" echo '' > {$api_temp}/memcached_min.log");
		        chmod("{$api_temp}/memcached_min.log",0777);

			foreach($check_list as $key=>$value)
			{
				exec (" echo '<?\$backup_list[]=\"{$value}\";?>' >> {$api_temp}/memcached_min.log");

			}
			// send to the system for memcached
				$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
				$path = '/manage/manage_webserver.php';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$params = array('command'=>'set_memcached','memcached'=>$check_list);
				curl_setopt($ch, CURLOPT_URL, $web_address . $path . '?' . 
		    	    	http_build_query($params) );
				$result = curl_exec($ch);
				$content=json_decode($result,true);
				if ($content['status'] != 'success') {
				    	print $content['error_msg'];
			    		return;
				}
				else {
				    	print 'Success';
				}

				curl_close($ch);

		
			
		}
$sql="select * from qlync.oem_info where Cat1='Admin Web Portal'";
sql($sql,$result_admin,$num_admin,0);
for($i=0;$i<$num_admin;$i++)
{
        fetch($db_admin,$result_admin,$i,0);
        $admin_web_portal[$db_admin["Cat2"]]=$db_admin["Content"];
}

//Excute check
foreach($webservers as $key=>$value)
{
        if( is_null($resource[$from_uid])  and bCheckLastMEMisZero($web_log, $value[uid]) )
        //if(($resource[$value["uid"]][0]["cpu"]) ==""  ) // CPU change to 0 and has backup server
        {
                $subject="Web Server CPU Zero Detection happened-".date("Y-m-d H:i:s");
                $body="uid: {$value[uid]} | Reson: NULL Service Detection {$resource[$from_uid][0][cpu]}";
                $web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
                $path = '/manage/manage_mail.php';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                $params = array(
                         'command' => 'send',
                         'receiver'=> $admin_web_portal[$db_admin["Mis_oem_email"]],
                         'subject' => $subject,
                         'body'    => $body,
                         'oem_id' => $oem);
                $url = $web_address . $path . '?' . http_build_query($params);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch);
                $content=json_decode($result,true);


        // start to switch
                $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','web_server_event','From: {$value[uid]} | Reson: CPU Zero Detection')";
//                sql($sql,$result_tmp,$num_tmp,0);//jinho stop logging every min

/*
                $h=fopen("{$p[0]}/html/log/".date("Ymd").".php","a+");
                fwrite($h,"<?\$sys_log[\"web_server_event\"][\"".date("Ymd-His")."\"]=\"From: {$value[uid]} Reson: CPU Zero Detection\"?>\n");
                fclose($h);
*/

        }
}
//jinho check last cpu =0 bCheckLastCPUisZero($web_log, $value[uid])
//echo shell_exec("tail {$api_temp}/web_min.log");
function bCheckLastCPUisZero($log,$uid)
{//web_log[uid][cpu][date]
  if (sizeof($log)>0){
    end($log[$uid]['cpu']);
    //echo key($log[$uid]['cpu']). " ,value=".current($log[$uid]['cpu'])."\n";
    if ( current($log[$uid]['cpu']) == "0" ) return true;
  }else return false;
}
function bCheckLastMEMisZero($log,$uid)
{//web_log[uid][mem][date]
  if (sizeof($log)>0){
    end($log[$uid]['mem']);
    //echo key($log[$uid]['mem']). " ,value=".current($log[$uid]['mem'])."\n";
    if ( current($log[$uid]['mem']) == "100" ) return true;
  }else return false;
}
function sendEmailAlarm($body)
{
	define("MIS_EMAIL","alarm@tdi-megasys.com");
	  global $api_id, $api_pwd, $api_ip, $api_port,$oem;
		$subject = "{$oem} Xpress4.1 Admin Notificaiton";
		$body.= "\n<br>The Tunnel Server Has Detected Error since Last Hour.\n<br>Please check Server ASAP.\n<br>";
		$url = "http://{$api_ip}:{$api_port}/Mailer/mailer.php";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		$postData['func'] = 'send';
		$postData['body'] = $body;
		$postData['subject'] = $subject;
		$postData['name'] = "Xpress4.1 Admin Alarm Reminder";
		$postData['email'] = MIS_EMAIL;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    $result = curl_exec($ch);
		//$content=json_decode($result,true);
}