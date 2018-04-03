<?php

require_once('global.php');
require_once('db_function.php');
require_once('license_db_function.php');
require_once('xmlrpc_client.php');
require_once('utility.php');

class StreamServer {
	protected $license_db;
	const DEFAULT_PURPOSE = 'RVME';
	protected $batch_assign_stmt;
	protected $batch_assign_stream_server;
	protected $batch_assign_purpose;
	protected $stream_server_list;
	const XMLRPC_PORT = '8087';
	const REGION_CACHE = 'streamserver_region';

// public interfaces

	public function __construct($license_db = false) {
		$this->license_db = $license_db;
	}

	public function getStreamServers() {
		return $this->getDB()->QueryRecordDataArray('stream_server', '1', 
			'id,uid,internal_address,external_port AS port, hostname,license_id,region');
	}
	
	public function checkExists($streamserver) {
		return $this->getDB()->QueryRecordNo('stream_server', "uid = $streamserver");
	}

	public function getCamerasByStreamServer($streamserver) {
		return $this->getDB()->QueryRecordDataArray('stream_server_assignment', "stream_server_uid = '$streamserver'", 'id,device_uid,purpose,dataplan,schedule,recycle');
	}

	public function getStats($streamserver, $history = 0) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('getStats', array('history'=>$history));
	}

	public function getRecordingStatus($streamserver, $uid = false) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('getRecordingStatus', $uid);
	}
	
	public function getStreamStatus($streamserver, $uid = false) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('getStreamStatus', $uid);
	}
	
	public function getVersion($streamserver) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('getVersion');
	}

	public function grantAuthForUser($user_id, $auth_key) {
		$expire = time() + 3600;
		$devices = array_merge($this->getOwnedDevices($user_id), 
			$this->getSharedDevices($user_id));
		$rpc_table = $this->generateAuthRpcTable($devices, $auth_key, $expire + 60);
		$errors = $this->callAuthRpc($rpc_table);
		return array('key'=>$auth_key, 'expire'=>$expire, 'errors'=>$errors);
	}

	public function grantAuthForDevice($user_id, $uid, $auth_key) {
		$expire = time() + 3600;
		$device = $this->checkOwnedDevice($user_id, $uid);
		if (!$device) {
			$device = $this->checkSharedDevice($user_id, $uid);
		}
		if (!$device) {
			throw new Exception('Device not found.');
		}
		$rpc_table = $this->generateAuthRpcTable($device, $auth_key, $expire + 60);
		$errors = $this->callAuthRpc($rpc_table);
		return array('key'=>$auth_key, 'expire'=>$expire, 'errors'=>$errors);
	}

	public function setRegion($uid, $region) {
		$table = 'stream_server';
		$params = array(':uid'=>$uid, ':region'=>$region);
		$dbh = $this->getDB()->GetHelper($params);
		$dbh->execute("UPDATE $table SET region = :region WHERE uid = :uid");
	}

	private function getOwnedDevices($user_id) {
		return $this->getDevicesAndStreamserver('device', 'd.owner_id = ?', $user_id);
	}

	private function getSharedDevices($user_id) {
		return $this->getDevicesAndStreamserver('device_share', 'd.visitor_id = ?', $user_id);
	}

	private function checkOwnedDevice($user_id, $uid) {
		$params = array(':user_id'=>$user_id, ':uid'=>$uid);
		return $this->getDevicesAndStreamserver('device',
			'd.owner_id = :user_id AND d.uid = :uid', $params);
	}

	private function checkSharedDevice($user_id, $uid) {
		$params = array(':user_id'=>$user_id, ':uid'=>$uid);
		return $this->getDevicesAndStreamserver('device_share',
			'd.visitor_id = :user_id AND d.uid = :uid', $params);
	}

	private function getDevicesAndStreamserver($main_table, $condition, $params) {
		$columns = 'DISTINCT d.uid, ss.internal_address';
		$tables = $main_table . ' AS d LEFT JOIN stream_server_assignment AS sa ON d.uid = sa.device_uid LEFT JOIN stream_server AS ss on sa.stream_server_uid = ss.uid';
		$condition.= ' AND ss.internal_address IS NOT NULL';
		return $this->getDB()->QueryRecordDataArray($tables, $condition, $columns, '', $params);
	}
	
	private function generateAuthRpcTable($devices, $auth_key, $expire) {
		$rcp_table = array();
		foreach ($devices as $d) {
			if (!$rpc_table[$d['internal_address']]) {
				$rpc_data = array($auth_key=> array(
					'expire' => $expire,
					'allowed' => array()
				));
				$rpc_table[$d['internal_address']] = $rpc_data;
			}
			$rpc_table[$d['internal_address']][$auth_key]['allowed'][] = $d['uid'];
		}
		return $rpc_table;
	}

	private function callAuthRpc($rpc_table) {
		$errors = array();
		foreach ($rpc_table as $addr => $data) {
			try {
				$rpc = "http://$addr:" . self::XMLRPC_PORT . "/";
				$client = new xmlrpc_client($rpc, false);
				$client->call('addAuth', $data);
			}
			catch (Exception $e) {
				$errors[$addr] = $e->getMessage();
			}
		}
		return $errors;
	}

	public function selectStreamServer() {
		$table = 'stream_server LEFT JOIN ' .
					'(SELECT stream_server_uid AS uid, COUNT(*) AS count FROM stream_server_assignment GROUP BY stream_server_uid) AS t_count ' .
					'ON stream_server.uid = t_count.uid';
		$condition = 'stream_server.license_id IS NOT NULL';
		$columns = 'stream_server.uid AS uid';
		$limit = 'ORDER BY t_count.count ASC LIMIT 1';
		$result = $this->getDB()->QueryRecordDataOne($table, $condition, $columns, $limit);
		if ($result)
			return $result['uid'];
		else
			return false;
	}


	public function assignDevice($cid, $pid, $mac, $purpose='', $streamserver = '', $check_integrity = false) {
		if (!$purpose) {
			$purpose = static::DEFAULT_PURPOSE;
		}
		
		if ($streamserver) {
			// Manual assignment
			if ($check_integrity) {
				$server_exists = $this->checkExists($streamserver);
				if (!$server_exists) {
					throw new Exception('Stream server not found.');
				}
			}
		}
		else {
			// Automatic assignment
			$streamserver = $this->selectStreamServer();
			if (!$streamserver) {
				throw new Exception('No stream server available.');
			}
		}
		
		if ($check_integrity) {
			if (!$this->getDB()->GetSeriesNumberByCidPidMac($cid, $pid, $mac)) {
				throw new Exception('Device not found.');
			}
		}
		
		$sql = 'INSERT INTO stream_server_assignment (device_uid, stream_server_uid, url_path, purpose) ' .
			'VALUES (:device_uid, :stream_server_uid, :url_path, :purpose)';
		$stmt = $this->getDB()->db->prepare( $sql );
		if (!$stmt) {
			$err = $this->getDB()->db->errorInfo();
			throw new Exception($err[2]);
		}
		$device_uid = $cid . $pid . '-' . $mac;
		$url_path = '/' . $device_uid;
		$result = $stmt->execute( array(
					':device_uid'=>$device_uid,
					':stream_server_uid'=>$streamserver,
					':url_path'=>$url_path,
					':purpose'=>$purpose
				));
		if (!$result) {
			$err = $stmt->errorInfo();
			throw new Exception($err[2]);
		}
		$row_count = $stmt->rowCount();
		if (!$row_count) {
			throw new Exception('Device not found');
		}
		
	}

	public function batchAssignBegin(&$device_uid, $device_count, $region=0) {
		// Get stream server list
		$this->stream_server_list = $this->getStreamServersAndCount($region);
		if (!$this->stream_server_list) {
			throw new Exception('Error getting stream server list');
		}
		
		// prepare PDO statement
		$sql = 'INSERT INTO stream_server_assignment (device_uid, stream_server_uid, url_path, purpose) ' .
			'VALUES (:device_uid, :stream_server_uid, CONCAT("/", :device_uid), :purpose)';
		$this->batch_assign_stmt = $this->getDB()->db->prepare( $sql );
		if (!$this->batch_assign_stmt) {
			$err = $this->getDB()->db->errorInfo();
			throw new Exception($err[2]);
		}
		$this->batch_assign_stmt->bindParam(':device_uid', $device_uid, PDO::PARAM_STR,18);
		$this->batch_assign_stmt->bindParam(':stream_server_uid', $this->batch_assign_stream_server, PDO::PARAM_STR,18);
		$this->batch_assign_stmt->bindParam(':purpose', $this->batch_assign_purpose, PDO::PARAM_STR,8);
	}
	
	public function batchAssignOne($purpose = NULL) {
		$this->batch_assign_stream_server = $this->stream_server_list[0]['uid'];
		if ($purpose) {
			$this->batch_assign_purpose = $purpose;
		}
		else {
			$this->batch_assign_purpose = static::DEFAULT_PURPOSE;
		}
		
		if (!$this->batch_assign_stmt->execute()) {
			$err = $this->batch_assign_stmt->errorInfo();
			throw new Exception($err[2]);
		}
		else {
			$this->batchAssignUpdateStreamServer();
		}
	}
	public function updateRecordingRecycle($mac, $days) {
		$sql = 'UPDATE stream_server_assignment SET recycle = :days WHERE device_uid = ' .
			'(SELECT CONCAT(license.cid, license.pid, "-", series_number.mac) from ' .
			'series_number left join license on series_number.license_id = license.id ' .
			'where series_number.mac = :mac)';
		$stmt = $this->getDB()->db->prepare( $sql );
		if (!$stmt) {
			$err = $this->getDB()->db->errorInfo();
			throw new Exception($err[2]);
		}
		$stmt->bindParam(':days', $days, PDO::PARAM_INT);
		$stmt->bindParam(':mac', $mac, PDO::PARAM_STR,12);
		$result = $stmt->execute();
		if (!$result) {
			$err = $stmt->errorInfo();
			throw new Exception($err[2]);
		}
	}

	public function updateCamera($mac, $purpose, $dataplan, $schedule, $days) {
		$columns = '';
		$param = array();
		
		if ($purpose) {
			if (!in_array($purpose, array('RVLO', 'RVME', 'RVHI')) ) {
				throw new Exception('Invalid purpose. Valid values: RVLO, RVME, RVHI.');
			}
			$columns .= 'purpose = :purpose ,';
			$param[':purpose'] = $purpose;
		}
		
		if ($dataplan) {
			if (!in_array($dataplan, array('D','LV','SR','AR', 'EV'))) {
				throw new Exception('Invalid dataplan. Valid values are: D, LV, SR, AR, EV.');
			}
			$columns .= 'dataplan = :dataplan ,';
			$param[':dataplan'] = $dataplan;
		}
		
		if ($schedule) {
			if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $schedule)) {
				throw new Exception('Invalid schedule.');
			}
			$columns .= 'schedule = :schedule ,';
			$param[':schedule'] = $schedule;
		}
		
		$days = intval($days);
		if ($days) {
			$columns .= 'recycle = :days ,';
			$param[':days'] = $days;
		}
		
		if (!$columns) {
			return;
		}
		$columns = substr($columns, 0, strlen($columns)-1);
		$param[':mac'] = $mac;
		
		$sql = 'UPDATE stream_server_assignment SET ' . $columns . ' WHERE device_uid = ' .
			'(SELECT CONCAT(license.cid, license.pid, "-", series_number.mac) from ' .
			'series_number left join license on series_number.license_id = license.id ' .
			'where series_number.mac = :mac)';
		$stmt = $this->getDB()->db->prepare( $sql );
		if (!$stmt) {
			$err = $this->getDB()->db->errorInfo();
			throw new Exception($err[2]);
		}
		$result = $stmt->execute($param);
		if (!$result) {
			$err = $stmt->errorInfo();
			throw new Exception($err[2]);
		}
	}

	public function restartStream($streamserver, $device_uid) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('restartStream', array('device_uid'=>$device_uid));
	}
	
	public function restartRecordings($streamserver, $uid_list) {
		$client = $this->getStreamServerRpc($streamserver);
		return $client->call('restartRecordings', array('uid_list'=>$uid_list));
	}
	
	public function isEnabled($stream_server) {
		$count = $this->getDB()->QueryRecordNo('stream_server', 'uid = ? AND license_id IS NOT NULL', $stream_server);
		return ($count > 0);
	}
	
	public function enable($stream_server) {
		$license_id = $this->getFirstLicenseId();
		$params = array('uid'=>$stream_server, 'license_id'=>$license_id);
		$dbh = $this->getDB()->getHelper($params);
		$dbh->execute('UPDATE stream_server SET license_id = :license_id ' .
				'WHERE uid = :uid AND license_id is NULL');

		$client = $this->getStreamServerRpc($stream_server);
		try {
			$client->call('updateLicense');
		}
		catch (Exception $e) {}
	}
	
	public function updateDevicesRegion($devices_uid, $region) {
		if (is_string($devices_uid)) {
			$devices_uid = array($devices_uid);
		}
		$devices_to_move = $this->getNotInRegion($devices_uid, $region);
		$count = count($devices_to_move);
		if (!$count) {
			return;
		}
		
		$destination = $this->getStreamServersAndCount($region);
		$destination_count = count($destination);
		if ($destination_count == 0) {
			throw new Exception('No more stream servers available');
		}
		$destination_cameras = 0;
		foreach($destination as $d) {
			$destination_cameras += $d['count'];
		}
		$move_device_index = 0;
		$this->getDB()->beginTransaction($transaction);
		try {
			foreach ($destination as $d) {
				$move_count = ceil(($destination_cameras + $count) / $destination_count - $d['count'] );
				if ($move_count <= 0) {
					continue;
				}
				$move_devices = array_slice($devices_to_move, $move_device_index, $move_count);
				$this->moveDevicesToStreamserver($move_devices, $d['uid']);
				$move_device_index += $move_count;
			}
			$this->getDB()->commit($transaction);
		}
		catch (Exception $e) {
			$this->getDB()->rollBack($transaction);
			throw $e;
		}
		$this->updateRegionCache($devices_uid, $region);
	}
	
	protected function getFirstLicenseId() {
		$row = $this->getDB()->QueryRecordDataOne('stream_server_license', 1, 'id');
		if ($row) {
			return $row['id'];
		}
		else {
			$dbi = new DbInsert($this->getDB()->db, 'stream_server_license');
			$dbi->add('LICENSE_ID', '1');
			$count = $dbi->insertOrIgnore();
			if ($count) {
				return $this->getDB()->db->lastInsertId();
			}
			else {
				return $this->getFirstLicenseId();
			}
		}
	}
	
	public function disable($stream_server) {
		$this->getDB()->beginTransaction($transaction);
		try {
			$dbh = $this->getDB()->getHelper($stream_server);
			$affected_count = $dbh->execute('UPDATE stream_server SET license_id = NULL WHERE uid = ?');
			
			$this->redistributeClients($stream_server);
			$this->getDB()->commit($transaction);
		}
		catch (Exception $e) {
			$this->getDB()->rollBack($transaction);
			throw $e;
		}

		$client = $this->getStreamServerRpc($stream_server);
		try {
			$client->call('updateLicense');
		}
		catch (Exception $e) {}
	}
	
	protected function redistributeClients($stream_server) {
		$params = array(':uid'=>$stream_server);
		$servers = $this->getDB()->QueryRecordDataArray( 'stream_server LEFT JOIN '.
				'stream_server AS region_table ON stream_server.region = region_table.region AND ' .
				'region_table.uid = :uid LEFT JOIN ( '.
				'SELECT stream_server_uid, COUNT(*) AS count FROM stream_server_assignment '.
				'GROUP BY stream_server_uid ) AS count_table '.
				'ON stream_server.uid = count_table.stream_server_uid',
				'region_table.uid IS NOT NULL',
				'stream_server.uid, stream_server.license_id, count_table.count',
                '', $params);

		$destination = array();
		$destination_cameras = 0;
		$source = null;
		foreach($servers as $s) {
			if ($s['uid'] == $stream_server) {
				$source = $s;
			}
			else if ($s['license_id']) {
				$destination[] = $s;
				$destination_cameras += $s['count'];
			}
		}
		
		if (!$source || $source['count'] == 0) {
			return;
		}
		$destination_count = count($destination);
		if ($destination_count == 0) {
			throw new Exception('No more stream servers available');
		}
		
		foreach ($destination as $d) {
			$move_count = ceil(($destination_cameras + $source ['count']) / $destination_count - $d['count'] );
			if ($move_count <= 0) {
				continue;
			}
			$params = array(':src'=>$source['uid'], ':dst'=>$d['uid'], ':limit'=>$move_count);
			$dbh = $this->getDB()->getHelper($params);
			$dbh->setDebug();
			$dbh->execute('UPDATE stream_server_assignment SET stream_server_uid = :dst WHERE '.
					'stream_server_uid = :src LIMIT :limit');
		}
	}
	
	public function removeRecordings($uid_list) {
		$servers = $this->getStreamServers();
		if (!$servers) {
			throw new Exception('No stream servers available');
		}
		
		foreach($servers as $s) {
			try {
				$client = $this->getStreamServerRpc($s);
				return $client->call('removeRecordings', array('uid_list'=>$uid_list));
			}
			catch (Exception $e){}
		}
		
		return false;
	}

	public function setPort($stream_server, $port) {
		$params = array(':port'=>$port, ':stream_server'=>$stream_server);
		$dbh = $this->getDB()->getHelper($params);
		$dbh->execute('UPDATE stream_server SET external_port = :port' .
				' WHERE uid = :stream_server');
	}
	
    public function copyRecording($from_uid, $to_uid, $start, $end) {
        $from_ss = $this->getStreamServerByDevice($from_uid);
        $to_ss = $this->getStreamServerByDevice($to_uid);
        $recordings = $this->getRecordings($from_uid, $start, $end);

        $count = 0;
        $client = $this->getStreamServerRpc($from_ss);
        $options = array(CURLOPT_TIMEOUT => 600);
        foreach($recordings as $recording) {
            $params = array(
                'from_uid' => $from_uid,
                'to_uid' => $to_uid,
                'start' => $recording['start'],
                'end' => $recording['end'],
                'stream_server_uid' => $to_ss
            );
            $response = $client->call('copyRecording', $params, $options);
            if (!array_key_exists('faultCode', $response)) {
                $count++;
            }
            else {
                throw new Exception($response['faultString']);
            }
        }

        $this->setFeature($to_uid);

        return $count;
    }

    public function transferRecording($from_uid, $to_uid, $start, $end) {
        $from_ss = $this->getStreamServerByDevice($from_uid);
        $to_ss = $this->getStreamServerByDevice($to_uid);
        $recordings = $this->getRecordings($from_uid, $start, $end);

        $count = 0;
        $client = $this->getStreamServerRpc($from_ss);
        $options = array(CURLOPT_TIMEOUT => 600);
        $sql = 'UPDATE recording_list SET device_uid = :to_uid, ' .
            'stream_server_uid = :to_ss, ' .
            'path = :path ' .
            'WHERE id = :id';

        foreach($recordings as $recording) {
            $params = array(
                'from_uid' => $from_uid,
                'to_uid' => $to_uid,
                'start' => $recording['start'],
                'end' => $recording['end']
            );
            $response = $client->call('transferRecording', $params, $options);
            if (!array_key_exists('faultCode', $response)) {
                $count++;

                $query_params = array(
                    ':to_uid' => $to_uid,
                    ':to_ss' => $to_ss,
                    ':path' => str_replace($from_uid, $to_uid, $recording['path']),
                    ':id' => $recording['id']
                );
                $this->getDB()->executeSql($sql, $query_params);
            }
            else {
                throw new Exception($response['faultString']);
            }
        }

        $this->setFeature($to_uid);

        return $count;
    }

    public function clearRecording($uid) {
        $ss = $this->getStreamServerByDevice($uid);
        $client = $this->getStreamServerRpc($ss);
        $params = array('uid_list'=>array($uid));
        $response = $client->call('removeRecordings', $params);
        if (!array_key_exists('faultCode', $response)) {
            $sql = 'DELETE FROM recording_list WHERE device_uid = ?';
            $this->getDB()->executeSql($sql, $uid);
        }
        else {
            throw new Exception($response['faultString']);
        }
    }

