<?php
include_once( "memcached.php" );
include_once('db_helper.php');
include_once('db_insert.php');
include_once('config/uid.php');

class BaseDBFunction
{
	////////////////////////////////////////////////////////////////////
	// public property
	////////////////////////////////////////////////////////////////////
	public $db = NULL;
	
	////////////////////////////////////////////////////////////////////
	// protected static property
	////////////////////////////////////////////////////////////////////
	protected static $db_array = array();
	
	////////////////////////////////////////////////////////////////////
	// protected static function for storing/searching db link
	////////////////////////////////////////////////////////////////////	
	protected static function GetDBLink( &$db_ptr, $db_type, $db_host, $db_name, $db_username, $db_password )
	{
		// reset db to null
		$db_ptr->db = NULL;
		
		// combine all info into key
		$db_key = $db_type . ':host=' . $db_host . ';dbname=' . $db_name;
		
		// check if exist, if not, create one
		if( !array_key_exists($db_key, self::$db_array) ) {
			$attributes = Array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES utf8');
			self::$db_array[$db_key] = new PDO($db_key, $db_username, $db_password,
											   $attributes);
		}
		// set the active db link
		$db_ptr->db = &self::$db_array[$db_key];
	}
	
	////////////////////////////////////////////////////////////////////
	// public function
	////////////////////////////////////////////////////////////////////
	// constructor
	public function __construct()
	{		
		try {
			$this->CheckAndCreateDB();
			if( $this->db != NULL )
			{
				$last_time_remove_expired_data = GetMemcacheTypeSafe(
						'last_time_remove_expired_data', $memcache, 'is_numeric' );
				$current_timestamp = time();
				if( !isset($last_time_remove_expired_data) || ($current_timestamp - $last_time_remove_expired_data) > 600 )
				{
					$this->RemoveExpiredData();
					$memcache->set( "last_time_remove_expired_data", $current_timestamp );
				}
			}
		}
		catch( PDOException $e ) {
			throw new Exception("Database loaded failed.");
		}
	}

	// common query functions
	public function QueryRecordNo( $table, $condition="1", $params = false )
	{		
		// check if db opened
		if( $this->db == NULL ) return 0;

		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage('Database query failed.');
		
		// do query
		$sql = "SELECT count(*) as TOTAL_COUNT FROM " . $table . " WHERE " . $condition;
		
		//echo $sql . "\n";
		
		$result = $dbh->run($sql);
	
		// check result false
		if( $result === FALSE ) return 0;
	
		// get one row
		$row = $result->fetch(PDO::FETCH_ASSOC);
		if( $row === FALSE ) return 0;

		// get real record no
		return intval( $row["TOTAL_COUNT"] );
	}
	
	/**** FOR DEBUG , RETURN SQL *****/
	public function GETSQL( $table, $condition="1", $columns_to_get="*", $limitation="" )
	{
		$sql = "";
		//$sql = "SELECT " . $columns_to_get . " FROM " . $table . " WHERE " . $condition . " " . $limitation;
		return $sql;
	}
	/**** FOR DEBUG , RETURN SQL *****/
	
	public function QueryRecordDataArray( $table, $condition="1", $columns_to_get="*", $limitation="", $params = false )
	{
		$sql = "SELECT " . $columns_to_get . " FROM " . $table . " WHERE " . $condition . " " . $limitation;
		
		// execute sql
		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage('Database query failed.');
		$stmt = $dbh->run($sql);
	
		// get result
		$result_array = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// check if result is ok
		if( $result_array === FALSE ) return array();
		
		return $result_array;
	}
	
