<?php

require_once ("_db_add_plugin_menu.inc");
  $support_email="servicedesk@iveda.com";
  $tel_android=" ";
  $tel_ios=" ";
$mis_email="alarm@tdi-megasys.com";
$web_url = rtrim($home_url,":8080");
$web_url= preg_replace('#^https?://#', '', $web_url);

    $link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"isat");
    $android_content="<h1>Atención al Cliente</h1>\n<dl>\n";
    $android_content=$android_content."<dt>E-mail</dt>\n<dd><a href='mailto:{$support_email}'>{$support_email}</a></dd>\n";
    $ios_content=$android_content."<dt>Tel</dt>\n<dd><a href='tel:{$tel_ios}'>{$tel_ios}</a></dd>\n</dl>\n";
    $android_content=$android_content."<dt>Tel</dt>\n<dd><a href='tel:{$tel_android}'>{$tel_android}</a></dd>\n</dl>\n";
    $sql="update isat.oem_info set contact_android=\"".$android_content."\"";
    $result=mysql_query($sql,$link);
    if ($result) $response = "Succeed\n";
    else $response = "Fail\n";
    printf ("command:".$sql." => ". $response); 
    $sql="update isat.oem_info set contact_ios=\"".$ios_content."\"";
    $result=mysql_query($sql,$link);
    if ($result) $response = "Succeed\n";
    else $response = "Fail\n";
    printf ("command:".$sql." => ". $response);
    mysql_close($link);//close isat for contact    

//force update web server (updateOemConfig)
	$params['command']='set';
	$params['oem_id']=$oem;
  $params['support_email']=$support_email;
  $ch=curl_init();
	$url = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_oem.php";
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	$result = curl_exec($ch);
	$content=json_decode($result,true);
//echo curl_error($ch);
	curl_close($ch);
  echo "set: ".$result."\n"; 

$result = exec("wget -q -O - '".$url."?oem_id={$oem}&command=download'");
echo "download: ".$result."\n";	

?>
