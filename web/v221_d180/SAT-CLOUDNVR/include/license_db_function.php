<?php
define('LICENSE_VALIDATE_FAILED', -1);
define('LICENSE_VALIDATE_NEW', 1);
define('LICENSE_VALIDATE_EXISTS', 2);
define('LICENSE_VALIDATE_UNNECESSARY', 3);
define('LICENSE_VALIDATE_OK', 4);

include_once( "memcached.php" );

class LicenseDBFunction extends BaseDBFunction
{
	// =========================================
	// Company
	// =========================================
	public function AddCompany($cid, $name, $description=NULL) {
		$dbi = new DbInsert($this->db, 'company');
		$dbi->setErrorMessage('Add company failed.');
		$dbi->add('cid', $cid);
		$dbi->add('name', $name);
		$dbi->add('description', $description);
		$dbi->execute();
	}

	public function GetCompanies() {
		$results = $this->QueryRecordDataArray('company', '1', '*', 'ORDER BY cid');
		if ($results) {
			return $results;
		}
		return array();
	}

	public function RemoveCompany($cid) {
		$results = $this->GetLicensesByCid($cid);
		$license_ids = array();
		foreach ($results as $result) {
			$license_ids[] = $result['id'];
		}
		$license_ids_string = implode(',', $license_ids);

		$dbh = $this->GetHelper();
		if ($license_ids_string) {
			$dbh->setErrorMessage('Remove series numbers of the company failed.');
			$license_ids_string = "($license_ids_string)";
			$sql = "DELETE FROM series_number WHERE license_id IN $license_ids_string";
			$dbh->execute($sql);

			$dbh->setErrorMessage('Remove licenses of the company failed.');
			$sql = "DELETE FROM license WHERE id IN $license_ids_string";
			$dbh->execute($sql);
		}

		$dbh->setErrorMessage('Remove company failed.');
		$sql = "DELETE FROM company WHERE cid=:cid";
		$dbh->add(':cid', $cid);
		$dbh->execute($sql);
	}

	public function UpdateCompany($cid, $params) {
		// Prepare set in sql
		$paramList = array('name', 'description');
		$sql_paramas = array(':cid'=>$cid);
		$set = '';
		$delim = 'SET';
		foreach ($paramList as $p) {
			$v = $params[$p];
			if ($v) {
				if (strtolower($v) == 'null') {
					$v = NULL;
				}

				$set .= "$delim $p=:$p";
				$sql_paramas[':'.$p] = $v;
				$delim = ',';
			}
		}
		if (!$set) {
			throw new Exception('Update company query failed.');
		}

		// Query
		$dbh = $this->GetHelper($sql_paramas);
		$dbh->setErrorMessage('Update company failed.');
		$sql = "UPDATE company $set WHERE cid=:cid";
		$dbh->execute($sql);
	}


	// =========================================
	// License
	// =========================================
	public function AddLicense($cid, $pid, $version='2-0-0-0-0') {
		$dbi = new DbInsert($this->db, 'license');
		$dbi->setErrorMessage('Add license failed.');
		$dbi->add('version', $version);
		$dbi->add('cid', $cid);
		$dbi->add('pid', $pid);
		$dbi->execute();
	}

	public function GetLicensesByCid($cid) {
		$conditions = '1';
		if ($cid) $conditions = "cid=?";

		$results = $this->QueryRecordDataArray('license', $conditions, '*', 'ORDER BY cid, pid', $cid);
		return $results;
	}

	public function GetLicenseByCidAndPid($cid, $pid) {
		$condition = "cid=:cid AND pid=:pid";
		$params = array(':cid'=>$cid, ':pid'=>$pid);
		$results = $this->QueryRecordDataOne('license', $condition, '*', $params);
		return $results;
	}

	public function GetLicenseByActivatedCode($activated_code) {
		$table = 'series_number LEFT JOIN license ON series_number.license_id = license.id';
		$condition = "series_number.activated_code = :activated_code";
		$results = $this->QueryRecordDataOne($table, $condition, '*', array(':activated_code'=>$activated_code));
		return $results;
	}

	public function RemoveLicense($license_id) {
		$dbh = $this->GetHelper($license_id);

		$dbh->setErrorMessage('Remove series numbers of the license failed');
		$sql = "DELETE FROM series_number WHERE license_id=?";
		$dbh->execute($sql);

		$dbh->setErrorMessage('Remove license failed');
		$sql = "DELETE FROM license WHERE id=?";
		$dbh->execute($sql);
	}


