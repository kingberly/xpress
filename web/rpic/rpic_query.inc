<?php
//include_once( "./include/log_db_function.php" ); //jinho for APP login
//define("LIMIT",300);//second  //jinho fix iOS double login issue

function AppLog($action,$user_info_row){
  include_once( "/var/www/SAT-CLOUDNVR/include/log_db_function.php" );
	$log_db = new LogDBFunction();
  $uagent = parse_user_agent();
  if (($uagent['platform'] == "Linux") and ($uagent['browser'] == "Wget")) 
    $uagent['platform']="Android | iOS";
	$log_db->InsertUserLog($user_info_row, $action, 'SUCCESS', 'sat', $uagent); //Wget/1.12 (linux-gnu) iOS
}
?>