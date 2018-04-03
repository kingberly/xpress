<?php
/*Ref HTTP Header
* HTTP_CLIENT_IP 
* * HTTP_X_FORWARDED_FOR 
* * HTTP_X_FORWARDED 
* * HTTP_X_CLUSTER_CLIENT_IP 
* * HTTP_FORWARDED_FOR 
* * HTTP_FORWARDED 
* * REMOTE_ADDR (真實 IP 或是 Proxy IP) 
* * HTTP_VIA (參考經過的 Proxy)
*/
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
?>