	// =========================================
	// Series Number
	// =========================================
	public function AddSeriesNumbers($license_id, $activated_codes, $macs = null) {
		$bind_mac = ($macs) ? 1 : 0;
		$dbi = new DbInsert($this->db, 'series_number');
		$dbi->setErrorMessage('Add series number failed.');
		$dbi->add('license_id',$license_id);
		$dbi->add('activated_code','');
		$dbi->add('mac', NULL);
		$dbi->add('bind_mac', $bind_mac);
		
		$dbi->batchBegin(false, false, true);
		foreach ($activated_codes as $i => $activated_code) {
			$dbi->batchUpdate('activated_code',$activated_code);
			if ($macs) {
				$dbi->batchUpdate('mac',$macs[$i]);
			}
			$dbi->batchExecuteOnce();
		}
		$dbi->batchFinalize();
	}

	public function GetSeriesNumbersByLicenseId($license_id) {
		$results = $this->QueryRecordDataArray('series_number', "license_id=?", '*', 'ORDER BY id', $license_id);
		if ($results) {
			return $results;
		}
		return array();
	}

	public function GetSeriesNumberByUid($uid) {
		$result = $this->QueryRecordDataOne('series_number', "uid=?", '*', $uid);
		if ($result === FALSE) {
			throw new Exception('Cannot find the uid in series number.');
		}
		return $result;
	}

	public function GetSeriesNumberByActivatedCode($activated_code) {
		$result = $this->QueryRecordDataOne('series_number', "activated_code=?", '*', $activated_code);
		if ($result === FALSE) {
			throw new Exception('Cannot find the activated_code in series number.');
		}
		return $result;
	}

	public function GetSeriesNumberByCidPidMac($cid, $pid, $mac) {
		$table = 'series_number LEFT JOIN license ON series_number.license_id = license.id';
		$params = array(':cid'=>$cid,':pid'=>$pid,':mac'=>$mac);
		$condition = "license.cid = :cid AND license.pid = :pid AND series_number.mac = :mac";
		$result = $this->QueryRecordDataOne($table, $condition, '*', $params);
		return $result;
	}

	public function RemoveSeriesNumber($series_number_id) {
		$this->beginTransaction($transaction);
		
		try {
			$dbh = new DbHelper($this->db);
			$dbh->add(':series_number_id', $series_number_id, PDO::PARAM_INT);

			// delete tunnel_server_assignment
			$sql = 'DELETE tunnel_server_assignment FROM '.
					'series_number LEFT JOIN license ON license.id = series_number.license_id '.
					'LEFT JOIN tunnel_server_assignment ON tunnel_server_assignment.device_uid = CONCAT(license.cid, license.pid, "-", series_number.mac) '.
					'WHERE series_number.id = :series_number_id AND tunnel_server_assignment.id IS NOT NULL';
			$dbh->execute($sql);
			
			// delete stream_server_assignment
			$sql = 'DELETE stream_server_assignment FROM '.
					'series_number LEFT JOIN license ON license.id = series_number.license_id '.
					'LEFT JOIN stream_server_assignment ON stream_server_assignment.device_uid = CONCAT(license.cid, license.pid, "-", series_number.mac) '.
					'WHERE series_number.id = :series_number_id AND stream_server_assignment.id IS NOT NULL';
			$dbh->execute($sql);

			// delete rtmp_server_assignment
			$sql = 'DELETE rtmp_server_assignment FROM '.
					'series_number LEFT JOIN license ON license.id = series_number.license_id '.
					'LEFT JOIN rtmp_server_assignment ON rtmp_server_assignment.device_uid = CONCAT(license.cid, license.pid, "-", series_number.mac) '.
					'WHERE series_number.id = :series_number_id AND rtmp_server_assignment.id IS NOT NULL';
			$dbh->execute($sql);
			
			// delete recording and events
			$sql = 'DELETE recording_list FROM '.
					'series_number LEFT JOIN license ON license.id = series_number.license_id '.
					'LEFT JOIN recording_list ON recording_list.device_uid = CONCAT(license.cid, license.pid, "-", series_number.mac) '.
					'WHERE series_number.id = :series_number_id AND recording_list.id IS NOT NULL';
			$dbh->execute($sql);
			$sql = 'DELETE cloud_event FROM '.
					'series_number LEFT JOIN license ON license.id = series_number.license_id '.
					'LEFT JOIN cloud_event ON cloud_event.device_uid = CONCAT(license.cid, license.pid, "-", series_number.mac) '.
					'WHERE series_number.id = :series_number_id AND cloud_event.id IS NOT NULL';
			$dbh->execute($sql);
			
			// delete series_number
			$sql = 'DELETE FROM series_number WHERE id=:series_number_id';
			$dbh->execute($sql);
			
			$this->commit($transaction);
		}
		catch (Exception $e) {
			$this->rollBack($transaction);
			throw $e;
		}
	}