// Private utilites
	private function getStreamServerRpc($streamserver) {
		if (is_string($streamserver)) {
			$addr = $this->getStreamServerAddress($streamserver);
		}
		else if (is_array($streamserver)) {
			$addr = $streamserver['internal_address'];
		}
		
		$rpc = "http://$addr:" . self::XMLRPC_PORT . "/";
		$client = new xmlrpc_client($rpc, false);
		return $client;
	}

	private function getStreamServerAddress($streamserver) {
		$row = $this->getDB()->QueryRecordDataOne('stream_server', "uid = '$streamserver'", 'internal_address');
		if (!$row)
			throw new Exception('Invalid streamserver');
		return $row['internal_address'];
	}

	private function getDB() {
		if (!$this->license_db) {
			$this->license_db = new LicenseDBFunction();
		}
		return $this->license_db;
	}

	private function batchAssignUpdateStreamServer() {
		$this->stream_server_list[0]['count']++;
		// partial sorting
		for ($i=0; $this->stream_server_list[$i]; $i++) {
			if ($this->stream_server_list[$i+1] && 
					$this->stream_server_list[$i]['count'] > $this->stream_server_list[$i+1]['count'] ) {
				$tmp = $this->stream_server_list[$i];
				$this->stream_server_list[$i] = $this->stream_server_list[$i+1];
				$this->stream_server_list[$i+1] = $tmp;
			}
			else {
				break;
			}
		}
	}

	protected function getStreamServersAndCount($region) {
		$params = array();
		$table = 'stream_server LEFT JOIN ' .
					'(SELECT stream_server_uid AS uid, COUNT(*) AS count FROM stream_server_assignment GROUP BY stream_server_uid) AS t_count ' .
					'ON stream_server.uid = t_count.uid';
		$condition = 'stream_server.license_id IS NOT NULL';
		$columns = 'stream_server.uid AS uid, t_count.count AS count';
		$order = 'ORDER BY count ASC';
		if ($region >=0) {
			$condition .= ' AND stream_server.region = :region';
			$params[':region'] = $region;
		}
		return $this->getDB()->QueryRecordDataArray($table, $condition, $columns, $order, $params);
	}

	protected function getNotInRegion($devices_uid, $region) {
		$result = array();
		$not_in_mc = array();
		$index = 1;

		// check memory cache
		foreach ($devices_uid as $uid) {
			$mac_addr = substr($uid, 6);
			$cached_data = GetMemcacheArray($mac_addr, $memcache);
			if ($cached_data && $cached_data[static::REGION_CACHE]) {
				if ($cached_data[static::REGION_CACHE] != $region) {
					$result[] = $uid;
				}
			}
			else {
				$not_in_mc[$index] = $uid;
				$index++;
			}
			
		}

		// check database
		if ($not_in_mc) {
			$uid_array = array_fill(0, count($not_in_mc), '?');
			$uid_query = implode(',', $uid_array);
			$table = 'stream_server_assignment ssa LEFT JOIN stream_server ss' .
					' ON ssa.stream_server_uid = ss.uid';
			$condition = "ssa.device_uid in ($uid_query) AND  ss.region != ?";
			$columns = 'ssa.device_uid';
			$params = $not_in_mc;
			$params[] = $region;
			$db_result = $this->getDB()->QueryRecordDataArray($table, $condition, $columns, '', $params);
			foreach ($db_result as $row) {
				$result[] = $row['device_uid'];
			}
		}
		
		return $result;
	}
	
	protected function moveDevicesToStreamserver($devices, $streamserver) {
		if (!$devices) {
			return;
		}
		
		$devices_count = count($devices);
		$devices_array = array_fill(0, $devices_count, '?');
		$devices_query = implode(',', $devices_array);
		$sql = "UPDATE stream_server_assignment SET stream_server_uid = ? WHERE device_uid in ($devices_query)";
		$params = array(1=>$streamserver);
		foreach ($devices as $d) {
			$params[] = $d;
		}
		$this->getDB()->executeSql($sql, $params);
	}
	
	protected function updateRegionCache($devices_uid, $region) {
		foreach ($devices_uid as $uid) {
			$mac_addr = substr($uid, 6);
			$cached_data = GetMemcacheArray($mac_addr, $memcache);
			if (!$cached_data) {
				$cached_data = array();
			}
			if ($cached_data[static::REGION_CACHE] !== $region) {
				$cached_data[static::REGION_CACHE] = $region;
				$memcache->set($mac_addr, $cached_data);
			}
		}
	}

    private function getStreamServerByDevice($uid) {
        $row = $this->getDB()->QueryRecordDataOne('stream_server_assignment', 'device_uid = ?', 'stream_server_uid', $uid);
        if (!$row) {
            return $False;
        }
        else {
            return $row['stream_server_uid'];
        }
    }

    private function getRecordings($uid, $start, $end) {
        $conditions = 'device_uid = :device_uid AND start BETWEEN :start AND :end';
        $params = array(
            ':device_uid' => $uid,
            ':start' => $start,
            ':end' => $end
        );
        return $this->getDB()->QueryRecordDataArray('recording_list', $conditions, '*', '', $params);
    }

    private function setFeature($uid) {
        $table = 'device LEFT JOIN device_models ON device.model_id = model_id';
        $columns = 'device_models.features';
        $conditions = 'device.uid = ?';
        $features = $this->getDB()->QueryRecordDataOne($table, $conditions, $columns, $uid);
        if ($features && strpos($features['features'], 'recording') !== false) {
            return;
        }

        $model_id = $this->getVirtualMacModelId();
        $dbh = $this->getDB()->GetHelper($uid);
        $dbh->execute("UPDATE device SET model_id = $model_id WHERE uid = ?");
    }

    private function getVirtualMacModelId() {
        $model = $this->getDB()->QueryRecordDataOne('device_models', 'manufacturer="Qlync" AND model="Virtual MAC"', 'id');
        if ($model) {
            return $model['id'];
        }
        else {
            $dbi = new DbInsert($this->getDB()->db, 'device_models');
            $dbi->add('manufacturer', 'Qlync');
            $dbi->add('model', 'Virtual MAC');
            $dbi->add('version', '0');
            $dbi->add('features', 'rtsp,recording');
            $dbi->insertOrUpdate();
            return $this->getDB()->db->lastInsertId();
        }
    }
}
?>
