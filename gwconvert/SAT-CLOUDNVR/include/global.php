<?php
// start the session usage, mainly used for id/pw check & login

if (isset($_PASS_SESSION) && $_PASS_SESSION === true) $_SESSION = array(); else session_start();

define("SVW_VERSION", "1.1.8");
define("SVN_REVISION", ".3754");

// database path
define('LICENSE_DB_TYPE', 'mysql');
define('LICENSE_DB_HOST', '192.168.1.140');
define('LICENSE_DB_NAME', 'isat');
define('LICENSE_DB_USERNAME', 'isatRoot');
define('LICENSE_DB_PASSWORD', 'isatPassword');

define('SIGNAL_DB_TYPE', 'mysql');
define('SIGNAL_DB_HOST', '192.168.1.140');
define('SIGNAL_DB_NAME', 'isat');
define('SIGNAL_DB_USERNAME', 'isatRoot');
define('SIGNAL_DB_PASSWORD', 'isatPassword');

// web site screenshot path
define("WEB_SITE_SCREENSHOT_PATH", "./web_site_screenshot/");

// administrator group id
define("ADMIN_GROUP_ID", 0);

// upload languate storage
define("LANGUAGE_UPLOAD_PATH", "/locale/upload/");
define("LANGUAGE_TABLE_NAME", "language_table.xls");

// HTTPS
define('FORCE_HTTPS', true);

// timezone
define('DEFAULT_TIMEZONE', 'Asia/Taipei');

#define('RECORDING_STORAGE', '/media/videos');
define('RECORDING_STORAGE', '/var/evostreamms/media');
define('PUBLIC_STREAM_AUTH', true);
define('PUSH_NOTIFICATION_MIN_INTERVAL', 15);

/**
 * File path to app root.
 * @const
 */
define('ROOT_PATH', dirname(dirname(__FILE__)));

/**
 * Url path to app root.
 * @const
 */
if (!defined('ROOT_URL')) {
    define('ROOT_URL', '/');
}

// not set, use the browser preference
if( !isset($_SESSION["user_language"]) || $_SESSION["user_language"] == "" )
{
	// do language parse
	$lang_sep = " ,;";
	$lang_tok = (array_key_exists("HTTP_ACCEPT_LANGUAGE",$_SERVER)) ? strtok( $_SERVER["HTTP_ACCEPT_LANGUAGE"], $lang_sep ) : false;
	while( $lang_tok !== false )
	{
		$pos = strpos($lang_tok, "-");
		if( $pos !== FALSE )
		{
			$lang_tok = strtolower(substr($lang_tok,0, $pos))."_".strtoupper(substr($lang_tok,$pos+1));
			$_SESSION["user_language"]  = $lang_tok;
			break;
		}
		$lang_tok = strtok( $lang_tok );
	}

	// not get any, use en_US as default
	if( $lang_tok === false ) $_SESSION["user_language"] = "en_US";

// don't know why, only get zh without _TW
//	$_SESSION["user_language"] = Locale::acceptFromHttp( $_SERVER["HTTP_ACCEPT_LANGUAGE"] );

}

if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
}

// start multi-lang handling
$lang = "";
if (isset($_SESSION["user_language"])) $lang = $_SESSION["user_language"];
setlocale(LC_MESSAGES,  $lang . ".UTF8");
//setlocale(LC_CTYPE, $lang . ".UTF8"); // prevent turkish locale problem!
setlocale(LC_NUMERIC, $lang . ".UTF8");
setlocale(LC_TIME, $lang . ".UTF8");
setlocale(LC_COLLATE, $lang . ".UTF8");
setlocale(LC_MONETARY, $lang . ".UTF8");
setlocale(LC_MESSAGES, $lang . ".UTF8");

bindtextdomain("messages", "locale");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");
