<?php
/****************
 *Validated on Apr-21,2016,  
 * Notify license expiration date before EXPIRE_ALARM_DAY
 * No authentication as this is a crontab task
 * fix echo html error  
 *Writer: JinHo, Chang   
*****************/
define("EXPIRE_ALARM_DAY",14);
define("EXPIRE_SSL_DAY",30);
$emailaddr = array(
"jinho.chang@tdi-megasys.com",
"eileen@tdi-megasys.com"
);
$emailaddrTest = array("alarm@tdi-megasys.com");
include("/var/www/qlync_admin/header.php");
require_once ("/var/www/qlync_admin/doc/config.php");
//require_once 'dbutil.php';

function notifyUser($content,$emailArray)
{
  global $api_ip, $api_port;
	$postData['func'] = 'send';
	$postData['body'] = $content;
	$postData['subject'] = "Xpress4.1 License System Notificaiton";
	$postData['name'] = "Xpress4.1 License Mgmt Reminder";

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
  foreach ($emailArray as $ad) {
    $postData['email'] = $ad;
	  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
       $result = curl_exec($ch);
  	if ($result == false) {
  		throw new Exception(curl_error($ch));
          return false;
  	}
  }
  return true;
}
function createServiceTable()
{
global $emailaddr, $emailaddrTest;
    //$link = lic_getLink();
    $sql = "select * from licservice.other_license";
    sql($sql,$result,$num,0);
    //$result=mysql_query($sql,$link);
	if ($result){
       	$services = array();
    	   $index = 0;
    	   for($i=0;$i<$num;$i++){
            fetch($arr,$result,$i,0);
            //$arr['expire'] = mysql_result($result,$i,'expire');
            //$arr['note'] = mysql_result($result,$i,'note');
            //$arr['oem_id'] = mysql_result($result,$i,'oem_id');
            $services[$index] = $arr;
        		$index++;
    	   }//for
	}

  $html = '';
  $now = time();
  foreach($services as $service)
  {
          $datediff = strtotime(substr($service['expire'],0,10)) - $now;
          $days_remain = floor($datediff/(60*60*24));
		      
          if($days_remain < 0){
            $html.= "\n<tr class=tr_2>\n";
              $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
               $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font></td>\n";
                $html.= "<td>{$service['note']}</td>\n";
                $html.= "<td><font color=#FF0000><b>Expired</b></font></td>\n";
                $html.= "</tr>\n";
          }else if ($days_remain < EXPIRE_SSL_DAY){
          $html.= "\n<tr class=tr_2>\n";
               $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
               $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font></td>\n";
               $html.= "<td>{$service['note']}</td>\n";
              $html.= "</td><font color=#FF0000><b>Expire Soon</b></font></td>\n";
              $html.= "</tr>\n";
              $body .= "The License of {$service['oem_id']}/{$service['note']} will be expired in {$days_remain} days!!<br>";
          }else{
            //$html.= "<td>{$service['expire']}</td>\n";
		        //$html.= "<td>{$service['oem_id']}</td>\n";
            //$html.= "<td>{$service['note']}</td>\n";
            //$html.= "<td></td>\n";
          }
    	    
	}
//tunnel server query
    $html.= "<tr class=topic_main><td colspan=4>Tunnel License</td></tr>";
    $sql = "select * from licservice.tunnel_server_license";
    	//$result=mysql_query($sql,$link);
      sql($sql,$result,$num,0);
	if ($result){
       	$services1 = array();
    	   $index = 0;
    	   for($i=0;$i<$num;$i++){
            fetch($arr,$result,$i,0);
            //$arr['expire'] = mysql_result($result,$i,'expire');
            //$arr['license_key'] = mysql_result($result,$i,'license_key');
            //$arr['oem_id'] = mysql_result($result,$i,'oem_id');
        		$services1[$index] = $arr;
		        $index++;
    	   }//for
	}

  foreach($services1 as $service)
  {
        $datediff = strtotime(substr($service['expire'],0,10)) - $now;
        $days_remain = floor($datediff/(60*60*24));
          if($days_remain < 0){
  		        $html.= "\n<tr class=tr_2>\n";
              $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
              $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font></td>\n";
              $html.= "<td>{$service['license_key']}</td>\n";
               $html.= "<td><font color=#FF0000><b>Expired</b></font></td>\n";
              $html.= "</tr>\n";
          }else if ($days_remain < EXPIRE_ALARM_DAY){
  		        $html.= "\n<tr class=tr_2>\n";
               $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
               $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font></td>\n";
               $html.= "<td>{$service['license_key']}</td>\n";
               $html.= "<td><font color=#FF0000><b>Expire Soon</b></font></td>\n";
               $html.= "</tr>\n";
              $body .= "The Tunnel License of {$service['oem_id']}/{$service['license_key']} will be expired in {$days_remain} days!! <a href='https://partner.qlync.com/html/activate_code/tunnel_order_inquery.php'>Apply</a><br>"; 
          }else{
              //$html.= "<td>{$service['expire']}</td>\n";
              //$html.= "<td>{$service['oem_id']}</td>\n";
              //$html.= "<td>{$service['license_key']}</td>\n";
            //$html.= "<td></td>\n";
          }
	}
  //end of tunnel license
	echo $html; //print table
     if ($body!=""){
        $body= "<h2>Xpress4.1 License Management Reminder from X02</h2><br>".$body;
        $result = notifyUser($body,$emailaddr);
        if ($result)
          return "{$body} <font color=blue>Send Notification Successful!!</font><br>";
        else
          return "{$body} <font color=red>Send Notification Fail!!</font><br>";
        notifyUser($body,$emailaddrTest);
     }
}
?>
<div align=center><b><font size=5>License Notification Manager</font></b></div>
<div class=bg_mid>
<div class=content>
<table class=main_table>
<col /><col /> <col /><col /><col width="140px" />
<tr class=topic_main>
<td>Expired</td>
<td>OEM_ID</td>
<td width=300>License</td>
<td>Status</td></tr>
<?php
 $msg_err=createServiceTable();
?>
</table>
<?php
if (isset($msg_err))
  echo $msg_err;
?>
</html>