	public function QueryRecordDataOne( $table, $condition="1", $columns_to_get="*", $params = false )
	{
		$sql = "SELECT " . $columns_to_get . " FROM " . $table . " WHERE " . $condition . " LIMIT 1";
		
		//echo $sql . "\n";
		
		// execute sql
		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage('Database query failed.');
		$stmt = $dbh->run($sql);

		// get result
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function DeleteRecordData( $table, $condition="1", $params = false)
	{
		$sql = "DELETE FROM " . $table . " WHERE " . $condition;
		$errorMessage = 'Remove failed.';		
		return $this->executeSql($sql, $params, $errorMessage);
	}
	
	public function beginTransaction(&$handle) {
		// Do not start transaction if already started
		$handle['inTransaction'] = !$this->db->inTransaction();
		if ($handle['inTransaction']) {
			$this->db->beginTransaction();
		}
	}

	public function commit(&$handle) {
		if ($handle['inTransaction']) {
			$this->db->commit();
			$handle['inTransaction'] = false;
		}
	}
		
	public function rollBack(&$handle) {
		if ($handle['inTransaction']) {
			$this->db->rollBack();
			$handle['inTransaction'] = false;
		}
	}

	public function GetHelper($params = false) {
		$dbh = new DbHelper($this->db);
		
		if (is_array($params)) {
			$dbh->addAll($params);
		}
		else if ($params !== false) {
			$dbh->add(1, $params);
		}
		
		return $dbh;
	}

	public function executeSql($sql, $params = false, $errorMessage = false) {
		$dbh = $this->GetHelper($params);
		if ($errorMessage) {
			$dbh->setErrorMessage($errorMessage);
		}
		return $dbh->execute($sql);
	}

	////////////////////////////////////////////////////////////////////
	// private function
	////////////////////////////////////////////////////////////////////		
	protected function CheckAndCreateDB() {}
	protected function RemoveExpiredData() {}
}

class DataDBFunction extends BaseDBFunction
{
	////////////////////////////////////////////////////////////////////
	// public function
	////////////////////////////////////////////////////////////////////
	public function InsertUser($caller_group_id, // user group to determine right
		$user_reg_id, // need this to ignore check user_reg name duplicate
		$group_id, $name, $pwd_hash, $reg_email, // required data
		$matrix_display_mode=3, $reg_date="", $expire_date="", $oem_id="" ) // optional data
	{
		// check name exist in user table
		if( $this->CheckUsernameDuplicate($name, "", $user_reg_id) ) throw new Exception( _("The user name is duplicated.") );

		// for non admin (only allow add normal user)
		if( $caller_group_id != ADMIN_GROUP_ID )
		{
			$group_id = 1;
		}

		if ($reg_date == "" || $expire_date == "" )
		{
			// set reg_date to now
			$reg_date = time();
			
			// expire_date to now + 60 days
			//$expire_date = $reg_date + 86400*60;
	
			// expire_date to now + 10 years
			$expire_date = mktime(date("H"), date("i"), date("s"), date("m") , date("d"), date("Y")+10);
		}
			
		$reg_email = strtolower($reg_email);
		$oem_id = $oem_id ? $oem_id : NULL;

		// prepare sql
		$dbi = new DbInsert($this->db, 'user');
		$dbi->setErrorMessage("Add user failed.");
		$dbi->add('group_id', $group_id);
		$dbi->add('name', $name);
		$dbi->add('pwd_hash', $pwd_hash);
		$dbi->add('reg_email', $reg_email);
		$dbi->add('reg_date', $reg_date);
		$dbi->add('login_date', 'unix_timestamp(now())', DbHelper::PARAM_MISC);
		$dbi->add('expire_date', $expire_date);
		$dbi->add('login_count', 0);
		$dbi->add('matrix_display_mode', $matrix_display_mode);
		$dbi->add('oem_id', $oem_id);
		$dbi->execute();
	
		// declare all the devices belong to this user
		$last_insert_user_id = $this->db->lastInsertId();
		$auto_add_params = array(':last_insert_user_id'=>$last_insert_user_id, ':name'=>$name);
		$auto_add_sql = "INSERT INTO device (name, owner_id, mac_addr, " . 
			"identifier, service_type, ip_addr, " . 
			"port, reg_date, update_date, " . 
			"expire_date, query_geo_locate, geo_locate_lat, " . 
			"geo_locate_lng, public_status, internal_ip_addr, " .
			"internal_port) " .
			"SELECT name, :last_insert_user_id, mac_addr, " .
			"identifier, 'WEB', ip_addr, " .
			"port, unix_timestamp(now()), unix_timestamp(now()), " .
			"unix_timestamp(date_add(now(),interval 1 year)), 1, 0, " .
			"0, 0, internal_ip_addr, " .
			"internal_port FROM device_reg WHERE owner_name=:name";
		$auto_add_errorMessage = "Auto add device failed.";
		
		$delete_reg_sql = "DELETE FROM device_reg WHERE owner_name=?";
		$delete_reg_errorMessage = "Delete device_reg records failed.";
		
		$this->executeSql($auto_add_sql, $auto_add_params, $auto_add_errorMessage);
		$this->executeSql($delete_reg_sql, $name, $delete_reg_errorMessage);

		return $last_insert_user_id;
	}

