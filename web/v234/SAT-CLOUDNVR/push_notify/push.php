<?php 
include_once('apns.php');
include_once('c2dm.php');
include_once('gcm.php');
include_once('jpush.php');
include_once('event_function.php');
include_once('../include/mail_function.php');
include_once('../include/db_function.php');
include_once('event_db_function.php');
include_once('../include/memcached.php');
include_once('../include/utility.php');

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

function isLogEnable() {
	$memcache = GetMemcacheInstance();
	$ary = $memcache->get( "event_enable_mac_list" );
	if ( $ary == false ) {
		$ary = array();
	}
	return array_key_exists($_GET['devicemac'], $ary);
}

function shouldSendNotification($uid) {
	$timeout = PUSH_NOTIFICATION_MIN_INTERVAL;
	$now = time();
	$last = apc_fetch("DVEV_$uid");
	if ($last && $now < $last + $timeout) {
		return false;
	}
	else {
		apc_store("DVEV_$uid", $now, $timeout);
		return true;
	}
}

$ret = array();
$ret['status'] = 'success';
$ret['error_msg'] = '';

$supportedCmdLevel1 = array('DVEV');
$supportedCmdLevel2 = array('FWUP', 'FWFN', 'FWER', 'INUP', 'SMSG', 'TPEV', 'SOEV', 'OEMB', 'DOOR');
$supportedServices = array('camera', 'nvr');

$now = new DateTime('now', new DateTimeZone('UTC'));
$date = $now->format('H:i m.d.Y');

