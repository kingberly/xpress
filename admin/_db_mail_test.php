<?php
require_once ("/var/www/qlync_admin/doc/config.php");
$link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"isat");
$sql="select isat.oem_id from mail";
$result=mysql_query($sql,$link);
$num=mysql_num_rows($result);
$d = date('Y-m-d H:i:s');
if ($num>0)
  $sql="update isat.mail set auth_type='NONE', username='', password='', host='10.6.77.49', port='25', from_email='servicedesk@iveda.com', from_name='IvedaXpress".$d."', brand='IvedaXpress".$d."', company='IvedaXpress', enc='' where oem_id='$oem'";
#  $sql="update isat.mail set auth_type='PLAIN', username='xpress', password='tXdi2999', host='mail.safecity.com.tw', port='587', from_email='xpress@safecity.com.tw', from_name='Xpress587', brand='IvedaXpress".$d."', company='IvedaXpress', enc='' where oem_id='$oem'";
#  $sql="update isat.mail set auth_type='PLAIN', username='xpress', password='tXdi2999', host='mail.safecity.com.tw', port='25', from_email='xpress@safecity.com.tw', from_name='Xpress25', brand='IvedaXpress".$d."', company='IvedaXpress', enc='' where oem_id='$oem'";
  #$sql="update isat.mail set auth_type='LOGIN', username='kingberly', password='Qwer1234', host='smtp.gmail.com', port='465', from_email='kingerly@gmail.com', from_name='Gmail', brand='IvedaXpress".$d."', company='IvedaXpress', enc='ssl' where oem_id='$oem'";
else
  $sql="INSERT INTO `mail` VALUES ('$oem','NONE','','','','','10.6.77.49',25,'servicedesk@iveda.com','IvedaXpress','IvedaXpress','Iveda Inc.')";
if ($sql!="")
{
  $result=mysql_query($sql,$link);
  if ($result) $response = "Succeed\n";
  else $response = "Fail\n";
  printf ("command:".$sql." => ". $response);
}else printf("Mail server info Exist!!\n");
mysql_close($link);//close qlync
$ch = curl_init();
$params = array(
   	 'command' => 'test_current',
#   	 'receiver' => 'xpress@safecity.com.tw',
      'receiver' => 'alarm@tdi-megasys.com',
   	 'oem_id' => $oem);
$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_mail.php';
$url = $web_address . $path . '?' . http_build_query($params);
echo $url;
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$content_test_email=json_decode($result,true);
curl_close($ch);
function connect_db($host,$user,$password,$dbname){
        $link=mysql_connect($host,$user,$password);
        if(!$link)
        {
                die("Can't connect to db ".$host);
        }

        $db_selected=mysql_select_db($dbname,$link);
        if(!$db_selected)
        {
                die("Can't open database: <br>".mysql_error($link));
        }
        mysql_query("SET CAMNAME 'utf8'");
        return $link;
}
?>