	public function ModifyUser($caller_group_id, // user group to determine right
		$id, $group_id, $name, $pwd, $reg_email, $reg_date, $expire_date, $matrix_display_mode = NULL )
	{
		include_once( "./include/password.php" );

		$params = array( ':pwd_hash'=>password_hash($pwd, PASSWORD_DEFAULT),
				':id'=>$id);

		// update sql command prefix		
		$sql = "UPDATE user SET ";
		//echo $caller_group_id."\n";
		//echo ADMIN_GROUP_ID."\n";
		// only admin can modify the following fields
		if( $caller_group_id == ADMIN_GROUP_ID )
		{
			if( $group_id != "" ) {
				$sql .= "group_id=:group_id,";
				$params[':group_id'] = $group_id;
			}
			if( $reg_date != "" ) {
				$sql .= "reg_date=:reg_date,";
				$params[':reg_date'] = $reg_date;
			}
			if( $expire_date != "" ) {
				$sql .= "expire_date=:expire_date,";
				$params[':expire_date'] = $expire_date;
			}
			if( $name != "" ) {
				// check name exist in user table
				if( $this->CheckUsernameDuplicate($name, $id) ) {
					throw new Exception( _("The user name is duplicated.") );
				}
				$sql .= "name=:name,";
				$params[':name'] = $name;
			}
			if( $reg_email != "" ) {
				$sql .= "reg_email=:reg_email,";
				$params[':reg_email'] = strtolower($reg_email);
			}
		}

		if ($matrix_display_mode !== NULL) {
			$params[':matrix_display_mode'] = $matrix_display_mode;
			$sql .= "matrix_display_mode=:matrix_display_mode,";
		}

		// common modifiable fields
		$sql .= "pwd_hash=:pwd_hash " .
				"WHERE id=:id";
		$errorMessage = "Modify user failed.";

		// execute sql
		$this->executeSql($sql, $params, $errorMessage);
		
		// update success --> update some session vars
		global $_SESSION;
		if( $_SESSION["user_id"] == $id ) $_SESSION["user_matrix_display_mode"] = $matrix_display_mode;
	}

	public function GetUserByName($name) {
		$table = 'user';
		$condition = "name=?";
		return $this->QueryRecordDataOne($table, $condition, '*', $name);
	}
	
	public function UpdateUserGoogleOpenID( $name , $open_id )
	{
		if( empty($open_id) ) {
			throw new Exception( "open_id is empty." );
		}

		$sql = "UPDATE user SET google_openid=:open_id where name=:name";
		$params = array(':open_id'=>$open_id, ':name'=>$name);
		$errorMessage = "Update UserGoogleOpenID Fail";
		
		$this->executeSql($sql, $params, $errorMessage);
	}
	
	public function UpdateAllUserExpireDate( $datetime )
	{
		$this->executeSql( "UPDATE user SET expire_date=?", $datetime );
	}
	
	public function UpdateUserLoginInfo( $id )
	{
		$this->executeSql( "UPDATE user SET login_date=unix_timestamp(now()), login_count=login_count+1 WHERE id=?", $id );
	}	
	
	
	
	public function UpdateNail( $device_id , $nail_page , $nail_position ,$isfavorite, $user_id)
	{
		$tablename = "position";
		if ($isfavorite == "true") $tablename = "myfavorite";
		
		$params = array( ':nail_page'=>$nail_page, 
				':nail_position'=>$nail_position,
				':user_id'=>$user_id,
				':device_id'=>$device_id );
		$sql = "UPDATE {$tablename} SET nail_page=:nail_page, nail_position=:nail_position WHERE " .
				"owner_id=:user_id and device_id=:device_id";
		$errorMessage = "Update Nail Fail";
	
		$this->executeSql($sql, $params, $errorMessage);
	}
	
	public function GetMaxNailPage( $id , $isfavorite )
	{
		if ($id == NULL ) return 0;

		if ($isfavorite == "true")
			$table = 'myfavorite';
		else
			$table = 'position';
		$columns = 'max(nail_page) as MAX_NAIL';
		$condition = 'owner_id = ?';
		$row = $this->QueryRecordDataOne($table, $condition, $columns, $id);
		if( $row === FALSE ) return 0;

		// get real max facovite id
		return intval( $row["MAX_NAIL"] );
	}
	
	
	public function UpdateDeviceAutoLocate( $id , $isAuto )
	{		
		$sql = 'UPDATE device SET query_geo_locate=:isAuto  WHERE id=:id';
		$params = array(':isAuto'=>$isAuto, ':id'=>$id);
		$this->executeSql($sql, $params);
	}
	
	public function UpdateDevicePublicStatus( $id , $isPublic )
	{		
		$sql = 'UPDATE device SET public_status=:isPublic WHERE id=:id';
		$params = array(':isPublic'=>$isPublic, ':id'=>$id);
		$this->executeSql($sql, $params);
	}
	
	public function AddRemoveSeq( $isadd, $isfavorite, $user_id, $device_id )
	{	
		$sql = "";
		$table = "position";
		if ($isfavorite == "true" || $isfavorite === true) $table = "myfavorite";		
		if ($isadd == "true" || $isadd === true) {
			$dbi = new DbInsert($this->db, $table);
			$dbi->add('owner_id', $user_id);
			$dbi->add('device_id', $device_id);
			$dbi->insertOrReplace();
		}
		else {
			if ($user_id == -1) {
				$this->DeleteRecordData($table, 'device_id = ?', $device_id);
			}
			else {
				$params = array(':owner_id'=>$user_id, ':device_id' => $device_id);
				$this->DeleteRecordData($table, 'device_id = :device_id AND owner_id = :owner_id', $params);
			}
		}
	}
	
