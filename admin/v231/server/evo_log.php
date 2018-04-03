<?PHP
/**************
 *jinho add stream monitor alarm if not set auto HA
 **************/
define("DEBUG_FLAG","OFF"); //OFF ON

ini_set('memory_limit', '256M');

$p=dirname(__FILE__);
$p=explode("/html",$p);

include("{$p[0]}/doc/config.php");
include("{$p[0]}/doc/mysql_connect.php");
include("{$p[0]}/doc/sql.php");
if(file_exists("{$api_temp}/evo_min.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/evo_min.log");
        chmod("{$api_temp}/evo_min.log",0777);
}
if(file_exists("{$api_temp}/evo_hourly.log"))
{}
else
{
        exec(" echo '' > {$api_temp}/evo_hourly.log");
        chmod("{$api_temp}/evo_hourly.log",0777);
}

include("{$api_temp}/evo_min.log");
include("{$api_temp}/evo_hourly.log");
#########################Linux AA check
/*
$t=scandir($disk_uuid_path);
$t2=implode("-",$t);
if($_REQUEST["active"]=="" )
{
  
//        exec("php5 ".dirname(__FILE__)." active");  //close the auto switch function
 exec("wget -q -O - '{$home_url}/html/server/evo_log.php?active=on&uuid={$t2}' --no-check-certificate");  //close the auto switch function

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
$path = '/manage/manage_streamserver.php';
######################
//deal with each day
if(date("Hi") == "0100")
{
        $h=fopen("{$api_temp}/evo_daily.log","a+");

        foreach($evo_log_hour as $key=>$value)
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
                fwrite($h,"<?\$evo_log_day[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

        foreach($evo_log_hour as $key=>$value)
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
                ///////////////jinho
                if ( ($hour=="100") and (bCheckLastMEMisZero($evo_log_hour, $key) ) )
									sendEmailAlarm("Stream {$key} Full Memory Usage Detected."); 
                //////////////
                fwrite($h,"<?\$evo_log_day[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

        fclose($h);
        $h=fopen("{$api_temp}/evo_hourly.log","w+");
}
else
{
        $h=fopen("{$api_temp}/evo_hourly.log","a+");

}

//deal with each hour
if(date("i") == "00")
{

  foreach($evo_log as $key=>$value)
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

    fwrite($h,"<?\$evo_log_hour[\"{$key}\"][\"cpu\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
  }

        foreach($evo_log as $key=>$value)
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
                ///////////////jinho
                if ( ($hour=="100") and (bCheckLastMEMisZero($evo_log, $key) ) )
									sendEmailAlarm("Stream {$key} Full Memory Usage Detected."); 
                //////////////

                fwrite($h,"<?\$evo_log_hour[\"{$key}\"][\"mem\"][\"".substr($key_2,0,10)."\"]=\"{$hour}\"?>\n");
        }

  fclose($h);
  $h=fopen("{$api_temp}/evo_min.log","w+");
}
else
{
  fclose($h);
  $h=fopen("{$api_temp}/evo_min.log","a+");

}




#######################

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
require_once("xmlrpc_client.php");
foreach ($streamservers as $s) {
    try {
     $rpc = new xmlrpc_client($s, $stats);
  $resource[$s[uid]]=$rpc->call();
//     print_r($rpc->call());
    }
    catch (Exception $e) {}
}

$sql="select * from qlync.menu where Name='Stream Server'";
sql($sql,$result_n,$num_n,0);
fetch($db_n,$result_n,0,0);

        foreach($streamservers as $key=>$value){
/*                $params = '?command=get_stats&streamserver='.$value["uid"].'&history=1';
                curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
                $result = curl_exec($ch);
                flush($cn_cpu);
                $cn_cpu=json_decode($result,true);
*/
    if($db_n["Link"]=='/html/server/vlc_server.php')
    {
            $params = '?command=is_enabled&streamserver='.$value["uid"];
            curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
            $result = curl_exec($ch);
            $r=json_decode($result,true);
            if($r["status"]=="success" and $r["enabled"])
            {
                      $streamservers[$key][license_id]="enabled";
                      $streamservers[$value["uid"]][license_id]="enabled";//jinho fix
            }
            else
            {
                    $streamservers[$key][license_id]="";
            }
    }

    $d=date("YmdHi");
    //if(($resource[$value[uid]][0][memtotal]+0)==0) //jinho remark
    //  $resource[$value[uid]][0][memtotal]++;
