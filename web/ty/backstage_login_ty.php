<?php
/****************
 *Validated on Jan-11,2016
 * reference from backstage_login.php 
 * /var/www/SAT-CLOUDNVR/  
 *Writer: JinHo, Chang 
*****************/

include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
include_once( "./include/oem_id.php" );

header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

$_GET['oem_id']="T05";

// the data array to return
$ret = array();
$ret["status"] = "success";
 
try {
//for taipei project site account login only
    define("USER_PWD","tN8V8bMTtuKycj7BNW2Esp8p");
    if ( (preg_match("/^[0-9A-Z]{5,15}$/",$_REQUEST['user_name'])) //Jan-11
       and ($_REQUEST['user_pwd'] == USER_PWD  )   )
    { //correct site format
    }else if ( (preg_match("/^[0-9-]{5,15}$/",$_REQUEST['user_name'])) //Jan-11
       and ($_REQUEST['user_pwd'] == USER_PWD  )   )
    { //correct site format with hyphen
    }else{
         throw new Exception('Please go to web portal for normal login');
    }
//end of  taipei project
	// Check number of failure attemps
	if (!$_SESSION['login_failure']) {
		$_SESSION['login_failure'] = array();
	}

	$failures = count($_SESSION['login_failure']);
	if ( $failures > 10) {
		$now = time();
		$last_failure = $_SESSION['login_failure'][$failures-1];
		if ($now > $last_failure + 600) {
			$_SESSION['login_failure'] = array();
		}
		else {
			throw new Exception('Too many failed login attempts. Please try again in 10 minutes.');
		}
	}

	if (isset($_POST['user_name'])) $_GET = $_POST;
	// open db
	$data_db = new DataDBFunction();

	// select user info
	$verify_result = VerifyUserWithPwd($data_db, $_GET['user_name'], $_GET['user_pwd'], $user_info_row, $_GET['oem_id']);
	if ($verify_result != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS) {
		ClearSessionExceptLanguage();
	}
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
			$_SESSION['login_failure'][] = time();
			throw new Exception( 'Invalid Username / Password.' );
			break;
	}

	if  ( !CheckExpire($user_info_row) ) {
		throw new Exception( 'Your ID is expire.' );
	}
	
	if ( !empty($user_info_row['google_openid']) ) {
		throw new Exception( 'Invalid Username / Password.' );
	}
	
	// store user info in session
	StoreUserInfoInSession( $user_info_row );
	//print_r($_SESSION);	
	// update login time/login count
	$data_db->UpdateUserLoginInfo( $user_info_row["id"] );

	if (isset($_SESSION['return_url'])) {
		$ret['return_url'] = $_SESSION['return_url'];
	}
}
catch( Exception $e ) {
	SetErrorState( $ret, $e->getMessage() );
}

try {
	if ($ret['status'] == 'success') {
		// Parse HTTP_USER_AGENT
		$user_agent = parse_user_agent();

		// Save user log.
		$log_db = new LogDBFunction();
		$log_db->InsertUserLog($user_info_row, 'LOGIN', 'SUCCESS', 'sat', $user_agent);
	}
}
catch (Exception $e) {
}

echo json_encode( $ret );
if (isset($_REQUEST['mode'])){
  echo header("Location:iveda/index.php?mode=personal&view_location=iveda%2Fshared_matrix.php");
}
else echo header("Location:index.php?mode=&view_location=shared_matrix.php");//echo header("Location:index.php");
?>