	public function UpdateSequence( $device_id , $sequential , $isfavorite ,$user_id)
	{
		$tablename = "position";
		if ($isfavorite=="true") $tablename = "myfavorite";
		
		$params = array(':sequential'=>$sequential, ':user_id'=>$user_id, ':device_id'=>$device_id);
		$sql = "UPDATE {$tablename} SET seq=:sequential WHERE owner_id=:user_id and device_id=:device_id";
		$errorMessage = "Update Sequence Fail";

		$this->executeSql($sql, $params, $errorMessage);
	}
	
	public function InsertUserReg( $name, $pwd, $reg_email, $auth_code, $oem_id='' )
	{
		// check name exist in user table
		if( $this->CheckUsernameDuplicate($name) ) throw new Exception( _("The user name is duplicated.") );

		$reg_email = strtolower($reg_email);
		if (!$oem_id) {
			$oem_id = null;
		}

		$dbi = new DbInsert($this->db, 'user_reg');
		$dbi->setErrorMessage("Add user into user_reg failed.");
		$dbi->add('name', $name);
		$dbi->add('pwd_hash', password_hash($pwd, PASSWORD_DEFAULT));
		$dbi->add('reg_email', $reg_email);
		$dbi->add('authentication_code', $auth_code);
		$dbi->add('reg_date', 'unix_timestamp(now())', DbHelper::PARAM_MISC);
		$dbi->add('oem_id', $oem_id);
		$dbi->execute();
	}

	public function DeleteDeviceByCondition($condition, $params) {
		$devices = $this->QueryRecordDataArray('device', $condition, 'DISTINCT uid', '', $params);
		$this->beginTransaction($transaction);
		try {
			foreach ($devices as $device) {
				$this->DeleteDevicesByUid($device['uid']);
			}
			$this->commit($transaction);
		}
		catch (Exception $e) {
			$this->rollBack($transaction);
			throw($e);
		}
	}

	public function DeleteDevicesByCondition($condition, $params) {
		// Get device information.
		$device = $this->QueryRecordDataOne('device', $condition, 'uid', $params);
		if ($device === false) return NULL;

		return $this->DeleteDevicesByUid($device['uid']);
	}

	public function DeleteDevicesByUid($uid) {
		// Delete all devices with the same uid.
		$params = array(':uid'=>$uid);
		
		$this->beginTransaction($transaction);
		
		try {
			$condition = "uid=:uid";
			$this->DeleteRecordData('device', $condition, $params);

			// 'position' will be removed on delete cascade of device (id).

			// 'device_link' will be removed on delete cascade of series_number (uid).
			// But error on delete set null.
			$link_condition = "linker = :uid OR linked = :uid";
			$this->DeleteRecordData('device_link', $link_condition, $params);

			$share_condition = "uid = :uid";
			$this->DeleteRecordData('device_share', $share_condition, $params);

			// Release activated code.
			$license_db = new LicenseDBFunction();
			$sn = $license_db->ReleaseActivatedCodeByUid($uid);
		}
		catch (Exception $e) {
			$this->rollBack($transaction);
			throw $e;
		}
		
		$this->commit($transaction);
		
		// del cache
		$mac = substr($uid, 6);
		$memcache = GetMemcacheInstance();
		$memcache->delete($mac);

		return $sn;
	}
	
	public function DeleteDeviceReg($id){
		$condition = 'id=?';
		$this->DeleteRecordData('device_reg', $condition, $id);
	}

	public function InsertDevice( $uid, $dname, $owner_id, $mac_addr, $identifier, $service_type,
			$device_type, $url_prefix, $ip_addr, $port, $url_path,
			$internal_ip_addr, $internal_port, $enc, $version,
			$query_geo_locate=1, $geo_locate_lat=0, $geo_locate_lng=0, $public_status=0, $auth='', $model_id=0 , $purpose='')
	{
		// XXX
		if ($enc == 'null') $enc = '';

		// prepare sql command
		$dbi = new DbInsert($this->db, 'device');
		$dbi->setErrorMessage("Insert device failed.");
		$dbi->add('uid', $uid);
		$dbi->add('name', $dname);
		$dbi->add('owner_id', $owner_id);
		$dbi->add('mac_addr', $mac_addr);
		$dbi->add('identifier', $identifier);
		$dbi->add('service_type', $service_type);
		$dbi->add('device_type', $device_type);
		$dbi->add('model_id', $model_id);
		$dbi->add('purpose', $purpose);
		$dbi->add('url_prefix', $url_prefix);
		$dbi->add('ip_addr', $ip_addr);
		$dbi->add('port', $port);
		$dbi->add('url_path', $url_path);
		$dbi->add('reg_date', 'unix_timestamp(now())',  DbInsert::PARAM_MISC);
		$dbi->add('update_date', 'unix_timestamp(now())',  DbInsert::PARAM_MISC);
		$dbi->add('expire_date', 'unix_timestamp(date_add(now(),interval 1 year))',  DbInsert::PARAM_MISC);
		$dbi->add('query_geo_locate', $query_geo_locate);
		$dbi->add('geo_locate_lat', $geo_locate_lat);
		$dbi->add('geo_locate_lng', $geo_locate_lng);
		$dbi->add('public_status', $public_status);
		$dbi->add('internal_ip_addr', $internal_ip_addr);
		$dbi->add('internal_port', $internal_port);
		$dbi->add('enc', $enc);
		$dbi->add('version', $version);
		$dbi->add('auth', $auth);
		$dbi->execute();
	}
	
