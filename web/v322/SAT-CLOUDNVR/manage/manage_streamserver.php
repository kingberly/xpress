<?php

require('../include/streamserver.php');

$ret = array();
$ret['status'] = 'success';
$ret['error_msg'] = '';

try {
	if ($_POST && $_POST['command'])
		$_GET=$_POST;
	$ss = new StreamServer();
	switch($_GET['command']) {
	case 'get_streamservers':
		$ret['streamservers'] = $ss->getStreamServers();
		$arguments = array('history' => 1);
		$ret['stats'] = array('port' => StreamServer::XMLRPC_PORT, 'path' => '/', 'function' => 'getStats', 
				'arguments' => $arguments );
		break;
	case 'get_cameras_by_streamserver':
		$ret['cameras'] = $ss->getCamerasByStreamServer($_GET['streamserver']);
		break;
	case 'get_stats':
		if (!isset($_GET['history']))
			$_GET['history'] = 0;
		$ret['stats'] = $ss->getStats($_GET['streamserver'], intval($_GET['history']));
		break;
	case 'get_recording_status':
		$ret['recording'] = $ss->getRecordingStatus($_GET['streamserver']);
		break;
	case 'get_stream_status':
		$ret['stream'] = $ss->getStreamStatus($_GET['streamserver']);
		break;
	case 'get_version':
		$ret['version'] = $ss->getVersion($_GET['streamserver']);
		break;
	case 'assign_device':
		$ss->assignDevice($_GET['cid'], $_GET['pid'], $_GET['mac'], $_GET['purpose'], $_GET['streamserver']);
		break;
	case 'update_recording_recycle':
		$ss->updateRecordingRecycle($_GET['mac'], $_GET['days']);
		break;
	case 'update_camera':
		$ss->updateCamera($_GET['mac'], $_GET['purpose'], $_GET['dataplan'], $_GET['schedule'], $_GET['days']);
		break;
	case 'restart_stream':
		$ss->restartStream($_GET['streamserver'], $_GET['device_uid']);
		break;
	case 'is_enabled':
		$ret['enabled'] = $ss->isEnabled($_GET['streamserver']);
		break;
	case 'enable':
		$ss->enable($_GET['streamserver']);
		break;
	case 'disable':
		$ss->disable($_GET['streamserver']);
		break;
	case 'set_port':
		$ss->setPort($_GET['streamserver'], $_GET['port']);
		break;
	case 'set_region':
		$ss->setRegion($_GET['streamserver'], $_GET['region']);
		break;
    case 'copy_recording':
        $count = $ss->copyRecording($_GET['from_uid'], $_GET['to_uid'], $_GET['start'], $_GET['end']);
        $ret['count'] = $count;
        break;
    case 'transfer_recording':
        $count = $ss->transferRecording($_GET['from_uid'], $_GET['to_uid'], $_GET['start'], $_GET['end']);
        $ret['count'] = $count;
        break;
    case 'remove_recording':
        $ss->clearRecording($_GET['uid']);
        break;
	default:
		throw new Exception('Invalid command');
		break;
	}
}
catch (Exception $e) {
	$ret['status'] = 'fail';
	$ret['error_msg'] = $e->getMessage();
}
echo json_encode($ret);
?>