	public function UpdateSeriesNumber($series_number_id, $params) {
		// Prepare set in sql
		$paramList = array('activated_code', 'sid', 'mac', 'uid', 'password', 'bind_mac');
		$sql_paramas = array(':series_number_id'=>$series_number_id);
		$set = '';
		$delim = 'SET';
		foreach ($paramList as $p) {
			$v = $params[$p];
			if ($v) {
				if (strtolower($v) == 'null') {
					$v = NULL;
				}

				$set .= "$delim $p=:$p";
				$sql_paramas[':'.$p] = $v;
				$delim = ',';
			}
		}
		if (!$set) {
			throw new Exception('Update series number query failed.');
		}

		// Query
		$dbh = $this->GetHelper($sql_paramas);
		$dbh->setErrorMessage('Update series number failed.');
		$sql = "UPDATE series_number $set WHERE id=:series_number_id";
		$dbh->execute($sql);
	}

	/**
	 * Directly add mac addresses into table series_number.
	 * @param $entries: [{sid:'%s', mac:'%s', uid:'%s', ...}, ...]
	 */
	public function AddAndActiveMacAddresses($license_id, &$entries) {
		if (count($entries) <= 0) {
			return;
		}

        // Get sns if ac is not specified.
        $hasAc = true;
        if (!isset($entries[0]['ac'])) {
            $hasAc = false;

            // Get available activation code
            $condition = "license_id=? AND uid is NULL";
            $limitation = 'ORDER BY id';
            $sns = $this->QueryRecordDataArray('series_number', $condition, '*', $limitation, $license_id);
            if ($sns === FALSE) {
                throw new Exception('Query activation code failed.');
            }

            $nSns = count($sns);
            $nEntries = count($entries);
            if ($nSns < $nEntries) {
                throw new Exception("Not enough activation code. Total: $nSns Need: $nEntries");
            }
        }

		// active activation code
		foreach ($entries as $i => $entry) {
			$activated_code = ($hasAc) ? $entry['ac'] : $sns[$i]['activated_code'];

			try {
				$this->ActiveActivatedCode($activated_code, $entry['sid'], $entry['mac'], $entry['uid']);
				$entries[$i]['activated'] = TRUE;
			} catch (Exception $e) {
				// Do nothing
			}
		}
	}


	// =========================================
	// Validation
	// =========================================
	// Release activated code
	public function ReleaseActivatedCodeByUid($uid) {
		$sn = $this->QueryRecordDataOne('series_number', "uid=?", '*', $uid);
		if ($sn === FALSE) {
			return NULL;
		}

		$dbh = $this->GetHelper($uid);

		// Remove those activated code is NULL.
		if ($sn['activated_code'] == NULL) {
			$dbh->setErrorMessage('Release activation code failed. (cannot remove)');
			$sql = "DELETE FROM series_number WHERE uid=? AND activated_code is NULL";
			$dbh->execute($sql);
		}
		// Clean those activated code is not NULL.
		else {
			$dbh->setErrorMessage('Release activation code failed. (cannot update)');
			if ($sn['bind_mac']) {
				$sql = "UPDATE series_number SET sid=NULL,uid=NULL,password=NULL,reg_date=NULL,update_date=NULL where uid=?";
			}
			else {
				$sql = "UPDATE series_number SET sid=NULL,mac=NULL,uid=NULL,password=NULL,reg_date=NULL,update_date=NULL where uid=?";
			}
			$dbh->execute($sql);
		}

		return $sn;
	}