	public function UpdateDevice( $id, $url_prefix, $ip_addr, $port, $url_path, $owner_id, $internal_ip_addr, $internal_port, $enc , $service_type, $model_id=0, $purpose='')
	{
		$params = array(':ip_addr'=>$ip_addr,
			':port'=>$port,
			':owner_id'=>$owner_id,
			':service_type'=>$service_type,
			':model_id'=>$model_id,
			':id'=>$id);

		// prepare sql
		$sql = "UPDATE device SET ";
	
		// check if need to update these optional args
		if( $purpose != "" ) {
			$sql .= "purpose=:purpose,";
			$params[':purpose'] = $purpose;
		}
		if( $url_prefix != "" ) {
			$sql .= "url_prefix=:url_prefix,";
			$params[':url_prefix'] = $url_prefix;
		}
		if( $url_path != "" ) {
			$sql .= "url_path=:url_path,";
			$params[':url_path'] = $url_path;
		}
		if( $internal_ip_addr != "" ) {
			$sql .= "internal_ip_addr=:internal_ip_addr,";
			$params[':internal_ip_addr'] = $internal_ip_addr;
		}
		if( $internal_port != "" ) {
			$sql .= "internal_port=:internal_port,";
			$params[':internal_port'] = $internal_port;
		}
		if( $enc != "" ) {
			// XXX
			if ($enc == 'null') {
				$sql .= "enc='',";
			}
			else {
				$sql .= "enc=:enc,";
				$params[':enc'] = $enc;
			}
		}

		// add the rest
		$sql .= "ip_addr=:ip_addr," .
			"port=:port," .
			"owner_id=:owner_id," .
			"update_date=unix_timestamp(now())," .
			"service_type=:service_type," . 
			"model_id=:model_id " .
			"WHERE id=:id";
		$errorMessage = "Update device failed.";

		$this->executeSql($sql, $params, $errorMessage);
	}

	public function InsertDevices(&$entries, $seeds) {
		if (count($entries) <= 0) {
			return;
		}

		$subsql = '';
		$delim = '';
		
		$dbi = new DbInsert($this->db, 'device');
		$dbi->add('reg_date', 'unix_timestamp(now())', DbHelper::PARAM_MISC);
		$dbi->add('update_date', 'unix_timestamp(now())', DbHelper::PARAM_MISC);
		$dbi->add('expire_date', 'unix_timestamp(date_add(now(),interval 1 year))', DbHelper::PARAM_MISC);
		$dbi->add('service_type', 'null');
		$dbi->add('device_type', 'p2p');
		$dbi->add('version', '');
		$dbi->add('auth', 'username-free');
		$dbi->add('uid', '');
		$dbi->add('owner_id', '');
		$dbi->add('mac_addr', '');
		$dbi->add('name', '');
		$dbi->add('purpose', '');

		$dbi->batchBegin(false, false, true);
		foreach ($entries as $entry) {
			if ($entry['activated'] == FALSE) {
				continue;
			}
			$dbi->batchUpdate('uid', $entry['uid']);
			$dbi->batchUpdate('owner_id', $entry['owner_id']);
			$dbi->batchUpdate('mac_addr', $entry['mac']);
		
			foreach ($seeds as $seed) {
				$dbi->batchUpdate('name', $seed['name']);
				$dbi->batchUpdate('purpose', $seed['name']);
				$dbi->batchExecuteOnce();
			}
		}
		$dbi->batchFinalize();
	}

