<html><body><pre>
<?php
//sudo vi /var/www/SAT-CLOUDNVR/test.php
echo("serveraddr:".$_SERVER['SERVER_ADDR']."\t");
echo("servername:".$_SERVER['SERVER_NAME']."\t");
$dt = new DateTime();
echo($dt->format('Y-m-d H:i:s')."\t");
echo("httphost:".$_SERVER['HTTP_HOST']."\t");
echo("https:".$_SERVER['HTTPS']."<br>");

if(!empty($_SERVER['HTTP_CLIENT_IP'])){
   $myip = $_SERVER['HTTP_CLIENT_IP'];
   echo "client ip:".$myip;
}
if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
   $myip = $_SERVER['HTTP_X_FORWARDED_FOR'];
   echo "\nxforwardfor:".$myip;
}
if(!empty($_SERVER['HTTP_X_FORWARDED'])){
   $myip = $_SERVER['HTTP_X_FORWARDED'];
   echo "\nxforward:".$myip;
}
if(!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])){
   $myip = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
   echo "\nxcluster clientip:".$myip;
}
if(!empty($_SERVER['HTTP_FORWARDED_FOR'])){
   $myip = $_SERVER['HTTP_FORWARDED_FOR'];
   echo "\nforwardfor:".$myip;
}
if(!empty($_SERVER['HTTP_FORWARDED'])){
   $myip = $_SERVER['HTTP_FORWARDED'];
   echo "\nforward:".$myip;
}
if(!empty($_SERVER['HTTP_VIA'])){
   $myip = $_SERVER['HTTP_VIA'];
   echo "\nvia:".$myip;
}
   $myip= $_SERVER['REMOTE_ADDR'];
   echo "\nremoteaddr:".$myip;

/*
echo "<hr>test session sticky over http and https<br>";
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
if ($protocol=="http://") {
  $_SESSION["user_name"]="testip";
  echo "assign user_name under http=<br>";
}else echo "check https session=<br>";

var_dump($_SESSION);

if ($protocol=="http://"){
echo "timeout to https at 5s\n";
echo "<script>\nsetTimeout(\"window.location.href='https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']."'\",5000);\n</script>\n"; 
}
*/ 
?>
</pre></body></html>