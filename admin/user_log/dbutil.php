<?php
if (isset($mysql_ip))  define("DB_HOST",$mysql_ip);
else define("DB_HOST",'127.0.0.1');
if (isset($mysql_id))  define("DB_USER",$mysql_id);
else  define("DB_USER",'isatRoot');
if (isset($mysql_pwd))  define("DB_PASSWORD",$mysql_pwd);
else  define("DB_PASSWORD",'isatPassword');
define("DB_NAME",'isat');

function lic_getLink(){
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