fwrite($h,"<?\$evo_log[\"{$value["uid"]}\"][\"cpu\"][\"".$d."\"]=\"".round($resource[$value[uid]][0][cpu]+0,2)."\"?>\n");
fwrite($h,"<?\$evo_log[\"{$value["uid"]}\"][\"mem\"][\"".$d."\"]=\"".round((1-$resource[$value[uid]][0][memfree]/$resource[$value[uid]][0][memtotal])*100,2)."\"?>\n");




}
fclose($h);
chmod("{$api_temp}/evo_min.log",0777);
curl_close($ch);
//Examtarget/to list

foreach($streamservers as $key=>$value)
{
//if (DEBUG_FLAG == "ON") echo $streamservers[$key][license_id];
//if (DEBUG_FLAG == "ON") echo $key."\n";
//if (DEBUG_FLAG == "ON") var_dump($resource[$value['uid']]); //null

  if($value[license_id] <> "")
  {
          $check_list[]=$value[uid];

  }
  else
  {
		if($resource[$value['uid']][0]['cpu'] >= "0" and  $resource[$value['uid']][0]['memtotal']<> "") //jinho updated
		{
		                  $backup_list[]=$value[uid];
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
foreach($check_list as $from_uid)
{
if (DEBUG_FLAG == "ON") var_dump($resource[$from_uid]); //null
if (DEBUG_FLAG == "ON") echo $streamservers[$from_uid][license_id]; //null
/////////jinho add alarm log
if ( is_null($resource[$from_uid]) and $streamservers[$from_uid][license_id]=="enabled" and bCheckLastMEMisZero($evo_log, $from_uid) ) 
{
    $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','enabled_stream_event','From: {$from_uid} | Reson: NULL Enable-Service Detection ')";
    sql($sql,$result_tmp,$num_tmp,0);
}
////////
      //jinho fix for cpu0/mem100 condition
      if(  $to_num >0 and $admin_web_portal["Aa_backup_switch_stream"]=="on" 
      and $resource[$from_uid][0][cpu] =="" and  $resource[$from_uid][0]['memtotal'] == "" ) 
//        if(($resource[$from_uid][0][cpu]+0) =="0" and $to_num >0 and $admin_web_portal["Aa_backup_switch_stream"]=="on") // CPU change to 0 and has backup server
        {
        // start to switch
                $l=sizeof($backup_list)-$to_num;
    if($db_n["Link"]=='/html/server/vlc_server.php')
    {
                        exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=enable&streamserver={$backup_list[$l]}'");  //close the auto switch function
                        exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=disable&streamserver={$from_uid}'");  //close the auto switch function

    }
    else
    {
      exec("wget -q -O - 'http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_streamserver.php?command=migrate&from={$from_uid}&to={$backup_list[$l]}&migrate_license=true'");  //close the auto switch function
    }

                $sql="insert into qlync.sys_log (Time_s1,Cat1,Content) values ('".date("Ymd-His")."','stream_auto_switch','From: {$from_uid} | To: {$backup_list[$l]} | Reson: CPU Zero Detection ')";
                sql($sql,$result_tmp,$num_tmp,0);

//    the switch email notification system
    $subject="VLC Stream Server Auto Backup Switch happened-".date("Y-m-d H:i:s");
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
                // " [date("Ymd H:i:s")][Stream Server][AA Switch] CPU 0%  From {$from_uid} to {$backup_list[$l]}
        // the number reduce for one
                $to_num--;

        }
}
//jinho check last cpu =0 bCheckLastCPUisZero($evo_log, $from_uid)
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
if (DEBUG_FLAG == "ON") echo key($log[$uid]['mem']). " ,value=".current($log[$uid]['mem'])."\n"; 
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

    $body.= "\n<br>".date("Ymd-His")." The Stream Server Has Detected Error since Last Hour.\n<br>Please check Server ASAP.\n<br>";
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