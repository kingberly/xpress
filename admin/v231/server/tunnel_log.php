<?php
/**************
 *jinho add tunnel monitor alarm if not set auto HA
 **************/
define("DEBUG_FLAG","OFF"); //OFF ON

ini_set('memory_limit', '256M');
$p=dirname(__FILE__);
$p=explode("/html",$p);

include("{$p[0]}/doc/config.php");
include("{$p[0]}/doc/mysql_connect.php");
include("{$p[0]}/doc/sql.php");
if(file_exists("{$api_temp}/tunnel_min.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/tunnel_min.log");
        chmod("{$api_temp}/tunnel_min.log",0777);
}
if(file_exists("{$api_temp}/tunnel_hourly.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/tunnel_hourly.log");
        chmod("{$api_temp}/tunnel_hourly.log",0777);
}

include("{$api_temp}/tunnel_min.log");
include("{$api_temp}/tunnel_hourly.log");
#########################Linux AA check
/*
$t=scandir($disk_uuid_path);
$t2=implode("-",$t);
if($_REQUEST["active"]=="" )
{

//        exec("php5 ".dirname(__FILE__)." active");  //close the auto switch function
 exec("wget -q -O - '{$home_url}/html/server/tunnel_log.php?active=on&uuid={$t2}' --no-check-certificate");  //close the auto switch functiona
//echo "wget -q -O - '{$home_url}/html/server/tunnel_log.php?active=on&uuid={$t2}' --no-check-certificate";

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
$path = '/manage/manage_tunnelserver.php';
######################
//deal with each day
if(date("Hi") == "0100")
{
        $h=fopen("{$api_temp}/tunnel_daily.log","a+");

        foreach($tunnel_log_hour as $key=>$value)
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

                fwrite($h,"<?\$tunnel_log_day[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }
        foreach($tunnel_log_hour as $key=>$value)
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
                if ( ($hour=="100") and (bCheckLastMEMisZero($tunnel_log_day, $key) ) )    sendEmailAlarm("Tunnel {$key} Full Memory Usage Detected."); 
                //////////////
                fwrite($h,"<?\$tunnel_log_day[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

        fclose($h);
        $h=fopen("{$api_temp}/tunnel_hourly.log","w+");
}else{
        $h=fopen("{$api_temp}/tunnel_hourly.log","a+");

}


//deal with each hour
if(date("i") == "00")
{

  foreach($tunnel_log as $key=>$value)
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
    fwrite($h,"<?\$tunnel_log_hour[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
  }
        foreach($tunnel_log as $key=>$value)
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
				    ///////////////jinho send email every hour
				    if ( ($hour=="100") and (bCheckLastMEMisZero($tunnel_log_hour, $key) ) )	sendEmailAlarm("Tunnel {$key} Full Memory Usage Detected."); 
				    //////////////
              fwrite($h,"<?\$tunnel_log_hour[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

  fclose($h);
  $h=fopen("{$api_temp}/tunnel_min.log","w+");
}
else
{
  fclose($h);
  $h=fopen("{$api_temp}/tunnel_min.log","a+");

}



#######################

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
require_once('xmlrpc_client.php');
foreach ($tunnelservers as $s) {
    try {
     $rpc = new xmlrpc_client($s, $stats);
  $resource[$s['uid']]=$rpc->call();
//     print_r($rpc->call());
    }
    catch (Exception $e) {}
}


foreach($tunnelservers as $key=>$value){
/*
                $params = '?command=get_stats&tunnelserver='.$value["uid"];
                curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                $result = curl_exec($ch);
                flush($cn_cpu);
                $cn_cpu=json_decode($result,true);
*/
    $d=date("YmdHi");
fwrite($h,"<?\$tunnel_log[\"{$value["uid"]}\"][\"cpu\"][\"".$d."\"]=\"".round($resource[$value['uid']]['cpu']+0,2)."\"?>\n");
fwrite($h,"<?\$tunnel_log[\"{$value["uid"]}\"][\"mem\"][\"".$d."\"]=\"".(round(1-$resource[$value['uid']]['memfree']/$resource[$value['uid']]['memtotal'],4)*100)."\"?>\n");
//     echo $cn_cpu[stats][cpu];

}
fclose($h);
chmod("{$api_temp}/tunnel_min.log",0777);
curl_close($ch);
##########################################################################
/////start to check if the tunnel server need to translate auto maticallya
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
flush($lic);
foreach($license as $key=>$value){
            if($value['tunnel_server_id']<> "")
            {
                $lic[$value['tunnel_server_id']]['id']=$value['id'];
                $lic[$value['tunnel_server_id']]['version']=$value['version'];
                $lic[$value['tunnel_server_id']]['begin']=gmdate("Y-m-d H:i:s",$value['begin']);
                $lic[$value['tunnel_server_id']]['expire']=gmdate("Y-m-d H:i:s",$value['expire']);
                $lic[$value['tunnel_server_id']]['license_key']=$value['license_key'];
            }

}


