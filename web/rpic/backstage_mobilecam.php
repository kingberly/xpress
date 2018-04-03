<?php
$_PASS_SESSION = true;
include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/device_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" ); 
include_once( "./include/streamserver.php" );

function GetDevice($data_db, $mac_addr) {
    $table = 'stream_server_assignment AS ssa LEFT JOIN device ' .
        'ON ssa.device_uid = device.uid AND ssa.purpose = device.purpose ' .
        'LEFT JOIN signal_server_online_client_list AS ocl ' .
        'ON device.uid = ocl.uid';
        
    $columns = 'device.uid, device.owner_id, ocl.id AS online, ' . 
        'ssa.stream_server_uid';
    $condition = 'device.mac_addr = ?';

    return $data_db->QueryRecordDataOne($table, $condition, $columns, $mac_addr);
}

function GetStatus($data_db, $user_id, $mac_addr, &$ret) {
    $device = GetDevice($data_db, $mac_addr);
    if (!$device || $device['owner_id'] != $user_id) {
        throw new Exception('Device not found' );
    }

    $ret['rtmp'] = $device['online']?1:0;

    $ss = new StreamServer();
    $live = $ss->getStreamStatus($device['stream_server_uid'], $device['uid']);
    if ($live && $live[0]) {
        $ret['live'] = $live[0]['status'];
    }
    else {
        $ret['live'] = 'Stopped';
    }

    $record = $ss->getRecordingStatus($device['stream_server_uid'], $device['uid']);
    if ($record && $record['record'] && $record['record'][0]) {
        $ret['record'] = $record['record'][0]['status'];
    }
    else {
        $ret['record'] = 'Stopped';
    }
}

if (count($_GET)==0) {
  $_GET = $_POST;
}

// the data array to return
$ret = array();

try {
    // open db
    $data_db = new DataDBFunction();

  // try to get user info
  $get_user_info_status = VerifyUserWithGet($data_db, $_GET, $user_info_row);
  if( $get_user_info_status != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS )
    throw new Exception( 'Authentication failed.' );

    switch($_GET['command']) {
    case 'get_status':
        GetStatus($data_db, $user_info_row['id'], $_GET['mac_addr'], $ret);
        break;
    default:
    throw new Exception('Invalid command');
        break;
    }

}
catch( Exception $e ) {
    SetErrorState( $ret, $e->getMessage() );
}

// encode & return
echo json_encode( $ret );
?>