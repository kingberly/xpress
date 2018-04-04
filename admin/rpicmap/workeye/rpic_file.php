<?php
/****************
 * Validated on May-3,2017,
 * Write GIS data to file: php /var/www/qlync_admin/plugin/rpic/rpic_file.php <OEM ID>
 *  $argv[0] is script name
 *  $argv[1] OEM_ID, N99 means run all
 *  include in /etc/crontab
 ** *5 * * * * root  /usr/bin/php5  "/var/www/SAT-CLOUDNVR/map/rpic_file.php" N99       
 *Writer: JinHo, Chang
*****************/
header("Content-Type:text/html; charset=utf-8");
if (isset($_REQUEST['oemid']))
	define ("OEM_ID", strtoupper($_REQUEST['oemid']));
else if (isset($argv[1]))
	define ("OEM_ID", strtoupper($argv[1]));
else die("missing parameter");
require_once("rpic.inc"); //OEM_ID before include
if (!isMapAvail(OEM_ID)){
	die("No Such GIS MAP!!!");
}

$FILE="";
if (OEM_ID== "N99"){
	writeAllMapFile();
	foreach ($GIS_FILE as $key=>$path) //get new map
	{
		$FILE.= "<br>\n{$path} ".filesize($path)." ".date("Y-m-d H:i:s",filemtime($path));
	}
}else{
	$result=array();
	getMap(OEM_ID,$result);
	writeMapFile($GIS_FILE[OEM_ID],$result); //write source to file
	$FILE = $GIS_FILE[OEM_ID];
}
 
?>
<html>
<head>
</head>
<body>
<?php
	echo "Write to file\n".$FILE."\nSUCCESS!!";
?>
</body>
</html>