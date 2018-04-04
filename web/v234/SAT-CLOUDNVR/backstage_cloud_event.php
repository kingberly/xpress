<?php
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/db_function.php" );
include_once( './include/user_function.php');

header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// the data array to return
$ret = array();
$ret["status"] = "success";

// to be compatibile for post
if (empty($_GET)) {
	$_GET = $_POST;
}

function GetCluodEvents($data_db, $user_id, $uid, $start=false, $end=false) {
	if ( !checkViewingPermission($data_db, $uid, $user_id) ) {
		throw new Exception('You do not have the permission to access this file');
	}
	
	if (!$start) {
		$dt = new DateTime('now', new DateTimeZone('UTC'));
		$dt->setTime(0,0,0);
		$dt->sub(new DateInterval('P7D'));
		$start = $dt->format('YmdHis');
	}
	if (!$end) {
		$dt = new DateTime('now', new DateTimeZone('UTC'));
		$dt->setTime(0,0,0);
		$dt->add(new DateInterval('P1D'));
		$end = $dt->format('YmdHis');
	}
	$table = 'cloud_event LEFT JOIN recording_list ON cloud_event.recording_id = recording_list.id';
	$condition = 'cloud_event.device_uid = :uid AND (cloud_event.date BETWEEN :start AND :end) AND cloud_event.recording_id IS NOT NULL';
	$columns = 'cloud_event.id, cloud_event.date AS date, recording_list.path as url, recording_list.stream_server_uid as stream_server_uid';
	$order = 'ORDER BY cloud_event.date ASC';
	$params = array(':uid'=>$uid, ':start'=>$start, ':end'=>$end);
	return $data_db->QueryRecordDataArray($table, $condition, $columns, $order, $params);
}

function DownloadCloudEvent($data_db, $user_id, $event_id) {
	$table = 'cloud_event LEFT JOIN recording_list ON cloud_event.recording_id = recording_list.id';
	$condition = 'cloud_event.id = ?';
	$columns = 'cloud_event.date, cloud_event.device_uid, recording_list.path, recording_list.start, recording_list.end';
	
	$event = $data_db->QueryRecordDataOne($table, $condition, $columns, $event_id);
	if (!$event) {
		httpError('404 Not Found', 'Invalid event id');
	}

	$source = preg_replace('/\/vod/', RECORDING_STORAGE, $event['path']);
	if (!file_exists($source)) {
		httpError('404 Not Found', 'Invalid event id');
	}
	
	if ( !checkViewingPermission($data_db, $event['device_uid'], $user_id) ) {
		httpError('403 Forbidden', 'You do not have the permission to access this file');
	}
	
	$date = StringToDateTime($event['date']);
	$start = StringToDateTime($event['start']);
	$end = StringToDateTime($event['end']);

	$event_seconds = $date->getTimestamp() - $start->getTimestamp();
	if ($event_seconds > 15) {
		$start_seconds = ' -ss ' . ($event_seconds - 15);
	}
	else {
		$start_seconds = '';
	}
	
	$bin = '/usr/bin/avconv';
	$input_parameters = ' -y';
	$output_parameters = ' -codec copy -t 30 -f mp4 ';
	$destination = tempnam('/tmp' , 'backstage_event' );
	$command = "$bin $start_seconds $input_parameters -i $source $output_parameters $destination";
	
	$success = false;
	try {
		system($command, $return_var);
		if ($return_var == 0) {
			$success = true;
		}
	}
	catch (Exception $e) {}
	
	if ($success) {
		$name = $event['date'] . '.mp4';
		header('Content-Type: video/MP4');
		header('Content-Disposition: attachment; filename=' . $name);
		header('Content-Length: ' . filesize($destination));
		header('Cache-Control: ');
		readfile($destination);
		unlink($destination);
	}
	else {
		unlink($destination);
		httpError('500 Error', 'Internal server error');
	}
}

function StringToDateTime($string) {
	$date_string = substr($string, 0, 4) . '-' .
			substr($string, 4, 2) . '-' .
			substr($string, 6, 2) . ' ' .
			substr($string, 8, 2) . ':' .
			substr($string, 10, 2) . ':' .
			substr($string, 12, 2);
			
	return new DateTime($date_string, new DateTimeZone('UTC'));
}

function httpError($code, $message) {
	header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
	echo $message . "\n";
	exit(0);
}

try {
	$user_id = $_SESSION['user_id'];
	$data_db = new DataDBFunction();

	switch ($_GET['command']) {
		case 'list':
			$ret['events'] = GetCluodEvents($data_db, $user_id, $_GET['uid'], $_GET['start'], $_GET['end']);
			break;
		case 'download':
			DownloadCloudEvent($data_db, $user_id, $_GET['event_id']);
			break;
		default:
			throw new Exception("Invalid command: ${_GET['command']}");
	}
}
catch( Exception $e ) {
	SetErrorState( $ret, $e->getMessage() );
}
echo json_encode( $ret );
?>