//Examtarget/to list

foreach($tunnelservers as $key=>$value)
{
if (DEBUG_FLAG == "ON") var_dump($resource[$value['uid']]); //null
      if($lic[$value["id"]]['id'] <> "")
      {
              $check_list[$value['id']]=$value['uid'];
             
      }
      else
      {
        if(($resource[$value['uid']]['cpu'] >= "0") and ($resource[$value['uid']]['memtotal'] <> "")) //jinho updated
        {       
          $backup_list[]=$value['uid'];
        }
      }
}
//Excute check 
$to_num=sizeof($backup_list);
$sql="select * from qlync.oem_info where Cat1='Admin Web Portal'";
sql($sql,$result_admin,$num_admin,0);
for($i=0;$i<$num_admin;$i++)
{
        fetch($db_admin,$result_admin,$i,0);
        $admin_web_portal[$db_admin["Cat2"]]=$db_admin["Content"];
}
foreach($check_list as $key_from_uid=>$from_uid)
{
/////////jinho lic server will do log
if ( is_null($resource[$from_uid]) and $lic[$key_from_uid]['license_key'] <>"" and bCheckLastMEMisZero($tunnel_log, $from_uid) ) 
{
    $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','lic_tunnel_event','From: {$from_uid} | Reson: NULL Lic-Service Detection ')";
    sql($sql,$result_tmp,$num_tmp,0);
}
////////
/*
if(($resource[$from_uid]['cpu']+0) == "0")
{
  sleep(2);
  $rpc = new xmlrpc_client($s, $stats);
  $dchk[$from_uid]=$rpc->call();
  $resource[$from_uid]['cpu']=$dchk[$from_uid]['cpu']+0;
}
*/
  if ($resource[$from_uid]['cpu'] == ""  and $resource[$from_uid]['memtotal'] == "" and $lic[$key_from_uid]['license_key'] <>""  
	and $admin_web_portal["Aa_backup_switch_tunnel"]=="on" and $to_num >0)
	// CPU change to 0 and has backup server
  //if($resource[$from_uid]['cpu'] == "" and $to_num >0 and $lic[$key_from_uid]['license_key'] <>"" and $admin_web_portal["Aa_backup_switch_tunnel"]=="on") 
  {
  // start to switch
    $l=sizeof($backup_list)-$to_num;
    exec ("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_tunnelserver.php?command=migrate&from={$from_uid}&to={$backup_list[$l]}&force=true&migrate_port=false&migrate_license=true'");
                $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','tunnel_auto_switch','From: {$from_uid} | To: {$backup_list[$l]} | Reson: CPU Zero Detection ')";
                sql($sql,$result_tmp,$num_tmp,0);

/*
                $h=fopen("{$p[0]}/html/log/".date("Ymd").".php","a+");
                fwrite($h,"<?\$sys_log[\"tunnel_auto_switch\"][\"".date("Ymd-His")."\"]=\"From: {$from_uid} | To: {$backup_list[$l]} | Reson: CPU Zero Detection\"?>\n");
                fclose($h);
*/

//              the switch email notification system
                $subject="Tunnel Server Auto Backup Switch happened-".date("Y-m-d H:i:s");
                $body="From: {$from_uid} | To: {$backup_list[$l]} | Reson: CPU Zero Detection"; //jinho remove  {$resource[$from_uid][0][cpu]}
                $web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
                $path = '/manage/manage_mail.php';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                $params = array(
                         'command' => 'send',
                         'receiver'=> $admin_web_portal["Mis_oem_email"],
                         'subject' => $subject,
                         'body'    => $body,
                         'oem_id' => $oem);
                $url = $web_address . $path . '?' . http_build_query($params);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch);
                $content=json_decode($result,true);



  // need to modified the system log
    // " [date("Ymd H:i:s")][Tunnel Server][AA Switch] CPU 0%  From {$from_uid} to {$backup_list[$l]}
  // the number reduce for one
    $to_num--;
    
  }
}

