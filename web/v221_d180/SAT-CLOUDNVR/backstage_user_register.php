<?php
include_once( "./include/global.php" );
include_once( "./include/index_title.php" );
include_once( "./include/oem_id.php" );
include_once( "./include/utility.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/PHPMailer_5.2.4/class.phpmailer.php" );
include_once( "./include/mail_class.php" );
include_once( "./include/mail_function.php" );
include_once( "./include/password.php" );

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

function CheckRecaptcha( $txtCaptcha, $security_code )
{
	if ( ($txtCaptcha == $security_code) && (!empty($txtCaptcha) && !empty($security_code)) )
		return true;
	else
		return false;
}

// process command function
function ProcessCommand( $data_db, &$ret, $command )
{
	global $_GET;
	global $_SESSION;
	global $oem_style_list;

	// check command
	switch( $command )
	{
		case "recaptcha":   //jinho added for login recaptcha
			if( !CheckRecaptcha($_GET["txtCaptcha"], $_SESSION["security_code"]) )
				throw new Exception( _('Security image verify failed.') );
			break;
		case "register":
			// check recaptcha
			if( !CheckRecaptcha($_GET["txtCaptcha"], $_SESSION["security_code"]) )
				throw new Exception( _('Security image verify failed.') );
					
			// check name/pwd/reg_email
			// if anyone is empty, just report fail
			if( !isset($_GET["name"]) || $_GET["name"] == "" ||
				!isset($_GET["pwd"]) || $_GET["pwd"] == "" ||
				!isset($_GET["reg_email"]) || $_GET["reg_email"] == "" )
				throw new Exception( "You need to enter name, password, and email." );
      //jinho fix QXP-214 
      if (!filter_var($_GET["reg_email"], FILTER_VALIDATE_EMAIL))
        throw new Exception( "Please enter a valid email address." ); 
			// Set default values
			if (!isset($_GET['oem_id'])) $_GET['oem_id'] = '';
			
			// check has confirm letter
			if( $data_db->QueryRecordNo("user_reg",	"name=?", $_GET["name"]) > 0 ) throw new Exception("Please check your confirm letter first.");

			// check email duplicate
			if( $data_db->CheckEmailIsExistedInReg($_GET["reg_email"]) ) throw new Exception( "E-mail is already in use." );

			// check email duplicate
			if( $data_db->CheckEmailisDuplicate($_GET["reg_email"]) ) throw new Exception( "E-mail is already in use." );
			
			if (!CheckUserFormat($_GET["name"]) || !CheckUserFormat($_GET["pwd"]))  throw new Exception( "User/Password format error." );
			
			// check user name duplicate
			//if( $data_db->CheckUsernameDuplicate($_GET["name"]) ) throw new Exception( "User name is already in use." );
			// it's already done in insert
			
			// generate a random authentication code
			$auth_code = GenerateRandomPassword( 12 );

			if ($oem_style_list['confirm_user_reg']) {
				// insert into user_reg
				$data_db->InsertUserReg( $_GET["name"], $_GET["pwd"], $_GET["reg_email"], $auth_code, $_GET['oem_id'] );
			}
			else {
				// insert into user
				$data_db->InsertUser(1, "",
					1, $_GET["name"], password_hash($_GET["pwd"], PASSWORD_DEFAULT), $_GET["reg_email"],
					3, "", "", $_GET['oem_id']);
			}
			
			// send email with authentication code
			if( !SendConfirmationMail($auth_code, $_GET["name"], $_GET["pwd"], $_GET["reg_email"]) )
				throw new Exception( "Send confirmation mail failed." );

			break;
			
		case "resend":
			// check name/pwd
			// if anyone is empty, just report fail
			if( !isset($_GET["name"]) || $_GET["name"] == "" ||
				!isset($_GET["reg_email"]) || $_GET["reg_email"] == "" )
				throw new Exception( "You need to enter name, password, and email." );

			// get user_reg information for resend
			$condition = "name=:name AND reg_email=:reg_email";
			$params = array(':name'=>$_GET['name'], ':reg_email'=>$_GET['reg_email']);
			$user_info_row = $data_db->QueryRecordDataOne( "user_reg", $condition, '*', $params );
			if( $user_info_row === FALSE ) throw new Exception( _("Please input the correct username and email address.") );

			// send email with authentication code
			if( !SendConfirmationMail($user_info_row["authentication_code"], $user_info_row["name"],
				$user_info_row["pwd"], $user_info_row["reg_email"]) )
				throw new Exception( "Send confirmation mail failed." );

			break;

		case "authentication":
			// check name/pwd/authentication_code
			// if anyone is empty, just report fail
			if( !isset($_GET["name"]) || $_GET["name"] == "" ||
				!isset($_GET["pwd"]) || $_GET["pwd"] == "" ||
				!isset($_GET["authentication_code"]) || $_GET["authentication_code"] == "" )
				throw new Exception( "need name, pwd, authentication_code." );

			// get user_reg information for authentication
			$condition = "name=:name AND authentication_code=:authentication_code";
			$params = array(':name'=>$_GET['name'], ':authentication_code'=>$_GET['authentication_code']);
			$user_info_row = $data_db->QueryRecordDataOne( "user_reg", $condition, '*', $params );
			if( $user_info_row === FALSE ) throw new Exception( "Unable to find the register information. " .
					"If you have already completed the registration process, " .
					"please login from http://sat.nuuolink.com/." );

			
			if( !password_verify($_GET['pwd'], $user_info_row['pwd_hash']) ) {
				throw new Exception( "Invalid Name/Password.");
			}
			$user_reg_id = $user_info_row['id'];

			// convert this user to normal user
			$user_info_row["group_id"] = 1; // default group to 1 (normal user)
			$user_info_row["matrix_display_mode"] = 3;
			try {
				$data_db->beginTransaction($transaction);
				$user_info_row["id"] = $data_db->InsertUser( $_SESSION["user_group_id"], $user_info_row["id"],
					$user_info_row["group_id"], $_GET["name"], $user_info_row['pwd_hash'], $user_info_row["reg_email"], 3, "", "", $user_info_row['oem_id'] );

				// delete the record in user_reg
				$data_db->DeleteRecordData( 'user_reg', 'id=?', $user_reg_id );

				$data_db->commit($transaction);
			}
			catch (Exception $e) {
				$data_db->rollBack($transaction);
				throw $e;
			}
			
			// store in session for auto login
			StoreUserInfoInSession( $user_info_row );			

			try {
				// Complete user_info_row
				$user_info_row['cloud_id'] = NULL;

				// Parse HTTP_USER_AGENT
				$user_agent = parse_user_agent();

				// Save user log.
				$log_db = new LogDBFunction();
				$log_db->InsertUserLog($user_info_row, 'ADD', 'SUCCESS', 'sat', $user_agent);
				$log_db->InsertUserLog($user_info_row, 'LOGIN', 'SUCCESS', 'sat', $user_agent);
			}
			catch (Exception $e) {
				// Do nothing.
			}
			break;
			
		case "password_recovery":
			// check recaptcha
/*			
			//if( !CheckRecaptcha($_GET["recaptcha_challenge"], $_GET["recaptcha_response"]) )
			if( !CheckRecaptcha($_GET["txtCaptcha"], $_SESSION["security_code"]) )
				throw new Exception( _('Security image verify failed.') );
*/
			// check name
			if( !isset($_GET["name"]) || $_GET["name"] == "" )
				throw new Exception( "Please provide your account name." );

			// get user information for send password
			$condition = "name=:name OR reg_email=:name";
			$params = array(':name'=>$_GET["name"]);
			$user_info_row = $data_db->QueryRecordDataOne( "user", $condition, '*', $params );
			if( $user_info_row === FALSE ) throw new Exception( _("The account does not exist.") );
			
			$recovery_code = false;
			// Check for key conflict
			for ($i=0; $i<5; $i++) {
				$recovery_code = getRandomString(64);
				if ( ! $data_db->QueryRecordNo('password_recovery', 
						'recovery_code = ?', $recovery_code) ) {
					break;
				}
				$recovery_code = false;
			}
			if (!$recovery_code) {
				throw new Exception('An error has occured. Please try again later');
			}
			
			$dbi = new DbInsert($data_db->db, 'password_recovery');
			$dbi->add('recovery_code', $recovery_code);
			$dbi->add('user_id', $user_info_row['id']);
			$dbi->insertOrReplace();
			
			// send email with password
			if( !SendPasswordMail($user_info_row["name"], $recovery_code, $user_info_row["reg_email"]) )
				throw new Exception( "Send password recovery mail failed." );

			break;
		case 'password_recovery_authentication':
			if( !CheckRecaptcha($_GET["txtCaptcha"], $_SESSION["security_code"]) )
				throw new Exception( "Security image verify failed." );				
					
			if (!CheckUserFormat($_GET["pwd"]))  throw new Exception( "Password format error." );
			
			$table = 'password_recovery LEFT JOIN user ON password_recovery.user_id = user.id';
			$condition = 'user.name=:name AND password_recovery.recovery_code = :recovery_code AND ' .
					'request_date >= (NOW() - INTERVAL 1 DAY)';
			$params = array(':name'=>$_GET['name'], ':recovery_code'=>$_GET['recovery_code']);

			$entry = $data_db->QueryRecordDataOne($table, $condition, 'user.id', $params);
			if (!$entry) {
				throw new Exception("There is no matching user ID or expired , please try again");
			}
			$data_db->ModifyUser('', $entry['id'], '', '', $_GET["pwd"], '', '', '', 3 );
			$data_db->DeleteRecordData('password_recovery', 'recovery_code=?', $_GET['recovery_code']);
			break;
		case 'syncme_password_recovery':
			// check type & param
			if (!isset($_GET['type']) || !isset($_GET['param'])) {
				throw new Exception('Lack of information.');
			}

			$condition = '';
			if ($_GET['type'] == 'email') {
				$condition = "reg_email=?";
			}
			else if ($_GET['type'] == 'username') {
				$condition = "name=?";
			}

			$user_info_row = $data_db->QueryRecordDataOne('user', $condition, '*', $_GET['param']);
			// send email with password
			if(!SendPasswordMail($user_info_row['name'], $user_info_row['pwd'], $user_info_row['reg_email']))
				throw new Exception('Send password recovery mail failed.');

			break;
			
		case "mobileadd":			
			// Set default value
			if (!isset($_GET['oem_id'])) $_GET['oem_id'] = '';

			$receive = md5($_GET["name"] . "NUUOLINK" . $_GET["pwd"] . "23641580" . $_GET["reg_email"]);
			if ($_GET["content"] != $receive)
				throw new Exception( "content match error" );
			
			if( $data_db->CheckEmailisDuplicate($_GET["reg_email"]) ) throw new Exception( "E-mail is already in use." );
			
			$user_entry = $data_db->QueryRecordDataOne( "user", 'name=?', '*', $_GET["name"]);
			
			if( $user_entry !== FALSE ) throw new Exception( "Name already in use." );
			
			if (!CheckUserFormat($_GET["name"]) || !CheckUserFormat($_GET["pwd"]))  throw new Exception( "User/Password format error." );
			
			$data_db->InsertUser(1, "",
				1, $_GET["name"], password_hash($_GET["pwd"], PASSWORD_DEFAULT), $_GET["reg_email"],
				3, "", "", $_GET['oem_id']);

			try {
				// Complete user_info_row
				$user_info_row = array();
				$user_info_row['oem_id'] = $_GET['oem_id'];
				$user_info_row['cloud_id'] = NULL;
				$user_info_row['name'] = $_GET['name'];
				$user_info_row['reg_email'] = $_GET['reg_email'];

				// Get HTTP_USER_AGENT
				$user_agent = array();
				$user_agent['platform'] = $_GET['platform'];
				$user_agent['browser'] = NULL;
				$user_agent['version'] = NULL;

				// Save user log.
				$log_db = new LogDBFunction();
				$log_db->InsertUserLog($user_info_row, 'ADD', 'SUCCESS', 'sat', $user_agent);
			}
			catch (Exception $e) {
				// Do nothing.
			}
			break;	
		case "mobilemodify":
			if (IsUserLoggedIn() === FALSE) throw new Exception( "You havent login." );
			if (!CheckUserFormat($_GET["pwd"]))  throw new Exception( "User/Password format error." );
			$data_db->ModifyUser($_SESSION["user_group_id"],
				$_SESSION["user_id"], "", "", $_GET["pwd"], "",
				"", "", 3);
			
			break;
		default:			
			break;
	}
}

// the data array to return
$ret = array();

// default status to success
$ret["status"] = "success";
$ret["error_msg"] = "";

try {
	// open db
	$data_db = new DataDBFunction();
	if ( isset($_POST['command']) ) $_GET = $_POST;
	// process first command
	ProcessCommand( $data_db, $ret, $_GET["command"] );
}
catch( Exception $e ) {
	SetErrorState( $ret, $e->getMessage() );
}

// encode & return
echo json_encode( $ret );
?>
