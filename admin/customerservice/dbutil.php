<?php
define("DB_NAME",'customerservice');
if (file_exists("/var/www/qlync_admin/doc/config.php")){ 
  require_once ("/var/www/qlync_admin/doc/config.php");
  define("DB_HOST",$mysql_ip);
  define("DB_USER",$mysql_id);
  define("DB_PASSWORD",$mysql_pwd);
}else{ //for other project
  define("DB_HOST",'127.0.0.1');
  define("DB_USER",'isatRoot');
  define("DB_PASSWORD",'isatPassword');
}
function getPDO($dbname=DB_NAME,$host=DB_HOST,$user=DB_USER,$password=DB_PASSWORD){
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  //if ($ref=="")//pre v3.2.1 vesion
  //  $pdo = new PDO('mysql:host='.$host.';dbname='.$dbname.'', $user, $password);
  //else//correct utf8 
	$pdo = new PDO('mysql:host='.$host.';dbname='.$dbname.'', $user, $password,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

	$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
	return $pdo;
}

function getLink(){
        $host = DB_HOST;
        $user = DB_USER;
        $password = DB_PASSWORD;
        $dbname = DB_NAME;
	$link=mysql_connect($host,$user,$password);
	if(!$link)
	{
		die("Can't connect to db ".$host);
	}

	$db_selected=mysql_select_db($dbname,$link);
	if(!$db_selected)
	{
		die("Can't open database: <br>".mysql_error($link));
	}
	mysql_query("SET CAMNAME 'utf8'");
	return $link;
}

function close_db($result,$link){
	// Release memory
	mysql_free_result($result);

	// Close database
	mysql_close($link);
}

function GetSQLValue($value, $type){
	switch ($type)
	{
		case "text":
			$value = ($value != "") ? "'" . htmlspecialchars($value, ENT_QUOTES) . "'" : "NULL";
			break;
		case "int":
			$value = ($value != "") ? intval($value) : "NULL";
			break;
		case "double":
			$value = ($value != "") ? "'" . doubleval($value) . "'" : "NULL";
			break;
		case "date":
			$value = ($value != "") ? "'" . htmlspecialchars($value, ENT_QUOTES) . "'" : "NULL";
			break;
	}
	return $value;
}

function dump($object, $verbose = false){
	echo "\n<pre>\n";
	echo ($verbose) ? var_dump($object) : print_r($object);
	echo "\n</pre>\n";
}

function myfetch(&$db, &$result,$i)
{
		mysql_data_seek($result,$i);
		$db=mysql_fetch_array($result,MYSQL_BOTH);
}
?>