//cehc the license notification
if(date("Hi") =="0100")
{
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
  flush($lic);
  foreach($license as $key=>$value){
                if($value[tunnel_server_id]<> "")
                {
      $chk_lic[$value[id]][id]  =$valule[tunnel_server_id];
      $chk_lic[$value[id]][expire]  =gmdate("Ymd",$value[expire]);
      $chk_lic[$value[id]][days]  =(gmdate("Y",$value[expire])-date("Y"))*365+(gmdate("m",$value[expire])-date("m"))*30+gmdate("d",$value[expire])-date("d");
      if($chk_lic[$value[id]][days] < 30)
      {
        $exp_lic[]=$value[id];// expired candidate
      }
    }
    else
    {
      $unchk_lic[$value[id]][expire]  =gmdate("Ymd",$value[expire]);
      
    }
                $lic[$value[id]][id]=$value[tunnel_server_id];
                $lic[$value[id]][version]=$value[version];
                $lic[$value[id]][begin]=gmdate("Y-m-d H:i:s",$value[begin]);
                $lic[$value[id]][expire]=gmdate("Y-m-d H:i:s",$value[expire]);
                $lic[$value[id]][license_key]=$value[license_key];

  }
  // cehck section

          $import_target_url = "http://partner.qlync.com/html/activate_code/tunnel_upload.php";
        $import_data_array = array();
    $li=0;
    foreach($license as $key=>$value)
    {
      $li++;
      $a=$li/10;
      $b=$li%10;
                $import_data_array[$a][$b] = array('server_no'=>$value['tunnel_server_id'],'license_id'=>$value[id],'begin'=>gmdate("Y-m-d H:i:s",$value[begin]),'expire'=>gmdate("Y-m-d H:i:s",$value[expire]),'license_key'=>$value[license_key]);
    }
    // $data_array[] = array('mac' => 'xxxxxx', 'ac' => 'yyyyyy');
    var_dump($import_data_array);

    // ...
  foreach($import_data_array as $key_s =>$value_s){
          $import_post = array('command'=> 'upload','cid'=>$oem,'data'=>json_encode($value_s));

// CURL Post
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $import_post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //Fixes the HTTP/1.1 417 Expectation Failed Bug
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result=curl_exec($ch);
        curl_close($ch);
  }





  // email content section
  $body="You have total ".sizeof($lic)." Tunnels in the system.<BR>";
  $body.="There are ".sizeof($unchk_lic)." License is free for one year on the server.<BR>";
  $body.="You have ".sizeof($chk_lic). " License will be expired in one month.<BR>";
  $body.="Please ask your sales to prepare the updated license for the coming year<BR>";
  $body.="Then then Licnese auto switch will be happened in next day.";
  $subject=" Tunnel License Expired Notification -".date("Y-m-d H:i:s");
  //email out section
                $web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
                $path = '/manage/manage_mail.php';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

                $params = array(
                         'command' => 'send',
                         'receiver'=> $mis_oem_email,
                         'subject' => $subject,
                         'body'    => $body,
                         'oem_id' => $oem);
                $url = $web_address . $path . '?' . http_build_query($params);
                curl_setopt($ch, CURLOPT_URL, $url);
//                $result = curl_exec($ch);
                $content=json_decode($result,true);
}
//jinho check last cpu =0   bCheckLastCPUisZero($tunnel_log, $from_uid)
function bCheckLastCPUisZero($log,$uid)
{//web_log[uid][cpu][date]
  if (sizeof($log)>0){
    end($log[$uid]['cpu']);
    //echo key($log[$uid]['cpu']). " ,value=".current($log[$uid]['cpu'])."\n";
    if ( current($log[$uid]['cpu']) == "0" ) return true;
  }else return false;
}
//jinho check last mem =100
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
    //add to log
    $tmp = explode("/",$_SERVER['PHP_SELF']);
    $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','".end($tmp)."','Send Email: {$body}')";
    sql($sql,$result_tmp,$num_tmp,0);

		$body.= "\n<br>".date("Ymd-His")." The Tunnel Server Has Detected Error since Last Hour.\n<br>Please check Server ASAP.\n<br>";
/* 
		$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
		$path = '/manage/manage_mail.php';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		$params = array(
		         'command' => 'send',
		         'receiver'=> MIS_EMAIL,
		         'subject' => $subject,
		         'body'    => $body,
		         'oem_id' => $oem);
		$url = $web_address . $path . '?' . http_build_query($params);
*/
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