	// Update a license by activated code
	public function ActiveActivatedCode($activated_code, $sid, $mac_addr, $uid) {
		$params = array(':sid'=>$sid,
				':mac_addr'=>$mac_addr,
				':uid'=>$uid,
				':activated_code'=>$activated_code );
		$sql="UPDATE series_number SET ".
				"sid=:sid, mac=:mac_addr, uid=:uid, reg_date=CURRENT_TIMESTAMP WHERE ".
				"activated_code=:activated_code AND reg_date is null";
		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage('Update series_number failed.');
		$dbh->execute($sql);
	}

	//Validate license
	public function ValidateLicense($ver, $mac_addr, $sid, $cid, $pid, $activated_code, $user) {
		// TODO a mac cannot has multiple activated_code

		$table = 'license';
		$columns_to_get = '*, UNIX_TIMESTAMP(gen_date) AS gen_date_ts';
		$condition = '1';
		$params = array();

		// If <pid>[1] is not 'C'.
		// Only need to check the license (<cid><pid>) exists or not.
		if ($pid[1] != 'C') { 
			$condition = "cid=:cid AND pid=:pid";
			$params[':cid'] = $cid;
			$params[':pid'] = $pid;
		}
		// If activation code exists, need to check series_number join license
		else if ($activated_code != "") {
			$table = 'series_number LEFT JOIN license ON series_number.license_id = license.id';
			$columns_to_get = "mac, sid, cid, pid, activated_code, UNIX_TIMESTAMP(gen_date) AS gen_date_ts";
			$condition = "cid=:cid AND pid=:pid AND activated_code=:activated_code";
			$params[':cid'] = $cid;
			$params[':pid'] = $pid;
			$params[':activated_code'] = $activated_code;
		}
		// If activation does not exists, we assume it has existed in series_number.
		// Hence, check series_number join license
		else if ($user != null){
			$table = 'series_number LEFT JOIN license ON series_number.license_id = license.id';
			$columns_to_get = "mac, sid, cid, pid, UNIX_TIMESTAMP(gen_date) AS gen_date_ts";
			$condition = "cid=:cid AND pid=:pid AND mac=:mac_addr AND sid=:sid";
			$params[':cid'] = $cid;
			$params[':pid'] = $pid;
			$params[':mac_addr'] = $mac_addr;
			$params[':sid'] = $sid;
		}
		/////////
		// For those device register without user information
		else{
			// Use shared memory
			$cache_data = GetMemcacheArray( $mac_addr, $memcache );
            if( isset($cache_data) && isset($cache_data["is_activated"]) )
			{
				if( $cache_data["is_activated"] === 1 ) return LICENSE_VALIDATE_EXISTS;
				else return LICENSE_VALIDATE_OK;
			}
            else
            {
				// First of all, check whether the license is activated.
				$ret_code = LICENSE_VALIDATE_OK;
				$table = 'series_number LEFT JOIN license ON series_number.license_id = license.id';
				$condition = "cid=:cid AND pid=:pid AND mac=:mac_addr AND sid=:sid";
				$params[':cid'] = $cid;
				$params[':pid'] = $pid;
				$params[':mac_addr'] = $mac_addr;
				$params[':sid'] = $sid;
				try {
					$results = $this->QueryRecordDataOne( $table, $condition, $columns_to_get, $params );
					if( !$results )
					{
						$cache_data["is_activated"] = 0;
						$ret_code = LICENSE_VALIDATE_OK;
					}
					else
					{
						$cache_data["is_activated"] = 1;
						$ret_code = LICENSE_VALIDATE_EXISTS;
					}

					// store modified cache
					$memcache->set( $mac_addr, $cache_data );
				}
				catch( Exception $e ){
					$ret_code = LICENSE_VALIDATE_OK;
				}

				return $ret_code;
            }
		}
		/////////

		$results = $this->QueryRecordDataOne($table, $condition, $columns_to_get, $params);
		if (!$results) {
			// License does not exist
			return LICENSE_VALIDATE_FAILED;
		}

		// TODO Check license limitation (expired date)
		if ($results['expire_date'] && time() > $results['expire_date']) {
			return LICENSE_VALIDATE_FAILED;
		}

		// No need to check series_number
		if ($pid[1] != 'C') {
			return LICENSE_VALIDATE_OK;
		}
		// Find an unactivated license
		else if ($activated_code != "" && 
				(($results["mac"] == "" && $results["sid"] == "") ||
				 ($results['mac'] && $results['sid'] == '' && $results['mac'] == $mac_addr))) {
			return LICENSE_VALIDATE_NEW;
		}
		// The license is existed and the device is matched.
		else if ($results["mac"] == $mac_addr && $results["sid"] == $sid) {
			return LICENSE_VALIDATE_EXISTS;
		}

		else {
			return LICENSE_VALIDATE_FAILED;
		}
	}