$res = '';
$event_db = null;
try {
	$event_db = new EventDBFunction();

	// Prepare parameters.
	if (array_key_exists('deviceuid', $_GET) === false && array_key_exists('oemid', $_GET) === false && array_key_exists('user', $_GET) === false) {
		throw new Exception('Invalid parameters.');
	}

	if (array_key_exists('devicemac', $_GET) === false) {
		$tmp = explode("-", $_GET['deviceuid']);
		$_GET['devicemac'] = $tmp[1];
	}
	$_GET['devicemac'] = strtoupper(str_replace(':', '', $_GET['devicemac']));

	$cmd = (array_key_exists('cmd', $_GET) === true) ? $_GET['cmd'] : 'DVEV';
	if (in_array($cmd, $supportedCmdLevel1) === false &&
		in_array($cmd, $supportedCmdLevel2) === false) {
		throw new Exception("Unknown command. ($cmd)");
	}

	$msg = '';
	$apiLevel = '1';
	if (in_array($cmd, $supportedCmdLevel2) === true) {
		$apiLevel = '2';
		$msg = (array_key_exists('msg', $_GET) === true) ? $_GET['msg'] : '';
	}

	$service = (array_key_exists('service', $_GET) === true) ? $_GET['service'] : 'camera';   
	if (in_array($service, $supportedServices) === false) {
		throw new Exception("Unknown service. ($service)");
	}

	// Get owner name by device uid, (url_prefix or purpose).
	$url_prefix = NULL;
	$purpose = NULL;
	if ($service == 'camera') {
		$url_prefix = 'rtsp://';
		$purpose = 'WBMJ';
	}
	else if ($service == 'nvr') {
		$purpose = 'WBNV';
	}

	$db = new DataDBFunction();
	if ( $cmd === 'OEMB' ) {
		$oem_id = (array_key_exists('oemid', $_GET) === true) ? $_GET['oemid'] : 'N99';
		// Get target registration id by owner name.
		$oem_ids = array();
		$oem_ids[0] = $oem_id;
		$device_event_infos = $event_db->GetDevicesByOemIds($oem_ids);
	}
	else if ( $cmd === 'DOOR' ) {
		// TODO: get door_id from msg
		$msgs = array();
		$msgs = explode("_", $msg);
		$door_ids = array();
		$door_ids[0] = $msgs[0];
		// TODO: query user_name by door_id
		$user_names = array();
		$user_names = $event_db->GetUserNamesByDoorIds($door_ids);
		// Get target registration id by owner name.
		$device_event_infos = $event_db->GetDevicesByOwnerNames($user_names);
	}
	else if ( $cmd === 'SMSG' && array_key_exists('user', $_GET) === true) {
		$user_names = array();
		$user_names[0] = $_GET['user'];
		$device_event_infos = $event_db->GetDevicesByOwnerNames($user_names);
	}
	else {
		$device = $db->GetDeviceByUidWithConditions($_GET['deviceuid'], $url_prefix, $purpose);
		if ($device === false) {
			throw new Exception('No matching device.');
		}
		// Get target registration id by owner name.
		$user_names = array();
		$user_names[0] = $device['user_name'];
		$device_event_infos = $event_db->GetDevicesByOwnerNames($user_names);
	}

	// Send email notification.
	if ($cmd == 'DVEV') {
		// Insert into database
		$rowid = $event_db->InsertCloudEvent($_GET['deviceuid'], $now);
		
		if ($rowid) {
			try {
				if (DEFAULT_TIMEZONE) {
					$tz = new DateTimeZone(DEFAULT_TIMEZONE);
				}
				else {
					$tz = NULL;
				}
				$local_now = new DateTime('now', $tz);
				$local_date = $local_now->format('H:i m.d.Y');
				
				$subject = "IvedaXpress Event Triggered - {$device['device_name']}";
				$body = "Motion Triggered: Motion Event detected at $local_date, " .
						'please log into your IvedaXpress account to view the event recordings.' .
						'Please Note: The recording might be pending on your recording packages.';
				SendEmailForIveda($device['reg_email'], $subject, $body, $device['user_name']);
			}
			catch (Exception $e) {
				$ret['email_error'] = $e->getMessage();
			}
		}
	}
	
	if (count($device_event_infos) > 0) {
		$res = count($device_event_infos);

		// Compose message.
		$message = array();
		$message['cmd'] = $cmd;
		$message['msg'] = $msg;
		$message['user'] = $device['user_name'];
		$message['openid'] = ($device['google_openid'] != '') ? $device['reg_email'] : '';
		$message['devicename'] = $device['device_name'];
		$message['deviceuid'] = $_GET['deviceuid'];
		$message['devicemac'] = $_GET['devicemac'];
    if (isset($_GET['oemid']))  //jinho add to fix log error
		$message['oemid'] = $_GET['oemid'];
    if (isset($door_id))  //jinho add to fix log error
		$message['doorid'] = $door_id;

		$alert = getAlert($apiLevel, $message);

		// Parse mobile device.
		$registrationIds = array(); // for android c2dm, ver=1
		$gcmRegistrationIds = array(); // for android gcm, ver=3
		$iosDeviceInfos = array(); // for ios
		$jpushTargets = array();
		foreach ($device_event_infos as $device_event_info) {
			// Filter out device which is in block list.
			$block_uids = $device_event_info['block_uids'];
			if (in_array($_GET['deviceuid'], $block_uids)) {
				continue;
			}

			// Filter out device which api level is lower than $apiLevel.
			if ($device_event_info['ver'] < $apiLevel) {
				continue;
			}
			
			// Prepare data for various event type
			if ($device_event_info['ver'] >= 4) {
				$notification_type = $device_event_info['notification_type'];
				if ($notification_type == 'jpush') {
					$domain = $device_event_info['domain'];
					$distr = $device_event_info['distr'];
					$jpushTargets["$domain:$distr"] = array(
						'domain' => $domain,
						'production' => ($distr == 'production' ? true : false)
					);
				}
				else if ($notification_type == 'gcm') {
					$gcmRegistrationIds[] = $device_event_info['registration_id'];	
				}
				else if ($notification_type == 'apns') {	
					$iosDeviceInfos[] = $device_event_info;
				}
			}
			else {
				if ($device_event_info['device_os'] == 'android') {
					if ($device_event_info['ver'] == 3) {
						$gcmRegistrationIds[] = $device_event_info['registration_id'];
					}
					else {
						$registrationIds[] = $device_event_info['registration_id'];
					}
				}
				else if ($device_event_info['device_os'] == 'ios') {
					$iosDeviceInfos[] = $device_event_info;
				}
			}
		}

		if ($cmd == 'DVEV' && shouldSendNotification($_GET['deviceuid'])) {

			// Send message to android/ios.
			try {
			// jpush for china service
				$jpush = new jpush();
				$tags = array($user_names[0]); // XXX tag -> user_name && uid to disable each camera's notification.
				$jpush->setTags($tags);
				$jpush->setTargets($jpushTargets);
				$jpush->sendMessage($message, $alert);
			} catch (Exception $e) {
			}
			if (!isServerInChina()) {
				try {
					// gcm - android google cloud messaging
					$gcm = new gcm();
					$gcm->setRegistrationIds($gcmRegistrationIds);
					$gcm->sendMessage($message);
				} catch (Exception $e) {
				}
			}

			try {
				// apns - apple push notification service
				$apns = new apns();
				$apns->sendMessage($message, $alert, $iosDeviceInfos, $apiLevel);
			} catch (Exception $e) {
			}
		}
	}
}
catch (Exception $e) {
	$ret['status'] = 'fail';
	$ret['error_msg'] = $e->getMessage();
	$res = $ret['error_msg'];
}

// log
try {
	if (isLogEnable()) {
		$event_db->InsertEventLog($_GET['deviceuid'], $_GET['devicemac'], var_export($_GET, true), $res);
	}
}
catch (Exception $e) {
	$ret['status'] = 'fail';
	$ret['error_msg'] = $ret['error_msg'] . $e->getMessage();
}

echo json_encode($ret);
?>
