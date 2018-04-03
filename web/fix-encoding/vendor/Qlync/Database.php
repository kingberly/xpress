<?php

namespace Qlync;

use \PDO;

class Database {
    protected $host = 'localhost';
    protected $name = 'isat';
    protected $username = 'isat_root';
    protected $password = 'isat_password';
    protected $connection = null;

    public function __construct($setNames=false) {
        $config = Config::get();
        $this->host = $config['db']['host'];
        $this->name = $config['db']['name'];
        $this->username = $config['db']['user'];
        $this->password = $config['db']['pass'];
        $this->setNames = $setNames;
    }

    public function get_db() {
        if (!$this->connection) {
            $db_key = 'mysql:host=' . $this->host . ';dbname=' . $this->name;
            $attributes = Array(PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION);
            if ($this->setNames) {
                $attributes[PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';
            }

            $this->connection = new PDO($db_key, $this->username, $this->password,
                                        $attributes);
        }
        return $this->connection;
    }

}

?>
