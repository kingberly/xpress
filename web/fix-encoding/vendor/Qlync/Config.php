<?php

namespace Qlync;

class Config {
    protected static $config = Array(
        'db'=> Array(
            'host'=> '192.168.1.140',
            'port'=> 3306,
            'user'=> 'isatRoot',
            'pass'=> 'isatPassword',
            'name'=> 'isat',
        ),
        'tables'=> Array(
            'device'=>Array(),
            'inbox_message'=>Array(),
            'user_metadata'=>Array(),
            //'user'=>Array(),
        ),
    );

    public static function get() {
        return self::$config;
    }
}

?>
