<?php
/****************
 *Validated on Aug-8,2017
 * camera API for user
 * parameter: user_name, user_pwd, command 
 * /var/www/SAT-CLOUDNVR/manage
* include shared library /rpic.inc   
 *Writer: JinHo, Chang  
*****************/
#for web
require_once('../include/global.php');
require_once('../include/db_function.php');
include_once('../include/utility.php');
include_once('../include/user_function.php');  //VerifyUserWithHash
include_once( "../include/index_title.php" ); //oem_id
include_once( "../rpic.inc" );   //OEM_ID

//ivedaManageUser:ivedaManagePassword@
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
define ("TYPE_SHARE","Share");

function getCameraArray($type,$user_name, $user_pwd, &$camArr)
{
	if ((OEM_ID=="T04") or (OEM_ID=="T05") or (OEM_ID=="K01") or (OEM_ID=="T06")) $myKey = "?"; //rpic only
	else  $myKey =getToken($user_name,$user_pwd);
	if ($type ==TYPE_SHARE)
			$sql="select uid,mac_addr,c1.name,is_signal_online,ip_addr from isat.query_share as c1 left join isat.user as c2 on c1.visitor_id=c2.id where c2.name='{$user_name}' group by uid";
	else $sql="select uid, mac_addr,name,is_signal_online,ip_addr from isat.query_info where user_name='{$user_name}' group by uid";
	sql($sql,$result,$num,0);
	for($i=0;$i<$num;$i++) //for($i=0;$i<$num;$i++) 
  {
      fetch($arr,$result,$i,0);
      if ($arr['is_signal_online']=="true")
				$myCamera= array(
						"MAC" => $arr['mac_addr'],
						"NAME" => $arr['name'],
			      "THUMBNAIL" => getURL($arr['uid'],$myKey,THUMBNAIL_TAG),
			      "STREAM_URL" => getURL($arr['uid'],$myKey)
			  );
			else
				$myCamera= array(
						"MAC" => $arr['mac_addr'],
						"NAME" => $arr['name'],
			      "THUMBNAIL" => "",
			      "STREAM_URL" => ""
			  ); 
		  array_push($camArr,$myCamera);
  }//for 
}

function InsertLog($user_name, $action, $action_result) {
    $user_agent= parse_user_agent();    
    if (!is_null($user_agent))    
      $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";
    else $user_agent_str = "";

    $client_ip = getClientIP();
		$sqlu = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'customerservice' AND TABLE_NAME = 'share_log' AND COLUMN_NAME = 'user_agent'";
		sql($sqlu,$resultu,$numu,0);
		if ($numu==0)  //if (OEM_ID == "T04")
			$sql ="insert into customerservice.share_log (mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr) values
    ('','','".$user_name."','','',  '{$action}','{$action_result}','{$client_ip}{$user_agent_str}' )";
    else $sql ="insert into customerservice.share_log (mac, owner_id,owner_name,visitor_id,visitor_name,  action,result,ip_addr, user_agent) values
    ('','','".$user_name."','','',  '{$action}','{$action_result}','{$client_ip}','{$user_agent_str}' )";
    //echo $sql;
    //$result=mysql_query($sql,$link);
    sql($sql,$result,$num,0);
    if (!$result) die("InsertLog Database Link Fail");
}


$cameraArray = array();
$status = "SUCCESS";

try {
	// open db
	$data_db = new DataDBFunction();
	// select user info
	$verify_result = VerifyUserWithPwd($data_db, $_REQUEST['user_name'], $_REQUEST['user_pwd'], $user_info_row, OEM_ID);

	switch ($verify_result) {
		case VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS:
			break;
		case VERIFY_USER_HASH_PRODUCT_UNMATCH:
			throw new Exception('Product unmatched.');
			break;
		case VERIFY_USER_HASH_IN_USER_REG_TABLE_SUCCESS:
			throw new Exception('Please check your confirm letter first.');
			break;
		default:
			throw new Exception( 'Invalid Username / Password.' );
			break;
	}
	try {
	  switch($_REQUEST['command']) {
	    case 'share_list':
				getCameraArray(TYPE_SHARE,$_REQUEST['user_name'],$_REQUEST['user_pwd'], $cameraArray);
				break; 
	  	case 'owner_list':
	  		getCameraArray("",$_REQUEST['user_name'],$_REQUEST['user_pwd'], $cameraArray);
	  		break;
			default:
	    	throw new Exception($errorMSG['301']);
	    	break;
   	}
  }catch( Exception $e ) {
		$ret= array("error" => $e->getMessage());
		array_push($cameraArray,$ret);
		$status = "FAIL:".getErrorCode($e->getMessage());
	}
	
}catch( Exception $e ) {
	//SetErrorState( $ret, $e->getMessage() );
	$ret= array("error" => $e->getMessage());
	array_push($cameraArray,$ret);
	$status = "FAIL:".getErrorCode($e->getMessage());
}

InsertLog($_REQUEST['user_name'], $_REQUEST['command'], $status );   
echo json_encode($cameraArray, JSON_UNESCAPED_SLASHES); 
?>