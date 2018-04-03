<?php
require '../include/global.php';
require '../include/db_function.php';
require '../include/user_function.php';
require '../include/streamserver.php';
require_once('../include/utility.php');

function GetRecordings($data_db, $user_id) {
    $table = 'recording_list AS rl LEFT JOIN device AS d ON rl.device_uid = d.uid' .
       ' LEFT JOIN stream_server AS ss on rl.stream_server_uid = ss.uid';
    $condition = 'd.owner_id = ?';
    $params = $user_id;
    $columns = 'DISTINCT d.name, rl.start as time, rl.path, ss.external_address, ss.external_port';
    $order = 'ORDER BY name ASC, time ASC';

    return $data_db->QueryRecordDataArray($table, $condition, $columns, '', $params);
}

if (!$_GET) {
    $_GET = $_POST;
}
$ret = array();
$ret["status"] = "success";


try {
    $data_db = new DataDbFunction();
    if (VerifyUserWithGet($data_db, $_GET, $user_info_row)
            != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS) {
        throw new Exception(_('Invalid Username / Password.'), 401);
    }
    $user_id = $user_info_row['id'];
    $auth_key = getRandomString(16);
    $ss = new StreamServer();
    $token = $ss->grantAuthForUser($user_id, $auth_key);
    $ret = array_merge($ret, $token);
    $ret['recordings'] = GetRecordings($data_db, $user_id);
}
catch (Exception $e) {
    $code = $e->getCode();
    $message = $e->getMessage();
    //jinho add login count
    if ($code == 401){
    	if (isset($_SESSION["login_err"]))
					$_SESSION["login_err"] = intval($_SESSION["login_err"]) + 1;
			else $_SESSION["login_err"] =1;
			$_SESSION['timeout'] = time();
			$message .= " Fail {$_SESSION["login_err"]} times.";
		}
			 
    if (!$code || $code < 400 || $code >= 600) {
        $code = 500;
    }

    header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
    echo $message;
    exit(0);
}
echo json_encode( $ret );

?>