	public function InsertDeviceReg( $uid, $dname, $mac_addr, $identifier, $service_type, $device_type,
			$url_prefix, $ip_addr, $port, $url_path, $owner_name, $internal_ip_addr, $internal_port, 
			$enc, $version, $model_id=0, $purpose='', $reg_ver ) {

		// prepare sql
		$dbi = new DbInsert($this->db, 'device_reg');
		$dbi->add('name', $dname);
		$dbi->add('mac_addr', $mac_addr);
		$dbi->add('identifier', $identifier);
		$dbi->add('service_type', $service_type);
		$dbi->add('device_type', $device_type);
		$dbi->add('model_id', $model_id);
		$dbi->add('purpose', $purpose);
		$dbi->add('url_prefix', $url_prefix);
		$dbi->add('ip_addr', $ip_addr);
		$dbi->add('port', $port);
		$dbi->add('url_path', $url_path);
		$dbi->add('reg_date', 'unix_timestamp(now())', DbHelper::PARAM_MISC);
		$dbi->add('owner_name', $owner_name);
		$dbi->add('internal_ip_addr', $internal_ip_addr);
		$dbi->add('internal_port', $internal_port);
		$dbi->add('enc', $enc);
		$dbi->add('uid', $uid);
		$dbi->add('version', $version);
		$dbi->add('reg_ver', $reg_ver);
		$dbi->add('region', $this->GetRegion());
		$dbi->setErrorMessage( "Insert device_reg failed." );
		$dbi->execute();
	}

	public function CheckEmailIsExistedInReg($email)
	{
		if( $this->QueryRecordNo("user_reg", "reg_email=?", strtolower($email)) > 0 ) return true;
		return false;
	}
	
	public function CheckEmailisDuplicate( $email )
	{
		if( $this->QueryRecordNo("user", "reg_email=?", strtolower($email)) > 0 ) return true;
		return false;
	}
	
	public function UpdateDeviceReg( $id, $dname, $url_prefix, $ip_addr, $port, $url_path, $owner_name, $internal_ip_addr, $internal_port, $enc, $model_id=0 , $purpose='', $reg_ver)
	{
		// prepare sql
		$params = array(':ip_addr'=>$ip_addr,
				':port'=>$port,
				':owner_name'=>$owner_name,
				':region'=>$this->GetRegion(),
				':reg_ver'=>$reg_ver,
				':id'=>$id);

		$sql = "UPDATE device_reg SET ";
		
		// check if need to update these optional args
		if( $model_id >=0) {
			$sql .= "model_id=:model_id,";
			$params[':model_id'] = $model_id;
		}
		if( $purpose != "" ) {
			$sql .= "purpose=:purpose,";
			$params[':purpose'] = $purpose;
		}
		if( $url_prefix != "" ) {
			$sql .= "url_prefix=:url_prefix,";
			$params[':url_prefix'] = $url_prefix;
		}
		if( $url_path != "" ) {
			$sql .= "url_path=:url_path,";
			$params[':url_path'] = $url_path;
		}
		if( $dname != "" ) {
			$sql .= "name=:dname,";
			$params[':dname'] = $dname;
		}
		if( $internal_ip_addr != "" ) {
			$sql .= "internal_ip_addr=:internal_ip_addr,";
			$params[':internal_ip_addr'] = $internal_ip_addr;
		}
		if( $internal_port != "" ) {
			$sql .= "internal_port=:internal_port,";
			$params[':internal_port'] = $internal_port;
		}
		if( $enc != "" ) {
			$sql .= "enc=:enc,";
			$params[':enc'] = $enc;
		}

		$sql .= "ip_addr=:ip_addr," .
			"port=:port," .
			"owner_name=:owner_name," .
			"region=:region," .
			"reg_ver=:reg_ver," .
			"reg_date=unix_timestamp(now()) " .
			"WHERE id=:id";
		$errorMessage = "Update device_reg failed.";
		
		$this->executeSql($sql, $params, $errorMessage);
	}

        public function UpdateGroupIdWithName($name, $group_id) {
                $sql = "UPDATE user SET group_id=:group_id WHERE name=:name";
                $params = array(':group_id'=>$group_id, ':name'=>$name);
                $errorMessage = "Update $name group id failed.";

                $dbh = $this->GetHelper($params);
                $dbh->setErrorMessage($errorMessage);

                $dbh->run($sql);
                //$this->executeSql($sql, $params, $errorMessage);
        }

        public function AddUserMetadata($id, $key, $value) {
		$sql = "INSERT INTO user_metadata (user_id, field, value) VALUES (:user_id, :field, :value)";
		$params = array(':user_id'=>$id, ':field'=>$key, ':value'=>$value);
		$errorMessage = "Add metadata failed.";

		$dbh = $this->GetHelper($params);
		$dbh->setErrorMessage($errorMessage);
		$dbh->run($sql);
        }

        public function updateUserMetadata($id, $key, $value) {
                $sql = "UPDATE user_metadata SET value=:value where user_id=:user_id AND field=:field";
                $params = array(':user_id'=>$id, ':field'=>$key, ':value'=>$value);
                $errorMessage = "Update metadata failed.";

                $dbh = $this->GetHelper($params);
                $dbh->setErrorMessage($errorMessage);
                $dbh->run($sql);
        }

