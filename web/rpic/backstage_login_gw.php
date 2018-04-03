<?php
include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
include_once( "./include/oem_id.php" );

//header('Access-Control-Allow-Origin: http://www.qlync.com');
header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// the data array to return
$ret = array();
$ret["status"] = "success";
 
try {
//for god watch project only

    if (preg_match("/^[0-9-]{10,13}/",$_REQUEST['user_name'])
       and ($_REQUEST['oem_id'] == "G09")  ) 
    { //correct site format
    }else if ($_REQUEST['oem_id'] == "X02")
    {//bypass Xpress site
    }else{
         throw new Exception('Please go to web portal for normal login');
    }
//end

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
  //echo header("Location:iveda/index.php?mode=personal&view_location=iveda%2Fshared_matrix.php");
  echo header("Location:iveda/index.php?mode=personal");
}
else echo header("Location:index.php?mode=");
//else echo header("Location:index.php?mode=&view_location=shared_matrix.php");//echo header("Location:index.php");
?>
