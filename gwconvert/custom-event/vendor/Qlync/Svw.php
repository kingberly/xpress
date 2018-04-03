<?php
namespace Qlync;

class Svw {
    protected static $params = null;

    public function __construct() {
        if (!self::$params) {
            $this->get_params();
        }
    }

    public function get_params() {
        if (self::$params) {
            return self::$params;
        }

        $options = Common::get_options();
        $document_root = $options['document-root'];
        if (!file_exists($document_root . '/include/global.php')) {
            throw new FileNotFoundException('SVW files not found.');
        }

        $_PASS_SESSION = true;
        require_once($document_root . '/include/global.php');

        $params = array();

        $params['db_host'] = SIGNAL_DB_HOST;
        $params['db_name'] = SIGNAL_DB_NAME;
        $params['db_username'] = SIGNAL_DB_USERNAME;
        $params['db_password'] = SIGNAL_DB_PASSWORD;
        $params['recording_storage'] = RECORDING_STORAGE;

        self::$params = $params;
    }
}

?>