        public function deleteUserMetadata($id, $key) {
                $sql = "DELETE FROM user_metadata WHERE user_id=:user_id AND field=:field";
                $params = array(':user_id'=>$id, ':field'=>$key);
                $errorMessage = "Delete metadata failed.";

                $dbh = $this->GetHelper($params);
                $dbh->setErrorMessage($errorMessage);
                $dbh->run($sql);
        }


	// XXX: for event server
	public function GetDeviceByUidWithConditions($uid, $url_prefix=NULL, $purpose=NULL) {
		$table = 'device JOIN user ON device.owner_id = user.id';
		$params = array();
		$conditions = "uid = :uid";
		$params[':uid'] = $uid;
		if ($url_prefix && $purpose ) {
			$conditions .= " AND (url_prefix = :url_prefix OR purpose = :purpose)";
			$params[':url_prefix'] = $url_prefix;
			$params[':purpose'] = $purpose;
		}
		else if ($purpose) {
			$conditions .= " AND purpose = :purpose";
			$params[':purpose'] = $purpose;
		}
		else if ($url_prefix) {
			$conditions .= " AND url_prefix = :url_prefix";
			$params[':url_prefix'] = $url_prefix;
		}
		$columns = '*, device.id AS device_id, device.name AS device_name, user.name AS user_name';

		return $this->QueryRecordDataOne($table, $conditions, $columns, $params);
	}

	public function GetModelID($manufacturer, $model, $version, $client_version = "", $default_id = "", $features = '' ) {
		if (!$manufacturer || !$model || !$version)
			return 0;

		// add cache by anry
		$condition = "manufacturer = :manufacturer AND model = :model AND version = :version";
		$params = array(':manufacturer'=>$manufacturer, ':model'=>$model, ':version'=>$version);
		$cache_key = "model_id_$manufacturer.$model.$version";

		if( $condition == "" ) return 0;
		$model_id = apc_fetch( $cache_key );
		if( $model_id === FALSE )
		{
			$result = $this->QueryRecordDataOne("device_models", $condition, "id", $params);
			if ($result) $model_id = $result["id"];
			else {
				$dbi = new DbInsert($this->db, 'device_models');
				$dbi->add('manufacturer',$manufacturer);
				$dbi->add('model',$model);
				$dbi->add('version',$version);
				$dbi->add('client_version',$client_version);
				$dbi->add('default_id',$default_id);
				$dbi->add('features',$features);
				
				if ( ! $dbi->insertOrIgnore() ) {
					$model_id = 0;
				}
				else {
					$model_id = $this->db->lastInsertId();
				}
			}
			if( $model_id != 0 ) apc_store( $cache_key, $model_id );
		}
		return $model_id;
	}

	public function GetRandomTunnelServer($owner_id = NULL) {
		// Select the region with the most cameras
		if ($owner_id !== NUll) {
			$region = $this->SelectRegionByOwner($owner_id);
		}
		else {
			$region = NULL;
		}
		
		// Select a tunnel server within this region
		$count = $this->GetTunnelServerCount($region);
		$selected = rand ( 0, $count-1);
		$conditions = $this->GetTunnelServerQuery($region);
		$tunnel_server = $this->QueryRecordDataArray('tunnel_server', $conditions, 
			'uid, external_address, external_port', "LIMIT 1 OFFSET $selected", $region);
		if ($tunnel_server)
			return $tunnel_server[0];
		else
			return null;
	}
	
	private function SelectRegionByOwner($owner_id) {
		/* Quiteria:
		 * 1. Region with most cameras ownd by this account.
		 * 2. If multiple regions has the same cameras,  
		 *    and one of them is in the same region as the viewer.
		 * 3. Random.
		 */
		
		$columns = 'COUNT(*) AS count, ts.region';
		$table = 'tunnel_server_assignment tsa LEFT JOIN device d'.
			' ON d.uid = tsa.device_uid AND d.url_prefix = tsa.url_prefix'.
			' LEFT JOIN tunnel_server ts ON ts.uid = tsa.tunnel_server_uid AND ts.purpose = "TUNNEL"';
		$condition = 'd.owner_id = ?';
		$limitation = 'GROUP BY ts.region ORDER BY count DESC';
		$rows = $this->QueryRecordDataArray($table, $condition, $columns, $limitation, $owner_id);
		if (!$rows) {
			return NULL;
		}
		
		$viewer_region = $this->GetRegion();
		$candidates = array();
		$max_cameras = $rows[0]['count'];
		foreach ($rows as $row) {
			if ($row['count'] < $max_cameras) {
				break;
			}
			else if ($row['region'] == $viewer_region) {
				// Quiteria 2
				return $row['region'];
			}
			else {
				$candidates[] = $row['region'];
			}
		}
		
		$selected = rand(0, count($candidates)-1);
		return $candidates[$selected];
	}
	
