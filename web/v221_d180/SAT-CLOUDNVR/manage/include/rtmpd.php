<?php

require_once('tunnelserver.php');

class Rtmpd extends TunnelServer {
	const XMLRPC_PORT = 8088;
    const PURPOSE = 'RTMPD';
	protected $batch_assign_stmt;
	protected $batch_assign_rtmp_server;
	protected $rtmp_server_list;
    
    public function getCamerasByTunnelServer($rtmpd) {
        return $dbh = $this->getDB()->QueryRecordDataArray('rtmp_server_assignment',
                'tunnel_server_uid = ?', 'id, device_uid', '', $rtmpd);
    }
    

	public function isEnabled($rtmpd) {
        $params = array(':uid'=>$rtmpd, ':purpose'=>static::PURPOSE);
		$count = $this->getDB()->QueryRecordNo('tunnel_server', 
                'uid = :uid  AND purpose = :purpose AND license_id IS NOT NULL', $params);
		return ($count > 0);
	}
	
	public function enable($rtmpd) {
		$license_id = $this->getLicenseId();
		$params = array(':uid'=>$rtmpd, ':purpose'=>static::PURPOSE, ':license_id'=>$license_id);
		$dbh = $this->getDB()->getHelper($params);
		$dbh->execute('UPDATE tunnel_server SET license_id = :license_id ' .
				'WHERE uid = :uid AND purpose = :purpose AND license_id is NULL');

		$client = $this->getTunnelServerRpc($rtmpd);
		try {
			$client->call('updateLicense');
		}
		catch (Exception $e) {}
	}
	
	protected function getLicenseId() {
		$dbi = new DbInsert($this->getDB()->db, 'tunnel_server_license');
        $dbi->add('id', -1);
        $dbi->add('license_key', 'RTMPD');
        $dbi->execute(false, true);
        return -1;
	}
	
	public function disable($rtmpd) {
		$this->getDB()->beginTransaction($transaction);
		try {
            $params = array(':uid'=>$rtmpd, ':purpose'=>static::PURPOSE);
			$dbh = $this->getDB()->getHelper($params);
			$affected_count = $dbh->execute(
                    'UPDATE tunnel_server SET license_id = NULL WHERE uid = :uid AND purpose = :purpose');
			
			$this->redistributeClients($rtmpd);
			$this->getDB()->commit($transaction);
		}
		catch (Exception $e) {
			$this->getDB()->rollBack($transaction);
			throw $e;
		}

		$client = $this->getTunnelServerRpc($rtmpd);
		try {
			$client->call('updateLicense');
		}
		catch (Exception $e) {}
	}
	
	protected function redistributeClients($rtmpd) {
        $params = array('purpose'=>static::PURPOSE);
		$servers = $this->getDB()->QueryRecordDataArray( 'tunnel_server LEFT JOIN ( '.
				'SELECT tunnel_server_uid, COUNT(*) AS count FROM rtmp_server_assignment '.
				'GROUP BY tunnel_server_uid ) AS count_table '.
				'ON tunnel_server.uid = count_table.tunnel_server_uid AND tunnel_server.purpose = :purpose',
				'tunnel_server.purpose = :purpose', 
                'tunnel_server.uid, tunnel_server.license_id, count_table.count',
                '', $params);

		$destination = array();
		$destination_cameras = 0;
		$source = null;
		foreach($servers as $s) {
			if ($s['uid'] == $rtmpd) {
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
			throw new Exception('No more RTMP server available');
		}
		
		foreach ($destination as $d) {
			$move_count = ceil(($destination_cameras + $source ['count']) / $destination_count - $d['count'] );
			if ($move_count < 0) {
				continue;
			}
			$params = array(':src'=>$source['uid'], ':dst'=>$d['uid'], ':limit'=>$move_count);
			$dbh = $this->getDB()->getHelper($params);
			$dbh->setDebug();
			$dbh->execute('UPDATE rtmp_server_assignment SET tunnel_server_uid = :dst WHERE '.
					'tunnel_server_uid = :src LIMIT :limit');
		}
	}

	public function batchAssignBegin(&$device_uid, $url_prefix_list = false) {
		// Get stream server list
        $params = array('purpose'=>static::PURPOSE);
		$table = 'tunnel_server LEFT JOIN ' .
					'(SELECT tunnel_server_uid AS uid, COUNT(*) AS count FROM rtmp_server_assignment GROUP BY tunnel_server_uid) AS t_count ' .
					'ON tunnel_server.uid = t_count.uid AND tunnel_server.purpose = :purpose';
		$condition = 'tunnel_server.license_id IS NOT NULL AND tunnel_server.purpose = :purpose';
		$columns = 'tunnel_server.uid AS uid, t_count.count AS count';
		$order = 'ORDER BY count ASC';
		$this->rtmp_server_list = $this->getDB()->QueryRecordDataArray($table, $condition, $columns, $order, $params);
		
		// prepare PDO statement
		$sql = 'INSERT INTO rtmp_server_assignment (device_uid, tunnel_server_uid) ' .
			'VALUES (:device_uid, :tunnel_server_uid)';
		$this->batch_assign_stmt = $this->getDB()->db->prepare( $sql );
		if (!$this->batch_assign_stmt) {
			$err = $this->getDB()->db->errorInfo();
			throw new Exception($err[2]);
		}
		$this->batch_assign_stmt->bindParam(':device_uid', $device_uid, PDO::PARAM_STR,18);
		$this->batch_assign_stmt->bindParam(':tunnel_server_uid', $this->batch_assign_rtmp_server, PDO::PARAM_STR,18);
	}
	
	public function batchAssignOne() {
		if (!$this->rtmp_server_list) {
			throw new Exception('No more RTMP server available');
		}

		$this->batch_assign_rtmp_server = $this->rtmp_server_list[0]['uid'];
		if (!$this->batch_assign_stmt->execute()) {
			$err = $this->batch_assign_stmt->errorInfo();
			throw new Exception($err[2]);
		}
		else {
			$this->batchAssignUpdateServer();
		}
    }

	private function batchAssignUpdateServer() {
		$this->rtmp_server_list[0]['count']++;
		// partial sorting
		for ($i=0; $this->rtmp_server_list[$i]; $i++) {
			if ($this->rtmp_server_list[$i+1] && 
					$this->rtmp_server_list[$i]['count'] > $this->rtmp_server_list[$i+1]['count'] ) {
				$tmp = $this->rtmp_server_list[$i];
				$this->rtmp_server_list[$i] = $this->rtmp_server_list[$i+1];
				$this->rtmp_server_list[$i+1] = $tmp;
			}
			else {
				break;
			}
		}
	}
}

?>
