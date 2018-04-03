<?php
/****************
 *Validated on Jan-5,2018
 * reference from /var/www/SAT-CLOUDNVR/backstage_login.php 
 * add login page if API does not provide user_name parameter  
 *Writer: JinHo, Chang
 *sed -i -e 's|XNN|X02|' /var/www/SAT-CLOUDNVR/backstage_login_rpic.php  
*****************/

include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
//include_once( "./include/oem_id.php" );
include_once( "./include/index_title.php" ); //oem_id
include_once( "rpic.inc" );
header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');

//special login if no input
if ($_REQUEST['mode']=='0') unset($_REQUEST['mode']);
if ( (!isset($_REQUEST['user_name'])) or ($_REQUEST['user_name']=="") or ($_REQUEST['user_pwd']=="") ) {
  $html = "<html><head><title>{$oem_style_list['title']}</title>";
  $html .= "<link href=\"{$oem_style_list['css']['index']}\" rel=\"stylesheet\" type=\"text/css\" charset=\"utf-8\">";
  $html .= "</head><body>";
  $html .= '<div id="banner">';
  $html .= '<div class="bannerMain">';
  $html .= '<div id="logo_img" class="single_logo_iveda"></div>';
  $html .= '<div class="line_banner"></div> </div></div>';
  $html .= '<div style="text-align:center;padding:100px">';
  $html .= "<form method='post' action='{$_SERVER['PHP_SELF']}'>";
  $html .= "<input type='text' name=user_name placeholder='使用者名稱'><br><br>";
  $html .= "<input type='password' name=user_pwd placeholder='密碼'><br><br>";
  $html .= "即時影像分享頁 <select name=mode><option value='0'></option><option value='1'>延展</option></select><br><br>";
  $html .= "<input style='width:157px;' type='submit' value='登入'>";
  $html .= "</form></div>";
  $html .= "<div id='footer_div'><div id='footer_logo' class='footer_logo_iveda'></div><div id='copyright'>Version ".SVW_VERSION.SVN_REVISION."<br/>{$oem_style_list['copyright_html']}</div></div>";
  $html .= "</body></html>";
  echo $html;
  exit();
}//end of login

header('Content-type: application/json');

if (!isset($_GET['oem_id']))  $_GET['oem_id']=OEM_ID; //for rpic project param


// the data array to return
$ret = array();
$ret["status"] = "success";
/* //clear session before rpic login
	$language = $_SESSION["user_language"];
	if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])){
		unset($_SERVER['PHP_AUTH_USER']); 
		unset($_SERVER['PHP_AUTH_PW']);
	}
	// clear session
	@session_unset(); 
	// 
	$_SESSION["user_language"] = $language;
*/
 
try {
//for taipei project site account login only
    if (checkSiteAccountRule($_GET['oem_id'],$_REQUEST['user_name'])  )
    { //correct site format with hyphen
    	if (($_GET['oem_id'] =="T04") or ($_GET['oem_id'] =="T05") or ($_GET['oem_id'] =="K01"))
    			if ($_REQUEST['user_pwd'] != APP_USER_PWD) //T04/T05/K01
    					header("Location:backstage_mobile.php?user_name=".$_REQUEST['user_name']."&user_pwd=".$_REQUEST['user_pwd']."&share"); 
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
	
	// store user info in session
	StoreUserInfoInSession( $user_info_row );
//jinho new added for v3.x
    if (!empty($_GET['return_info'])) {
        $ret['info'] = $_SESSION['user_info'];
    }
//end of new added

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
if (DEBUG_FLAG == "ON") exit;
if (isset($_REQUEST['mode'])){
  echo header("Location:iveda/index.php?mode=personal&view_location=iveda%2Fshared_matrix.php");
}
else echo header("Location:index.php?mode=&view_location=shared_matrix.php");//echo header("Location:index.php");
?>