	private function GetTunnelServerCount($region = NULL) {
		// check cache
		$key = "tunnel-server-count-in-region-$region";
		if ( ($result = apc_fetch($key)) !== FALSE) {
			return $result;
		}
		
		$conditions = $this->GetTunnelServerQuery($region);
		$count = $this->QueryRecordNo('tunnel_server', $conditions, $region);
		
		// store in cache
		apc_store($key, $count, 60);
		
		return $count;
	}
	
	private function GetTunnelServerQuery($region = NULL) {
		$conditions = 'license_id IS NOT NULL AND external_address IS NOT NULL'.
			' AND external_port IS NOT NULL AND purpose = "TUNNEL"';
		if ($region !== NULL) {
			$conditions .= ' AND region = ?';
		}
		return $conditions;
	}

	////////////////////////////////////////////////////////////////////
	// private function
	////////////////////////////////////////////////////////////////////		
	protected function CheckAndCreateDB()
	{
		parent::GetDBLink( $this, SIGNAL_DB_TYPE, SIGNAL_DB_HOST, SIGNAL_DB_NAME,
			SIGNAL_DB_USERNAME, SIGNAL_DB_PASSWORD );
	}
	
	protected function RemoveExpiredData()
	{
		// remove expired device_reg
		// set valid time to be one hour
		$this->db->exec( "DELETE FROM device_reg WHERE (unix_timestamp(now())-reg_date)>3600;" );

		// remove expired user_reg
		// set valid time to be 48 hours
		$this->db->exec( "DELETE FROM user_reg WHERE (unix_timestamp(now())-reg_date)>172800;" );
		
		// remove expired password recovery
		// set valid time to be one hour
		$this->db->exec( "DELETE FROM password_recovery WHERE request_date < (NOW() - INTERVAL 1 DAY)" );
	}
	
	
	
	
	public function CheckUsernameDuplicate( $name, $user_id="", $user_reg_id="" )
	{
		// set condition
		$user_params = array();
		$user_condition = "name=:name";
		$user_params[':name'] = $name;
		$user_reg_condition = $user_condition;
		$user_reg_params = $user_params;
		
		// check if need to add id
		if( $user_id != "" ) {
			$user_condition .=  " AND id<>:id";
			$user_params[':id'] = $user_id;
		}
		if( $user_reg_id != "" ) {
			$user_reg_condition .=  " AND id<>:id" ;
			$user_reg_params[':id'] = $user_reg_id;
		}

		// do query
		if( $this->QueryRecordNo("user", $user_condition, $user_params) > 0 ||
				$this->QueryRecordNo("user_reg", $user_reg_condition, $user_reg_params) > 0 )
			return true;
			
		// default return not duplicate
		return false;
	}
	
	public function BoundGoogleWithUser( $user_id, $cloud_id="", $refresh_token="" )
	{
		//echo "UPDATE user SET cloud_id=$cloud_id , cloud_refresh_token=\"$refresh_token\" WHERE id=$user_id";
		$result = $this->db->exec("UPDATE user SET cloud_id=$cloud_id , cloud_refresh_token=\"$refresh_token\" WHERE id=$user_id");
		if( $result === FALSE ) throw new Exception( "Update User Data Failed" );
	}
	
	public function GetTunnelServer($uid) {
        $table = 'tunnel_server_assignment LEFT JOIN tunnel_server ' . 
                'ON tunnel_server.uid = tunnel_server_assignment.tunnel_server_uid AND ' .
                'tunnel_server.purpose = "TUNNEL"';
		$condition = "tunnel_server_assignment.device_uid = ?";
		$columns = 'tunnel_server.uid as uid, tunnel_server.external_address AS addr, tunnel_server.external_port AS port';
		return $this->QueryRecordDataOne($table, $condition, $columns, $uid);
	}
	
	public function GetRtmpServer($uid) {
        $table = 'rtmp_server_assignment LEFT JOIN tunnel_server ' . 
                'ON tunnel_server.uid = rtmp_server_assignment.tunnel_server_uid AND ' .
                'tunnel_server.purpose = "RTMPD"';
		$condition = "rtmp_server_assignment.device_uid = ?";
		$columns = 'tunnel_server.uid as uid, tunnel_server.external_address AS addr, tunnel_server.external_port AS port';
		return $this->QueryRecordDataOne($table, $condition, $columns, $uid);
	}
	
	public function GetRegion() {
		global $web_server_uid;
		if (!$web_server_uid) {
			return 0;
		}
		
		$cache_key = "region-$web_server_uid";
		$region = apc_fetch($cache_key);
		if ($region !== false) {
			return $region;
		}
		
		$row = $this->QueryRecordDataOne('web_server', 'uid = ?', 'region', $web_server_uid);
		if ($row) {
			$region = $row['region'];
		}
		else {
			$region = 0;
		}
		
		apc_store($cache_key, $region, 60);
		return $region;
	}
}
?>