	public function UpdateSeriesNumberAndGetPassword($mac_addr, $sid, $uid, $cid, $pid, $activated_code='') {
		$password = $this->GeneratePassword(8);

		$params = array();
		if ($activated_code != "") {
			$sql="UPDATE series_number JOIN license ON series_number.license_id=license.id SET " .
					"mac=:mac_addr, sid=:sid, uid=:uid, password=:password " .
					"WHERE cid=:cid AND pid=:pid AND activated_code=:activated_code";
			$params[':mac_addr'] = $mac_addr;
			$params[':sid'] = $sid;
			$params[':uid'] = $uid;
			$params[':password'] = $password;
			$params[':cid'] = $cid;
			$params[':pid'] = $pid;
			$params[':activated_code'] = $activated_code;
		}
		else {
			$sql="UPDATE series_number SET password=:password WHERE uid=:uid";
			$params[':uid'] = $uid;
			$params[':password'] = $password;
		}

		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage('Update series_number failed.');
		$dbh->execute($sql);

		return $password;
	}

	// TODO: maintain hashkey database.
	public static function GetHashKey($key_index) {
		return 'qwertyui';
	}


	// =========================================
	// Log
	// =========================================

	public function InsertSeriesNumberLog($event, $sn) {
		$dbi = new DbInsert($this->db, 'series_number_log');
		$params = $sn;
		$params['event'] = $event;
		$dbi->addAll($params);
		$dbi->setErrorMessage('Insert log failed');
		$dbi->execute();
	}

	public function GetSeriesNumberLogCount($license_id, $year=NULL, $month=NULL) {
		$table = 'series_number_log';

		$condition = "license_id=:license_id";
		$params = array(':license_id' => $license_id);
		if ($year) {
			$condition .= " AND YEAR(ts)=:year";
			$params[':year'] = $year;
		}
		if ($month) {
			$condition .= " AND MONTH(ts)=:month";
			$params[':month'] = $month;
		}

		$result = $this->QueryRecordNo($table, $condition, $params);
		return $result;
	}

	public function GetSeriesNumberLog($license_id, $year=NULL, $month=NULL, $pageNo=-1, $nRecordsPerPage=30) {
		$table = 'series_number_log';

		$condition = "license_id=:license_id";
		$params = array(':license_id' => $license_id);
		if ($year) {
			$condition .= " AND YEAR(ts)=:year";
			$params[':year'] = $year;
		}
		if ($month) {
			$condition .= " AND MONTH(ts)=:month";
			$params[':month'] = $month;
		}

		$limitation = "ORDER BY ts DESC";
		if ($pageNo > 0) {
			$pageNo = $pageNo - 1;
			$skip = $pageNo * $nRecordsPerPage;
			$limitation .= " LIMIT :skip, :nRecordsPerPage";
			$params[':skip'] = $skip;
			$params[':nRecordsPerPage'] = $nRecordsPerPage;
		}

		$results = $this->QueryRecordDataArray($table, $condition, '*', $limitation, $params);
		return $results;
	}



	////////////////////////////////////////////////////////////////////
	// private function
	////////////////////////////////////////////////////////////////////
	protected function CheckAndCreateDB()
	{
		parent::GetDBLink( $this, LICENSE_DB_TYPE, LICENSE_DB_HOST, LICENSE_DB_NAME,
			LICENSE_DB_USERNAME, LICENSE_DB_PASSWORD );
	}

	protected function GeneratePassword($n)
	{
		$charlist = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		$n_charlist = strlen($charlist);

		$password = '';
		for ($i = 0; $i < $n; ++$i) {
			$ran = rand() % $n_charlist;
			$password .= substr($charlist, $ran, 1);  
		}
		return $password;
	}
}
?>
