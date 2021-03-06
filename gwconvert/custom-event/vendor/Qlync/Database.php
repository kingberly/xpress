<?php

namespace Qlync;

use \PDO;

class Database {
    protected $host = 'localhost';
    protected $name = 'isat';
    protected $username = 'isat_root';
    protected $password = 'isat_password';

    protected static $connection = null;
    protected static $cache = array();

    public function __construct() {
        $svw = new Svw();
        $params = $svw->get_params();
        $this->host = $params['db_host'];
        $this->name = $params['db_name'];
        $this->username = $params['db_username'];
        $this->password = $params['db_password'];
    }

    public function get_db() {
        if (!self::$connection) {
            $db_key = 'mysql:host=' . $this->host . ';dbname=' . $this->name;
            self::$connection = new PDO( $db_key, $this->username, $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        }
        return self::$connection;
    }

    public function get_entry($mac_addr) {
        if (!array_key_exists('entry', self::$cache)) {
            $sql = 'SELECT sn.uid, ssa.stream_server_uid from series_number AS sn ' .
                'LEFT JOIN stream_server_assignment AS ssa ON sn.uid = ssa.device_uid ' .
                'WHERE sn.mac = ?';

            $stmt = $this->get_db()->prepare($sql);
            $stmt->bindValue(1, $mac_addr);
            $stmt->execute();
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$entry) {
                throw new DeviceNotFoundException('Device not found.');
            }
            if (!$entry['uid']) {
                throw new DeviceNotFoundException('Device not activated.');
            }
            self::$cache['entry'] = $entry;
        }
        return self::$cache['entry'];
    }

    public function create_recording($mac_addr, $start, $end, $path) {
        $entry = $this->get_entry($mac_addr);

        $sql = 'INSERT INTO recording_list (stream_server_uid, start, end, path) ' .
               'VALUES (:ssu, :start, :end, :path)';
        $stmt = $this->get_db()->prepare($sql);
        $stmt->bindValue(':ssu', $entry['stream_server_uid']);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->bindValue(':path', $path);
        $stmt->execute();
        return $this->get_db()->lastInsertId();
    }

    public function create_event($mac_addr, $date, $recording_id) {
        $entry = $this->get_entry($mac_addr);

        $sql = 'INSERT INTO cloud_event (device_uid, date, recording_id) ' .
               'VALUES (:uid, :date, :recording_id)';
        $stmt = $this->get_db()->prepare($sql);
        $stmt->bindValue(':uid', $entry['uid']);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':recording_id', $recording_id);
        $stmt->execute();
        return $this->get_db()->lastInsertId();
    }
}

?>
