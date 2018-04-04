<?php
/****************
 *Validated on Nov-27,2014,  
 * Revised from Taro's plugin
 * this file is not necessary if sql string use <db>.<table> 
 *Writer: JinHo, Chang   
*****************/
if (isset($mysql_ip))  define("DB_HOST",$mysql_ip);
else define("DB_HOST","127.0.0.1");
if (isset($mysql_id))  define("DB_USER",$mysql_id);
else  define("DB_USER","isatRoot");
if (isset($mysql_pwd))  define("DB_PASSWORD",$mysql_pwd);
else  define("DB_PASSWORD","isatPassword");
define("DB_NAME","licservice");
define("PAGE_LIMIT",50);
define("HOME_PATH","/var/www/qlync_admin/plugin/licservice");
define("DL_PATH","/log/");
//default admin account, admin@localhost.com / 1qaz2wsx
//replace below by include header.php, add db licservice. to sql,  sql($sql,$result,$result_num,0); fetch($arr,$result,$i,0);
function lic_getLink(){
        $host = DB_HOST;
        $user = DB_USER;
        $password = DB_PASSWORD;
        $dbname = DB_NAME;

	$link=mysql_connect($host,$user,$password);
	if(!$link)
	{
		die("Can't connect to db {$host}: {$user}/{$password}");
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


function hash4($cid,$pid,$mac,$ac) {
		$key1 = 'love';
		$key2 = 'qlync';
		$text = "{$cid}{$pid}{$mac}{$ac}";
		$hash = md5($key1 . $text . $key2);
		return $hash;
}

function myfetch(&$db, &$result,$i)
{
		mysql_data_seek($result,$i);
		$db=mysql_fetch_array($result,MYSQL_BOTH);
}

function chkSourceFromMegasys(&$cip)//true / false
{
  $MegasysIP = [
  "118.163.90.31",
  "59.124.70.86",
  "59.124.70.90",
  "125.227.139.173",
  "125.227.139.174",
  "123.193.125.132",
  "61.216.61.162"
  ];
    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" ) 
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
    for ($i=0;$i<sizeof($MegasysIP);$i++){
      if (strpos($client_ip, $MegasysIP[$i]) !== FALSE){
        $cip=$MegasysIP[$i];
        return true;
      }
    }
    return false;
}
?>